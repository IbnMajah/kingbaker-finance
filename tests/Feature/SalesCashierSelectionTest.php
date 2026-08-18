<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\HrCashier;
use App\Models\Sale;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SalesCashierSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_forms_offer_active_hr_cashiers(): void
    {
        $user = User::factory()->create(['owner' => true]);
        $branch = Branch::create(['name' => 'Central', 'location' => 'Accra']);
        $shift = Shift::create(['name' => 'Morning', 'start_time' => '06:00', 'end_time' => '14:00', 'active' => true]);

        $active = HrCashier::create([
            'employee_id' => 'EMP-1',
            'name' => 'Ama Mensah',
            'status' => 'active',
            'branch_id' => $branch->id,
        ]);
        HrCashier::create([
            'employee_id' => 'EMP-2',
            'name' => 'Inactive Cashier',
            'status' => 'inactive',
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->get('/sales/create')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Sales/Create')
                ->has('cashiers', 1)
                ->where('cashiers.0.name', 'Ama Mensah')
                ->where('cashiers.0.employee_id', 'EMP-1'));

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'shift_id' => $shift->id,
            'sales_date' => now()->toDateString(),
            'amount' => 100,
            'cashier' => 'Legacy Free Text',
        ]);

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->get("/sales/{$sale->id}/edit")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Sales/Edit')
                ->has('cashiers', 1)
                ->where('sale.cashier', 'Legacy Free Text')
                ->where('cashiers.0.id', $active->id));
    }

    public function test_sale_cashier_must_be_a_synced_hr_cashier(): void
    {
        $user = User::factory()->create(['owner' => true]);
        $branch = Branch::create(['name' => 'Central', 'location' => 'Accra']);
        $shift = Shift::create(['name' => 'Morning', 'start_time' => '06:00', 'end_time' => '14:00', 'active' => true]);
        $cashier = HrCashier::create([
            'employee_id' => 'EMP-1',
            'name' => 'Ama Mensah',
            'status' => 'active',
            'branch_id' => $branch->id,
        ]);

        $payload = [
            'branch_id' => $branch->id,
            'shift_id' => $shift->id,
            'sales_date' => now()->toDateString(),
            'amount' => 50,
            'cash_amount' => 50,
        ];

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->from('/sales/create')
            ->post('/sales', array_merge($payload, ['cashier' => 'Typed By Hand']))
            ->assertRedirect('/sales/create')
            ->assertSessionHasErrors('cashier');

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->post('/sales', array_merge($payload, ['hr_cashier_id' => $cashier->id]))
            ->assertRedirect('/sales');

        $this->assertDatabaseHas('sales', [
            'branch_id' => $branch->id,
            'hr_cashier_id' => $cashier->id,
            'cashier' => 'Ama Mensah',
        ]);
    }

    public function test_editing_a_sale_can_keep_a_historical_cashier_name(): void
    {
        $user = User::factory()->create(['owner' => true]);
        $branch = Branch::create(['name' => 'Central', 'location' => 'Accra']);
        $shift = Shift::create(['name' => 'Morning', 'start_time' => '06:00', 'end_time' => '14:00', 'active' => true]);
        $sale = Sale::create([
            'branch_id' => $branch->id,
            'shift_id' => $shift->id,
            'sales_date' => now()->toDateString(),
            'amount' => 75,
            'cashier' => 'Old Paper Name',
        ]);

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->put("/sales/{$sale->id}", [
                'branch_id' => $branch->id,
                'shift_id' => $shift->id,
                'sales_date' => now()->toDateString(),
                'amount' => 75,
                'cashier' => 'Old Paper Name',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'cashier' => 'Old Paper Name',
        ]);
    }
}
