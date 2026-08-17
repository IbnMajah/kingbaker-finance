<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserTwoFactorDevice;
use App\Providers\AppServiceProvider;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function setup(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.challenge');
        }

        $secret = $request->session()->get('two_factor_setup_secret')
            ?? $this->startSetup($request);

        return Inertia::render('Auth/TwoFactorSetup', [
            'qrCode' => $this->qrCode($user->email, $secret),
            'secret' => $secret,
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        if ($request->user()->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.challenge');
        }

        $request->merge(['name' => trim((string) $request->input('name'))]);

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('user_two_factor_devices')->where('user_id', $request->user()->id),
            ],
        ]);

        $secret = $request->session()->get('two_factor_setup_secret');

        if (!$secret) {
            return back()->withErrors(['code' => 'Session expired. Please refresh and try again.']);
        }

        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'The provided code is invalid. Please try again.']);
        }

        $request->user()->twoFactorDevices()->create([
            'name' => $request->string('name')->trim()->value(),
            'secret' => $secret,
            'confirmed_at' => now(),
        ]);

        $request->session()->forget('two_factor_setup_secret');
        $request->session()->put('two_factor_authenticated', true);

        return redirect()->intended(AppServiceProvider::HOME);
    }

    public function challenge(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        if ($request->session()->get('two_factor_authenticated')) {
            return redirect()->intended(AppServiceProvider::HOME);
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $devices = $request->user()->twoFactorDevices()
            ->whereNotNull('confirmed_at')
            ->get();

        if ($devices->isEmpty()) {
            return back()->withErrors(['code' => 'Two-factor authentication is not enabled. Please contact the administrator.']);
        }

        $valid = $devices->contains(
            fn (UserTwoFactorDevice $device) => $this->google2fa->verifyKey($device->secret, $request->code)
        );

        if (!$valid) {
            return back()->withErrors(['code' => 'The provided code is invalid. Please try again.']);
        }

        $request->session()->put('two_factor_authenticated', true);

        return redirect()->intended(AppServiceProvider::HOME);
    }

    public function devices(Request $request): Response
    {
        $pending = $request->session()->get('two_factor_pending_device');

        return Inertia::render('Auth/TwoFactorDevices', [
            'devices' => $request->user()->twoFactorDevices()
                ->whereNotNull('confirmed_at')
                ->orderBy('created_at')
                ->get(['id', 'name', 'confirmed_at', 'created_at']),
            'pendingDevice' => $pending ? [
                'name' => $pending['name'],
                'secret' => $pending['secret'],
                'qrCode' => $this->qrCode($request->user()->email, $pending['secret']),
            ] : null,
        ]);
    }

    public function storeDevice(Request $request): RedirectResponse
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('user_two_factor_devices')->where('user_id', $request->user()->id),
            ],
        ]);

        $request->session()->put('two_factor_pending_device', [
            'name' => trim($validated['name']),
            'secret' => $this->google2fa->generateSecretKey(),
        ]);

        return redirect()->route('two-factor.devices.index');
    }

    public function confirmDevice(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $pending = $request->session()->get('two_factor_pending_device');

        if (!$pending) {
            return back()->withErrors(['code' => 'Device setup expired. Please start again.']);
        }

        if (!$this->google2fa->verifyKey($pending['secret'], $request->code)) {
            return back()->withErrors(['code' => 'The provided code is invalid. Please try again.']);
        }

        $nameIsTaken = $request->user()->twoFactorDevices()
            ->where('name', $pending['name'])
            ->exists();

        if ($nameIsTaken) {
            return back()->withErrors(['code' => 'A device with this name already exists.']);
        }

        $request->user()->twoFactorDevices()->create([
            'name' => $pending['name'],
            'secret' => $pending['secret'],
            'confirmed_at' => now(),
        ]);

        $request->session()->forget('two_factor_pending_device');

        return redirect()->route('two-factor.devices.index')
            ->with('success', 'Authenticator device added.');
    }

    public function cancelDevice(Request $request): RedirectResponse
    {
        $request->session()->forget('two_factor_pending_device');

        return redirect()->route('two-factor.devices.index');
    }

    public function destroyDevice(Request $request, UserTwoFactorDevice $device): RedirectResponse
    {
        abort_unless($device->user_id === $request->user()->id, 404);

        $request->validate([
            'password' => ['required', 'current_password:web'],
        ]);

        $device->delete();

        if (!$request->user()->hasTwoFactorEnabled()) {
            $request->session()->forget('two_factor_authenticated');

            return redirect()->route('two-factor.setup')
                ->with('success', 'Your last authenticator was removed. Set up a new device to continue.');
        }

        return redirect()->route('two-factor.devices.index')
            ->with('success', 'Authenticator device removed.');
    }

    private function startSetup(Request $request): string
    {
        $secret = $this->google2fa->generateSecretKey();
        $request->session()->put('two_factor_setup_secret', $secret);

        return $secret;
    }

    private function qrCode(string $email, string $secret): string
    {
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'KingBakers Finance'),
            $email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $svg = (new Writer($renderer))->writeString($qrCodeUrl);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
