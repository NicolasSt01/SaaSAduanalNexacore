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
            <li class="breadcrumb-item active">Expediente #{{ $expediente->numero_pedimento }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-dark text-white">
                <div class="card-body">
                    <h3 class="mb-2 fw-bold">
                        <i class="fas fa-file-invoice me-2"></i>
                        Expediente #{{ $expediente->numero_pedimento }}
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
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                    <h2 class="fw-bold mb-0">{{ $totalTramites }}</h2>
                    <small>Total Trámites</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h2 class="fw-bold mb-0">{{ $verdes }}</h2>
                    <small>Verdes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h2 class="fw-bold mb-0">{{ $rojos }}</h2>
                    <small>Rojos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-percentage fa-2x mb-2"></i>
                    <h2 class="fw-bold mb-0">
                        {{ $totalTramites > 0 ? round(($rojos / $totalTramites) * 100, 1) : 0 }}%
                    </h2>
                    <small>% Rojos</small>
                </div>
            </div>
        </div>
    </div>

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
                            <th>Fecha</th>
                            <th>Factura</th>
                            <th>Producto</th>
                            <th>Cliente</th>
                            <th>Thermo</th>
                            <th>Alpha</th>
                            <th>DODA</th>
                            <th class="text-center">Modulación</th>
                            <th>Bodega</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($operaciones as $index => $op)
                            <tr>
                                <td class="text-center fw-bold">{{ $index + 1 }}</td>
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
                                <td>
                                    <code class="bg-light p-1 rounded">{{ $op->num_thermo ?? 'N/A' }}</code>
                                </td>
                                <td>
                                    <code class="bg-light p-1 rounded">{{ $op->codigo_alpha ?? 'N/A' }}</code>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $op->num_doda ?? 'N/A' }}</small>
                                </td>
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
                                <td>
                                    <small>{{ $op->bodega->nombre_bodega ?? 'N/A' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
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