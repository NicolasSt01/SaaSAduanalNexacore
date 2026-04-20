<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificacionSistema extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notificaciones_sistema';

    protected $fillable = [
        'tenant_id',
        'tipo',
        'titulo',
        'mensaje',
        'accion_url',
        'accion_texto',
        'nivel',
        'leida',
        'leida_en',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'leida' => 'boolean',
        'leida_en' => 'datetime',
    ];

    /**
     * Relación con el tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope: Notificaciones no leídas
     */
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    /**
     * Scope: Notificaciones para un tenant específico (incluye globales)
     */
    public function scopeParaTenant($query, $tenantId)
    {
        return $query->where(function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)
              ->orWhereNull('tenant_id'); // Notificaciones globales
        });
    }

    /**
     * Scope: Por nivel de severidad
     */
    public function scopeNivel($query, $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    /**
     * Scope: Por tipo de notificación
     */
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope: Del mes actual
     */
    public function scopeMesActual($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Marcar como leída
     */
    public function marcarLeida(): bool
    {
        return $this->update([
            'leida' => true,
            'leida_en' => now(),
        ]);
    }

    /**
     * Obtener icono según nivel
     */
    public function getIconoAttribute(): string
    {
        return match($this->nivel) {
            'info' => 'fa-info-circle',
            'warning' => 'fa-exclamation-triangle',
            'error' => 'fa-times-circle',
            'success' => 'fa-check-circle',
            default => 'fa-bell',
        };
    }

    /**
     * Obtener color según nivel
     */
    public function getColorAttribute(): string
    {
        return match($this->nivel) {
            'info' => 'blue',
            'warning' => 'amber',
            'error' => 'red',
            'success' => 'emerald',
            default => 'gray',
        };
    }
}
