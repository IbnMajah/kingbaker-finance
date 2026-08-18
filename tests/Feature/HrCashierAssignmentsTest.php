<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\HrCashier;
use App\Models\Shift;
use App\Models\User;
use App\Services\HrCashierSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class HrCashierAssignmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_sync_updates_identity_and_preserves_local_assignments(): void
    {
        config(['services.hr.base_url' => 'https://hr.test', 'services.hr.token' => 'secret']);
        $branch = Branch::create(['name' => 'Finance Branch', 'location' => 'Accra']);
        $shift = Shift::create(['name' => 'Morning', 'start_time' => '06:00', 'end_time' => '14:00', 'active' => true]);
        $cashier = HrCashier::create([
            'employee_id' => 'EMP-1',
            'name' => 'Old Name',
            'status' => 'active',
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
        ]);

        Http::fake(['hr.test/*' => Http::response(['data' => [[
            'employee_id' => 'EMP-1',
            'first_name' => 'Ama',
            'last_name' => 'Mensah',
            'name' => 'Ama Mensah',
            'email' => 'ama@example.com',
            'status' => 'active',
            'role' => 'Cashier',
            'branch' => ['code' => 'HR-01', 'name' => 'Central'],
        ]]], 200)]);

        $this->app->make(HrCashierSyncService::class)->sync();

        $cashier->refresh();
        $this->assertSame('Ama Mensah', $cashier->name);
        $this->assertSame('HR-01', $cashier->hr_branch_code);
        $this->assertSame($branch->id, $cashier->branch_id);
        $this->assertSame($shift->id, $cashier->default_shift_id);
        $this->assertNotNull($cashier->last_synced_at);
    }

    public function test_failed_sync_retains_cached_rows(): void
    {
        config(['services.hr.base_url' => 'https://hr.test', 'services.hr.token' => 'secret']);
        HrCashier::create(['employee_id' => 'EMP-1', 'name' => 'Cached Cashier', 'status' => 'active']);
        Http::fake(['hr.test/*' => Http::response(['message' => 'Unavailable'], 503)]);

        try {
            $this->app->make(HrCashierSyncService::class)->sync();
            $this->fail('Expected sync failure.');
        } catch (RuntimeException) {
            $this->assertDatabaseHas('hr_cashiers', [
                'employee_id' => 'EMP-1',
                'name' => 'Cached Cashier',
                'status' => 'active',
            ]);
        }
    }

    public function test_complete_sync_marks_missing_cashiers_inactive(): void
    {
        config(['services.hr.base_url' => 'https://hr.test', 'services.hr.token' => 'secret']);
        HrCashier::create(['employee_id' => 'OLD-1', 'name' => 'Historical Cashier', 'status' => 'active']);
        Http::fake(['hr.test/*' => Http::response(['data' => []], 200)]);

        $this->app->make(HrCashierSyncService::class)->sync();

        $this->assertDatabaseHas('hr_cashiers', ['employee_id' => 'OLD-1', 'status' => 'inactive']);
    }

    public function test_authorized_user_can_assign_cashier_and_view_stale_cache(): void
    {
        $user = User::factory()->create(['owner' => true]);
        $branch = Branch::create(['name' => 'Finance Branch', 'location' => 'Accra']);
        $shift = Shift::create(['name' => 'Morning', 'start_time' => '06:00', 'end_time' => '14:00', 'active' => true]);
        $cashier = HrCashier::create([
            'employee_id' => 'EMP-1',
            'name' => 'Ama Mensah',
            'status' => 'active',
            'last_synced_at' => now()->subHours(2),
        ]);
        config(['services.hr.cache_stale_after' => 3600]);

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->get('/cashier-assignments')
            ->assertInertia(fn (Assert $page) => $page
                ->component('CashierAssignments/Index')
                ->where('cache.stale', true)
                ->has('cashiers.data', 1));

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->put("/cashier-assignments/{$cashier->id}", [
                'branch_id' => $branch->id,
                'default_shift_id' => $shift->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_cashiers', [
            'id' => $cashier->id,
            'branch_id' => $branch->id,
            'default_shift_id' => $shift->id,
        ]);
    }

    public function test_cashier_assignment_routes_require_permission(): void
    {
        $user = User::factory()->create(['owner' => false, 'role' => 'staff', 'permissions' => []]);

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->get('/cashier-assignments')
            ->assertForbidden();
    }
}
