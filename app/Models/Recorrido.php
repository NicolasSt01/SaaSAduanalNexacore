<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recorrido extends Model
{
    use HasFactory;

    protected $fillable = [
        'operacion_id',
        'origen',
        'destino',
        'ubicacion',
        'lat',
        'lng',
        'estatus',
        'observacion'
    ];

    public function operacion()
    {
        return $this->belongsTo(Operacion::class);
    }
}
