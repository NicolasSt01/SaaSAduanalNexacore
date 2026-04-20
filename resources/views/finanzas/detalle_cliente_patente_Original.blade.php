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
            <li class="breadcrumb-item active">{{ $cliente->nombre_empresa }} - Patente {{ $patente->numero_patente }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 fw-bold">
                                <i class="fas fa-building me-2"></i>{{ $cliente->nombre_empresa }}
                            </h3>
                            <p class="mb-0">
                                <i class="fas fa-file-contract me-2"></i>
                                <strong>Patente:</strong> {{ $patente->numero_patente }}
                                <span class="ms-3">
                                    <i class="fas fa-calendar me-2"></i>
                                    Semana {{ $semana }} del {{ $year }} 
                                    ({{ $fechaInicio->format('d/m/Y') }} - {{ $fechaFin->format('d/m/Y') }})
                                </span>
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('finanzas.exportar.detalle.pdf', [
                                    'clienteId' => $cliente->id, 
                                    'patenteId' => $patente->id,
                                    'year' => $year,
                                    'semana' => $semana
                                ]) }}" 
                               class="btn btn-light">
                                <i class="fas fa-file-pdf me-2"></i>Exportar PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-folder-open fa-2x text-primary mb-2"></i>
                    <h2 class="fw-bold mb-0">{{ $expedientes->count() }}</h2>
                    <small class="text-muted">Expedientes</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-2x text-success mb-2"></i>
                    <h2 class="fw-bold mb-0">{{ $expedientes->sum('total_tramites') }}</h2>
                    <small class="text-muted">Total Trámites</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                    <h2 class="fw-bold mb-0">{{ $expedientes->sum('rojos') }}</h2>
                    <small class="text-muted">Trámites Rojos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Expedientes -->
    <div class="row">
        @forelse($expedientes as $exp)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-file-invoice text-primary me-2"></i>
                            Expediente #{{ $exp['expediente_numero'] }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Fechas -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                <span class="small">
                                    <i class="fas fa-calendar-alt text-success me-1"></i>
                                    <strong>Apertura:</strong>
                                </span>
                                <span class="badge bg-success">
                                    {{ $exp['fecha_apertura'] ? \Carbon\Carbon::parse($exp['fecha_apertura'])->format('d/m/Y') : 'N/A' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                <span class="small">
                                    <i class="fas fa-calendar-check text-danger me-1"></i>
                                    <strong>Cierre:</strong>
                                </span>
                                <span class="badge bg-danger">
                                    {{ $exp['fecha_cierre'] ? \Carbon\Carbon::parse($exp['fecha_cierre'])->format('d/m/Y') : 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <!-- Estadísticas del Expediente -->
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="p-3 bg-success bg-opacity-10 rounded">
                                    <h4 class="mb-0 fw-bold text-success">{{ $exp['total_tramites'] }}</h4>
                                    <small class="text-muted">Trámites</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-danger bg-opacity-10 rounded">
                                    <h4 class="mb-0 fw-bold text-danger">{{ $exp['rojos'] }}</h4>
                                    <small class="text-muted">Rojos</small>
                                </div>
                            </div>
                        </div>

                        <!-- Porcentaje de Rojos -->
                        @php
                            $porcentajeRojos = $exp['total_tramites'] > 0 
                                ? round(($exp['rojos'] / $exp['total_tramites']) * 100, 1) 
                                : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Rojos</small>
                                <small class="text-muted">{{ $porcentajeRojos }}%</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-danger" 
                                     role="progressbar" 
                                     style="width: {{ $porcentajeRojos }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Botón Ver Operaciones -->
                        <a href="{{ route('finanzas.detalle.expediente', [
                                'expedienteId' => $exp['pedimento_id'],
                                'year' => $year,
                                'semana' => $semana
                            ]) }}" 
                           class="btn btn-primary w-100">
                            <i class="fas fa-list-ul me-2"></i>Ver Operaciones
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    No se encontraron expedientes para esta combinación de cliente y patente.
                </div>
            </div>
        @endforelse
    </div>

    <!-- Botón Volver -->
    <div class="row mt-4">
        <div class="col-12">
            <a href="{{ route('finanzas.index', ['year' => $year, 'semana' => $semana]) }}" 
               class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al Resumen
            </a>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.hover-card {
    transition: all 0.3s ease;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}
</style>
@endsection