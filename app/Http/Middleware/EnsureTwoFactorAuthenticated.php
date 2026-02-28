<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthenticated
{
    protected array $excludedRoutes = [
        'two-factor.*',
        'logout',
        'login',
        'login.store',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($this->isExcludedRoute($request)) {
            return $next($request);
        }

        if ($request->session()->get('two_factor_authenticated')) {
            return $next($request);
        }

        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        return redirect()->route('two-factor.challenge');
    }

    protected function isExcludedRoute(Request $request): bool
    {
        foreach ($this->excludedRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
