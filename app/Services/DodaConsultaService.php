<?php

namespace App\Services;

use App\Models\Operacion;
use App\Models\OperacionHistorialDoda;
use App\Models\Tenant;
use App\Models\NotificacionSistema;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * DodaConsultaService
 *
 * Servicio independiente para la consulta masiva de DODA/Modulaciones
 * contra el portal PECEM/SOIA del SAT.
 *
 * Diseñado para:
 * - Manejar ~120 operaciones/hora de ~20 agencias simultáneamente
 * - Multi-tenant: cada tenant tiene su propia configuración PECEM
 * - Scraping estructurado del HTML del portal SAT
 * - Detección inteligente de cambios de modulación
 * - Logging JSON completo para auditoría
 * - Respetar límites de consultas por tenant
 */
class DodaConsultaService
{
    protected string $logChannel = 'doda_bot';
    protected string $executionId;
    protected NotificacionModulacionService $notificacionService;
    protected SistemaNotificacionesService $notificacionesService;

    // Flag para indicar si es ejecución manual (true) o automática (false)
    protected bool $esEjecucionManual = false;

    // Tracking de consultas procesadas por tenant
    protected array $consultasPorTenant = [];

    // Contadores de la ejecución actual
    protected int $totalConsultadas = 0;
    protected int $totalCambios = 0;
    protected int $totalErrores = 0;
    protected array $resultados = [];

    public function __construct(
        NotificacionModulacionService $notificacionService,
        SistemaNotificacionesService $notificacionesService
    ) {
        $this->notificacionService = $notificacionService;
        $this->notificacionesService = $notificacionesService;
        $this->executionId = uniqid('doda_', true);
    }

    /**
     * Marcar la ejecución como manual (para notificaciones completas)
     */
    public function setEjecucionManual(bool $manual = true): void
    {
        $this->esEjecucionManual = $manual;
    }

