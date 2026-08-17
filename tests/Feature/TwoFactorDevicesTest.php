<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserTwoFactorDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorDevicesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Google2FA $google2fa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $this->google2fa = new Google2FA();
    }

    public function test_initial_setup_creates_a_named_device(): void
    {
        $secret = $this->google2fa->generateSecretKey();

        $this->actingAs($this->user)
            ->withSession(['two_factor_setup_secret' => $secret])
            ->post('/two-factor/confirm', [
                'name' => 'Personal phone',
                'code' => $this->google2fa->getCurrentOtp($secret),
            ])
            ->assertRedirect('/');

        $device = $this->user->twoFactorDevices()->sole();

        $this->assertSame('Personal phone', $device->name);
        $this->assertSame($secret, $device->secret);
        $this->assertTrue($this->user->fresh()->hasTwoFactorEnabled());
    }

    public function test_a_code_from_any_registered_device_is_accepted(): void
    {
        $firstSecret = $this->google2fa->generateSecretKey();
        $secondSecret = $this->google2fa->generateSecretKey();
        $this->createDevice('Personal phone', $firstSecret);
        $this->createDevice('Tablet', $secondSecret);

        $this->actingAs($this->user)
            ->post('/two-factor/verify', [
                'code' => $this->google2fa->getCurrentOtp($secondSecret),
            ])
            ->assertRedirect('/');

        $this->assertTrue(session('two_factor_authenticated'));
    }

    public function test_device_management_requires_a_completed_two_factor_challenge(): void
    {
        $this->createDevice('Personal phone');

        $this->actingAs($this->user)
            ->get('/two-factor/devices')
            ->assertRedirect('/two-factor/challenge');
    }

    public function test_user_can_view_and_add_an_authenticator_device(): void
    {
        $this->createDevice('Personal phone');

        $this->actingAs($this->user)
            ->withSession(['two_factor_authenticated' => true])
            ->get('/two-factor/devices')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/TwoFactorDevices')
                ->has('devices', 1)
                ->where('devices.0.name', 'Personal phone')
                ->where('pendingDevice', null)
            );

        $response = $this->actingAs($this->user)
            ->withSession(['two_factor_authenticated' => true])
            ->post('/two-factor/devices', ['name' => 'Work phone']);

        $response->assertRedirect('/two-factor/devices')
            ->assertSessionHas('two_factor_pending_device');

        $pending = session('two_factor_pending_device');

        $this->actingAs($this->user)
            ->withSession([
                'two_factor_authenticated' => true,
                'two_factor_pending_device' => $pending,
            ])
            ->post('/two-factor/devices/confirm', [
                'code' => $this->google2fa->getCurrentOtp($pending['secret']),
            ])
            ->assertRedirect('/two-factor/devices');

        $this->assertDatabaseHas('user_two_factor_devices', [
            'user_id' => $this->user->id,
            'name' => 'Work phone',
        ]);
    }

    public function test_user_can_remove_their_device_with_their_password(): void
    {
        $device = $this->createDevice('Personal phone');

        $this->actingAs($this->user)
            ->withSession(['two_factor_authenticated' => true])
            ->delete("/two-factor/devices/{$device->id}", [
                'password' => 'password',
            ])
            ->assertRedirect('/two-factor/setup');

        $this->assertDatabaseMissing('user_two_factor_devices', ['id' => $device->id]);
        $this->assertFalse($this->user->fresh()->hasTwoFactorEnabled());
        $this->assertFalse(session()->has('two_factor_authenticated'));
    }

    public function test_device_removal_password_attempts_are_rate_limited(): void
    {
        $device = $this->createDevice('Personal phone');

        $this->actingAs($this->user)
            ->withSession(['two_factor_authenticated' => true]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->delete("/two-factor/devices/{$device->id}", [
                'password' => 'incorrect-password',
            ])->assertSessionHasErrors('password');
        }

        $this->delete("/two-factor/devices/{$device->id}", [
            'password' => 'incorrect-password',
        ])->assertStatus(429);

        $this->assertDatabaseHas('user_two_factor_devices', ['id' => $device->id]);
    }

    public function test_user_cannot_remove_another_users_device(): void
    {
        $otherUser = User::factory()->create();
        $device = $otherUser->twoFactorDevices()->create([
            'name' => 'Other phone',
            'secret' => $this->google2fa->generateSecretKey(),
            'confirmed_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->withSession(['two_factor_authenticated' => true])
            ->delete("/two-factor/devices/{$device->id}", [
                'password' => 'password',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('user_two_factor_devices', ['id' => $device->id]);
    }

    private function createDevice(
        string $name,
        ?string $secret = null
    ): UserTwoFactorDevice {
        return $this->user->twoFactorDevices()->create([
            'name' => $name,
            'secret' => $secret ?? $this->google2fa->generateSecretKey(),
            'confirmed_at' => now(),
        ]);
    }
}
