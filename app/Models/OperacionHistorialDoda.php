<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperacionHistorialDoda extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'operacion_historial_doda';

    protected $fillable = [
        'operacion_id',
        'tenant_id',
        'doda',
        'estatus_anterior',
        'estatus_nuevo',
        'hubo_cambio',
        'respuesta_json',
        'execution_id',
        'source',
        'consultado_at',
    ];

    protected $casts = [
        'hubo_cambio' => 'boolean',
        'respuesta_json' => 'array',
        'consultado_at' => 'datetime',
    ];

    public function operacion()
    {
        return $this->belongsTo(Operacion::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Scope: Solo entradas del bot
     */
    public function scopeDelBot($query)
    {
        return $query->where('source', 'bot');
    }

    /**
     * Scope: Solo entradas con cambio detectado
     */
    public function scopeConCambio($query)
    {
        return $query->where('hubo_cambio', true);
    }
}