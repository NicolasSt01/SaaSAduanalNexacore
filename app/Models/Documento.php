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
        'pedimento_id', // Antes pedimento_id
        'operacion_id', // Antes operacion_id
        'factura_id',
        'concepto_adicional_id',
        'nombre', // Antes nombre_documento
        'ruta', // Antes ruta_archivo
        'url_archivo', 
        'peso', 
        'extension',
        'tipo_documento', // Mantener o adaptar
        'created_at',
        'updated_at'
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

    // Scopes
    public function scopeDeOperaciones($query)
    {
        return $query->whereNotNull('operacion_id');
    }

    public function scopeDePedimentos($query)
    {
        return $query->whereNotNull('pedimento_id');
    }

    // Helpers
    public function getUrlAttribute()
    {
        return $this->ruta ? asset('storage/' . $this->ruta) : null;
    }
}