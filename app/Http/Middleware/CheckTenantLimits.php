<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantCapabilityService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para validar los límites de uso del tenant.
 * 
 * Este middleware verifica si el tenant ha alcanzado sus límites
 * antes de permitir ciertas acciones (crear recursos, usar el bot, etc.)
 */
class CheckTenantLimits
{
    protected TenantCapabilityService $capabilityService;

    public function __construct(TenantCapabilityService $capabilityService)
    {
        $this->capabilityService = $capabilityService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $recurso = null): Response
    {
        // Solo aplicar si el usuario está autenticado y tiene tenant
        if (!auth()->check() || !auth()->user()->tenant) {
            return $next($request);
        }

        // Super admins no tienen límites
        if (auth()->user()->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = auth()->user()->tenant;
        $recurso = $recurso ?? $this->detectResourceFromRoute($request);

        if ($recurso) {
            $check = $this->capabilityService->checkResourceLimit($tenant, $recurso);

            if (!$check['allowed']) {
                // Log del intento
                \Illuminate\Support\Facades\Log::warning('Tenant intentó exceder límite', [
                    'tenant_id' => $tenant->id,
                    'tenant' => $tenant->nombre_empresa,
                    'recurso' => $recurso,
                    'limite' => $check['limite'],
                    'uso' => $check['uso'],
                ]);

                // Notificar al admin del tenant si está cerca del límite
                if ($check['limite'] && $check['uso'] >= ($check['limite'] * 0.9)) {
                    $this->capabilityService->notifyNearLimitResources($tenant);
                }

                // Retornar error apropiado según el tipo de request
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => $check['message'],
                        'error_type' => 'resource_limit_exceeded',
                        'resource' => $recurso,
                        'limit' => $check['limite'],
                        'current_usage' => $check['uso'],
                    ], 429); // Too Many Requests
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', $check['message']);
            }
        }

        return $next($request);
    }

    /**
     * Detecta qué recurso se está intentando crear/usar basado en la ruta.
     */
    protected function detectResourceFromRoute(Request $request): ?string
    {
        $route = $request->route();
        if (!$route)
            return null;

        $action = $route->getActionName();
        $uri = $request->path();

        // Mapeo de rutas a recursos
        $resourceMap = [
            'clientes' => ['clientes', 'cliente'],
            'importadores' => ['importadores', 'importador'],
            'bodegas' => ['bodegas', 'bodega'],
            'aduanas' => ['aduanas', 'aduana'],
            'patentes' => ['patentes', 'patente'],
            'pedimentos_mes' => ['expedientes', 'pedimentos'],
            'documentos_mes' => ['documentos', 'documentos'],
            'reportes_mes' => ['reportes', 'reportes'],
        ];

        foreach ($resourceMap as $resource => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($uri, $pattern)) {
                    // Solo validar en métodos de creación (POST)
                    if ($request->isMethod('post') || $request->isMethod('put')) {
                        return $resource;
                    }
                }
            }
        }

        return null;
    }
}
