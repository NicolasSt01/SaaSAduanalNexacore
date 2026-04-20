@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('finanzas.index', ['year' => $year, 'semana' => $semana]) }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Resumen
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="javascript:history.back()" class="text-decoration-none">
                    Cliente-Patente
                </a>
            </li>
            <li class="breadcrumb-item active">Pedimento #{{ $expediente->numero_pedimento }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-dark text-white">
                <div class="card-body">
                    
                    <h3 class="mb-2 fw-bold">
                        <i class="fas fa-file-invoice me-2"></i>
                        Pedimento #{{ substr($year, -2) }} {{$expediente->aduana->clave_aduana}} {{ $expediente->patente->numero_patente }} {{ $expediente->numero_pedimento }}
                    </h3>
                    <p class="mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        Semana {{ $semana }} del {{ $year }} 
                        ({{ $fechaInicio->format('d/m/Y') }} - {{ $fechaFin->format('d/m/Y') }})
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Fechas del Expediente -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-calendar-alt text-success me-2"></i>Fecha de Apertura
                    </h6>
                    <h4 class="mb-0 fw-bold">
                        {{ $expediente->fecha_apertura ? \Carbon\Carbon::parse($expediente->fecha_apertura)->format('d/m/Y') : 'No registrada' }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-calendar-check text-danger me-2"></i>Fecha de Cierre
                    </h6>
                    <h4 class="mb-0 fw-bold">
                        {{ $expediente->fecha_cierre ? \Carbon\Carbon::parse($expediente->fecha_cierre)->format('d/m/Y') : 'No registrada' }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                    <h2 class="fw-bold mb-0">{{ $totalTramites }}</h2>
                    <small>Total Trámites</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h2 class="fw-bold mb-0">{{ $verdes }}</h2>
                    <small>Verdes</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h2 class="fw-bold mb-0">{{ $rojos }}</h2>
                    <small>Rojos</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-weight-hanging fa-2x mb-2"></i>
                    <h2 class="fw-bold mb-0">{{ $sobrepesos }}</h2>
                    <small>Sobrepesos</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-balance-scale fa-2x mb-2"></i>
                    <h2 class="fw-bold mb-0">{{ $taras }}</h2>
                    <small>Taras</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-plus-circle fa-2x mb-2"></i>
                    <h2 class="fw-bold mb-0">
                        @php
                         $cantidadConceptos = 0;
                            if (isset($detalleAdicionalesFiltrados) && is_array($detalleAdicionalesFiltrados)) {
                                foreach ($detalleAdicionalesFiltrados as $concepto) {
                                 $cantidadConceptos += $concepto['cantidad'];
                                }
                            }
                        @endphp
                        {{ $cantidadConceptos }}
                    </h2>
                    <small>Adicionales</small>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Conceptos Adicionales -->
    @if(isset($detalleAdicionales) && !empty($detalleAdicionales))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-dollar-sign me-2 text-success"></i>
                    Conceptos Adicionales del Expediente
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($detalleAdicionales as $tipo => $info)
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="card-body text-white">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0 fw-bold">
                                            <i class="fas fa-tag me-2"></i>
                                            {{ $info['nombre'] }}
                                        </h6>
                                        <span class="badge bg-white text-dark fw-bold">
                                            {{ $info['cantidad'] }}x
                                        </span>
                                    </div>
                                    {{--<h3 class="mb-0 fw-bold">
                                        ${{ number_format($info['monto'], 2) }}
                                    </h3>
                                    <small class="opacity-75">
                                        ${{ number_format($info['monto'] / $info['cantidad'], 2) }} c/u
                                    </small>--}}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Totales -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-0 bg-dark text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 opacity-75">Total Conceptos Adicionales</h6>
                                        <small class="opacity-50">{{ $cantidadConceptos }} conceptos registrados</small>
                                    </div>
                                    {{--<h2 class="mb-0 fw-bold">
                                        
                                        ${{ number_format($totalAdicionales ?? 0, 2) }}
                                    </h2>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info mb-4 border-0 shadow-sm">
            <i class="fas fa-info-circle me-2"></i>
            No hay conceptos adicionales registrados para este expediente.
        </div>
    @endif

    <!-- Tabla de Operaciones -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-list-ul me-2 text-primary"></i>
                Operaciones Registradas ({{ $totalTramites }})
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>#Ref</th>
                            <th>Fecha</th>
                            <th>Factura</th>
                            <th>Producto</th>
                            <th>Cliente</th>
                            <th>Economico</th>
                            
                            <th>DODA</th>
                            <th>Sobrepeso</th>
                            <th class="text-center">Modulación</th>
                            
                            <th class="text-center">Adicionales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($operaciones as $index => $op)
                            @php
                                // Contar conceptos adicionales de esta operación específica
                                $conceptosOp = $op->conceptosAdicionales->count();
                            @endphp
                            <tr>
                                <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $op->referencia }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($op->fecha)->format('d/m/Y') }}
                                    </small>
                                </td>
                                
                                <td>
                                    <strong>{{ $op->num_factura }}</strong>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $op->nombre_producto }}">
                                        {{ $op->nombre_producto }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $op->cliente->nombre_empresa ?? 'N/A' }}</small>
                                </td>
                                {{--<td>
                                    <code class="bg-light p-1 rounded">{{ $op->num_thermo ?? 'N/A' }}</code>
                                </td>--}}
                                <td>
                                    <code class="bg-light p-1 rounded">{{ $op->codigo_alpha ?? 'N/A' }}</code>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $op->num_doda ?? 'N/A' }}</small>
                                </td>
                                {{-- sobrepesos --}}
                                <td class="text-center fw-bold">
                                    @if($op->sobrepeso == 1)
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Sobrepeso
                                        </span>
                                    @elseif($op->sobrepeso == 0)
                                        <span class="badge bg-secondary">N/A</span>
                                    @else
                                        <code class="bg-light p-1 rounded">{{ $op->sobrepeso ?? 'N/A' }}</code>
                                    @endif
                                </td>
                                
                                {{-- Fin Sobrepesos --}}
                                <td class="text-center">
                                    @if($op->modulacion == 'DESADUANAMIENTO LIBRE')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Verde
                                        </span>
                                    @elseif($op->modulacion == 'RECONOCIMIENTO ADUANERO CONCLUIDO')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Rojo
                                        </span>
                                    @elseif($op->modulacion == 'RECONOCIMIENTO ADUANERO')
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock me-1"></i>En Proceso
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-question me-1"></i>Sin Módulo
                                        </span>
                                    @endif
                                </td>
                                {{--<td>
                                    <small>{{ $op->bodega->nombre_bodega ?? 'N/A' }}</small>
                                </td>--}}
                                <td class="text-center">
                                    @if($conceptosOp > 0)
                                        <button class="btn btn-sm btn-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#conceptosModal{{ $op->id }}">
                                            <i class="fas fa-eye me-1"></i>{{ $conceptosOp }}
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">No hay operaciones registradas para este expediente en la semana seleccionada.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

        <!-- Modales de Conceptos por Operación -->
        @foreach($operaciones as $op)
            @if($op->conceptosAdicionales->count() > 0)
                <div class="modal fade" id="conceptosModal{{ $op->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content rounded-4 shadow-lg">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-dollar-sign me-2"></i>
                                    Conceptos Adicionales - Operación REF#{{ $op->referencia }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <strong>Economico:</strong> {{ $op->num_thermo }} |
                                        <strong>Alpha:</strong> {{ $op->codigo_alpha }} |
                                        <strong>Factura:</strong> {{ $op->num_factura }}
                                    </small>
                                </div>

                                <div class="list-group">
                                    @foreach($op->conceptosAdicionales as $concepto)
                                        <div class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1">
                                                                <i class="fas fa-tag text-success me-2"></i>
                                                                {{ ucfirst(str_replace('_', ' ', $concepto->tipo_concepto)) }}
                                                            </h6>
                                                            <span
                                                                class="badge bg-{{ $concepto->ambito == 'camion' ? 'primary' : 'secondary' }}">
                                                                {{ $concepto->ambito == 'camion' ? 'Por Camión' : 'Por Operación' }}
                                                            </span>
                                                            @if($concepto->descripcion)
                                                                <p class="mb-0 mt-2 small text-muted">
                                                                    <i class="fas fa-comment-dots me-1"></i>
                                                                    {{ $concepto->descripcion }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Vista Previa del Documento -->
                                                <div class="col-md-2">
                                                    @php
                                                        $documento = $concepto->documentos->first();
                                                    @endphp

                                                    @if($documento)
                                                        <div class="text-center">
                                                            @php
                                                                $extension = strtolower(pathinfo($documento->ruta_archivo, PATHINFO_EXTENSION));
                                                                $rutaArchivo = route('documentos.preview', $documento);
                                                            @endphp

                                                            @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                                <!-- Vista previa de imagen -->
                                                                <a href="{{ $rutaArchivo }}" target="_blank" class="d-block">
                                                                    <img src="{{ $rutaArchivo }}" alt="Documento" class="img-thumbnail"
                                                                        style="max-height: 100px; width: auto; cursor: pointer;">
                                                                </a>
                                                            @elseif($extension == 'pdf')
                                                                <!-- Icono PDF -->
                                                                <a href="{{ $rutaArchivo }}" target="_blank" class="text-decoration-none">
                                                                    <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                                                    <p class="small mb-0 mt-1">Ver</p>
                                                                </a>
                                                            @else
                                                                <!-- Otros archivos -->
                                                                <a href="{{ $rutaArchivo }}" target="_blank" class="text-decoration-none">
                                                                    <i class="fas fa-file fa-3x text-secondary"></i>
                                                                    <p class="small mb-0 mt-1">Ver archivo</p>
                                                                </a>
                                                            @endif

                                                            <small class="text-muted d-block mt-1">
                                                                {{ $documento->nombre_original }}
                                                            </small>
                                                        </div>
                                                    @else
                                                        <div class="text-center text-muted">
                                                            <i class="fas fa-file-circle-xmark fa-2x"></i>
                                                            <p class="small mb-0 mt-1">Sin documento</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        
    <!-- Botón Volver -->
    <div class="row mt-4">
        <div class="col-12">
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>
</div>

<style>
.table tbody tr {
    transition: all 0.2s ease;
}
.table tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.01);
}
</style>
@endsection