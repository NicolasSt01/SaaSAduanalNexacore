<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class Patente extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $table = 'patentes';

    protected $fillable = [
        'tenant_id',
        'numero',
        'nombre',
        'rfc'
    ];

    protected $dates = ['deleted_at'];
    public function aduanas()
    {
        return $this->belongsToMany(Aduana::class , 'aduana_patente')
            ->withTimestamps();
    }
}