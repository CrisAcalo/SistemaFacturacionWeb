<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActiveApi
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user) {
            // Inactivo
            if (isset($user->status) && $user->status !== 'active') {
                if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Tu cuenta está inactiva. Contacta al administrador.'
                ], 403);
            }
            // Eliminado (soft delete)
            if (method_exists($user, 'trashed') && $user->trashed()) {
                if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Tu cuenta ha sido desactivada. Contacta al administrador.'
                ], 403);
            }
        }
        return $next($request);
    }
}
