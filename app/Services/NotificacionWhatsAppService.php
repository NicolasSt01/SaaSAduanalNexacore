<?php

namespace App\Services;

use App\Models\Operacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * NotificacionWhatsAppService
 *
 * Servicio para el despacho de notificaciones de modulación por WhatsApp.
 * 
 * Construye el payload con los datos de la operación, el tenant y los
 * destinatarios con WhatsApp habilitado, y lo envía al webhook de n8n.
 * 
 * El email sigue manejándose en NotificacionModulacionService (INC-024).
 */
class NotificacionWhatsAppService
{
    protected string $webhookUrl;
    protected string $webhookToken;
    protected string $logChannel = 'doda_bot';

    public function __construct()
    {
        $this->webhookUrl = env('N8N_MODULACION_WHATSAPP_URL', '');
        $this->webhookToken = env('N8N_MODULACION_WHATSAPP_TOKEN', '');
    }

    /**
     * Verificar si el servicio está disponible (webhook configurado).
     */
    public function isConfigured(): bool
    {
        return !empty($this->webhookUrl);
    }

    /**
     * Notificar modulación por WhatsApp a los contactos del directorio
     * que tengan número de WhatsApp y canal_preferido compatible.
     *
     * @param Operacion $operacion
     * @param string    $nuevoEstatus   Estatus detectado por el bot
     * @param array     $destinatarios  Mismo array que usa el email (de obtenerDestinatariosCliente)
     * @param bool      $bypassLimit    Si true, ignora el límite mensual (para reintentos)
     */
    public function notificar(Operacion $operacion, string $nuevoEstatus, array $destinatarios, bool $bypassLimit = false): void
    {
        if (!$this->isConfigured()) {
            $this->log('info', '⏭ WhatsApp no configurado (sin N8N_MODULACION_WHATSAPP_URL)');
            return;
        }

        // Filtrar destinatarios con WhatsApp
        $whatsappDestinatarios = array_filter($destinatarios, function ($d) {
            $canal = $d['canal'] ?? 'email';
            $tieneWhatsApp = !empty($d['whatsapp'] ?? null);
            return $tieneWhatsApp && in_array($canal, ['whatsapp', 'ambos']);
        });

        if (empty($whatsappDestinatarios)) {
            $this->log('info', '⏭ Sin destinatarios con WhatsApp habilitado');
            return;
        }

        $tenant = $operacion->tenant;
        $tenantConfig = $tenant->configuracion ?? [];
        $evolutionConfig = $tenantConfig['evolution_api'] ?? [];

        // Verificar que el tenant tenga instancia conectada
        if (empty($evolutionConfig['instance']) || empty($evolutionConfig['connected'])) {
            $this->log('warning', '⚠️ Tenant sin instancia WhatsApp conectada', [
                'tenant_id' => $tenant->id,
                'instance' => $evolutionConfig['instance'] ?? 'N/A',
                'connected' => $evolutionConfig['connected'] ?? false,
            ]);
            return;
        }

        // Determinar plantilla WhatsApp (custom > seleccionada > default)
        $whatsappPlantilla = $evolutionConfig['whatsapp_plantilla_custom'] ?? null;
        $whatsappPlantillaKey = 'breve';
        if (!$whatsappPlantilla) {
            $whatsappPlantillaKey = $evolutionConfig['whatsapp_plantilla'] ?? 'breve';
        }

        // Verificar límite mensual de WhatsApp (omitir en reintentos)
        if (!$bypassLimit && !$tenant->canSendWhatsapp()) {
            $limite = $tenant->getLimiteFuncionalidad('whatsapp_mes');
            $uso = $tenant->getWhatsappUsadosMes();
            $this->log('warning', "⏭ Límite mensual de WhatsApp alcanzado ({$uso}/{$limite}). Guardando en pendientes.", [
                'operacion_id' => $operacion->id,
                'tenant_id' => $tenant->id,
                'destinatarios' => count($whatsappDestinatarios),
            ]);

            // Guardar en cola de pendientes
            $tenant->addPendingNotification('whatsapp', [
                'operacion_id' => $operacion->id,
                'destinatarios' => array_values(array_map(function ($d) {
                    return ['nombre' => $d['nombre'] ?? 'Cliente', 'numero' => $d['whatsapp']];
                }, $whatsappDestinatarios)),
                'modulacion' => $nuevoEstatus,
                'plantilla' => $whatsappPlantillaKey,
                'plantilla_custom' => $whatsappPlantilla,
            ]);
            return;
        }

        $payload = [
            'evolution_api' => [
                'base_url' => rtrim(env('EVOLUTION_API_BASE_URL', ''), '/'),
                'api_key' => env('EVOLUTION_API_KEY', ''),
                'instance' => $evolutionConfig['instance'],
            ],
            'tenant' => [
                'id' => $tenant->id,
                'nombre_empresa' => $tenant->nombre_empresa ?? 'Agencia Aduanal',
                'whatsapp_plantilla' => $whatsappPlantillaKey,
                'whatsapp_plantilla_custom' => $whatsappPlantilla,
            ],
            'operacion' => [
                'id' => $operacion->id,
                'referencia' => $operacion->referencia,
                'num_factura' => $operacion->num_factura,
                'nombre_producto' => $operacion->nombre_producto,
                'num_thermo' => $operacion->num_thermo,
                'codigo_alpha' => $operacion->codigo_alpha,
                'num_doda' => $operacion->num_doda,
                'modulacion' => $nuevoEstatus,
                'fecha_modulacion' => $operacion->fecha_modulacion?->toIso8601String() ?? now()->toIso8601String(),
            ],
            'destinatarios_whatsapp' => array_values(array_map(function ($d) {
                return [
                    'nombre' => $d['nombre'] ?? 'Cliente',
                    'numero' => $d['whatsapp'],
                ];
            }, $whatsappDestinatarios)),
        ];

        try {
            $response = Http::withToken($this->webhookToken)
                ->timeout(30)
                ->post($this->webhookUrl, $payload);

            $this->log('info', $response->successful()
                ? "✅ WhatsApp: notificación enviada a n8n"
                : "❌ WhatsApp: error HTTP {$response->status()}",
                [
                    'operacion_id' => $operacion->id,
                    'tenant_id' => $tenant->id,
                    'destinatarios' => count($whatsappDestinatarios),
                    'status' => $response->status(),
                ]);

            if ($response->successful()) {
                $tenant->incrementarConsumoWhatsapp();
            }

        } catch (Exception $e) {
            $this->log('error', '✗ WhatsApp: excepción al contactar n8n', [
                'operacion_id' => $operacion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        try {
            Log::channel($this->logChannel)->$level("[WHATSAPP] {$message}", $context);
        } catch (Exception $e) {
            Log::$level("[WHATSAPP] {$message}", $context);
        }
    }
}
