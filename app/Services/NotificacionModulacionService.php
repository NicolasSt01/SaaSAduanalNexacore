<?php

namespace App\Services;

use App\Models\Operacion;
use App\Models\Notificacion;
use App\Models\User;
use App\Models\Directorio;
use App\Jobs\EnviarNotificacionModulacionJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * NotificacionModulacionService
 *
 * Servicio inteligente de routing de notificaciones por modulación.
 * 
 * Características:
 * - Multi-tenant: cada tenant recibe notificaciones solo de sus operaciones
 * - Multi-cliente: el routing varía según la configuración del cliente
 * - Usa el catálogo Directorio para determinar destinatarios y canales
 * - Soporta reglas por tenant (solo rojos, BCC fijos, etc.)
 * - Funciona sin usuario autenticado (contexto de bot/cron)
 */
class NotificacionModulacionService
{
    protected string $logChannel = 'doda_bot';

    /**
     * Procesar y despachar todas las notificaciones de un cambio de modulación
     */
    public function procesarNotificacionModulacion(
        Operacion $operacion,
        string    $nuevoEstatus,
        ?string   $estatusAnterior,
        string    $executionId
    ): void {
        try {
            $this->log('info', "📧 Procesando notificaciones para modulación", [
                'execution_id' => $executionId,
                'operacion_id' => $operacion->id,
                'tenant_id' => $operacion->tenant_id,
                'cliente_id' => $operacion->cliente_id,
                'de' => $estatusAnterior,
                'a' => $nuevoEstatus,
            ]);

            // 1. Notificación interna (tabla notificaciones) para usuarios del tenant
            $this->crearNotificacionesInternas($operacion, $nuevoEstatus, $executionId);

            // 2. Notificación externa (email/whatsapp) al cliente
            $this->despacharNotificacionExterna($operacion, $nuevoEstatus, $estatusAnterior, $executionId);

        } catch (Exception $e) {
            $this->log('error', "✗ Error procesando notificaciones", [
                'execution_id' => $executionId,
                'operacion_id' => $operacion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Crear notificaciones internas para los usuarios del tenant de la operación.
     * Se notifica a los usuarios con roles relevantes (admin, documentador, trafico equivalente).
     */
    protected function crearNotificacionesInternas(Operacion $operacion, string $nuevoEstatus, string $executionId): void
    {
        try {
            $tenant = $operacion->tenant;
            if (!$tenant) {
                $this->log('warning', "⚠️ Operación sin tenant, no se crean notificaciones internas", [
                    'operacion_id' => $operacion->id,
                ]);
                return;
            }

            $modulacionTexto = $this->modulacionATextoAmigable($nuevoEstatus);
            $clienteNombre = $operacion->cliente->nombre ?? 'Cliente desconocido';
            $referencia = $operacion->referencia ?? $operacion->num_factura ?? 'S/R';

            $titulo = "🚦 Modulación: {$modulacionTexto}";
            $mensaje = "La operación {$referencia} del cliente {$clienteNombre} "
                . "cambió a {$modulacionTexto}. "
                . "Económico: {$operacion->num_thermo}, Alpha: {$operacion->codigo_alpha}";

            // Obtener usuarios del tenant que deben recibir la notificación
            $usuarios = User::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->where('active', true)
                ->whereIn('role', ['admin', 'admin_n2', 'documentador'])
                ->get();

            $notificaciones = [];
            foreach ($usuarios as $usuario) {
                $notificaciones[] = [
                    'tenant_id' => $tenant->id,
                    'user_id' => $usuario->id,
                    'operacion_id' => $operacion->id,
                    'tipo' => 'modulacion_bot',
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'leida' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($notificaciones)) {
                DB::table('notificaciones')->insert($notificaciones);
                $this->log('info', "✅ Notificaciones internas creadas", [
                    'execution_id' => $executionId,
                    'operacion_id' => $operacion->id,
                    'tenant_id' => $tenant->id,
                    'cantidad' => count($notificaciones),
                ]);
            }

        } catch (Exception $e) {
            $this->log('error', "Error creando notificaciones internas", [
                'operacion_id' => $operacion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Despachar notificación externa (email) al cliente.
     * Usa el catálogo Directorio del tenant para obtener los contactos.
     * Si no hay directorio configurado, usa el correo del cliente.
     * Respeta las reglas de notificación del tenant (solo_rojos, etc.)
     */
    protected function despacharNotificacionExterna(
        Operacion $operacion,
        string    $nuevoEstatus,
        ?string   $estatusAnterior,
        string    $executionId
    ): void {
        try {
            $tenant = $operacion->tenant;
            $cliente = $operacion->cliente;

            if (!$tenant || !$cliente) {
                $this->log('warning', "⚠️ No se puede enviar notificación externa - falta tenant o cliente", [
                    'operacion_id' => $operacion->id,
                    'tenant_exists' => !is_null($tenant),
                    'cliente_exists' => !is_null($cliente),
                ]);
                return;
            }

            // Verificar reglas de notificación del tenant
            $tenantConfig = $tenant->configuracion ?? [];
            $notifConfig = $tenantConfig['notificaciones'] ?? [];

            // Regla: ¿Este cliente solo quiere notificaciones en rojo?
            $soloRojosClientes = $notifConfig['solo_rojos_clientes'] ?? [];
            if (in_array($cliente->nombre, $soloRojosClientes)) {
                $esRojo = in_array(strtoupper($nuevoEstatus), [
                    'RECONOCIMIENTO ADUANERO',
                    'RECONOCIMIENTO ADUANERO CONCLUIDO',
                ]);

                if (!$esRojo) {
                    $this->log('info', "⏭ Cliente solo recibe rojos, saltando verde", [
                        'execution_id' => $executionId,
                        'operacion_id' => $operacion->id,
                        'cliente' => $cliente->nombre,
                    ]);
                    // Aunque el cliente no reciba, sí enviar a correos internos del tenant
                    $this->enviarSoloInternos($operacion, $nuevoEstatus, $tenantConfig, $executionId);
                    return;
                }
            }

            // Obtener destinatarios del Directorio del cliente
            $destinatarios = $this->obtenerDestinatariosCliente($cliente->id, $tenant->id);

            // BCC fijos del tenant
            $bccFijos = $notifConfig['correos_bcc_fijos'] ?? [];
            $pecemBcc = $tenantConfig['pecem']['correos_internos_bcc'] ?? [];
            $todoBcc = array_unique(array_merge($bccFijos, $pecemBcc));

            // Si RECONOCIMIENTO ADUANERO, modificar el texto para el cliente
            $estatusParaCliente = $nuevoEstatus;
            if (strtoupper($nuevoEstatus) === 'RECONOCIMIENTO ADUANERO') {
                $estatusParaCliente = 'TRAMITE EN PROCESO DE REVISION';
            }

            // Datos del trámite para el correo
            $datosTramite = [
                'factura' => $operacion->num_factura,
                'nombre_producto' => $operacion->nombre_producto,
                'no_economico' => $operacion->num_thermo,
                'no_alpha' => $operacion->codigo_alpha,
                'referencia' => $operacion->referencia,
                'doda' => $operacion->num_doda,
            ];

            // Despachar Job de notificación
            EnviarNotificacionModulacionJob::dispatch(
                $operacion->id,
                $operacion->tenant_id,
                $cliente->id,
                $datosTramite,
                $estatusParaCliente,
                $destinatarios,
                $todoBcc,
                $executionId
            );

            $this->log('info', "✅ Job de notificación despachado", [
                'execution_id' => $executionId,
                'operacion_id' => $operacion->id,
                'cliente' => $cliente->nombre,
                'destinatarios' => count($destinatarios),
                'bcc' => count($todoBcc),
            ]);

        } catch (Exception $e) {
            $this->log('error', "Error despachando notificación externa", [
                'operacion_id' => $operacion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Enviar solo a correos internos del tenant (cuando el cliente está filtrado)
     */
    protected function enviarSoloInternos(
        Operacion $operacion,
        string    $nuevoEstatus,
        array     $tenantConfig,
        string    $executionId
    ): void {
        $bccFijos = $tenantConfig['notificaciones']['correos_bcc_fijos'] ?? [];
        $pecemBcc = $tenantConfig['pecem']['correos_internos_bcc'] ?? [];
        $internos = array_unique(array_merge($bccFijos, $pecemBcc));

        if (empty($internos)) return;

        $datosTramite = [
            'factura' => $operacion->num_factura,
            'nombre_producto' => $operacion->nombre_producto,
            'no_economico' => $operacion->num_thermo,
            'no_alpha' => $operacion->codigo_alpha,
            'referencia' => $operacion->referencia,
            'doda' => $operacion->num_doda,
        ];

        EnviarNotificacionModulacionJob::dispatch(
            $operacion->id,
            $operacion->tenant_id,
            $operacion->cliente_id,
            $datosTramite,
            $nuevoEstatus,
            [], // Sin destinatarios del cliente
            $internos,
            $executionId
        );
    }

    /**
     * Notificar internamente de una inconsistencia en los datos del DODA / Pedimento
     */
    public function notificarInconsistenciaDoda(Operacion $operacion, array $errores, string $doda, string $executionId): void
    {
        try {
            $tenant = $operacion->tenant;
            if (!$tenant) {
                return;
            }

            // Crear notificación interna solo para administradores y documentadores de ese tenant
            $titulo = "⚠️ Inconsistencia de DODA vs Operación";
            
            $listaErrores = implode(', ', $errores);
            $referencia = $operacion->referencia ?? $operacion->num_factura ?? 'S/R';
            $mensaje = "El DODA {$doda} consultado arroja datos que no coinciden con nuestro registro de la operación {$referencia}. "
                . "Errores encontrados: {$listaErrores}. Por favor rectifique la captura del pedimento o del DODA.";

            $usuarios = User::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->where('active', true)
                ->whereIn('role', ['admin', 'admin_n2', 'documentador'])
                ->get();

            $notificaciones = [];
            foreach ($usuarios as $usuario) {
                $notificaciones[] = [
                    'tenant_id' => $tenant->id,
                    'user_id' => $usuario->id,
                    'operacion_id' => $operacion->id,
                    'tipo' => 'alerta_bot',
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'leida' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($notificaciones)) {
                DB::table('notificaciones')->insert($notificaciones);
                $this->log('warning', "✅ Notificaciones internas de inconsistencia creadas", [
                    'execution_id' => $executionId,
                    'operacion_id' => $operacion->id,
                ]);
            }
        } catch (Exception $e) {
            $this->log('error', "Error creando notificaciones de inconsistencia", [
                'operacion_id' => $operacion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Obtener destinatarios del catálogo Directorio para un cliente.
     * Si no hay contactos en Directorio, fallback al correo del cliente.
     * 
     * @return array [['email' => ..., 'nombre' => ..., 'canal' => ...], ...]
     */
    protected function obtenerDestinatariosCliente(int $clienteId, int $tenantId): array
    {
        // Buscar en Directorio los contactos que reciben notificaciones
        $contactos = DB::table('directorio')
            ->where('tenant_id', $tenantId)
            ->where('cliente_id', $clienteId)
            ->where('activo', true)
            ->where('recibe_notificaciones', true)
            ->get();

        if ($contactos->isNotEmpty()) {
            return $contactos->map(function ($contacto) {
                return [
                    'email' => $contacto->correo,
                    'nombre' => $contacto->nombre,
                    'canal' => $contacto->canal_preferido ?? 'email',
                    'whatsapp' => $contacto->whatsapp,
                ];
            })->filter(function ($dest) {
                return !empty($dest['email']);
            })->values()->toArray();
        }

        // Fallback: usar el correo del cliente directamente
        $cliente = DB::table('cliente')->find($clienteId);
        if ($cliente && !empty($cliente->correo)) {
            return [
                [
                    'email' => $cliente->correo,
                    'nombre' => $cliente->nombre,
                    'canal' => 'email',
                    'whatsapp' => $cliente->telefono ?? null,
                ],
            ];
        }

        return [];
    }

    /**
     * Convertir estatus técnico a texto amigable
     */
    protected function modulacionATextoAmigable(string $estatus): string
    {
        return match (strtoupper(trim($estatus))) {
            'DESADUANAMIENTO LIBRE' => '🟢 Verde (Desaduanamiento Libre)',
            'RECONOCIMIENTO ADUANERO' => '🔴 Roja (Reconocimiento Aduanero)',
            'RECONOCIMIENTO ADUANERO CONCLUIDO' => '🔴 Roja Concluida',
            'TRAMITE EN PROCESO DE REVISION' => '🟡 En Revisión',
            default => $estatus,
        };
    }

    /**
     * Helper de logging
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        try {
            Log::channel($this->logChannel)->$level($message, $context);
        } catch (Exception $e) {
            Log::$level("[NOTIF_MODULACION] {$message}", $context);
        }
    }
}
