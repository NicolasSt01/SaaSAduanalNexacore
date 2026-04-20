<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que verifica si el trial del tenant ha expirado.
 * Si el trial expiró, redirige a una página de upgrade.
 */
class CheckTrialExpired
{
    /**
     * Rutas que no requieren verificación de trial.
     */
    protected array $except = [
        'public.*',
        'logout',
        'trial.expired',
        'trial.upgrade',
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

        // Si el usuario no tiene tenant o no es trial, continuar
        if (!$user->tenant || !$user->tenant->isTrial()) {
            return $next($request);
        }

        // Verificar si la ruta actual está en las excepciones
        $currentRoute = $request->route()?->getName();

        foreach ($this->except as $pattern) {
            if ($currentRoute && fnmatch($pattern, $currentRoute)) {
                return $next($request);
            }
        }

        // Verificar si el trial ha expirado
        if ($user->tenant->hasTrialExpired()) {
            return redirect()->route('trial.expired')
                ->with('error', 'Tu período de prueba ha expirado. Por favor actualiza tu plan para continuar usando NexaCore Aduanal.');
        }

        return $next($request);
    }
}
