<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario no está autenticado, continuar (otros middleware se encargarán)
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Verificar si el usuario está inactivo
        if (isset($user->status) && $user->status === 'inactive') {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tu cuenta está inactiva. Por favor, contacta al administrador.',
                    'error' => 'account_inactive'
                ], 403);
            }

            return redirect()->route('login')->with('error', 'Tu cuenta está inactiva. Por favor, contacta al administrador.');
        }

        // Verificar si el usuario fue eliminado (soft delete)
        // Consultar directamente la base de datos incluyendo registros eliminados
        $userWithTrashed = \App\Models\User::withTrashed()->find($user->id);
        if ($userWithTrashed && $userWithTrashed->deleted_at !== null) {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tu cuenta ha sido desactivada. Por favor, contacta al administrador.',
                    'error' => 'account_deleted'
                ], 403);
            }

            return redirect()->route('login')->with('error', 'Tu cuenta ha sido desactivada. Por favor, contacta al administrador.');
        }

        return $next($request);
    }
}
