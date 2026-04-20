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
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'es_trial' => 'boolean',
        'configuracion' => 'array',
        'bot_config' => 'array',
        'features_enabled' => 'array',
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
     * 
     * @param int $cantidad Cantidad de consultas a agregar (default: 1)
     */
    public function incrementarBotConsultas(int $cantidad = 1): void
    {
        $config = $this->getConfig();
        $periodoActual = now()->format('Y-m');

        // Si es un nuevo mes, resetear el contador
        $periodoAlmacenado = $config['bot']['consultas_mes_periodo'] ?? null;
        if ($periodoAlmacenado !== $periodoActual) {
            $config['bot']['consultas_mes'] = 0;
            $config['bot']['consultas_mes_periodo'] = $periodoActual;
        }

        // Incrementar el contador
        $config['bot']['consultas_mes'] = ($config['bot']['consultas_mes'] ?? 0) + $cantidad;

        $this->update(['configuracion' => $config]);
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
        $this->trial_ends_at = now()->addDays(7);
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
            'features_enabled' => ['basic_dashboard', 'email_notifications'],
        ];

        $this->configuracion = $config;
        $this->max_usuarios = 1;
        $this->max_operaciones_mes = 20;
        $this->plan = 'basico';
        $this->estado = 'activo';
        $this->es_trial = true;
        $this->fecha_inicio = now();
        $this->fecha_vencimiento = now()->addDays(7);
    }
}