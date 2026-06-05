<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddonContratado extends Model
{
    protected $table = 'addons_contratados';

    protected $fillable = [
        'tenant_id', 'addon_id', 'estado',
        'fecha_inicio', 'fecha_fin',
        'monto_base', 'monto_iva', 'monto_total',
        'referencia_pago', 'approved_by', 'approved_at',
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

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function aprobar(?int $userId = null): void
    {
        $this->update([
            'estado' => 'activo',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addDays(30)->toDateString(),
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now(),
        ]);

        $this->addon->aplicarATenant($this->tenant);
    }

    public function rechazar(?string $motivo = null): void
    {
        $this->update([
            'estado' => 'rechazado',
        ]);
    }

    public function vencer(): void
    {
        $this->update(['estado' => 'vencido']);
        $this->addon->removerDeTenant($this->tenant);
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo' && $this->fecha_fin && now()->lte($this->fecha_fin);
    }

    public function diasRestantes(): ?int
    {
        if (!$this->fecha_fin || !$this->estaActivo()) return null;
        return max(0, (int) now()->startOfDay()->diffInDays($this->fecha_fin, false));
    }

    public static function generarReferencia(): string
    {
        return 'ADD-' . now()->format('Y') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
    }
}
