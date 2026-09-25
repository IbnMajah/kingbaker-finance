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

class SalesBagDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_bag_discount_is_stored_and_included_in_the_sale_total(): void
    {
        [$user, $branch, $shift, $cashier] = $this->saleContext();

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->from('/sales/create')
            ->post('/sales', [
                'branch_id' => $branch->id,
                'shift_id' => $shift->id,
                'sales_date' => now()->toDateString(),
                'amount' => 50,
                'cash_amount' => 50,
                'bag_discount_amount' => '25.50',
                'hr_cashier_id' => $cashier->id,
                'cashier' => $cashier->name,
            ])
            ->assertRedirect('/sales/create')
            ->assertSessionHasErrors('bag_discount_amount');

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->post('/sales', [
                'branch_id' => $branch->id,
                'shift_id' => $shift->id,
                'sales_date' => now()->toDateString(),
                'amount' => 50,
                'cash_amount' => 50,
                'bag_discount_amount' => 25,
                'hr_cashier_id' => $cashier->id,
                'cashier' => $cashier->name,
            ])
            ->assertRedirect('/sales');

        $sale = Sale::query()->first();

        $this->assertSame('25.00', $sale->bag_discount_amount);
        $this->assertSame('75.00', $sale->total_amount);

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->get("/sales/{$sale->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Sales/Show')
                ->where('sale.bag_discount_amount', '25.00'));

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->get("/sales/{$sale->id}/edit")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Sales/Edit')
                ->where('sale.bag_discount_amount', '25.00'));

        $this->actingAs($user)
            ->withSession(['two_factor_authenticated' => true])
            ->put("/sales/{$sale->id}", [
                'branch_id' => $branch->id,
                'shift_id' => $shift->id,
                'sales_date' => now()->toDateString(),
                'amount' => 50,
                'cash_amount' => 50,
                'bag_discount_amount' => 40,
                'hr_cashier_id' => $cashier->id,
                'cashier' => $cashier->name,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'bag_discount_amount' => 40,
            'total_amount' => 90,
        ]);
    }

    /**
     * @return array{0: User, 1: Branch, 2: Shift, 3: HrCashier}
     */
    private function saleContext(): array
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

        return [$user, $branch, $shift, $cashier];
    }
}
