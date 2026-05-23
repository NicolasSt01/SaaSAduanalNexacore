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
    // Relación con documentos (Art. 36-A a nivel cliente)
    public function documentos()
    {
        return $this->hasMany(Documento::class, 'cliente_id');
    }
    // Documentos maestros Art. 36-A (sin expediente asociado)
    public function documentosMaestros()
    {
        return $this->documentos()->whereNull('pedimento_id');
    }
}