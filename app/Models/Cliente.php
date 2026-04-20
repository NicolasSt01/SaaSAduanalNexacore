<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class Cliente extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $table = 'cliente';

    protected $fillable = [
        'tenant_id',
        'nombre',
        'rfc',
        'tax_id',
        'correo',
        'telefono',
        'direccion'
    ];
    protected $dates = ['deleted_at'];
    // Relación con operaciones
    public function operaciones()
    {
        return $this->hasMany(Operacion::class , 'cliente_id');
    }
}