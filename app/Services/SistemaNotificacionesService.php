<?php

namespace App\Services;

use App\Models\NotificacionSistema;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para gestionar notificaciones del sistema.
 * 
 * Maneja alertas de límites, recursos cercanos al límite,
 * y notificaciones generales para tenants.
 */
class SistemaNotificacionesService
{
    /**
     * Crear una notificación para un tenant
     */
    public function crearNotificacion(
        int $tenantId,
        string $tipo,
        string $titulo,
        string $mensaje,
        string $nivel = 'info',
        ?string $accionUrl = null,
        ?string $accionTexto = null,
        array $metadata = []
    ): NotificacionSistema {
        $notificacion = NotificacionSistema::create([
            'tenant_id' => $tenantId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'nivel' => $nivel,
            'accion_url' => $accionUrl,
            'accion_texto' => $accionTexto,
            'metadata' => $metadata,
        ]);

        Log::info('Notificación creada', [
            'tenant_id' => $tenantId,
            'tipo' => $tipo,
            'nivel' => $nivel,
            'titulo' => $titulo,
        ]);

        return $notificacion;
    }

    /**
     * Verificar y crear alertas de límite del bot para todos los tenants.
     * 
     * @param bool $esEjecucionManual Si es true, crea notificaciones completas.
     *                                Si es false (automático), solo notifica UNA vez al 100%.
     */
    public function verificarLimitesBot(bool $esEjecucionManual = false): void
    {
        $tenants = Tenant::where('estado', 'activo')
            ->whereNotNull('bot_consultas_limite_mes')
            ->get();

        foreach ($tenants as $tenant) {
            $limite = $tenant->getBotConsultasLimite();
            if (!$limite) continue;

            $usadas = $tenant->getBotConsultasUsadas();
            $porcentaje = ($usadas / $limite) * 100;

            if ($esEjecucionManual) {
                // Modo MANUAL: Crear notificaciones completas en todos los niveles
                if ($porcentaje >= 80 && $porcentaje < 90) {
                    $this->crearNotificacion(
                        $tenant->id,
                        'bot_near_limit',
                        '⚠️ Límite de SOIA-Bot cercano',
                        "Has usado {$usadas} de {$limite} consultas este mes ({$porcentaje}%). Te quedan " . ($limite - $usadas) . " consultas disponibles.",
                        'warning',
                        '#',
                        'Actualizar Plan',
                        ['consultas_usadas' => $usadas, 'limite' => $limite, 'porcentaje' => $porcentaje]
                    );
                }

                if ($porcentaje >= 90 && $porcentaje < 100) {
                    $this->crearNotificacion(
                        $tenant->id,
                        'bot_near_limit',
                        '🚨 Límite de SOIA-Bot muy cercano',
                        "Has usado {$usadas} de {$limite} consultas este mes ({$porcentaje}%). Solo te quedan " . ($limite - $usadas) . " consultas.",
                        'error',
                        '#',
                        'Actualizar Plan Ahora',
                        ['consultas_usadas' => $usadas, 'limite' => $limite, 'porcentaje' => $porcentaje]
                    );
                }

                if ($porcentaje >= 100) {
                    $this->crearNotificacion(
                        $tenant->id,
                        'bot_limit_reached',
                        '🚫 Límite de SOIA-Bot alcanzado',
                        "Has alcanzado tu límite de {$limite} consultas al SOIA-Bot este mes. Actualiza tu plan para continuar usando el bot.",
                        'error',
                        '#',
                        'Actualizar Plan Ahora',
                        ['consultas_usadas' => $usadas, 'limite' => $limite, 'porcentaje' => $porcentaje]
                    );
                }
            } else {
                // Modo AUTOMÁTICO: Solo notificar UNA vez cuando alcanza el 100%
                if ($porcentaje >= 100) {
                    $notificacionExistente = NotificacionSistema::where('tenant_id', $tenant->id)
                        ->where('tipo', 'bot_limit_reached')
                        ->mesActual()
                        ->first();

                    if (!$notificacionExistente) {
                        // Primera vez que alcanza el límite este mes → Notificar
                        $this->crearNotificacion(
                            $tenant->id,
                            'bot_limit_reached',
                            '🚫 Límite de SOIA-Bot alcanzado',
                            "Has alcanzado tu límite de {$limite} consultas al SOIA-Bot este mes. Se omitieron operaciones pendientes. Actualiza tu plan para continuar usando el bot.",
                            'error',
                            '#',
                            'Actualizar Plan Ahora',
                            ['consultas_usadas' => $usadas, 'limite' => $limite, 'porcentaje' => $porcentaje, 'modo' => 'automatico']
                        );

                        Log::info("📧 Notificación única enviada a {$tenant->nombre_empresa} por límite alcanzado (modo automático)");
                    } else {
                        Log::info("🔕 Notificación omitida para {$tenant->nombre_empresa} (ya fue notificado este mes)");
                    }
                }
            }
        }
    }

    /**
     * Obtener notificaciones no leídas de un tenant
     */
    public function obtenerNoLeidas(int $tenantId)
    {
        return NotificacionSistema::paraTenant($tenantId)
            ->noLeidas()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Obtener todas las notificaciones recientes de un tenant
     */
    public function obtenerRecientes(int $tenantId, int $limite = 10)
    {
        return NotificacionSistema::paraTenant($tenantId)
            ->orderBy('created_at', 'desc')
            ->limit($limite)
            ->get();
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarLeida(int $notificacionId): bool
    {
        $notificacion = NotificacionSistema::find($notificacionId);
        
        if (!$notificacion) return false;

        return $notificacion->marcarLeida();
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function marcarTodasLeidas(int $tenantId): int
    {
        return NotificacionSistema::paraTenant($tenantId)
            ->noLeidas()
            ->update([
                'leida' => true,
                'leida_en' => now(),
            ]);
    }

    /**
     * Contar notificaciones no leídas
     */
    public function contarNoLeidas(int $tenantId): int
    {
        return NotificacionSistema::paraTenant($tenantId)
            ->noLeidas()
            ->count();
    }

    /**
     * Verificar si un tenant ya fue notificado sobre su límite este mes
     */
    public function yaFueNotificadoLimite(int $tenantId): bool
    {
        return NotificacionSistema::where('tenant_id', $tenantId)
            ->where('tipo', 'bot_limit_reached')
            ->mesActual()
            ->exists();
    }
}
