<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class Importador extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $table = 'importadores';
    protected $fillable = [
        'tenant_id',
        'nombre',
        'tax_id',
        'rfc',
        'pais'
    ];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i',
        'updated_at' => 'datetime:d/m/Y H:i',
    ];
}