@extends('layouts.app')
@section('customcss')
<style>
.hover-shadow {
    transition: all 0.3s ease;
}
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}
</style>

@endsection
@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 text-dark fw-bold">📊 Resumen de Operaciones - Finanzas</h2>
                    <p class="text-muted mb-0">
                        Semana {{ $semanaActual }} del {{ $yearActual }} 
                        <small class="ms-2">({{ $fechaInicio->format('d/m/Y') }} - {{ $fechaFin->format('d/m/Y') }})</small>
                    </p>
                </div>
                <div>
                    <a href="{{ route('finanzas.exportar.pdf', ['year' => $yearActual, 'semana' => $semanaActual]) }}" 
                       class="btn btn-danger">
                        <i class="fas fa-file-pdf me-2"></i>Exportar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body bg-light">
            <form method="GET" action="{{ route('finanzas.index') }}" class="row g-3">
                <!-- Año -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-dark">Año</label>
                    <select name="year" class="form-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $y == $yearActual ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Semana -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-dark">Semana</label>
                    <select name="semana" class="form-select">
                        @for($i = 1; $i <= 52; $i++)
                            <option value="{{ $i }}" {{ $i == $semanaActual ? 'selected' : '' }}>
                                Semana {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Cliente -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark">Buscar Cliente</label>
                    <input type="text" 
                           name="cliente" 
                           class="form-control" 
                           placeholder="Nombre del cliente..." 
                           value="{{ $clienteBusqueda }}">
                </div>

                <!-- Botón -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title text-white-50">Total Clientes-Patentes</h6>
                    <h2 class="mb-0 fw-bold">{{ $resumen->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title text-white-50">Total Trámites</h6>
                    <h2 class="mb-0 fw-bold">{{ $resumen->sum('total_tramites') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title text-white-50">Trámites Rojos</h6>
                    <h2 class="mb-0 fw-bold">{{ $resumen->sum('rojos') }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Clientes-Patentes -->
    @if($resumen->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No se encontraron operaciones para la semana {{ $semanaActual }} del {{ $yearActual }}.
        </div>
    @else
        <div class="row">
            @foreach($resumen as $item)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 hover-shadow">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 text-dark fw-bold">
                                <i class="fas fa-building text-primary me-2"></i>
                                {{ $item['cliente_nombre'] }}
                            </h5>
                            <small class="text-muted">
                                <i class="fas fa-file-contract me-1"></i>
                                Patente: {{ $item['patente_numero'] }}
                            </small>
                        </div>
                        <div class="card-body">
                            <!-- Estadísticas -->
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <h3 class="mb-0 fw-bold text-success">{{ $item['total_tramites'] }}</h3>
                                        <small class="text-muted">Total Trámites</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <h3 class="mb-0 fw-bold text-danger">{{ $item['rojos'] }}</h3>
                                        <small class="text-muted">Rojos</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Fechas de Expedientes -->
                            <div class="mb-3">
                                <p class="mb-1 small">
                                    <i class="fas fa-calendar-alt text-success me-2"></i>
                                    <strong>Apertura:</strong> 
                                    {{ $item['fecha_apertura'] ? \Carbon\Carbon::parse($item['fecha_apertura'])->format('d/m/Y') : 'N/A' }}
                                </p>
                                <p class="mb-0 small">
                                    <i class="fas fa-calendar-check text-danger me-2"></i>
                                    <strong>Cierre:</strong> 
                                    {{ $item['fecha_cierre'] ? \Carbon\Carbon::parse($item['fecha_cierre'])->format('d/m/Y') : 'N/A' }}
                                </p>
                            </div>

                            <!-- Expedientes -->
                            <div class="alert alert-info mb-3 py-2">
                                <i class="fas fa-folder-open me-2"></i>
                                <strong>{{ $item['expedientes_count'] }}</strong> 
                                {{ $item['expedientes_count'] == 1 ? 'Expediente' : 'Expedientes' }}
                            </div>

                            <!-- Botón Ver Detalle -->
                            <a href="{{ route('finanzas.detalle.cliente.patente', [
                                    'clienteId' => $item['cliente_id'], 
                                    'patenteId' => $item['patente_id'],
                                    'year' => $yearActual,
                                    'semana' => $semanaActual
                                ]) }}" 
                               class="btn btn-primary w-100">
                                <i class="fas fa-eye me-2"></i>Ver Detalle por Expedientes
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>


@endsection