<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'check.user.status' => \App\Http\Middleware\CheckUserStatus::class,
        ]);

        // Middleware para API - asegurar respuestas JSON
        $middleware->api(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // El middleware CheckUserStatus se aplicará solo en rutas específicas
        // No se aplica globalmente para evitar conflictos con APIs
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Configurar respuestas JSON para rutas API cuando hay errores de autenticación
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado. Token requerido.',
                    'error' => 'Unauthenticated'
                ], 401);
            }
        });
    })->create();
