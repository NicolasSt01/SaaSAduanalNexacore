<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckReportAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $reporte): Response
    {
        $user = auth()->user();

        if (!$user || !$user->tenant) {
            abort(403, 'No tienes acceso a este recurso.');
        }

        $tenant = $user->tenant;

        // Verificar si el tenant tiene acceso al reporte
        if (!$tenant->hasReportAccess($reporte)) {
            // Log del intento de acceso
            \Log::warning('Intento de acceso a reporte sin permiso', [
                'tenant_id' => $tenant->id,
                'tenant_nombre' => $tenant->nombre_empresa,
                'reporte' => $reporte,
                'user_id' => $user->id,
            ]);

            // Redirigir a la página de upgrade con información del reporte
            return redirect()->route('reportes.upgrade', ['reporte' => $reporte]);
        }

        return $next($request);
    }
}
