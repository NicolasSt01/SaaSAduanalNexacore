<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReporteAcceso extends Model
{
    //
    protected $table = 'reportes_acceso';
    
    protected $fillable = [
        'cliente_id',
        'token',
        'fecha_desde',
        'fecha_hasta',
        'expira_en',
        'accesos',
        'ultimo_acceso'
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
        'expira_en' => 'datetime',
        'ultimo_acceso' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Generar token único y seguro
     */
    public static function generarToken(): string
    {
        do {
            $token = Str::random(64);
        } while (self::where('token', $token)->exists());
        
        return $token;
    }

    /**
     * Verificar si el token está vigente
     */
    public function estaVigente(): bool
    {
        return $this->expira_en->isFuture();
    }

    /**
     * Registrar acceso
     */
    public function registrarAcceso()
    {
        $this->increment('accesos');
        $this->update(['ultimo_acceso' => now()]);
    }
}
