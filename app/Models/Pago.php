<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'tenant_id', 'monto', 'fecha_pago', 'metodo', 'comprobante',
        'periodo_inicio', 'periodo_fin', 'notas',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function factura()
    {
        return $this->hasOne(Factura::class);
    }
}
