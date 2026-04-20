<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que verifica si el usuario debe cambiar su contraseña.
 * Si must_change_password es true, redirige al formulario de cambio de contraseña.
 */
class MustChangePassword
{
    /**
     * Rutas que no requieren cambio de contraseña.
     */
    protected array $except = [
        'public.*',
        'logout',
        'change-first-password',
        'change-first-password.store',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Si el usuario no debe cambiar contraseña, continuar
        if (!$user->mustChangePassword()) {
            return $next($request);
        }

        // Verificar si la ruta actual está en las excepciones
        $currentRoute = $request->route()?->getName();

        foreach ($this->except as $pattern) {
            if ($currentRoute && fnmatch($pattern, $currentRoute)) {
                return $next($request);
            }
        }

        // Redirigir al formulario de cambio de contraseña
        return redirect()->route('change-first-password')
            ->with('warning', 'Debes cambiar tu contraseña temporal antes de continuar.');
    }
}
