<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    protected $table = 'planes';

    protected $fillable = [
        'nombre', 'precio_mensual', 'max_usuarios', 'max_operaciones_mes',
        'max_documentos_mes', 'features', 'activo',
    ];

    protected $casts = [
        'precio_mensual' => 'decimal:2',
        'features' => 'array',
        'activo' => 'boolean',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }
}
