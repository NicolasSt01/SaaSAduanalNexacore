<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $table = 'facturas';

    protected $fillable = [
        'tenant_id', 'pago_id', 'folio', 'periodo', 'monto', 'estado', 'pdf_path',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }
}
