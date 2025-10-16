<?php

namespace App\Http\Middleware;

use Closure;

class TelescopeBasicAuth
{
    public function handle($request, Closure $next)
    {
        $AUTH_USER = 'Admin';
        $AUTH_PASS = 'admin';

        if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) ||
            $_SERVER['PHP_AUTH_USER'] !== $AUTH_USER ||
            $_SERVER['PHP_AUTH_PW'] !== $AUTH_PASS) {
            return response('Acceso restringido.', 401, [
                'WWW-Authenticate' => 'Basic realm="Telescope"',
            ]);
        }

        return $next($request);
    }
}
