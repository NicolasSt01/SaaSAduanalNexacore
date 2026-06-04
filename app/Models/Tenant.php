<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'nombre_empresa',
        'rfc',
        'correo_admin',
        'telefono',
        'logo_url',
        'plan',
        'estado',
        'fecha_inicio',
        'fecha_vencimiento',
        'trial_started_at',
        'trial_ends_at',
        'es_trial',
        'max_usuarios',
        'max_operaciones_mes',
        'configuracion',
        'referencia_prefijo',
        'referencia_consecutivo',
        'plan_id', 'renta_mensual', 'periodo_gracia_dias', 'fecha_corte',
        'ultimo_pago_fecha', 'saldo_pendiente',
    ];

    protected $casts = [
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_corte' => 'date',
        'ultimo_pago_fecha' => 'date',
        'configuracion' => 'array',
        'es_trial' => 'boolean',
        'renta_mensual' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Genera la siguiente referencia consecutiva para este tenant.
     * Formato: PREFIJO-YYCONSECUTIVO  Ej: ABC-2600001
     * El año (YY) se obtiene del año actual.
     * El consecutivo se reinicia cada año nuevo.
     */
    public function generarReferencia(): string
    {
        $yearPrefix = date('y'); // 26, 27, etc.

        // Detectar si cambió de año comparando con la última referencia almacenada
        $ultimaOperacion = DB::table('operaciones')
            ->where('tenant_id', $this->id)
            ->whereNotNull('referencia')
            ->orderByDesc('id')
            ->value('referencia');

        $resetear = false;
        if ($ultimaOperacion) {
            // Extraer el año de la última referencia (ej. "ABC-2600003" -> "26")
            $parts = explode('-', $ultimaOperacion);
            $numPart = end($parts);
            $lastYear = substr($numPart, 0, 2);
            if ($lastYear !== $yearPrefix) {
                $resetear = true;
            }
        }

        if ($resetear) {
            DB::table('tenants')
                ->where('id', $this->id)
                ->update(['referencia_consecutivo' => 0]);
        }

        // Incrementar el consecutivo de forma atómica
        DB::table('tenants')
            ->where('id', $this->id)
            ->increment('referencia_consecutivo');

        $this->refresh();
        $consecutivo = $this->referencia_consecutivo;

        $prefijo = strtoupper($this->referencia_prefijo ?? 'REF');
        $numero = $yearPrefix . str_pad($consecutivo, 5, '0', STR_PAD_LEFT);

        return "{$prefijo}-{$numero}";
    }

    // ==========================================
    // MÉTODOS DE CAPACIDADES Y LÍMITES
    // ==========================================

    /**
     * Obtiene la configuración completa del tenant.
     */
    public function getConfig(): array
    {
        return $this->configuracion ?? [];
    }

    /**
     * Actualiza un valor específico en la configuración.
     */
    public function updateConfig(string $key, $value): void
    {
        $config = $this->configuracion ?? [];
        data_set($config, $key, $value);
        $this->update(['configuracion' => $config]);
    }

    /**
     * Verifica si el tenant tiene una feature habilitada.
     */
    public function hasFeature(string $feature): bool
    {
        $config = $this->getConfig();
        $features = $config['features_enabled'] ?? [];
        return in_array($feature, $features);
    }

    /**
     * Obtiene el modo del SOIA-Bot para este tenant.
     * Retorna: 'manual', 'automatico', 'deshabilitado'
     */
    public function getBotMode(): string
    {
        $config = $this->getConfig();
        return $config['bot']['mode'] ?? 'manual';
    }

    /**
     * Verifica si el bot está habilitado para este tenant.
     */
    public function isBotEnabled(): bool
    {
        return $this->getBotMode() !== 'deshabilitado';
    }

    /**
     * Verifica si el bot está en modo automático.
     */
    public function isBotAutomatic(): bool
    {
        return $this->getBotMode() === 'automatico';
    }

    /**
     * Verifica si el tenant está activo.
     */
    public function isActive(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * Verifica si el tenant está suspendido.
     */
    public function isSuspended(): bool
    {
        return $this->estado === 'suspendido';
    }

    /**
     * Suspender el tenant — bloquea acceso a todos sus usuarios.
     */
    public function suspend(): void
    {
        $this->update(['estado' => 'suspendido']);
    }

    /**
     * Reactivar el tenant — restaura acceso a todos sus usuarios.
     */
    public function reactivate(): void
    {
        $this->update(['estado' => 'activo']);
    }

    /**
     * Verifica si el tenant tiene habilitadas las notificaciones por WhatsApp.
     * Controlado por el superadmin desde el panel de capabilities.
     * 
     * El tenant también debe tener una instancia de Evolution API conectada.
     */
    public function whatsappHabilitado(): bool
    {
        // Flag del superadmin en features_enabled
        if (!$this->hasFeature('whatsapp_notifications')) {
            return false;
        }

        // Debe tener instancia configurada y conectada
        $evolutionConfig = $this->configuracion['evolution_api'] ?? [];
        return !empty($evolutionConfig['instance'])
            && !empty($evolutionConfig['connected']);
    }

    /**
     * Obtiene el límite de consultas del bot para este mes.
     */
    public function getBotConsultasLimite(): ?int
    {
        $config = $this->getConfig();
        return $config['bot']['consultas_limite_mes'] ?? null;
    }

    /**
     * Obtiene el uso actual de consultas del bot este mes.
     * Usa el contador directo en la configuración JSON.
     */
    public function getBotConsultasUsadas(): int
    {
        $config = $this->getConfig();
        $periodoActual = now()->format('Y-m');

        // Verificar si el periodo actual es el mismo que el almacenado
        $periodoAlmacenado = $config['bot']['consultas_mes_periodo'] ?? null;

        if ($periodoAlmacenado !== $periodoActual) {
            // Es un nuevo mes, el contador está "virtualmente" en 0
            return 0;
        }

        return $config['bot']['consultas_mes'] ?? 0;
    }

    /**
     * Incrementa el contador de consultas del bot.
     * Usa JSON_SET en MySQL para operación atómica — evita race conditions.
     * 
     * @param int $cantidad Cantidad de consultas a agregar (default: 1)
     */
    public function incrementarBotConsultas(int $cantidad = 1): void
    {
        $periodoActual = now()->format('Y-m');

        // Obtener el periodo almacenado actual (necesario para ver si hay que resetear)
        $periodoAlmacenado = $this->configuracion['bot']['consultas_mes_periodo'] ?? null;

        if ($periodoAlmacenado !== $periodoActual) {
            // Reset: nuevo mes, reiniciar contador
            $this->updateConfig('bot.consultas_mes', $cantidad);
            $this->updateConfig('bot.consultas_mes_periodo', $periodoActual);
        } else {
            // Incremento atómico usando DB::raw sobre el JSON
            DB::table('tenants')
                ->where('id', $this->id)
                ->update([
                    'configuracion' => DB::raw("
                        JSON_SET(
                            configuracion,
                            '$.bot.consultas_mes',
                            COALESCE(JSON_EXTRACT(configuracion, '$.bot.consultas_mes'), 0) + {$cantidad}
                        )
                    "),
                ]);

            // Refrescar el modelo para tener el valor actualizado en memoria
            $this->refresh();
        }
    }

    /**
     * Verifica si el tenant puede realizar más consultas al bot.
     */
    public function canMakeBotConsulta(): bool
    {
        $limite = $this->getBotConsultasLimite();
        if (!$limite)
            return true; // Sin límite

        return $this->getBotConsultasUsadas() < $limite;
    }

    /**
     * Obtiene el límite de un recurso específico.
     */
    public function getLimite(string $recurso): ?int
    {
        $config = $this->getConfig();
        return $config['limites']['recursos'][$recurso] ?? null;
    }

    /**
     * Obtiene el uso actual de un recurso específico.
     */
    public function getUso(string $recurso): int
    {
        return match ($recurso) {
            'clientes' => $this->clientes()->count(),
            'importadores' => $this->importadores()->count(),
            'bodegas' => $this->bodegas()->count(),
            'aduanas' => 0, // Las aduanas son globales, no cuentan para límites por tenant
            'patentes' => $this->patentes()->count(),
            'pedimentos_mes' => \App\Models\Expediente::where('tenant_id', $this->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'documentos_mes' => \App\Models\Documento::where('tenant_id', $this->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'reportes_mes' => 0, // Implementar según necesidad
            'correos_dia' => 0, // Implementar logging de correos
            'whatsapp_mes' => 0, // Implementar logging de WhatsApp
            default => 0,
        };
    }

    // ==========================================
    // TRACKING DE CONSUMO (CORREOS, WHATSAPP)
    // ==========================================

    /**
     * Verifica si el tenant puede enviar un correo hoy.
     */
    public function canSendCorreo(): bool
    {
        $limite = $this->getLimiteFuncionalidad('correos_dia');
        if (!$limite) return true;
        return $this->getCorreosUsadosHoy() < $limite;
    }

    /**
     * Verifica si el tenant puede enviar un WhatsApp este mes.
     */
    public function canSendWhatsapp(): bool
    {
        $limite = $this->getLimiteFuncionalidad('whatsapp_mes');
        if (!$limite) return true;
        return $this->getWhatsappUsadosMes() < $limite;
    }

    /**
     * Obtiene el límite de una funcionalidad específica.
     */
    public function getLimiteFuncionalidad(string $func): ?int
    {
        $config = $this->getConfig();
        return $config['limites']['funcionalidades'][$func] ?? null;
    }

    /**
     * Correos enviados hoy (contador en config JSON).
     */
    public function getCorreosUsadosHoy(): int
    {
        $config = $this->getConfig();
        $hoy = now()->format('Y-m-d');
        $fechaAlmacenada = $config['limites']['funcionalidades']['correos_dia_fecha'] ?? null;

        if ($fechaAlmacenada !== $hoy) return 0;
        return $config['limites']['funcionalidades']['correos_dia_count'] ?? 0;
    }

    /**
     * WhatsApp enviados este mes.
     */
    public function getWhatsappUsadosMes(): int
    {
        $config = $this->getConfig();
        $periodo = now()->format('Y-m');
        $periodoAlmacenado = $config['limites']['funcionalidades']['whatsapp_mes_periodo'] ?? null;

        if ($periodoAlmacenado !== $periodo) return 0;
        return $config['limites']['funcionalidades']['whatsapp_mes_count'] ?? 0;
    }

    /**
     * Incrementa el contador de correos del día.
     */
    public function incrementarConsumoCorreos(int $count = 1): void
    {
        $hoy = now()->format('Y-m-d');
        $fechaAlmacenada = $this->configuracion['limites']['funcionalidades']['correos_dia_fecha'] ?? null;

        if ($fechaAlmacenada !== $hoy) {
            $this->updateConfig('limites.funcionalidades.correos_dia_count', $count);
            $this->updateConfig('limites.funcionalidades.correos_dia_fecha', $hoy);
        } else {
            DB::table('tenants')->where('id', $this->id)->update([
                'configuracion' => DB::raw("JSON_SET(configuracion, '$.limites.funcionalidades.correos_dia_count', COALESCE(JSON_EXTRACT(configuracion, '$.limites.funcionalidades.correos_dia_count'), 0) + {$count})"),
            ]);
            $this->refresh();
        }
    }

    /**
     * Incrementa el contador de WhatsApp del mes.
     */
    public function incrementarConsumoWhatsapp(int $count = 1): void
    {
        $periodo = now()->format('Y-m');
        $periodoAlmacenado = $this->configuracion['limites']['funcionalidades']['whatsapp_mes_periodo'] ?? null;

        if ($periodoAlmacenado !== $periodo) {
            $this->updateConfig('limites.funcionalidades.whatsapp_mes_count', $count);
            $this->updateConfig('limites.funcionalidades.whatsapp_mes_periodo', $periodo);
        } else {
            DB::table('tenants')->where('id', $this->id)->update([
                'configuracion' => DB::raw("JSON_SET(configuracion, '$.limites.funcionalidades.whatsapp_mes_count', COALESCE(JSON_EXTRACT(configuracion, '$.limites.funcionalidades.whatsapp_mes_count'), 0) + {$count})"),
            ]);
            $this->refresh();
        }
    }

    // ==========================================
    // COLA DE PENDIENTES (cuando se excede límite)
    // ==========================================

    /**
     * Agrega una notificación pendiente a la cola del tenant.
     */
    public function addPendingNotification(string $type, array $data): string
    {
        $config = $this->getConfig();
        $id = uniqid('pend_', true);

        $config['pending_notifications'][] = [
            'id' => $id,
            'type' => $type,
            'data' => $data,
            'created_at' => now()->toDateTimeString(),
        ];

        $this->update(['configuracion' => $config]);
        return $id;
    }

    /**
     * Obtiene notificaciones pendientes (opcionalmente filtradas por tipo).
     */
    public function getPendingNotifications(?string $type = null): array
    {
        $config = $this->getConfig();
        $pendientes = $config['pending_notifications'] ?? [];

        if ($type) {
            $pendientes = array_filter($pendientes, fn($p) => $p['type'] === $type);
        }

        return array_values($pendientes);
    }

    /**
     * Elimina una notificación pendiente por ID.
     */
    public function removePendingNotification(string $id): void
    {
        $config = $this->getConfig();
        $config['pending_notifications'] = array_values(array_filter(
            $config['pending_notifications'] ?? [],
            fn($p) => $p['id'] !== $id
        ));
        $this->update(['configuracion' => $config]);
    }

    /**
     * Elimina todas las notificaciones pendientes de un tipo.
     */
    public function clearPendingNotifications(string $type): void
    {
        $config = $this->getConfig();
        $config['pending_notifications'] = array_values(array_filter(
            $config['pending_notifications'] ?? [],
            fn($p) => $p['type'] !== $type
        ));
        $this->update(['configuracion' => $config]);
    }

    /**
     * Cuenta notificaciones pendientes (opcionalmente por tipo).
     */
    public function countPendingNotifications(?string $type = null): int
    {
        return count($this->getPendingNotifications($type));
    }

    /**
     * Verifica si el tenant puede agregar más de un recurso.
     */
    public function canAddResource(string $recurso): bool
    {
        $limite = $this->getLimite($recurso);
        if (!$limite)
            return true; // Sin límite

        $uso = $this->getUso($recurso);
        return $uso < $limite;
    }

    /**
     * Obtiene el porcentaje de uso de un recurso.
     */
    public function getUsoPorcentaje(string $recurso): float
    {
        $limite = $this->getLimite($recurso);
        if (!$limite)
            return 0;

        $uso = $this->getUso($recurso);
        return min(100, ($uso / $limite) * 100);
    }

    /**
     * Obtiene información completa de uso vs límite de un recurso.
     */
    public function getRecursoInfo(string $recurso): array
    {
        $limite = $this->getLimite($recurso);
        $uso = $this->getUso($recurso);

        return [
            'recurso' => $recurso,
            'limite' => $limite,
            'uso' => $uso,
            'disponible' => $limite ? max(0, $limite - $uso) : null,
            'porcentaje' => $limite ? min(100, ($uso / $limite) * 100) : 0,
            'sin_limite' => $limite === null,
        ];
    }

    // ==========================================
    // MÉTODOS DE CONTROL DE ACCESO A REPORTES
    // ==========================================

    /**
     * Lista de todos los reportes disponibles en el sistema
     */
    public static function getAllAvailableReports(): array
    {
        return [
            'clientes' => [
                'name' => 'Reporte de Clientes',
                'description' => 'Listado y estadísticas de clientes',
                'icon' => 'fa-users',
                'color' => 'blue',
                'status' => 'active',
            ],
            'operacion_semanal' => [
                'name' => 'Operación Semanal',
                'description' => 'Resumen semanal de operaciones',
                'icon' => 'fa-calendar-week',
                'color' => 'green',
                'status' => 'active',
            ],
            'remesas' => [
                'name' => 'Reporte de Remesas',
                'description' => 'Control de remesas',
                'icon' => 'fa-money-bill-wave',
                'color' => 'emerald',
                'status' => 'active',
            ],
            'clientes_pdf' => [
                'name' => 'Envío PDF Clientes',
                'description' => 'Envío automático de PDF a clientes',
                'icon' => 'fa-file-pdf',
                'color' => 'red',
                'status' => 'active',
            ],
            'aduanas' => [
                'name' => 'Reporte Aduanas',
                'description' => 'Estadísticas por aduana',
                'icon' => 'fa-building',
                'color' => 'purple',
                'status' => 'active',
            ],
            'patron_clientes' => [
                'name' => 'Patrón de Clientes',
                'description' => 'Análisis de patrones de clientes',
                'icon' => 'fa-chart-line',
                'color' => 'orange',
                'status' => 'active',
            ],
            'financiero' => [
                'name' => 'Reporte Financiero',
                'description' => 'Análisis financiero detallado',
                'icon' => 'fa-chart-pie',
                'color' => 'indigo',
                'status' => 'coming_soon',
            ],
            'logistica' => [
                'name' => 'Logística y Tiempo',
                'description' => 'Análisis de logística y tiempos de entrega',
                'icon' => 'fa-truck',
                'color' => 'teal',
                'status' => 'coming_soon',
            ],
            'pedimentos' => [
                'name' => 'Reporte de Pedimentos',
                'description' => 'Directorio completo de pedimentos y su estado de cumplimiento',
                'icon' => 'fa-file-invoice',
                'color' => 'blue',
                'status' => 'active',
            ],
        ];
    }

    /**
     * Verifica si el tenant tiene acceso a un reporte específico
     */
    public function hasReportAccess(string $reporte): bool
    {
        $config = $this->getConfig();
        $enabled = $config['reportes']['enabled'] ?? [];

        return in_array($reporte, $enabled);
    }

    /**
     * Obtiene todos los reportes habilitados
     */
    public function getEnabledReports(): array
    {
        $config = $this->getConfig();
        return $config['reportes']['enabled'] ?? [];
    }

    /**
     * Obtiene todos los reportes deshabilitados
     */
    public function getDisabledReports(): array
    {
        $config = $this->getConfig();
        return $config['reportes']['disabled'] ?? [];
    }

    /**
     * Verifica si puede generar un reporte específico (alias de hasReportAccess)
     */
    public function canGenerateReport(string $reporte): bool
    {
        return $this->hasReportAccess($reporte);
    }

    /**
     * Obtiene la configuración completa de reportes
     */
    public function getReportConfig(): array
    {
        $config = $this->getConfig();
        return $config['reportes'] ?? [
            'enabled' => [],
            'disabled' => [],
        ];
    }

    /**
     * Obtiene solo los reportes activos (no coming_soon) que están habilitados
     */
    public function getActiveEnabledReports(): array
    {
        $enabledReports = $this->getEnabledReports();
        $allReports = self::getAllAvailableReports();

        $activeReports = [];
        foreach ($enabledReports as $reportId) {
            if (isset($allReports[$reportId]) && $allReports[$reportId]['status'] === 'active') {
                $activeReports[$reportId] = $allReports[$reportId];
            }
        }

        return $activeReports;
    }

    // Relationships (asumiendo que ya existen en otros archivos)
    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    public function importadores()
    {
        return $this->hasMany(Importador::class);
    }

    public function bodegas()
    {
        return $this->hasMany(Bodega::class);
    }

    public function patentes()
    {
        return $this->hasMany(Patente::class);
    }

    // ==========================================
    // MÉTODOS DE TRIAL
    // ==========================================

    /**
     * Verifica si el tenant está en período de trial.
     */
    public function isTrial(): bool
    {
        return $this->es_trial ?? false;
    }

    /**
     * Verifica si el trial ha expirado.
     */
    public function hasTrialExpired(): bool
    {
        if (!$this->isTrial()) {
            return false;
        }

        if (!$this->trial_ends_at) {
            return false;
        }

        return now()->greaterThan($this->trial_ends_at);
    }

    /**
     * Inicia el trial del tenant.
     * Se llama cuando el usuario hace su primer login.
     */
    public function startTrial(): void
    {
        if ($this->trial_started_at) {
            return; // Ya inició el trial
        }

        $this->trial_started_at = now();
        $this->trial_ends_at = now()->addDays(15);
        $this->es_trial = true;
        $this->save();
    }

    /**
     * Aplica la configuración de trial al tenant.
     * Se llama cuando se crea el tenant desde registro público.
     */
    public function applyTrialConfig(): void
    {
        $config = [
            'bot' => [
                'mode' => 'manual',
                'consultas_limite_mes' => 20,
                'consultas_mes' => 0,
                'consultas_mes_periodo' => now()->format('Y-m'),
            ],
            'limites' => [
                'recursos' => [
                    'clientes' => 5,
                    'importadores' => 2,
                    'bodegas' => 1,
                    'aduanas' => 1,
                    'patentes' => 1,
                    'pedimentos_mes' => 20,
                    'documentos_mes' => 40,
                ],
                'funcionalidades' => [
                    'reportes_mes' => 0,
                    'correos_dia' => 10,
                    'whatsapp_mes' => 0,
                ],
            ],
            'reportes' => [
                'enabled' => ['clientes', 'operacion_semanal', 'aduanas', 'pedimentos'],
                'disabled' => ['remesas', 'clientes_pdf', 'patron_clientes', 'financiero', 'logistica'],
            ],
            'features_enabled' => ['email_notifications'],
        ];

        $this->configuracion = $config;
        $this->max_usuarios = 1;
        $this->max_operaciones_mes = 20;
        $this->plan = 'basico';
        $this->estado = 'activo';
        $this->es_trial = true;
        $this->fecha_inicio = now();
        $this->fecha_vencimiento = now()->addDays(15);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    public function estaAlCorriente(): bool
    {
        return $this->saldo_pendiente <= 0;
    }

    public function diasHastaVencimiento(): ?int
    {
        if (!$this->fecha_corte || $this->estaAlCorriente()) return null;
        return max(0, (int) now()->startOfDay()->diffInDays($this->fecha_corte, false));
    }

    public function estaVencido(): bool
    {
        if (!$this->fecha_corte || $this->estaAlCorriente()) return false;
        return now()->startOfDay()->gt($this->fecha_corte->addDays($this->periodo_gracia_dias ?? 5));
    }
}
