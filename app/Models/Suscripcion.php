<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suscripcion extends Model
{
    protected $table = 'suscripciones';

    protected $fillable = [
        'tenant_id', 'plan_custom_id', 'estado',
        'fecha_inicio', 'fecha_fin',
        'monto_base', 'monto_iva', 'monto_total',
        'referencia_pago', 'comprobante_path', 'notas',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'monto_base' => 'decimal:2',
        'monto_iva' => 'decimal:2',
        'monto_total' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'approved_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(PlanCustom::class, 'plan_custom_id');
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function aprobar(?int $userId = null): void
    {
        $this->update([
            'estado' => 'activa',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addDays(30)->toDateString(),
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now(),
        ]);

        $this->aplicarPlanATenant();
    }

    public function rechazar(?string $motivo = null): void
    {
        $this->update([
            'estado' => 'rechazada',
            'notas' => $motivo,
        ]);
    }

    public function vencer(): void
    {
        $this->update(['estado' => 'vencida']);
    }

    public function aplicarPlanATenant(): void
    {
        $plan = $this->plan;
        $tenant = $this->tenant;

        $config = $tenant->configuracion ?? [];

        $config['limites']['recursos']['clientes'] = $plan->max_operaciones_mes ? null : null;
        $config['limites']['recursos']['pedimentos_mes'] = $plan->max_operaciones_mes;
        $config['limites']['recursos']['documentos_mes'] = $plan->max_documentos_mes;

        if ($plan->max_modulaciones_mes) {
            $config['bot']['consultas_limite_mes'] = $plan->max_modulaciones_mes;
        }

        $config['reportes']['enabled'] = $plan->reportes_habilitados ?? [];
        $config['reportes']['disabled'] = array_diff(
            array_keys(Tenant::getAllAvailableReports()),
            $config['reportes']['enabled']
        );

        $config['features_enabled'] = $plan->features_habilitadas ?? ['email_notifications'];

        $tenant->update([
            'configuracion' => $config,
            'max_usuarios' => $plan->max_usuarios,
            'max_operaciones_mes' => $plan->max_operaciones_mes,
            'plan_id' => null,
            'estado' => 'activo',
        ]);
    }

    public function estaActiva(): bool
    {
        return $this->estado === 'activa' && $this->fecha_fin && now()->lte($this->fecha_fin);
    }

    public function diasRestantes(): ?int
    {
        if (!$this->fecha_fin || !$this->estaActiva()) return null;
        return max(0, (int) now()->startOfDay()->diffInDays($this->fecha_fin, false));
    }

    public static function generarReferencia(): string
    {
        $prefix = 'NX';
        $config = ConfiguracionFacturacion::first();
        if ($config && $config->banco_referencia_prefix) {
            $prefix = $config->banco_referencia_prefix;
        }
        return $prefix . '-' . now()->format('Y') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
    }
}
