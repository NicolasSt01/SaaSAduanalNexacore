<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConceptoAdicional extends Model
{
    use HasFactory;

    protected $table = 'conceptos_adicionales';

    protected $fillable = [
        'operacion_id',
        'tipo_concepto',
        'ambito',
        'monto',
        'descripcion'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con la tabla operaciones
     */
    public function operacion()
    {
        return $this->belongsTo(Operacion::class, 'operacion_id');
    }

    /**
     * Scope para filtrar por ámbito de camión
     */
    public function scopeCamion($query)
    {
        return $query->where('ambito', 'camion');
    }

    /**
     * Scope para filtrar por ámbito de operación
     */
    public function scopeOperacion($query)
    {
        return $query->where('ambito', 'operacion');
    }

    /**
     * Scope para filtrar por tipo de concepto
     */
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo_concepto', $tipo);
    }

    /**
     * Formatea el nombre del tipo de concepto para mostrar
     */
    public function getTipoConceptoFormateadoAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->tipo_concepto));
    }
    /**
     * Relacion con Documentos
     */
    public function documentos()
    {
        return $this->hasMany(Documento::class, 'concepto_adicional_id');
    }
}