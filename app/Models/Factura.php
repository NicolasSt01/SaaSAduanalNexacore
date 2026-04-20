<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pedimento_id',
        'cliente_id',
        'patente_id',
        'numero_factura',
        'fecha_factura',
        'monto_total',
        'year',
        'semana',
        'cantidad_tramites',
        'cantidad_rojos',
        'cantidad_sobrepesos',
        'monto_adicionales',
        'notas_adicionales',
        'registrado_por',
        'estado',
    ];

    protected $casts = [
        'fecha_factura' => 'date',
        'monto_total' => 'decimal:2',
        'monto_adicionales' => 'decimal:2',
    ];

    // ==================== RELACIONES ====================

    /**
     * Relación con Expediente
     */
    public function expediente()
    {
        return $this->belongsTo(Expediente::class);
    }

    /**
     * Relación con Cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relación con Patente
     */
    public function patente()
    {
        return $this->belongsTo(Patente::class);
    }

    /**
     * Relación con Usuario que registró
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * Relación con Documentos - TODOS los archivos asociados
     * Una factura puede tener múltiples documentos:
     * - PDFs de factura
     * - XMLs de factura  
     * - Complementos de pago (PDF y XML)
     * - Cualquier otro documento relacionado
     */
    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    // ==================== RELACIONES ESPECÍFICAS DE DOCUMENTOS ====================

    /**
     * Obtener TODOS los documentos PDF de esta factura
     */
    public function documentosPdf()
    {
        return $this->hasMany(Documento::class)
            ->where('tipo_documento', 'LIKE', '%pdf%')
            ->orWhere('tipo_documento', 'LIKE', '%PDF%');
    }

    /**
     * Obtener TODOS los documentos XML de esta factura
     */
    public function documentosXml()
    {
        return $this->hasMany(Documento::class)
            ->where('tipo_documento', 'LIKE', '%xml%')
            ->orWhere('tipo_documento', 'LIKE', '%XML%');
    }

    /**
     * Obtener el primer PDF de factura (para mostrar en vistas)
     */
    public function documentoPdfPrincipal()
    {
        return $this->hasOne(Documento::class)
            ->where('tipo_documento', 'factura_pdf')
            ->latest();
    }

    /**
     * Obtener el primer XML de factura (para mostrar en vistas)
     */
    public function documentoXmlPrincipal()
    {
        return $this->hasOne(Documento::class)
            ->where('tipo_documento', 'factura_xml')
            ->latest();
    }

    /**
     * Obtener documentos de complemento de pago
     */
    public function documentosComplemento()
    {
        return $this->hasMany(Documento::class)
            ->where('tipo_documento', 'LIKE', '%complemento%');
    }

    // ==================== SCOPES ====================

    /**
     * Scope para filtrar por semana
     */
    public function scopePorSemana($query, $year, $semana)
    {
        return $query->where('year', $year)->where('semana', $semana);
    }

    /**
     * Scope para filtrar por cliente y patente
     */
    public function scopePorClientePatente($query, $clienteId, $patenteId)
    {
        return $query->where('cliente_id', $clienteId)
                     ->where('patente_id', $patenteId);
    }

    /**
     * Scope para filtrar por expediente
     */
    public function scopePorExpediente($query, $expedienteId)
    {
        return $query->where('pedimento_id', $expedienteId);
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para facturas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope para facturas facturadas
     */
    public function scopeFacturadas($query)
    {
        return $query->where('estado', 'facturada');
    }

    /**
     * Scope para facturas pagadas
     */
    public function scopePagadas($query)
    {
        return $query->where('estado', 'pagada');
    }

    // ==================== MÉTODOS DE VERIFICACIÓN ====================

    /**
     * Verificar si tiene al menos un archivo PDF
     */
    public function tienePdf()
    {
        return $this->documentos()
            ->where(function($query) {
                $query->where('tipo_documento', 'LIKE', '%pdf%')
                      ->orWhere('tipo_documento', 'LIKE', '%PDF%');
            })
            ->exists();
    }

    /**
     * Verificar si tiene al menos un archivo XML
     */
    public function tieneXml()
    {
        return $this->documentos()
            ->where(function($query) {
                $query->where('tipo_documento', 'LIKE', '%xml%')
                      ->orWhere('tipo_documento', 'LIKE', '%XML%');
            })
            ->exists();
    }

    /**
     * Verificar si tiene complemento de pago
     */
    public function tieneComplemento()
    {
        return $this->documentos()
            ->where('tipo_documento', 'LIKE', '%complemento%')
            ->exists();
    }

    /**
     * Verificar si la factura está completa (tiene PDF y XML)
     */
    public function estaCompleta()
    {
        return $this->tienePdf() && $this->tieneXml();
    }

    // ==================== ATRIBUTOS CALCULADOS ====================

    /**
     * Contar total de documentos
     */
    public function getTotalDocumentosAttribute()
    {
        return $this->documentos()->count();
    }

    /**
     * Contar PDFs
     */
    public function getTotalPdfsAttribute()
    {
        return $this->documentosPdf()->count();
    }

    /**
     * Contar XMLs
     */
    public function getTotalXmlsAttribute()
    {
        return $this->documentosXml()->count();
    }

    /**
     * Obtener clase CSS del badge según el estado
     */
    public function getEstadoBadgeClassAttribute()
    {
        return match($this->estado) {
            'pendiente' => 'bg-secondary',
            'facturada' => 'bg-warning text-dark',
            'pagada' => 'bg-success',
            'complemento_pago' => 'bg-info',
            default => 'bg-secondary'
        };
    }

    /**
     * Obtener texto legible del estado
     */
    public function getEstadoTextoAttribute()
    {
        return match($this->estado) {
            'pendiente' => 'Pendiente',
            'facturada' => 'Facturada',
            'pagada' => 'Pagada',
            'complemento_pago' => 'Complemento de Pago',
            default => 'Pendiente'
        };
    }

    /**
     * Obtener icono según el estado
     */
    public function getEstadoIconoAttribute()
    {
        return match($this->estado) {
            'pendiente' => 'fa-clock',
            'facturada' => 'fa-file-invoice',
            'pagada' => 'fa-check-circle',
            'complemento_pago' => 'fa-file-invoice-dollar',
            default => 'fa-question-circle'
        };
    }

    // ==================== MÉTODOS DE UTILIDAD ====================

    /**
     * Cambiar estado de la factura
     */
    public function cambiarEstado($nuevoEstado)
    {
        $estadosPermitidos = ['pendiente', 'facturada', 'pagada', 'complemento_pago'];
        
        if (in_array($nuevoEstado, $estadosPermitidos)) {
            $this->estado = $nuevoEstado;
            return $this->save();
        }
        
        return false;
    }

    /**
     * Marcar como facturada
     */
    public function marcarComoFacturada()
    {
        return $this->cambiarEstado('facturada');
    }

    /**
     * Marcar como pagada
     */
    public function marcarComoPagada()
    {
        return $this->cambiarEstado('pagada');
    }

    /**
     * Obtener nombre completo del expediente (para mostrar en vistas)
     */
    public function getNombreExpedienteAttribute()
    {
        return $this->expediente ? $this->expediente->numero_pedimento : 'Sin expediente';
    }

    /**
     * Obtener nombre del cliente (para mostrar en vistas)
     */
    public function getNombreClienteAttribute()
    {
        return $this->cliente ? $this->cliente->nombre_empresa : 'Sin cliente';
    }

    /**
     * Obtener número de patente (para mostrar en vistas)
     */
    public function getNumeroPatenteAttribute()
    {
        return $this->patente ? $this->patente->numero_patente : 'Sin patente';
    }
}