    /**
     * Punto de entrada principal: Ejecuta consulta masiva de todos los tenants
     * 
     * @return array Resumen de la ejecución
     */
    public function ejecutarConsultaMasiva(): array
    {
        $inicio = microtime(true);

        $this->log('info', '══════════════════════════════════════════════════');
        $this->log('info', '🤖 INICIO EJECUCIÓN SOIA-BOT DODA MULTI-TENANT', [
            'execution_id' => $this->executionId,
            'timestamp' => now()->toDateTimeString(),
        ]);

        try {
            // Paso 1: Obtener TODAS las operaciones pendientes de TODOS los tenants
            $operacionesPendientes = $this->obtenerOperacionesPendientes();

            if ($operacionesPendientes->isEmpty()) {
                $this->log('info', '✅ No hay operaciones pendientes de consultar');
                return $this->generarResumen($inicio, 'sin_datos');
            }

            $this->log('info', "📊 Operaciones pendientes encontradas", [
                'total' => $operacionesPendientes->count(),
                'tenants' => $operacionesPendientes->pluck('tenant_id')->unique()->count(),
            ]);

            // Paso 2: Agrupar por DODA único + su contexto (aduana, patente)
            $consultasPorDoda = $this->prepararConsultas($operacionesPendientes);

            $this->log('info', "🔗 DODAs únicos a consultar", [
                'total_dodas' => count($consultasPorDoda),
            ]);

            // Paso 3: Ejecutar consultas concurrentes al PECEM
            $this->ejecutarConsultasConcurrentes($consultasPorDoda);

            // Paso 4: Crear notificaciones POST-EJECUCIÓN (importante para modo manual)
            $this->crearNotificacionesPostEjecucion();

            $this->log('info', '══════════════════════════════════════════════════');
            $this->log('info', '🏁 FIN EJECUCIÓN BOT DODA', [
                'execution_id' => $this->executionId,
                'consultadas' => $this->totalConsultadas,
                'cambios_detectados' => $this->totalCambios,
                'errores' => $this->totalErrores,
                'duracion_segundos' => round(microtime(true) - $inicio, 2),
            ]);

            return $this->generarResumen($inicio, 'completado');

        } catch (Exception $e) {
            $this->log('critical', '💀 ERROR CRÍTICO EN EJECUCIÓN', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->generarResumen($inicio, 'error', $e->getMessage());
        }
    }

    /**
     * Crear notificaciones DESPUÉS de ejecutar las consultas.
     * 
     * Este método:
     * 1. Incrementa los contadores de consultas por tenant
     * 2. Verifica si alcanzaron el límite
     * 3. Crea las notificaciones apropiadas según el modo de ejecución
     */
    protected function crearNotificacionesPostEjecucion(): void
    {
        $this->log('info', '📧 Verificando notificaciones post-ejecución', [
            'esEjecucionManual' => $this->esEjecucionManual,
            'tenants_procesados' => count($this->consultasPorTenant),
            'consultas_por_tenant' => $this->consultasPorTenant,
        ]);

        // Primero, incrementar los contadores de cada tenant
        foreach ($this->consultasPorTenant as $tenantId => $cantidad) {
            $tenant = Tenant::find($tenantId);

            if (!$tenant) {
                $this->log('warning', "⚠️ Tenant no encontrado: {$tenantId}");
                continue;
            }

            $tenant->incrementarBotConsultas($cantidad);

            $this->log('info', "✅ Contador actualizado para {$tenant->nombre_empresa}", [
                'consultas_procesadas' => $cantidad,
                'total_acumulado' => $tenant->getBotConsultasUsadas(),
            ]);
        }

        // Ahora verificar límites y crear notificaciones
        $tenants = Tenant::where('estado', 'activo')->get()
            ->filter(fn($t) => $t->isBotEnabled());

        $this->log('info', "🔍 Tenants activos encontrados: {$tenants->count()}");

        foreach ($tenants as $tenant) {
            $limite = $tenant->getBotConsultasLimite();
            $usadas = $tenant->getBotConsultasUsadas();

            $this->log('info', "📊 Revisando tenant: {$tenant->nombre_empresa}", [
                'tenant_id' => $tenant->id,
                'limite' => $limite ?? 'SIN LÍMITE',
                'usadas' => $usadas,
                'esEjecucionManual' => $this->esEjecucionManual,
            ]);

            // Si no tiene límite configurado, no crear notificaciones de límite
            if (!$limite) {
                $this->log('info', "⏭️ Tenant {$tenant->nombre_empresa} no tiene límite configurado, omitiendo");
                continue;
            }

            $porcentaje = ($usadas / $limite) * 100;

            $this->log('info', "📈 Porcentaje de uso: {$porcentaje}% ({$usadas}/{$limite})");

            // MODO MANUAL: Crear notificaciones en todos los niveles
            if ($this->esEjecucionManual) {
                $this->log('info', "🔧 Modo MANUAL - Creando notificaciones si aplica");

                if ($porcentaje >= 100) {
                    $this->log('info', "🚨 Tenant alcanzó 100% - Creando notificación de límite alcanzado");

                    $this->notificacionesService->crearNotificacion(
                        $tenant->id,
                        'bot_limit_reached',
                        '🚫 Límite de SOIA-Bot alcanzado',
                        "Has alcanzado tu límite de {$limite} consultas al SOIA-Bot este mes. Se procesaron {$usadas} operaciones. Actualiza tu plan para continuar usando el bot.",
                        'error',
                        '#',
                        'Actualizar Plan Ahora',
                        ['consultas_usadas' => $usadas, 'limite' => $limite, 'porcentaje' => $porcentaje, 'modo' => 'manual']
                    );

                    $this->log('info', "✅ Notificación creada exitosamente para {$tenant->nombre_empresa}");
                } elseif ($porcentaje >= 80) {
                    $this->log('info', "⚠️ Tenant en {$porcentaje}% - Creando notificación de límite cercano");

                    $this->notificacionesService->crearNotificacion(
                        $tenant->id,
                        'bot_near_limit',
                        '⚠️ Límite de SOIA-Bot cercano',
                        "Has usado {$usadas} de {$limite} consultas este mes (" . number_format($porcentaje, 1) . "%). Te quedan " . ($limite - $usadas) . " consultas disponibles.",
                        $porcentaje >= 90 ? 'error' : 'warning',
                        '#',
                        $porcentaje >= 90 ? 'Actualizar Plan Ahora' : 'Ver mi Plan',
                        ['consultas_usadas' => $usadas, 'limite' => $limite, 'porcentaje' => $porcentaje, 'modo' => 'manual']
                    );

                    $this->log('info', "✅ Notificación creada exitosamente para {$tenant->nombre_empresa}");
                } else {
                    $this->log('info', "✅ Tenant en {$porcentaje}% - No requiere notificación (< 80%)");
                }
            }
            // MODO AUTOMÁTICO: Solo notificar UNA vez cuando alcanza el 100%
            else {
                $this->log('info', "🤖 Modo AUTOMÁTICO - Solo notificación única al 100%");

                if ($porcentaje >= 100) {
                    $notificacionExistente = NotificacionSistema::where('tenant_id', $tenant->id)
                        ->where('tipo', 'bot_limit_reached')
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->first();

                    if (!$notificacionExistente) {
                        $this->log('info', "📧 Primera notificación del mes - Creando notificación única");

                        $this->notificacionesService->crearNotificacion(
                            $tenant->id,
                            'bot_limit_reached',
                            '🚫 Límite de SOIA-Bot alcanzado',
                            "Has alcanzado tu límite de {$limite} consultas al SOIA-Bot este mes. Se omitieron operaciones pendientes. Actualiza tu plan para continuar usando el bot.",
                            'error',
                            '#',
                            'Actualizar Plan Ahora',
                            ['consultas_usadas' => $usadas, 'limite' => $limite, 'porcentaje' => $porcentaje, 'modo' => 'automatico']
                        );

                        $this->log('info', "✅ Notificación única creada para {$tenant->nombre_empresa}");
                    } else {
                        $this->log('info', "🔕 Notificación omitida - Ya existe notificación este mes");
                    }
                }
            }
        }

        $this->log('info', '✅ Verificación de notificaciones post-ejecución completada');
    }

    /**
     * Obtener todas las operaciones que necesitan consulta DODA
     * SIN aplicar el global scope de tenant (consulta cross-tenant)
     * RESPETANDO los límites de consultas por tenant
     */
    protected function obtenerOperacionesPendientes()
    {
        $tenants = Tenant::where('estado', 'activo')->get()
            ->filter(fn($t) => $t->isBotEnabled());

        $operacionesPendientes = collect();

        foreach ($tenants as $tenant) {
            // Verificar límite de consultas del bot
            $consultasUsadas = $tenant->getBotConsultasUsadas();
            $limite = $tenant->getBotConsultasLimite();

            // Si tiene límite y ya lo alcanzó, saltar este tenant
            if ($limite && $consultasUsadas >= $limite) {
                $this->log('warning', "⚠️ Tenant {$tenant->nombre_empresa} alcanzó límite de consultas", [
                    'limite' => $limite,
                    'usadas' => $consultasUsadas,
                ]);

                // NOTIFICACIÓN SEGÚN MODO DE EJECUCIÓN:
                if ($this->esEjecucionManual) {
                    // Modo MANUAL: Crear notificación completa para el tenant
                    $this->notificacionesService->crearNotificacion(
                        $tenant->id,
                        'bot_limit_reached',
                        '🚫 Límite de SOIA-Bot alcanzado',
                        "Has alcanzado tu límite de {$limite} consultas al SOIA-Bot este mes. Ya se procesaron {$consultasUsadas} operaciones. Actualiza tu plan para continuar usando el bot.",
                        'error',
                        '#',
                        'Actualizar Plan Ahora',
                        ['consultas_usadas' => $consultasUsadas, 'limite' => $limite]
                    );
                } else {
                    // Modo AUTOMÁTICO: Solo notificar UNA vez
                    $notificacionExistente = NotificacionSistema::where('tenant_id', $tenant->id)
                        ->where('tipo', 'bot_limit_reached')
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->first();

                    if (!$notificacionExistente) {
                        // Primera vez que alcanza el límite este mes → Notificar
                        $this->notificacionesService->crearNotificacion(
                            $tenant->id,
                            'bot_limit_reached',
                            '🚫 Límite de SOIA-Bot alcanzado',
                            "Has alcanzado tu límite de {$limite} consultas al SOIA-Bot este mes. Se omitieron operaciones pendientes. Actualiza tu plan para continuar usando el bot.",
                            'error',
                            '#',
                            'Actualizar Plan Ahora',
                            ['consultas_usadas' => $consultasUsadas, 'limite' => $limite, 'modo' => 'automatico']
                        );

                        $this->log('info', "📧 Notificación única enviada a {$tenant->nombre_empresa} por límite alcanzado (modo automático)");
                    } else {
                        $this->log('info', "🔕 Notificación omitida para {$tenant->nombre_empresa} (ya fue notificado este mes)");
                    }
                }

                continue;
            }

            // Calcular cuántas consultas restantes tiene
            $consultasRestantes = $limite ? ($limite - $consultasUsadas) : null;

            // Obtener operaciones pendientes de este tenant
            $opsTenant = Operacion::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->whereNotNull('num_doda')
                ->where('num_doda', '!=', '')
                ->where(function ($query) {
                    $query->whereNull('modulacion')
                        ->orWhere('modulacion', '')
                        ->orWhere('modulacion', '0')
                        ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                        ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado')
                        ->orWhere('modulacion', 'ERROR DODA NO COINCIDE');
                })
                ->with(['cliente', 'aduana', 'patente', 'tenant'])
                ->get();

            // Si hay límite, limitar las operaciones a procesar
            if ($consultasRestantes !== null && $opsTenant->count() > $consultasRestantes) {
                $opsTenant = $opsTenant->take($consultasRestantes);

                $this->log('info', "📊 Limitando operaciones del tenant {$tenant->nombre_empresa}", [
                    'disponibles' => $opsTenant->count(),
                    'limite_restante' => $consultasRestantes,
                ]);

                // Notificar que se están limitando operaciones (solo modo manual y si quedan pocas)
                if ($this->esEjecucionManual && $consultasRestantes <= ($limite * 0.2)) {
                    $this->notificacionesService->crearNotificacion(
                        $tenant->id,
                        'bot_near_limit',
                        '⚠️ Últimas consultas disponibles',
                        "Solo te quedan {$consultasRestantes} consultas al SOIA-Bot este mes. Se procesarán {$opsTenant->count()} operaciones ahora.",
                        'warning',
                        '#',
                        'Ver mi Plan'
                    );
                }
            }

            $operacionesPendientes = $operacionesPendientes->merge($opsTenant);
        }

        return $operacionesPendientes;
    }

    /**
     * Preparar las consultas agrupando por DODA único.
     * Cada DODA puede pertenecer a operaciones de distintos tenants/clientes.
     * 
     * La URL de consulta se construye dinámicamente:
     * Primero intenta usar la config del tenant, y si no, usa la config global del .env
     * 
     * @return array [doda => ['url' => ..., 'operaciones' => [...]]]
     */
    protected function prepararConsultas($operaciones): array
    {
        $consultas = [];
        $urlBase = 'https://pecem.mat.sat.gob.mx/app/qr/ce/faces/pages/mobile/validadorqr.jsf';

        foreach ($operaciones as $operacion) {
            $doda = trim($operacion->num_doda);

            if (empty($doda))
                continue;

            if (!isset($consultas[$doda])) {
                // Construir URL
                $tenantConfig = $operacion->tenant->configuracion ?? [];
                $pecemConfig = $tenantConfig['pecem'] ?? [];

                $urlBase = $pecemConfig['url_base'] ?? null;

                if (empty($urlBase)) {
                    $urlBase = config('app.pecem_api_url', env('PECEM_API_URL'));
                }

                if (empty($urlBase)) {
                    $this->log('warning', "⚠️ No se encontró URL base (PECEM_API_URL) para consultar", [
                        'doda' => $doda,
                        'tenant_id' => $operacion->tenant_id,
                    ]);
                    continue;
                }

                // Asegurar formato correcto
                if (str_ends_with($urlBase, 'D3=')) {
                    $url = $urlBase . $doda;
                } else if (strpos($urlBase, '?') !== false) {
                    $url = $urlBase . '&D3=' . $doda;
                } else {
                    $url = $urlBase . '?D3=' . $doda;
                }

                $consultas[$doda] = [
                    'url' => $url,
                    'operaciones' => [],
                ];
            }

            $consultas[$doda]['operaciones'][] = $operacion;
        }

        return $consultas;
    }

    /**
     * Ejecutar las consultas HTTP de forma concurrente usando Guzzle Pool
     */
    protected function ejecutarConsultasConcurrentes(array $consultasPorDoda): void
    {
        $client = new Client([
            'verify' => false,
            'timeout' => 30,
            'connect_timeout' => 10,
            'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=0'],
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'es-MX,es;q=0.9',
            ],
        ]);

