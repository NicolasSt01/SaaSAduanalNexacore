<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\User;
use App\Models\Operacion;

class NotificacionService
{
    /**
     * Crear una notificación para usuarios de tráfico
     */
    public function notificarTrafico($tipo, $titulo, $mensaje, $operacion, $creadorId = null)
    {
        // Obtener todos los usuarios con rol 'trafico'
        $usuariosTrafico = User::where('role', 'Trafico')->get();
        
        // Si no hay usuarios de tráfico, no hacer nada
        if ($usuariosTrafico->isEmpty()) {
            return;
        }

        // Obtener datos adicionales de la exportación
        $datos = [
            'num_factura' => $operacion->num_factura,
            'num_thermo' => $operacion->num_thermo,
            'codigo_alpha' => $operacion->codigo_alpha,
            'cliente' => $operacion->cliente->nombre_empresa ?? null,
        ];

        // Crear una notificación para cada usuario de tráfico
        foreach ($usuariosTrafico as $usuario) {
            Notificacion::create([
                'user_id' => $usuario->id,
                'tipo' => $tipo,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'operacion_id' => $operacion->id,
                'datos' => $datos,
                'created_by' => $creadorId ?? auth()->id(),
                'leida' => false,
            ]);
        }
    }

    /**
     * Notificar cuando se suben documentos
     */
    public function notificarDocumentosSubidos(Operacion $operacion, $cantidadDocumentos = 1)
    {
        $creador = auth()->user();
        $nombreCreador = $creador->name ?? 'Usuario';
        
        $titulo = "Nuevos documentos subidos";
        $mensaje = "{$nombreCreador} subió {$cantidadDocumentos} documento(s) a la operación con factura {$operacion->num_factura}";
        
        $this->notificarTrafico(
            'documento_subido',
            $titulo,
            $mensaje,
            $operacion,
            $creador->id
        );
    }

    /**
     * Notificar cuando se marca operación como completada
     */
    public function notificarOperacionCompletada(Operacion $operacion)
    {
        $creador = auth()->user();
        $nombreCreador = $creador->name ?? 'Usuario';
        
        $titulo = "Operación completada";
        $mensaje = "{$nombreCreador} marcó como completada la operación con factura {$operacion->num_factura} del Thermo {$operacion->num_thermo}";
        
        $this->notificarTrafico(
            'operacion_completada',
            $titulo,
            $mensaje,
            $operacion,
            $creador->id
        );
    }

    /**
     * Notificar cuando se actualiza modulación
     */
    public function notificarModulacionActualizada_OLD(Operacion $operacion, $nuevaModulacion)
    {
        $creador = auth()->user();
        $nombreCreador = $creador->name ?? 'Usuario';
        
        $modulacionTexto = match(strtoupper($nuevaModulacion)) {
            'DESADUANAMIENTO LIBRE' => 'Verde',
            'RECONOCIMIENTO ADUANERO CONCLUIDO' => 'Roja',
            'RECONOCIMIENTO ADUANERO' => 'Roja',
            default => $nuevaModulacion
        };
        
        $titulo = "Modulación actualizada";
        //$mensaje = "{$nombreCreador} actualizó la modulación a {$modulacionTexto} para el Thermo {$operacion->num_thermo}";
        $mensaje = "Se actualizó la modulación a {$modulacionTexto} para el Thermo {$operacion->num_thermo} - {$operacion->codigo_alpha}";

        
        $this->notificarTrafico(
            'modulacion_actualizada',
            $titulo,
            $mensaje,
            $operacion,
            $creador->id
        );
    }
    
