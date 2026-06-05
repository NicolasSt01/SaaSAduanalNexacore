<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanCustom extends Model
{
    use SoftDeletes;

    protected $table = 'planes_custom';

    protected $fillable = [
        'nombre', 'descripcion', 'precio_base',
        'max_usuarios', 'max_operaciones_mes', 'max_documentos_mes', 'max_modulaciones_mes',
        'reportes_habilitados', 'features_habilitadas', 'activo',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'reportes_habilitados' => 'array',
        'features_habilitadas' => 'array',
        'activo' => 'boolean',
    ];

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class, 'plan_custom_id');
    }

    public function tenantsActivos(): int
    {
        return $this->suscripciones()->where('estado', 'activa')->distinct('tenant_id')->count();
    }
}
