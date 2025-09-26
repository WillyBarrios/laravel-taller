<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // En esta API no existe una ruta nombrada 'login' para redirección web tradicional.
        // Si es petición que espera JSON devolvemos null para que el middleware de Auth
        // responda 401 sin intentar redirigir. Para cualquier otra petición también
        // devolvemos null porque es un backend API + multi-tenant.
        return null;
    }
}