    /**
     * Notificar cuando se actualiza modulación
     * IMPORTANTE: Este método puede ser llamado desde un bot/cron sin usuario autenticado
     */
    public function notificarModulacionActualizada(Operacion $operacion, $nuevaModulacion)
    {
        try {
            \Log::info("Iniciando notificación de modulación", [
                'operacion_id' => $operacion->id,
                'nueva_modulacion' => $nuevaModulacion,
                'auth_check' => auth()->check()
            ]);

            // Verificar si hay usuario autenticado (puede ser null en bots/cron)
            $creador = auth()->user();
            $creadorId = $creador ? $creador->id : null;
            $nombreCreador = $creador ? ($creador->name ?? 'Usuario') : 'Sistema Automático';

            \Log::info("Usuario identificado para notificación", [
                'operacion_id' => $operacion->id,
                'creador_id' => $creadorId,
                'nombre_creador' => $nombreCreador,
                'es_bot' => is_null($creador)
            ]);

            $modulacionTexto = match (strtoupper($nuevaModulacion)) {
                'DESADUANAMIENTO LIBRE' => 'Verde',
                'RECONOCIMIENTO ADUANERO CONCLUIDO' => 'Roja',
                'RECONOCIMIENTO ADUANERO' => 'Roja',
                'TRAMITE EN PROCESO DE REVISION' => 'Roja',
                default => $nuevaModulacion
            };

            $titulo = "Modulación actualizada";
            $mensaje = "{$nombreCreador} actualizó la modulación a {$modulacionTexto} para el Economico {$operacion->num_thermo}";

            $this->notificarTrafico(
                'modulacion_actualizada',
                $titulo,
                $mensaje,
                $operacion,
                $creadorId
            );

            \Log::info("Notificación de modulación completada", [
                'operacion_id' => $operacion->id,
                'modulacion' => $modulacionTexto
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error("Error en notificarModulacionActualizada", [
                'operacion_id' => $operacion->id ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // No re-lanzar el error para que el bot continúe procesando otras operaciones
            return false;
        }
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarComoLeida($notificacionId)
    {
        $notificacion = Notificacion::find($notificacionId);
        
        if ($notificacion && $notificacion->user_id === auth()->id()) {
            $notificacion->marcarComoLeida();
            return true;
        }
        
        return false;
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function marcarTodasComoLeidas($userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        Notificacion::where('user_id', $userId)
            ->where('leida', false)
            ->update([
                'leida' => true,
                'leida_at' => now()
            ]);
    }

    /**
     * Obtener notificaciones no leídas
     */
    public function obtenerNoLeidas($userId = null, $limit = 10)
    {
        $userId = $userId ?? auth()->id();
        
        return Notificacion::where('user_id', $userId)
            ->noLeidas()
            ->recientes($limit)
            ->with(['creador', 'operacion.cliente'])
            ->get();
    }

    /**
     * Contar notificaciones no leídas
     */
    public function contarNoLeidas($userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        return Notificacion::where('user_id', $userId)
            ->noLeidas()
            ->count();
    }
    
    /**
 * Notificar a Documentación cuando se registra una operación sin código Alpha
 */
public function notificarAlphaPendiente(Operacion $operacion)
{
    // Obtener todos los usuarios con rol 'Documentador'
    $usuariosDocumentacion = User::where('role', 'Documentador')->get();
    
    if ($usuariosDocumentacion->isEmpty()) {
        return;
    }

    $creador = auth()->user();
    $nombreCreador = $creador->name ?? 'Usuario';
    
    $titulo = "⚠️ Operación sin código Alpha";
    $mensaje = "{$nombreCreador} registró una nueva operación (Factura: {$operacion->num_factura}, Thermo: {$operacion->num_thermo}) sin código Alpha. Pendiente de actualización por Tráfico.";
    
    $datos = [
        'num_factura' => $operacion->num_factura,
        'num_thermo' => $operacion->num_thermo,
        'codigo_alpha' => null,
        'cliente' => $operacion->cliente->nombre_empresa ?? null,
        'alerta' => 'alpha_pendiente'
    ];

    foreach ($usuariosDocumentacion as $usuario) {
        Notificacion::create([
            'user_id' => $usuario->id,
            'tipo' => 'alpha_pendiente',
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'operacion_id' => $operacion->id,
            'datos' => $datos,
            'created_by' => $creador->id,
            'leida' => false,
        ]);
    }
}

/**
 * Notificar a Documentación cuando se actualiza el código Alpha
 */
public function notificarAlphaActualizado(Operacion $operacion)
{
    // Obtener todos los usuarios con rol 'Documentador'
    $usuariosDocumentacion = User::where('role', 'Documentador')->get();
    
    if ($usuariosDocumentacion->isEmpty()) {
        return;
    }

    $creador = auth()->user();
    $nombreCreador = $creador->name ?? 'Usuario';
    
    $titulo = "✅ Código Alpha actualizado";
    $mensaje = "{$nombreCreador} actualizó el código Alpha ({$operacion->codigo_alpha}) para la operación con Factura: {$operacion->num_factura}, Thermo: {$operacion->num_thermo}";
    
    $datos = [
        'num_factura' => $operacion->num_factura,
        'num_thermo' => $operacion->num_thermo,
        'codigo_alpha' => $operacion->codigo_alpha,
        'cliente' => $operacion->cliente->nombre_empresa ?? null,
    ];

    foreach ($usuariosDocumentacion as $usuario) {
        Notificacion::create([
            'user_id' => $usuario->id,
            'tipo' => 'alpha_actualizado',
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'operacion_id' => $operacion->id,
            'datos' => $datos,
            'created_by' => $creador->id,
            'leida' => false,
        ]);
    }
}

/**
     * Crear notificación global para múltiples roles
     */
    public function crearNotificacionGlobal_old(array $roles, string $titulo, string $mensaje, string $tipo = 'info', ?int $operacionId = null)
    {
        try {
            // Obtener usuarios con los roles especificados
            $usuarios = User::whereIn('role', $roles)->get();

            $notificaciones = [];

            foreach ($usuarios as $usuario) {
                $notificaciones[] = [
                    'user_id' => $usuario->id,
                    'creador_id' => auth()->id(),
                    'operacion_id' => $operacionId,
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'tipo' => $tipo,
                    'leida' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Insertar todas las notificaciones en lote
            if (!empty($notificaciones)) {
                Notificacion::insert($notificaciones);
            }

            return true;

        } catch (\Exception $e) {
            \Log::error('Error creando notificación global: ' . $e->getMessage());
            return false;
        }
    }

    public function crearNotificacionGlobal(array $roles, string $titulo, string $mensaje, string $tipo = 'info', ?int $operacionId = null)
{
    try {
        \Log::info("=== CREANDO NOTIFICACIÓN GLOBAL ===");
        \Log::info("Roles destino: " . implode(', ', $roles));
        \Log::info("Título: {$titulo}");
        \Log::info("Mensaje: {$mensaje}");
        \Log::info("Tipo: {$tipo}");
        \Log::info("Exportación ID: {$operacionId}");

        // Obtener usuarios con los roles especificados
        $usuarios = User::whereIn('role', $roles)->get();
        
        \Log::info("Usuarios encontrados: " . $usuarios->count());

        $notificaciones = [];
        
        foreach ($usuarios as $usuario) {
            $notificaciones[] = [
                'user_id' => $usuario->id,
                'created_by' => auth()->id(),
                'operacion_id' => $operacionId,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'tipo' => $tipo,
                'leida' => false,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            \Log::info("Notificación para usuario: {$usuario->name} (ID: {$usuario->id})");
        }
        
        // Insertar todas las notificaciones en lote
        if (!empty($notificaciones)) {
            $insertados = Notificacion::insert($notificaciones);
            \Log::info("Notificaciones insertadas: " . ($insertados ? 'SÍ' : 'NO'));
            
            if ($insertados) {
                \Log::info("Total de notificaciones creadas: " . count($notificaciones));
            }
        } else {
            \Log::warning("No hay notificaciones para insertar");
        }
        
        \Log::info("=== FIN NOTIFICACIÓN GLOBAL ===");
        return true;
        
    } catch (\Exception $e) {
        \Log::error('Error creando notificación global: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return false;
    }
}

    
    
}