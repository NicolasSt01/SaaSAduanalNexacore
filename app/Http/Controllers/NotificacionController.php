<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificacionService;
use App\Models\Notificacion;

class NotificacionController extends Controller
{
    protected $notificacionService;

    public function __construct(NotificacionService $notificacionService)
    {
        $this->notificacionService = $notificacionService;
    }

    /**
     * Obtener notificaciones no leídas (para el badge y dropdown)
     * IMPORTANTE: Solo retorna notificaciones del tenant del usuario autenticado.
     */
    public function noLeidas()
    {
        if (!auth()->check()) {
            return response()->json(['notificaciones' => [], 'count' => 0]);
        }

        $user = auth()->user();

        // Obtener notificaciones del usuario, filtradas por su tenant
        $query = Notificacion::where('user_id', $user->id)
            ->noLeidas()
            ->with(['creador', 'operacion.cliente'])
            ->orderBy('created_at', 'desc')
            ->limit(10);

        // Si el usuario tiene tenant, asegurar el filtrado por tenant
        if ($user->tenant_id) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $notificaciones = $query->get();
        $count = $notificaciones->count();

        // Normalizar formato para el frontend
        $notificacionesNormalizadas = $notificaciones->map(function ($notif) {
            return [
                'id' => $notif->id,
                'tipo' => $notif->tipo,
                'titulo' => $notif->titulo,
                'mensaje' => $notif->mensaje,
                'nivel' => 'info', // Default, se puede mejorar
                'accion_url' => $notif->operacion ? route('trafico.operaciones.show', $notif->operacion_id) : null,
                'accion_texto' => 'Ver operación',
                'icono' => 'fa-bell',
                'color' => 'blue',
                'created_at' => $notif->created_at->diffForHumans(),
                'fuente' => 'usuario'
            ];
        });

        return response()->json([
            'notificaciones' => $notificacionesNormalizadas,
            'count' => $count
        ]);
    }

    /**
     * Obtener nuevas notificaciones (para polling y toasts)
     */
    public function nuevas(Request $request)
    {
        // Obtener timestamp de la última consulta
        $ultimaConsulta = $request->input('ultima_consulta');

        $query = auth()->user()->notificaciones()
            ->noLeidas()
            ->with(['creador', 'operacion.cliente'])
            ->orderBy('created_at', 'desc');

        // Si hay timestamp, solo obtener las más recientes
        if ($ultimaConsulta) {
            $query->where('created_at', '>', $ultimaConsulta);
        }

        $nuevas = $query->get();
        $count = $this->notificacionService->contarNoLeidas();

        return response()->json([
            'nuevas' => $nuevas,
            'count' => $count,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Marcar una notificación como leída
     * IMPORTANTE: Solo permite marcar notificaciones del propio tenant.
     */
    public function marcarLeida($id)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
        }

        $user = auth()->user();

        // Verificar que la notificación pertenezca al usuario y su tenant
        $query = Notificacion::where('id', $id)->where('user_id', $user->id);

        if ($user->tenant_id) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $notificacion = $query->first();

        if (!$notificacion) {
            return response()->json(['success' => false, 'message' => 'Notificación no encontrada o no pertenece a tu tenant'], 404);
        }

        $notificacion->update(['leida' => true]);

        // Contar restantes
        $countQuery = Notificacion::where('user_id', $user->id)->noLeidas();
        if ($user->tenant_id) {
            $countQuery->where('tenant_id', $user->tenant_id);
        }
        $count = $countQuery->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Marcar todas como leídas
     * IMPORTANTE: Solo marca notificaciones del propio tenant.
     */
    public function marcarTodasLeidas()
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
        }

        $user = auth()->user();

        $query = Notificacion::where('user_id', $user->id)->where('leida', false);

        if ($user->tenant_id) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $updated = $query->update(['leida' => true]);

        return response()->json([
            'success' => true,
            'count' => 0,
            'marcadas' => $updated
        ]);
    }

    /**
     * Ver todas las notificaciones (página completa)
     */
    public function index()
    {
        $notificaciones = auth()->user()->notificaciones()
            ->with(['creador', 'operacion.cliente'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notificaciones.index', compact('notificaciones'));
    }
}