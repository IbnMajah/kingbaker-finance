<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{
    /**
     * Keep session alive
     */
    public function keepAlive(Request $request): JsonResponse
    {
        // Regenerate session ID for security
        $request->session()->migrate();

        return response()->json([
            'success' => true,
            'message' => 'Session kept alive',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Refresh session and CSRF token
     */
    public function refresh(Request $request): JsonResponse
    {
        // Regenerate session ID
        $request->session()->migrate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Session refreshed',
            'csrf_token' => $request->session()->token(),
            'timestamp' => now()->toISOString()
        ]);
    }
}
