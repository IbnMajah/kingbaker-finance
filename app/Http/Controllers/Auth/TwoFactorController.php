<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\AppServiceProvider;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $secret = $request->session()->get('two_factor_setup_secret');
        if (!$secret) {
            $secret = $this->google2fa->generateSecretKey();
            $request->session()->put('two_factor_setup_secret', $secret);
        }

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'KingBakers Finance'),
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($qrCodeUrl);
        $qrCodeSvg = 'data:image/svg+xml;base64,' . base64_encode($svg);

        return Inertia::render('Auth/TwoFactorSetup', [
            'qrCode' => $qrCodeSvg,
            'secret' => $secret,
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = $request->session()->get('two_factor_setup_secret');

        if (!$secret) {
            return back()->withErrors(['code' => 'Session expired. Please refresh and try again.']);
        }

        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'The provided code is invalid. Please try again.']);
        }

        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

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

        $user = $request->user();
        $secret = $user->two_factor_secret;

        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'The provided code is invalid. Please try again.']);
        }

        $request->session()->put('two_factor_authenticated', true);

        return redirect()->intended(AppServiceProvider::HOME);
    }
}
