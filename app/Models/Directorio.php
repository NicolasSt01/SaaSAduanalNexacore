<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Directorio extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'directorio';

    protected $fillable = [
        'tenant_id',
        'cliente_id',
        'nombre',
        'puesto',
        'correo',
        'telefono',
        'whatsapp',
        'recibe_notificaciones',
        'canal_preferido',
        'activo',
    ];

    protected $casts = [
        'recibe_notificaciones' => 'boolean',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeRecibeNotificaciones($query)
    {
        return $query->where('recibe_notificaciones', true);
    }
}