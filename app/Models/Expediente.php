<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToTenant;

class Expediente extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'cliente_id',
        'patente_id',
        'aduana_id',
        'numero_pedimento',
        'tipo_expediente', // Nuevo campo: Unico | Consolidado
        'fecha_pago_pedimento', // Solo Unico
        'fecha_apertura', // Solo Consolidado
        'fecha_cierre', // Solo Consolidado
        'categoria',
        'observaciones',
        'estado', // En proceso | Abierto | Cerrado | Cancelado
        'registrado_por', // Usuario que registró
        'cerrado_por', // Usuario que cerró
        'clave_pedimento', //H1, A1, RT
        'checklist_cumplimiento',
    ];

    protected $casts = [
        'fecha_pago_pedimento' => 'date',
        'fecha_apertura' => 'date',
        'fecha_cierre' => 'date',
        'checklist_cumplimiento' => 'array',
    ];

    protected $dates = ['deleted_at'];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    public function documentador()
    {
        return $this->belongsTo(User::class , 'registrado_por');
    }
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
    public function patente()
    {
        return $this->belongsTo(Patente::class);
    }

    public function aduana()
    {
        return $this->belongsTo(Aduana::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class , 'registrado_por');
    }
    public function cerradoPor()
    {
        return $this->belongsTo(User::class , 'cerrado_por');
    }
    public function documentos()
    {
        return $this->hasMany(Documento::class, 'pedimento_id');
    }
    /* ==========================
     📌 Scopes de utilidad
     ========================== */

    // Expedientes consolidados abiertos
    public function scopeConsolidadosAbiertos($query)
    {
        return $query->where('tipo_expediente', 'Consolidado')
            ->where('estado', 'Abierto');
    }

    // Expedientes que deberían cerrarse (8 días o más abiertos)
    public function scopePorCerrar($query)
    {
        return $query->where('tipo_expediente', 'Consolidado')
            ->where('estado', 'Abierto')
            ->whereDate('fecha_apertura', '<=', now()->subDays(7));
    }

    // Expedientes únicos pendientes de cierre
    public function scopeUnicosPendientes($query)
    {
        return $query->where('tipo_expediente', 'Unico')
            ->where('estado', 'En proceso');
    }
    // Accessor para alerta
    public function getAlertaAttribute()
    {

        // Solo aplica a Consolidados "Abierto" o "En proceso"
        if ($this->tipo_expediente !== 'Consolidado' ||
        !in_array($this->estado, ['Abierto', 'En proceso'])) {
            return null;
        }

        if (empty($this->fecha_apertura)) {
            return 'sin_fecha';
        }

        $fechaApertura = Carbon::parse($this->fecha_apertura);
        $diasTranscurridos = $fechaApertura->diffInDays(now());

        if ($diasTranscurridos >= 6)
            return 'urgente';
        if ($diasTranscurridos >= 5)
            return 'advertencia';
        if ($diasTranscurridos >= 4)
            return 'info';

        return null;
    }
    public function operaciones()
    {
        return $this->hasMany(Operacion::class , 'expediente_id');
    }
    public function getDiasRestantesAttribute()
    {
        if ($this->tipo_expediente === 'Consolidado'
        && in_array($this->estado, ['Abierto', 'En proceso'])
        && !empty($this->fecha_apertura)) {

            $fechaApertura = Carbon::parse($this->fecha_apertura);
            $diasTranscurridos = $fechaApertura->diffInDays(now());

            return max(0, 7 - $diasTranscurridos);
        }

        return null;
    }

    /**
     * Documentos del Expediente Maestro (Permanentes)
     */
    const MAESTRO_DOCS = [
        'acta' => 'Acta Constitutiva',
        'poder' => 'Poder Notarial',
        'identificacion' => 'Identificación Oficial',
        'rfc' => 'Constancia CSF (RFC)',
        'domicilio' => 'Comprobante de Domicilio',
    ];

    /**
     * Documentos por Operación (Transaccionales)
     */
    const OPERACION_DOCS = [
        'factura' => 'Factura Comercial',
        'encargo' => 'Encargo Conferido',
        'transporte' => 'Documentos de Transporte',
        'empaque' => 'Lista de Empaque',
        'origen' => 'Certificado de Origen',
        'rrna' => 'Cumplimiento RRNA\'s',
        'gastos' => 'Gastos Incrementables',
        'doda' => 'DODA / PITA',
        'cupo' => 'Carta de Cupo',
        'val' => 'Certificación de Valor',
    ];

    public function isDocComplete($type)
    {
        // 1. Si se marcó manualmente como completado/no aplica, está listo
        $checklist = $this->checklist_cumplimiento ?? [];
        if (!empty($checklist[$type])) {
            return true;
        }

        // 2. Si es un documento del Expediente Maestro (Cliente)
        // INC-019: Los documentos maestros se consultan del CLIENTE, no del expediente
        if (array_key_exists($type, self::MAESTRO_DOCS)) {
            if (!$this->cliente_id) return false;

            // Usar colección eager-loaded si está disponible (evita N+1)
            if ($this->relationLoaded('cliente') && $this->cliente->relationLoaded('documentosMaestros')) {
                return $this->cliente->documentosMaestros
                    ->where('tipo_documento', $type)
                    ->isNotEmpty();
            }

            // Fallback: query directa a BD
            return Documento::where('cliente_id', $this->cliente_id)
                ->where('tipo_documento', $type)
                ->whereNull('pedimento_id')
                ->exists();
        }

        // 3. Si es un documento por Operación, validamos que TODAS las operaciones lo tengan
        if (array_key_exists($type, self::OPERACION_DOCS)) {
            $totalOps = $this->operaciones()->count();
            
            // Si no hay operaciones, no puede estar completo (a menos que se marque manual)
            if ($totalOps === 0) return false;

            // Contamos cuántas operaciones tienen al menos un documento de este tipo vinculado
            // Nota: Los documentos pueden estar vinculados directamente al expediente o a la operación
            // pero store2 los vincula a ambos (pedimento_id y operacion_id).
            $opsConDoc = $this->operaciones()
                ->whereHas('documentos', function($query) use ($type) {
                    $query->where('tipo_documento', $type);
                })->count();

            return $opsConDoc >= $totalOps;
        }

        // Caso genérico para otros tipos de documentos
        return $this->documentos()->where('tipo_documento', $type)->exists();
    }

    /**
     * Atributo para saber si el cumplimiento digital está al 100%
     */
    public function getCumplimientoCompletoAttribute()
    {
        foreach (self::MAESTRO_DOCS as $key => $label) {
            if (!$this->isDocComplete($key)) return false;
        }

        foreach (self::OPERACION_DOCS as $key => $label) {
            if (!$this->isDocComplete($key)) return false;
        }

        return true;
    }

    /**
     * Lista de leyendas de documentos pendientes
     */
    public function getDocumentosPendientesAttribute()
    {
        $pendientes = [];
        foreach (self::MAESTRO_DOCS as $key => $label) {
            if (!$this->isDocComplete($key)) $pendientes[] = $label;
        }
        foreach (self::OPERACION_DOCS as $key => $label) {
            if (!$this->isDocComplete($key)) {
                $count = $this->operaciones()->count();
                $opsConDoc = $this->operaciones()->whereHas('documentos', function($q) use ($key) {
                    $q->where('tipo_documento', $key);
                })->count();
                
                if ($count > 0 && $opsConDoc < $count) {
                    $pendientes[] = "$label ($opsConDoc/$count ops)";
                } else {
                    $pendientes[] = $label;
                }
            }
        }
        return $pendientes;
    }
}