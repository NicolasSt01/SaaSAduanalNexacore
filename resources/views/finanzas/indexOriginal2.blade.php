@extends('layouts.app')

@section('customcss')
<style>
.cliente-group {
    border-left: 4px solid #6c757d;
    transition: all 0.3s ease;
}
.cliente-group:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.cliente-group.completado {
    border-left-color: #28a745;
    opacity: 0.7;
}
.cliente-header-pendiente {
    background-color: #6c757d !important;
}
.cliente-header-completado {
    background-color: #28a745 !important;
}
.patente-row {
    transition: background-color 0.2s ease;
}
.patente-row:hover {
    background-color: #f8f9fa;
}
.badge-stat {
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
    font-weight: 600;
}
.expediente-facturado {
    background-color: #d4edda !important;
}
.btn-facturar {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
.documento-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
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
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-dark">Año</label>
                    <select name="year" class="form-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $y == $yearActual ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-dark">Semana</label>
                    <select name="semana" class="form-select">
                        @for($i = 1; $i <= 52; $i++)
                            <option value="{{ $i }}" {{ $i == $semanaActual ? 'selected' : '' }}>Semana {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-dark">Buscar Cliente</label>
                    <input type="text" name="cliente" class="form-control" placeholder="Nombre del cliente..." value="{{ $clienteBusqueda }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-dark">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                        <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completados</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm" style="background-color: #6c757d;">
                <div class="card-body text-center text-white">
                    <h6 class="mb-2 opacity-75">Clientes</h6>
                    <h2 class="mb-0 fw-bold">{{ $resumen->groupBy('cliente_id')->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm" style="background-color: #17a2b8;">
                <div class="card-body text-center text-white">
                    <h6 class="mb-2 opacity-75">Expedientes</h6>
                    <h2 class="mb-0 fw-bold">{{ $resumen->sum('expedientes_count') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm" style="background-color: #28a745;">
                <div class="card-body text-center text-white">
                    <h6 class="mb-2 opacity-75">Trámites</h6>
                    <h2 class="mb-0 fw-bold">{{ $resumen->sum('total_tramites') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm" style="background-color: #dc3545;">
                <div class="card-body text-center text-white">
                    <h6 class="mb-2 opacity-75">Rojos</h6>
                    <h2 class="mb-0 fw-bold">{{ $resumen->sum('rojos') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm" style="background-color: #ffc107;">
                <div class="card-body text-center text-white">
                    <h6 class="mb-2 opacity-75">Sobrepesos</h6>
                    <h2 class="mb-0 fw-bold">{{ $resumen->sum('sobrepesos') ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm" style="background-color: #6c757d;">
                <div class="card-body text-center text-white">
                    <h6 class="mb-2 opacity-75">Adicionales</h6>
                    <h2 class="mb-0 fw-bold">${{ number_format($resumen->sum('adicionales') ?? 0, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="alert alert-light border mb-4">
        <div class="d-flex align-items-center gap-4">
            <small class="text-muted"><strong>Leyenda:</strong></small>
            <small><span class="badge" style="background-color: #6c757d;">Gris</span> = Pendiente de facturar</small>
            <small><span class="badge bg-success">Verde</span> = Todos los expedientes facturados</small>
        </div>
    </div>

    <!-- Lista de Clientes y Patentes -->
    @if($resumen->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No se encontraron operaciones para la semana {{ $semanaActual }} del {{ $yearActual }}.
        </div>
    @else
        @php
            $clientesAgrupados = $resumen->groupBy('cliente_id');
            $clientesPendientes = [];
            $clientesCompletados = [];
            
            foreach($clientesAgrupados as $clienteId => $patentes) {
                $todosFacturados = $patentes->every(function($patente) {
                    return isset($patente['expedientes_facturados']) && 
                           $patente['expedientes_facturados'] >= $patente['expedientes_count'];
                });
                
                if ($todosFacturados) {
                    $clientesCompletados[$clienteId] = $patentes;
                } else {
                    $clientesPendientes[$clienteId] = $patentes;
                }
            }
            
            $clientesOrdenados = $clientesPendientes + $clientesCompletados;
        @endphp

        @foreach($clientesOrdenados as $clienteId => $patentes)
            @php
                $todosFacturados = $patentes->every(function($patente) {
                    return isset($patente['expedientes_facturados']) && 
                           $patente['expedientes_facturados'] >= $patente['expedientes_count'];
                });
                $headerClass = $todosFacturados ? 'cliente-header-completado' : 'cliente-header-pendiente';
                $cardClass = $todosFacturados ? 'completado' : '';
            @endphp
            
            <div class="card shadow-sm mb-4 border-0 cliente-group {{ $cardClass }}">
                <div class="card-header {{ $headerClass }} text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-building me-2"></i>
                                {{ $patentes->first()['cliente_nombre'] }}
                            </h5>
                            @if($todosFacturados)
                                <small class="opacity-75">
                                    <i class="fas fa-check-circle me-1"></i>Todos los expedientes facturados
                                </small>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark badge-stat">{{ $patentes->count() }} {{ $patentes->count() == 1 ? 'Patente' : 'Patentes' }}</span>
                            <span class="badge bg-light text-dark badge-stat">{{ $patentes->sum('expedientes_count') }} Expedientes</span>
                            <span class="badge bg-light text-dark badge-stat">{{ $patentes->sum('total_tramites') }} Trámites</span>
                            <span class="badge bg-light text-dark badge-stat">{{ $patentes->sum('rojos') }} Rojos</span>
                            <span class="badge bg-light text-dark badge-stat">{{ $patentes->sum('sobrepesos') ?? 0 }} Sobrepesos</span>
                            <span class="badge bg-light text-dark badge-stat">${{ number_format($patentes->sum('adicionales') ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Patente</th>
                                    <th class="text-center">Expedientes</th>
                                    <th class="text-center">Trámites</th>
                                    <th class="text-center">Rojos</th>
                                    <th class="text-center">Sobrepesos</th>
                                    <th class="text-center">Adicionales</th>
                                    <th>Apertura</th>
                                    <th>Cierre</th>
                                    <th class="text-center">Facturación</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($patentes as $patente)
                                    @php
                                        $expedientesFacturados = $patente['expedientes_facturados'] ?? 0;
                                        $expedientesTotal = $patente['expedientes_count'];
                                        $patenteCompleta = $expedientesFacturados >= $expedientesTotal;
                                        $rowClass = $patenteCompleta ? 'expediente-facturado' : '';
                                    @endphp
                                    <tr class="patente-row {{ $rowClass }}">
                                        <td><strong class="text-dark">{{ $patente['patente_numero'] }}</strong></td>
                                        <td class="text-center"><span class="badge bg-secondary">{{ $expedientesTotal }}</span></td>
                                        <td class="text-center"><span class="badge" style="background-color: #28a745;">{{ $patente['total_tramites'] }}</span></td>
                                        <td class="text-center"><span class="badge" style="background-color: #dc3545;">{{ $patente['rojos'] }}</span></td>
                                        <td class="text-center"><span class="badge" style="background-color: #ffc107; color: #000;">{{ $patente['sobrepesos'] ?? 0 }}</span></td>
                                        <td class="text-center"><span class="badge" style="background-color: #6c757d;">${{ number_format($patente['adicionales'] ?? 0, 2) }}</span></td>
                                        <td><small class="text-muted">{{ $patente['fecha_apertura'] ? \Carbon\Carbon::parse($patente['fecha_apertura'])->format('d/m/Y') : 'N/A' }}</small></td>
                                        <td><small class="text-muted">{{ $patente['fecha_cierre'] ? \Carbon\Carbon::parse($patente['fecha_cierre'])->format('d/m/Y') : 'N/A' }}</small></td>
                                        <td class="text-center">
                                            @if($patenteCompleta)
                                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Completo</span>
                                            @else
                                                <span class="badge bg-warning text-dark">{{ $expedientesFacturados }}/{{ $expedientesTotal }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('finanzas.detalle.cliente.patente', [
                                                    'clienteId' => $patente['cliente_id'], 
                                                    'patenteId' => $patente['patente_id'],
                                                    'year' => $yearActual,
                                                    'semana' => $semanaActual
                                                ]) }}" 
                                               class="btn btn-sm btn-dark"
                                               title="Ver y facturar expedientes">
                                                <i class="fas fa-file-invoice"></i> Gestionar
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td><strong>Totales:</strong></td>
                                    <td class="text-center"><strong>{{ $patentes->sum('expedientes_count') }}</strong></td>
                                    <td class="text-center"><strong>{{ $patentes->sum('total_tramites') }}</strong></td>
                                    <td class="text-center"><strong>{{ $patentes->sum('rojos') }}</strong></td>
                                    <td class="text-center"><strong>{{ $patentes->sum('sobrepesos') ?? 0 }}</strong></td>
                                    <td class="text-center"><strong>${{ number_format($patentes->sum('adicionales') ?? 0, 2) }}</strong></td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection

@section('scripts')
<script>
// Aquí irán los scripts para modales y gestión de facturas
</script>
@endsection