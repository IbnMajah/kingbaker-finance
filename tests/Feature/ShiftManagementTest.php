<?php

namespace Tests\Feature;

use App\Models\HrCashier;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_update_and_soft_delete_unreferenced_shift(): void
    {
        $user = User::factory()->create(['owner' => true]);

        $this->actingAs($user)->withSession(['two_factor_authenticated' => true])->post('/shifts', [
            'name' => 'Morning',
            'start_time' => '06:00',
            'end_time' => '14:00',
            'description' => 'Opening shift',
            'active' => true,
        ])->assertRedirect('/shifts');

        $shift = Shift::firstOrFail();
        $this->actingAs($user)->withSession(['two_factor_authenticated' => true])->put("/shifts/{$shift->id}", [
            'name' => 'Early Morning',
            'start_time' => '05:00',
            'end_time' => '13:00',
            'description' => null,
            'active' => false,
        ])->assertRedirect('/shifts');

        $this->assertDatabaseHas('shifts', ['id' => $shift->id, 'name' => 'Early Morning', 'active' => false]);

        $this->actingAs($user)->withSession(['two_factor_authenticated' => true])
            ->delete("/shifts/{$shift->id}")->assertRedirect();
        $this->assertSoftDeleted('shifts', ['id' => $shift->id]);
    }

    public function test_overnight_overlap_is_rejected_but_touching_boundary_is_allowed(): void
    {
        $user = User::factory()->create(['owner' => true]);
        Shift::create(['name' => 'Night', 'start_time' => '22:00', 'end_time' => '06:00', 'active' => true]);

        $this->actingAs($user)->withSession(['two_factor_authenticated' => true])->post('/shifts', [
            'name' => 'Overlap',
            'start_time' => '05:00',
            'end_time' => '07:00',
            'active' => true,
        ])->assertSessionHasErrors('start_time');

        $this->actingAs($user)->withSession(['two_factor_authenticated' => true])->post('/shifts', [
            'name' => 'Morning',
            'start_time' => '06:00',
            'end_time' => '14:00',
            'active' => true,
        ])->assertSessionHasNoErrors()->assertRedirect('/shifts');
    }

    public function test_referenced_shift_is_deactivated_instead_of_deleted(): void
    {
        $user = User::factory()->create(['owner' => true]);
        $shift = Shift::create(['name' => 'Morning', 'start_time' => '06:00', 'end_time' => '14:00', 'active' => true]);
        HrCashier::create([
            'employee_id' => 'EMP-1',
            'name' => 'Ama Mensah',
            'status' => 'active',
            'default_shift_id' => $shift->id,
        ]);

        $this->actingAs($user)->withSession(['two_factor_authenticated' => true])
            ->delete("/shifts/{$shift->id}")->assertRedirect();

        $this->assertDatabaseHas('shifts', ['id' => $shift->id, 'active' => false, 'deleted_at' => null]);
    }

    public function test_shift_routes_require_manage_shifts_permission(): void
    {
        $user = User::factory()->create(['owner' => false, 'role' => 'staff', 'permissions' => []]);

        $this->actingAs($user)->withSession(['two_factor_authenticated' => true])
            ->get('/shifts')->assertForbidden();
    }
}
