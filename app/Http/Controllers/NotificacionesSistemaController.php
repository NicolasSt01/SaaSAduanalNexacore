<?php

namespace App\Http\Controllers;

use App\Services\SistemaNotificacionesService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificacionesSistemaController extends Controller
{
    protected SistemaNotificacionesService $notificacionesService;

    public function __construct(SistemaNotificacionesService $notificacionesService)
    {
        $this->notificacionesService = $notificacionesService;
    }

    /**
     * Obtener notificaciones no leídas (AJAX)
     * Solo para administradores. Otros usuarios usan la tabla 'notificaciones'.
     */
    public function obtenerNoLeidas(): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['no_leidas' => 0, 'notificaciones' => []]);
        }

        $user = auth()->user();

        // Si no es admin ni super_admin, retornar vacío (ellos usan 'notificaciones')
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json(['no_leidas' => 0, 'notificaciones' => []]);
        }

        // Para admins: obtener notificaciones del sistema del tenant
        if (!$user->tenant) {
            return response()->json(['no_leidas' => 0, 'notificaciones' => []]);
        }

        $tenantId = $user->tenant_id;
        $noLeidas = $this->notificacionesService->obtenerNoLeidas($tenantId);
        $count = $this->notificacionesService->contarNoLeidas($tenantId);

        return response()->json([
            'no_leidas' => $count,
            'notificaciones' => $noLeidas->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'tipo' => $notif->tipo,
                    'titulo' => $notif->titulo,
                    'mensaje' => $notif->mensaje,
                    'nivel' => $notif->nivel,
                    'accion_url' => $notif->accion_url,
                    'accion_texto' => $notif->accion_texto,
                    'icono' => $notif->icono,
                    'color' => $notif->color,
                    'created_at' => $notif->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    /**
     * Marcar notificación como leída
     * IMPORTANTE: Solo permite marcar notificaciones del propio tenant.
     */
    public function marcarLeida($id): JsonResponse
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'No autorizado']);
        }

        $user = auth()->user();

        // Verificar que la notificación pertenezca al tenant del usuario
        $notificacion = \App\Models\NotificacionSistema::where('id', $id)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$notificacion) {
            return response()->json(['success' => false, 'message' => 'Notificación no encontrada o no pertenece a tu tenant']);
        }

        $this->notificacionesService->marcarLeida($id);

        return response()->json(['success' => true]);
    }

    /**
     * Marcar todas como leídas
     */
    public function marcarTodasLeidas(): JsonResponse
    {
        if (!auth()->check() || !auth()->user()->tenant) {
            return response()->json(['success' => false, 'message' => 'Sin tenant']);
        }

        if (!in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'No autorizado']);
        }

        $count = $this->notificacionesService->marcarTodasLeidas(auth()->user()->tenant_id);

        return response()->json(['success' => true, 'marcadas' => $count]);
    }
}
