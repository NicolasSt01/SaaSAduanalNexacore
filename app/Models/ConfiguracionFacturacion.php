<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionFacturacion extends Model
{
    protected $table = 'configuracion_facturacion';

    protected $fillable = [
        'empresa_nombre', 'empresa_rfc',
        'banco_nombre', 'banco_clabe', 'banco_cuenta', 'banco_referencia_prefix',
        'iva_porcentaje', 'email_notificaciones', 'logo_url', 'notas_legales',
    ];

    protected $casts = [
        'iva_porcentaje' => 'integer',
    ];

    public static function get(): self
    {
        return self::first() ?? self::create([
            'empresa_nombre' => 'NexaCore Aduanal',
            'iva_porcentaje' => 8,
            'banco_referencia_prefix' => 'NX',
        ]);
    }
}