        $requests = function () use ($client, $consultasPorDoda) {
            foreach ($consultasPorDoda as $doda => $datos) {
                yield $doda => function () use ($client, $datos) {
                    return $client->getAsync($datos['url']);
                };
            }
        };

        // Concurrencia de 10 para no sobrecargar el servidor PECEM
        $pool = new Pool($client, $requests(), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $doda) use ($consultasPorDoda) {
                $this->totalConsultadas++;
                $this->procesarRespuestaDoda(
                    $doda,
                    $response,
                    $consultasPorDoda[$doda]['operaciones']
                );
            },
            'rejected' => function ($reason, $doda) {
                $this->totalErrores++;
                $this->log('error', "✗ Error al consultar DODA", [
                    'doda' => $doda,
                    'error' => (string) $reason,
                ]);
                $this->resultados[] = [
                    'doda' => $doda,
                    'status' => 'error',
                    'error' => (string) $reason,
                ];
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();
    }

    /**
     * Procesar la respuesta HTML del PECEM para un DODA específico
     */
    protected function procesarRespuestaDoda(string $doda, $response, array $operaciones): void
    {
        try {
            $html = (string) $response->getBody();
            $statusCode = $response->getStatusCode();

            $this->log('info', "📡 Respuesta recibida para DODA", [
                'doda' => $doda,
                'status_code' => $statusCode,
                'html_length' => strlen($html),
            ]);

            // Extraer datos del HTML (Full Scraping)
            $datosExtraidos = $this->extraerDatosCompletos($html);
            $nuevoEstatus = $datosExtraidos['modulacion'] ?? 'DODA no presentado al Mecanismo de Selección Automatizado';

            $this->log('info', "📋 Datos extraídos", [
                'doda' => $doda,
                'estatus' => $nuevoEstatus,
                'datos_completos' => !empty($datosExtraidos['integracion']),
            ]);

            // Procesar CADA operación asociada a este DODA
            foreach ($operaciones as $operacion) {
                $this->procesarOperacion($operacion, $nuevoEstatus, $datosExtraidos, $doda);
            }

            $this->resultados[] = [
                'doda' => $doda,
                'status' => 'success',
                'estatus_detectado' => $nuevoEstatus,
                'operaciones_procesadas' => count($operaciones),
            ];

        } catch (Exception $e) {
            $this->totalErrores++;
            $this->log('error', "✗ Error procesando respuesta DODA", [
                'doda' => $doda,
                'error' => $e->getMessage(),
            ]);
            $this->resultados[] = [
                'doda' => $doda,
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Procesar una operación individual con el nuevo estatus
     */
    protected function procesarOperacion(Operacion $operacion, string $nuevoEstatus, array $datosExtraidos, string $doda): void
    {
        $estatusAnterior = $operacion->modulacion;

        // Recargar relaciones necesarias para la validación
        if (!$operacion->relationLoaded('expediente') || !$operacion->relationLoaded('aduana') || !$operacion->relationLoaded('patente')) {
            $operacion->load(['expediente', 'aduana', 'patente', 'cliente', 'tenant']);
        }

        $erroresValidacion = $this->validarCoincidenciaOperacion($operacion, $datosExtraidos);

        if (!empty($erroresValidacion)) {
            $this->log('warning', "⚠️ Inconsistencia detectada en DODA", [
                'operacion_id' => $operacion->id,
                'doda' => $doda,
                'errores' => $erroresValidacion
            ]);

            // Solo registramos el historial pero NO actualizamos modulación de forma definitiva
            // y NO enviamos notificaciones externas al cliente.
            $this->registrarHistorial($operacion, $doda, $estatusAnterior, 'INCONSISTENCIA DODA', true, [
                'errores_validacion' => $erroresValidacion,
                'datos_scraped' => $datosExtraidos
            ]);

            // Actualizamos la operación para que el dashboard muestre el error inmediatamente (Placa roja)
            $botLogs = $operacion->bot_logs_json ?? [];
            $botLogs[] = [
                'timestamp' => now()->toIso8601String(),
                'execution_id' => $this->executionId,
                'status' => 'error_inconsistencia',
                'errores' => $erroresValidacion,
                'scraped_data' => $datosExtraidos,
            ];
            if (count($botLogs) > 50)
                $botLogs = array_slice($botLogs, -50);

            Operacion::withoutGlobalScope('tenant')
                ->where('id', $operacion->id)
                ->update([
                    'modulacion' => 'ERROR DODA NO COINCIDE',
                    'ultimo_scraping_at' => now(),
                    'bot_logs_json' => json_encode($botLogs)
                ]);

            // Notificamos internamente del error SOLO la primera vez para no generar spam
            if ($estatusAnterior !== 'ERROR DODA NO COINCIDE') {
                $this->notificacionService->notificarInconsistenciaDoda($operacion, $erroresValidacion, $doda, $this->executionId);
            }

            // IMPORTANTE: NO contar esta consulta contra el límite del tenant
            // Los errores de validación no deben consumir crédito del tenant
            $this->log('info', "❌ Consulta NO contada para tenant {$operacion->tenant_id} (error de validación)");
            return; // Detenemos el flujo normal
        }

        // Si no hay inconsistencias, comprobamos si hubo cambio real
        $huboCambio = $this->detectarCambio($estatusAnterior, $nuevoEstatus);

        $this->log('info', "🔄 Procesando operación", [
            'operacion_id' => $operacion->id,
            'tenant_id' => $operacion->tenant_id,
            'doda' => $doda,
            'estatus_anterior' => $estatusAnterior,
            'estatus_nuevo' => $nuevoEstatus,
            'hubo_cambio' => $huboCambio,
        ]);

        // Trackear consulta por tenant SOLO si es una operación válida
        $tenantId = $operacion->tenant_id;
        if (!isset($this->consultasPorTenant[$tenantId])) {
            $this->consultasPorTenant[$tenantId] = 0;
        }
        $this->consultasPorTenant[$tenantId]++;

        // 1. Registrar en historial SIEMPRE (para auditoría)
        $this->registrarHistorial($operacion, $doda, $estatusAnterior, $nuevoEstatus, $huboCambio, $datosExtraidos);

        // 2. Actualizar la operación
        $updateData = [
            'modulacion' => $nuevoEstatus,
            'ultimo_scraping_at' => now(),
        ];

        if ($huboCambio && $this->esEstatusDefinitivo($nuevoEstatus)) {
            $updateData['modulacion_detectada_at'] = now();
            $updateData['fecha_modulacion'] = now();
        }

        // Agregar al JSON de logs
        $botLogs = $operacion->bot_logs_json ?? [];
        $botLogs[] = [
            'timestamp' => now()->toIso8601String(),
            'execution_id' => $this->executionId,
            'status' => 'success',
            'scraped_data' => $datosExtraidos,
        ];

        // Mantener solo los últimos 50 logs para no crecer indefinidamente
        if (count($botLogs) > 50) {
            $botLogs = array_slice($botLogs, -50);
        }

        $updateData['bot_logs_json'] = json_encode($botLogs);

        // Usar withoutGlobalScope para actualizar sin restricción de tenant
        Operacion::withoutGlobalScope('tenant')
            ->where('id', $operacion->id)
            ->update($updateData);

        // 3. Si hubo cambio, disparar notificaciones (Solo internas y cliente externo)
        if ($huboCambio) {
            $this->totalCambios++;
            $this->log('info', "🔔 Cambio detectado - Disparando notificaciones", [
                'operacion_id' => $operacion->id,
                'tenant_id' => $operacion->tenant_id,
                'cliente_id' => $operacion->cliente_id,
                'de' => $estatusAnterior,
                'a' => $nuevoEstatus,
            ]);

            // Recargar la operación actualizada con sus relaciones
            $operacion->refresh();
            $operacion->load(['cliente', 'tenant', 'aduana', 'patente']);

            $this->notificacionService->procesarNotificacionModulacion(
                $operacion,
                $nuevoEstatus,
                $estatusAnterior,
                $this->executionId
            );
        }
    }

    /**
     * Extraer datos completos del HTML del PECEM (Full Scraping)
     */
    protected function extraerDatosCompletos(string $html): array
    {
        $datos = [
            'integracion' => null,
            'modulacion' => null,
            'tipo_pedimento' => null,
            'pedimento' => null,
            'clave_pedimento' => null,
            'tipo_operacion' => null,
            'vehiculo' => null,
            'cantidad_mercancia' => null,
            'pago' => [
                'linea_captura' => null,
                'banco' => null,
                'fecha' => null,
                'operacion_bancaria' => null,
                'transaccion_sat' => null,
                'prevalidador' => null,
            ],
            'contenedores' => [],
            'candados' => [],
            'carta_porte' => null,
            'remesas' => null,
            'cove' => null,
        ];

        // 1. Extraer modulación (patrón principal)
        preg_match_all('/\*\*\*([A-Z ]{10,50})\*\*\*/', $html, $matchesModulacion);
        if (!empty($matchesModulacion[1])) {
            $datos['modulacion'] = trim(last($matchesModulacion[1]));
        }

        // 2. Extraer número de integración
        if (preg_match('/N[uú]mero\s+de\s+Integraci[oó]n[:\s]*(\d+)/i', $html, $m)) {
            $datos['integracion'] = $m[1];
        }

        // 3. Extraer datos del pedimento
        if (preg_match('/Tipo\s+de\s+Pedimento[:\s]*([A-Z0-9\-\s]+)/i', $html, $m)) {
            $datos['tipo_pedimento'] = trim($m[1]);
        }

        // Modificado para buscar el patrón del pedimento (ej: 300-1068-6009954/15 ó "16 43 3009 6009954")
        // Como el HTML usa datatables, "Pedimento:" y su valor están separados por muchas etiquetas
        $textForRegex = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
        if (preg_match('/(\d{2,3}(?:\s\d{2})?[\s\-]\d{4}[\s\-]\d{7}(?:\/\d+)?)/', $textForRegex, $m)) {
            $datos['pedimento'] = trim($m[1]);
        }

        if (preg_match('/Clave\s+de\s+Pedimento[:\s]*([A-Z0-9]+)/i', $html, $m)) {
            $datos['clave_pedimento'] = trim($m[1]);
        }

        // 4. Extraer info financiera
        if (preg_match('/L[ií]nea\s+de\s+Captura[:\s]*([A-Z0-9\-]+)/i', $html, $m)) {
            $datos['pago']['linea_captura'] = trim($m[1]);
        }

        if (preg_match('/Instituci[oó]n\s+Bancaria[:\s]*([A-Za-z\s]+)/i', $html, $m)) {
            $datos['pago']['banco'] = trim($m[1]);
        }

        if (preg_match('/Fecha\s+y\s+Hora\s+del?\s+Pago[:\s]*([\d\-\/\s:]+)/i', $html, $m)) {
            $datos['pago']['fecha'] = trim($m[1]);
        }

        if (preg_match('/N[uú]mero\s+de\s+Operaci[oó]n\s+Bancaria[:\s]*([A-Z0-9]+)/i', $html, $m)) {
            $datos['pago']['operacion_bancaria'] = trim($m[1]);
        }

        if (preg_match('/N[uú]mero\s+de\s+Transacci[oó]n\s+SAT[:\s]*([A-Z0-9]+)/i', $html, $m)) {
            $datos['pago']['transaccion_sat'] = trim($m[1]);
        }

        if (preg_match('/Clave\s+del?\s+Prevalidador[:\s]*([A-Z0-9]+)/i', $html, $m)) {
            $datos['pago']['prevalidador'] = trim($m[1]);
        }

        // 5. Extraer vehículo
        if (preg_match('/Datos\s+de\s+Identificaci[oó]n\s+del?\s+Veh[ií]culo[:\s]*([A-Z0-9\-\s]+)/i', $html, $m)) {
            $datos['vehiculo'] = trim($m[1]);
        }

        // 6. CFDI / Carta Porte UUID
        if (preg_match('/([0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12})/', $html, $m)) {
            $datos['carta_porte'] = strtoupper(trim($m[1]));
        }

        // 7. Tipo de operación
        if (preg_match('/Tipo\s+de\s+Operaci[oó]n[:\s]*([A-Z\s]+)/i', $html, $m)) {
            $datos['tipo_operacion'] = trim($m[1]);
        }

        return $datos;
    }

    /**
     * Detectar si hubo un cambio significativo en la modulación
     */
    protected function detectarCambio(?string $anterior, string $nuevo): bool
    {
        // Si no tenía modulación y ahora sí, es cambio
        if (empty($anterior) || $anterior === '0' || $anterior === 'DODA no presentado al Mecanismo de Selección Automatizado') {
            return $nuevo !== 'DODA no presentado al Mecanismo de Selección Automatizado';
        }

        // Si cambió de valor, es cambio
        return strtoupper(trim($anterior)) !== strtoupper(trim($nuevo));
    }

    /**
     * Validar si los datos extraídos concuerdan con la operación en base de datos.
     * Retorna un arreglo de errores (vacío si está todo bien).
     */
    protected function validarCoincidenciaOperacion(Operacion $operacion, array $datosExtraidos): array
    {
        $errores = [];
        $pedimentoPcem = $datosExtraidos['pedimento'] ?? null;

        if ($pedimentoPcem) {
            // Normalizar la cadena para separar por guiones, diagonales o espacios
            // "300-1068-6009954/15" o "16 43 3009 6009954"
            $partes = preg_split('/[\s\-\/]+/', $pedimentoPcem);

            // Si el formato es "300-1068-6009954/15" nos dará: [300, 1068, 6009954, 15]
            // Si el formato es "16 43 3009 6009954" nos dará: [16, 43, 3009, 6009954] 
            // Tomamos los últimos componentes antes de una posible remesa

            // Caso 3 partes identificables (Aduana, Patente, Pedimento)
            if (count($partes) >= 3) {
                // Heurística simple: 
                // En "300 1068 6009954 15", pos 0 es aduana, pos 1 es patente, pos 2 es pedimento
                // En "16 43 3009 6009954" (Aduana secc, patente año, numero), nos interesa aduana (16 o 164), patente (3009), y numero (6009954)

                $esFormatoConAnio = (count($partes) >= 4 && strlen($partes[0]) <= 2);

                if ($esFormatoConAnio) {
                    // 16 43 3009 6009954 (0: aduana(secc), 1: año, 2: patente, 3: num)
                    $aduanaPcem = trim($partes[0]);
                    $patentePcem = trim($partes[2]);
                    $pedimentoNumeroPcem = trim($partes[3]);
                } else {
                    // 300-1068-6009954/15 (0: aduana, 1: patente, 2: num, 3: remesa)
                    $aduanaPcem = trim($partes[0]);
                    $patentePcem = trim($partes[1]);
                    $pedimentoNumeroPcem = trim($partes[2]);
                }

                $aduanaOp = $operacion->aduana ? (string) $operacion->aduana->clave : null;
                $patenteOp = $operacion->patente ? (string) $operacion->patente->numero : null;
                $pedimentoOp = $operacion->expediente ? (string) $operacion->expediente->numero_pedimento : null;

                // Validación de Aduana
                if ($aduanaOp && strpos($aduanaPcem, $aduanaOp) === false && strpos($aduanaOp, $aduanaPcem) === false) {
                    $errores[] = "Aduana no coincide (Operación: $aduanaOp, SAT: $aduanaPcem)";
                }

                // Validación de Patente
                if ($patenteOp && $patenteOp != $patentePcem) {
                    // A veces la patente tiene padding de ceros
                    if (ltrim($patenteOp, '0') != ltrim($patentePcem, '0')) {
                        $errores[] = "Patente no coincide (Operación: $patenteOp, SAT: $patentePcem)";
                    }
                }

                // Validación de Pedimento
                if ($pedimentoOp && $pedimentoOp != $pedimentoNumeroPcem) {
                    if (strpos($pedimentoOp, $pedimentoNumeroPcem) === false && strpos($pedimentoNumeroPcem, ltrim($pedimentoOp, '0')) === false) {
                        $errores[] = "Número de Pedimento no coincide (Operación: $pedimentoOp, SAT: $pedimentoNumeroPcem)";
                    }
                }
            }
        }

        return $errores;
    }

    /**
     * Verificar si un estatus es definitivo (merece notificación)
     */
    protected function esEstatusDefinitivo(string $estatus): bool
    {
        return in_array(strtoupper(trim($estatus)), [
            'DESADUANAMIENTO LIBRE',
            'RECONOCIMIENTO ADUANERO',
            'RECONOCIMIENTO ADUANERO CONCLUIDO',
        ]);
    }

    /**
     * Registrar en la tabla operacion_historial_doda
     */
    protected function registrarHistorial(
        Operacion $operacion,
        string $doda,
        ?string $estatusAnterior,
        string $estatusNuevo,
        bool $huboCambio,
        array $datosExtraidos
    ): void {
        try {
            OperacionHistorialDoda::withoutGlobalScope('tenant')->create([
                'operacion_id' => $operacion->id,
                'tenant_id' => $operacion->tenant_id,
                'doda' => $doda,
                'estatus_anterior' => $estatusAnterior,
                'estatus_nuevo' => $estatusNuevo,
                'hubo_cambio' => $huboCambio,
                'respuesta_json' => json_encode($datosExtraidos),
                'execution_id' => $this->executionId,
                'source' => 'bot',
                'consultado_at' => now(),
            ]);
        } catch (Exception $e) {
            $this->log('error', "Error registrando historial", [
                'operacion_id' => $operacion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generar resumen de la ejecución
     */
    protected function generarResumen(float $inicio, string $estado, ?string $error = null): array
    {
        return [
            'execution_id' => $this->executionId,
            'estado' => $estado,
            'timestamp' => now()->toIso8601String(),
            'duracion_segundos' => round(microtime(true) - $inicio, 2),
            'total_consultadas' => $this->totalConsultadas,
            'total_cambios' => $this->totalCambios,
            'total_errores' => $this->totalErrores,
            'resultados' => $this->resultados,
            'error' => $error,
        ];
    }

    /**
     * Helper para logging consistente
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        $context['execution_id'] = $this->executionId;

        try {
            Log::channel($this->logChannel)->$level($message, $context);
        } catch (Exception $e) {
            // Si el canal no existe, usar el default
            Log::$level("[DODA_BOT] {$message}", $context);
        }
    }
}
