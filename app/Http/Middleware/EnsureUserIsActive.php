<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->status !== 'active') {
            // Revocar el token actual si existe
            if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta está inactiva. Contacta al administrador.'
            ], 403);
        }
        return $next($request);
    }
}
