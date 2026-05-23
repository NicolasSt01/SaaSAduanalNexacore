<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operacion extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $table = 'operaciones';

    protected $fillable = [
        'tenant_id',
        'referencia',
        'fecha_registro',
        'fecha_cruce_estimada',
        'cliente_id',
        'importador_id',
        'nombre_producto',
        'bodega_id',
        'num_factura',
        'aduana_id',
        'patente_id',
        'expediente_id',
        'num_thermo',
        'codigo_alpha',
        'num_doda',
        'modulacion',
        'fecha_modulacion',
        'usuario_registro_id',
        'usuario_cierre_id',
        'prioridad',
        'estado',
        'observaciones',
        'motivo_cancelacion',
        'fecha_cancelacion',
        'usuario_cancelacion_id',
        'bot_logs_json',
        'ultimo_scraping_at',
        'modulacion_detectada_at',
    ];

    protected $casts = [
        'fecha_registro' => 'date',
        'fecha_cruce_estimada' => 'date',
        'fecha_modulacion' => 'datetime',
        'fecha_cancelacion' => 'datetime',
        'bot_logs_json' => 'array',
        'ultimo_scraping_at' => 'datetime',
        'modulacion_detectada_at' => 'datetime',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class , 'cliente_id');
    }

    public function importador()
    {
        return $this->belongsTo(Importador::class , 'importador_id');
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class);
    }

    public function aduana()
    {
        return $this->belongsTo(Aduana::class);
    }

    public function patente()
    {
        return $this->belongsTo(Patente::class);
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class , 'expediente_id');
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class , 'usuario_registro_id');
    }

    public function usuarioCierre()
    {
        return $this->belongsTo(User::class, 'usuario_cierre_id');
    }

    public function usuarioCancelacion()
    {
        return $this->belongsTo(User::class, 'usuario_cancelacion_id');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class , 'operacion_id');
    }

    public function conceptosAdicionales()
    {
        return $this->hasMany(ConceptoAdicional::class , 'operacion_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class , 'operacion_id');
    }

    public function historialDoda()
    {
        return $this->hasMany(OperacionHistorialDoda::class , 'operacion_id');
    }

    public static function generarSiguienteReferencia(): string
    {
        $tenant = auth()->user()->tenant;
        if ($tenant) {
            return $tenant->generarReferencia();
        }

        // Fallback fallback if no tenant is found
        $ultimaReferencia = self::query()
            ->selectRaw('MAX(CAST(referencia AS UNSIGNED)) as max_ref')
            ->value('max_ref');

        $siguiente = $ultimaReferencia ? (int)$ultimaReferencia + 1 : 1;

        return (string)$siguiente;
    }
}