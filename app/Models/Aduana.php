<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aduana extends Model
{
    //
    use HasFactory, SoftDeletes;
    protected $table = 'aduanas';
    protected $fillable = [
        'nombre',
        'clave',
    ];
    protected $dates = ['deleted_at'];

    public function patentes()
    {
        return $this->belongsToMany(Patente::class,'aduana_patente')
        ->withTimestamps();
    }
}
