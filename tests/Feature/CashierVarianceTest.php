<?php

namespace Tests\Feature;

use App\Jobs\ProcessCashierVarianceOutbox;
use App\Models\Branch;
use App\Models\CashierReconciliation;
use App\Models\CashierVarianceLedgerEntry;
use App\Models\HrCashier;
use App\Models\Sale;
use App\Models\Shift;
use App\Models\User;
use App\Services\CashierVarianceCalculator;
use App\Services\CashierVarianceWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use LogicException;
use RuntimeException;
use Tests\TestCase;

class CashierVarianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_expected_minus_actual_matches_the_pos_surplus(): void
    {
        $result = app(CashierVarianceCalculator::class)->calculate('46250.00', '46310.00');

        $this->assertSame('-60.00', $result['amount']);
        $this->assertSame('surplus', $result['classification']);
    }

    public function test_positive_variance_is_a_shortage(): void
    {
        $result = app(CashierVarianceCalculator::class)->calculate('100.00', '80.00');

        $this->assertSame('20.00', $result['amount']);
        $this->assertSame('shortage', $result['classification']);
    }

    public function test_recording_a_sale_creates_a_calculated_cashier_variance(): void
    {
        $user = User::factory()->create(['owner' => true]);
        [$branch, $shift, $cashier] = $this->makeCashierFixtures();

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->post('/sales', [
                'branch_id' => $branch->id,
                'shift_id' => $shift->id,
                'sales_date' => now()->toDateString(),
                'amount' => 80,
                'cash_amount' => 100,
                'hr_cashier_id' => $cashier->id,
                'expected_total' => 80,
            ])
            ->assertRedirect('/sales');

        $sale = Sale::firstOrFail();
        $this->assertSame('100.00', $sale->total_amount);
        $this->assertSame('80.00', $sale->expected_total);
        $this->assertDatabaseHas('cashier_reconciliations', [
            'sale_id' => $sale->id,
            'hr_cashier_id' => $cashier->id,
            'expected_total' => 80,
            'actual_total' => 100,
            'calculated_variance' => -20,
            'classification' => 'surplus',
            'status' => 'draft',
        ]);
    }

    public function test_approval_posts_an_immutable_ledger_entry_and_queues_shortage_for_hr(): void
    {
        Bus::fake();
        [$reconciliation, $creator, $approver] = $this->makeReconciliation('20.00', 'shortage');

        app(CashierVarianceWorkflow::class)->approve($reconciliation, $approver);

        $reconciliation->refresh();
        $this->assertSame('approved', $reconciliation->status);
        $this->assertDatabaseHas('cashier_variance_ledger_entries', [
            'cashier_reconciliation_id' => $reconciliation->id,
            'entry_type' => 'shortage',
            'variance_amount' => 20,
        ]);
        $this->assertDatabaseHas('cashier_variance_outbox', [
            'cashier_reconciliation_id' => $reconciliation->id,
            'event_type' => 'shortage.created',
            'status' => 'pending',
        ]);
        Bus::assertNothingDispatched();

        $entry = CashierVarianceLedgerEntry::firstOrFail();
        $this->expectException(LogicException::class);
        $entry->update(['reason' => 'Tampered']);
    }

    public function test_surplus_is_not_sent_to_hr(): void
    {
        Bus::fake();
        [$reconciliation, , $approver] = $this->makeReconciliation('-60.00', 'surplus');

        app(CashierVarianceWorkflow::class)->approve($reconciliation, $approver);

        $this->assertDatabaseHas('cashier_variance_ledger_entries', [
            'cashier_reconciliation_id' => $reconciliation->id,
            'entry_type' => 'surplus',
            'variance_amount' => -60,
        ]);
        $this->assertDatabaseCount('cashier_variance_outbox', 0);
        Bus::assertNothingDispatched();
    }

    public function test_approved_reconciliation_values_are_immutable(): void
    {
        Bus::fake();
        [$reconciliation, , $approver] = $this->makeReconciliation('-60.00', 'surplus');
        app(CashierVarianceWorkflow::class)->approve($reconciliation, $approver);

        $this->expectException(LogicException::class);
        $reconciliation->fresh()->update(['actual_total' => '1.00']);
    }

    public function test_outbox_event_can_retry_safely_after_hr_failure(): void
    {
        Bus::fake();
        config([
            'services.hr.base_url' => 'https://hr.test',
            'services.hr.token' => 'service-token',
        ]);
        [$reconciliation, , $approver] = $this->makeReconciliation('20.00', 'shortage');
        app(CashierVarianceWorkflow::class)->approve($reconciliation, $approver);
        $event = $reconciliation->outboxEvents()->firstOrFail();

        Http::fakeSequence()
            ->push(['message' => 'Unavailable'], 503)
            ->push(['data' => ['id' => 'HR-42']], 200);
        try {
            (new ProcessCashierVarianceOutbox($event->id))->handle();
            $this->fail('Expected the HR request to fail.');
        } catch (\RuntimeException) {
            $this->assertDatabaseHas('cashier_variance_outbox', [
                'id' => $event->id,
                'status' => 'pending',
                'attempts' => 1,
            ]);
        }

        (new ProcessCashierVarianceOutbox($event->id))->handle();

        $this->assertDatabaseHas('cashier_variance_outbox', [
            'id' => $event->id,
            'status' => 'processed',
            'attempts' => 2,
            'external_reference' => 'HR-42',
        ]);
    }

    public function test_reversal_creates_an_offsetting_entry_without_changing_the_original(): void
    {
        Bus::fake();
        [$reconciliation, , $approver] = $this->makeReconciliation('-60.00', 'surplus');
        $workflow = app(CashierVarianceWorkflow::class);
        $workflow->approve($reconciliation, $approver);
        $original = CashierVarianceLedgerEntry::firstOrFail();

        $workflow->reverse($reconciliation->fresh(), $approver, 'Receipt entered twice');

        $this->assertDatabaseHas('cashier_variance_ledger_entries', [
            'reverses_entry_id' => $original->id,
            'entry_type' => 'surplus_reversal',
            'variance_amount' => 60,
        ]);
        $this->assertSame('reversed', $reconciliation->fresh()->status);
        $this->assertSame('-60.00', $original->fresh()->variance_amount);
    }

    public function test_staff_cannot_self_approve(): void
    {
        [$reconciliation] = $this->makeReconciliation('20.00', 'shortage');
        $staff = User::factory()->create([
            'owner' => false,
            'role' => 'staff',
        ]);
        $reconciliation->update(['created_by' => $staff->id]);

        try {
            app(CashierVarianceWorkflow::class)->approve($reconciliation->fresh(), $staff);
            $this->fail('Expected staff self-approval to be rejected.');
        } catch (RuntimeException $exception) {
            $this->assertSame('You cannot approve a reconciliation you created.', $exception->getMessage());
        }

        $this->assertSame('submitted', $reconciliation->fresh()->status);
    }

    public function test_admin_can_approve_own_shortage(): void
    {
        Bus::fake();
        [$reconciliation, $admin] = $this->makeReconciliation('20.00', 'shortage');

        $this->actingAs($admin)
            ->withSession(['two_factor_authenticated' => true])
            ->put("/cashier-variances/{$reconciliation->id}/approve")
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame('approved', $reconciliation->fresh()->status);
        $this->assertSame($admin->id, $reconciliation->fresh()->approved_by);
    }

    public function test_manager_can_approve_own_surplus(): void
    {
        Bus::fake();
        [$reconciliation] = $this->makeReconciliation('-60.00', 'surplus');
        $manager = User::factory()->create([
            'owner' => false,
            'role' => 'manager',
        ]);
        $reconciliation->update(['created_by' => $manager->id]);

        app(CashierVarianceWorkflow::class)->approve($reconciliation->fresh(), $manager);

        $this->assertSame('approved', $reconciliation->fresh()->status);
        $this->assertSame($manager->id, $reconciliation->fresh()->approved_by);
        $this->assertDatabaseCount('cashier_variance_outbox', 0);
    }

    public function test_duplicate_sale_reconciliation_is_rejected(): void
    {
        [$existing, $creator] = $this->makeReconciliation('20.00', 'shortage');

        $this->actingAs($creator)
            ->withSession(['two_factor_authenticated' => true])
            ->from('/cashier-variances/create')
            ->post('/cashier-variances', [
                'sale_id' => $existing->sale_id,
                'expected_total' => '80.00',
                'submit' => true,
            ])
            ->assertRedirect('/cashier-variances/create')
            ->assertSessionHasErrors('sale_id');

        $this->assertDatabaseCount('cashier_reconciliations', 1);
    }

    public function test_cashier_show_page_includes_sales_and_ledger(): void
    {
        Bus::fake();
        [$reconciliation, , $approver] = $this->makeReconciliation('20.00', 'shortage');
        app(CashierVarianceWorkflow::class)->approve($reconciliation, $approver);

        $this->actingAs($approver)
            ->withSession(['two_factor_authenticated' => true])
            ->get("/cashiers/{$reconciliation->hr_cashier_id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cashiers/Show')
                ->where('cashier.employee_id', 'KB-0817-0042')
                ->has('sales.data', 1)
                ->has('ledger.data', 1)
                ->where('summary.sales_count', 1));
    }

    /**
     * @return array{CashierReconciliation, User, User}
     */
    private function makeReconciliation(string $variance, string $classification): array
    {
        [$branch, $shift, $cashier] = $this->makeCashierFixtures();
        $creator = User::factory()->create(['owner' => true]);
        $approver = User::factory()->create(['owner' => true]);
        $sale = Sale::create([
            'branch_id' => $branch->id,
            'shift_id' => $shift->id,
            'hr_cashier_id' => $cashier->id,
            'sales_date' => today(),
            'amount' => $classification === 'shortage' ? 80 : 46310,
            'total_amount' => $classification === 'shortage' ? 100 : 46250,
            'expected_total' => $classification === 'shortage' ? 80 : 46310,
            'cashier' => $cashier->name,
        ]);

        $reconciliation = CashierReconciliation::create([
            'uuid' => (string) Str::uuid(),
            'sale_id' => $sale->id,
            'hr_cashier_id' => $cashier->id,
            'branch_id' => $branch->id,
            'shift_id' => $shift->id,
            'cashier_employee_id' => $cashier->employee_id,
            'cashier_name' => $cashier->name,
            'branch_name' => $branch->name,
            'shift_name' => $shift->name,
            'till_number' => 'SALE-'.$sale->id,
            'pos_reference' => 'SALE-'.$sale->id,
            'business_date' => today(),
            'closed_at' => now(),
            'expected_total' => $classification === 'shortage' ? '100.00' : '46250.00',
            'actual_total' => $classification === 'shortage' ? '80.00' : '46310.00',
            'calculated_variance' => $variance,
            'classification' => $classification,
            'status' => 'submitted',
            'created_by' => $creator->id,
            'verified_by' => $creator->id,
            'submitted_at' => now(),
        ]);

        return [$reconciliation, $creator, $approver];
    }

    /**
     * @return array{Branch, Shift, HrCashier}
     */
    private function makeCashierFixtures(): array
    {
        $branch = Branch::create([
            'name' => 'Turntable',
            'location' => 'Turntable',
            'active' => true,
        ]);
        $shift = Shift::create([
            'name' => 'Afternoon',
            'start_time' => '14:00',
            'end_time' => '22:00',
            'active' => true,
        ]);
        $cashier = HrCashier::create([
            'employee_id' => 'KB-0817-0042',
            'name' => 'Test Cashier',
            'status' => 'active',
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
            'last_seen_at' => now(),
            'last_synced_at' => now(),
        ]);

        return [$branch, $shift, $cashier];
    }
}
