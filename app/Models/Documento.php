<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'pedimento_id',
        'operacion_id',
        'factura_id',
        'concepto_adicional_id',
        'cliente_id',
        'nombre',
        'ruta',
        'url_archivo',
        'peso',
        'extension',
        'fecha_vencimiento',
        'tipo_documento',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
    ];

    // Relaciones
    public function pedimento()
    {
        return $this->belongsTo(Expediente::class , 'pedimento_id');
    }

    public function operacion()
    {
        return $this->belongsTo(Operacion::class , 'operacion_id');
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Scopes
    public function scopeDeOperaciones($query)
    {
        return $query->whereNotNull('operacion_id');
    }

    public function scopeDePedimentos($query)
    {
        return $query->whereNotNull('pedimento_id');
    }

    public function scopeDeCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId)->whereNull('pedimento_id');
    }

    // Helpers
    public function getUrlAttribute()
    {
        // INC-001: Priorizar URL de R2 si existe
        if ($this->url_archivo) {
            return $this->url_archivo;
        }

        // Fallback a almacenamiento local legacy
        return $this->ruta ? asset('storage/' . $this->ruta) : null;
    }

    /**
     * Determina si el documento está almacenado en R2.
     */
    public function getEnR2Attribute(): bool
    {
        return !empty($this->url_archivo) && str_starts_with($this->url_archivo, 'https://');
    }

    /**
     * Retorna la URL de preview segura.
     * Si está en R2, retorna la URL pública o firmada.
     */
    public function getUrlPreviewAttribute(): ?string
    {
        if ($this->en_r2) {
            return $this->url_archivo;
        }

        return $this->url;
    }
}