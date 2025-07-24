<?php

namespace App\Listeners;

use App\Events\UserStatusChanged;
use Illuminate\Support\Facades\DB;

class HandleUserStatusChanged
{
    /**
     * Handle the event.
     */
    public function handle(UserStatusChanged $event): void
    {
        // Si el usuario fue desactivado, invalidar todas sus sesiones activas
        if ($event->newStatus === 'inactive') {
            // Obtener todas las sesiones del usuario
            $sessions = DB::table('sessions')
                ->where('user_id', $event->user->id)
                ->get();

            // Eliminar todas las sesiones del usuario
            DB::table('sessions')
                ->where('user_id', $event->user->id)
                ->delete();

            // Log de la acción
            activity('session_invalidated')
                ->performedOn($event->user)
                ->withProperties([
                    'reason' => 'User status changed to inactive',
                    'sessions_invalidated' => $sessions->count()
                ])
                ->log('Sesiones invalidadas por cambio de estado a inactivo');
        }
    }
}
