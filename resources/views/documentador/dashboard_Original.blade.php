@extends('layouts.app')

@section('title', 'Dashboard - Documentador')

@section('content')
<div class="container-fluid py-4">
    <!-- Header de Bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1">Bienvenido, <strong>{{ Auth::user()->name }}</strong></h1>
                            <p class="mb-0">Documentador | Último acceso: {{ now()->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-primary fs-6 p-2">Rendimiento: {{ $stats['efectividad'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Trámites Hoy</h6>
                            <h2 class="mb-0">{{ $stats['total_hoy'] }}</h2>
                            <small class="text-success">Total asignados para hoy</small>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-alt fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-success border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Completados</h6>
                            <h2 class="mb-0">{{ $stats['completados_hoy'] }}</h2>
                            <small class="text-muted">{{ $stats['efectividad'] }}% de efectividad</small>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-warning border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Pendientes</h6>
                            <h2 class="mb-0">{{ $stats['pendientes'] }}</h2>
                            <small class="text-muted">Por terminar</small>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-info border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Ranking Semanal</h6>
                            <h2 class="mb-0">#{{ $stats['ranking']['posicion'] }}</h2>
                            <small class="text-info">{{ $stats['ranking']['variacion'] }} posiciones</small>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-trophy fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sección de Operaciones Asignadas -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Operaciones Asignadas</h5>
                    <span class="badge bg-primary">{{ $operaciones->count() }} trámites</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($operaciones as $operacion)
                        @php
                            // Determinar color según prioridad y estado
                            $borderColor = 'border-';
                            $badgeColor = 'bg-';
                            
                            if ($operacion->estado == 'completado') {
                                $borderColor .= 'secondary';
                                $badgeColor .= 'secondary';
                            } else {
                                switch($operacion->prioridad) {
                                    case 'urgente':
                                        $borderColor .= 'danger';
                                        $badgeColor .= 'danger';
                                        break;
                                    case 'media':
                                        $borderColor .= 'warning';
                                        $badgeColor .= 'warning';
                                        break;
                                    case 'regular':
                                    default:
                                        $borderColor .= 'info';
                                        $badgeColor .= 'info';
                                        break;
                                }
                            }
                        @endphp

                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 border-start {{ $borderColor }} border-3">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-0">{{ $operacion->cliente->nombre_empresa }}</h6>
                                            <small class="text-muted">Estado: {{ ucfirst($operacion->estado) }}</small>
                                        </div>
                                        <span class="badge {{ $badgeColor }}">{{ ucfirst($operacion->prioridad) }}</span>
                                    </div>
                                    
                                    <p class="card-text small mb-1">
                                        <strong>Producto:</strong> {{ $operacion->nombre_producto }}
                                    </p>
                                    <p class="card-text small mb-1">
                                        <strong>Factura:</strong> {{ $operacion->num_factura }}
                                    </p>
                                    @if($operacion->num_thermo)
                                    <p class="card-text small mb-2">
                                        <strong>Thermo:</strong> {{ $operacion->num_thermo }}
                                    </p>
                                    @endif

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-light text-dark">#{{ $operacion->id }}</span>
                                        
                                        @if($operacion->estado == 'completado')
                                            <span class="badge bg-success">Finalizado</span>
                                        @else
                                            <a href="{{ route('documentador.trabajar', $operacion->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                Trabajar
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No hay trámites asignados</h5>
                                <p class="text-muted">No tienes operaciones asignadas para hoy</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Gráficos y Ranking -->
        <div class="col-xl-4 col-lg-5">
            <!-- Gráfico de Progreso -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Progreso del Día</h5>
                </div>
                <div class="card-body">
                    <canvas id="progresoChart" height="250"></canvas>
                    <div class="mt-3 text-center">
                        <span class="badge bg-success me-3"><i class="fas fa-check-circle"></i> Completados: {{ $stats['completados_hoy'] }}</span>
                        <span class="badge bg-warning me-3"><i class="fas fa-clock"></i> Pendientes: {{ $stats['pendientes'] }}</span>
                        <span class="badge bg-info"><i class="fas fa-list"></i> Total: {{ $stats['total_hoy'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Ranking de Documentadores -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Ranking Semanal</h5>
                    <span class="badge bg-primary">Actualizado</span>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <!-- El ranking se mantiene estático como en tu plantilla original -->
                        <div class="list-group-item d-flex align-items-center">
                            <span class="badge bg-primary me-2">1</span>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Ana Rodríguez</h6>
                                <small class="text-muted">42 trámites</small>
                            </div>
                            <span class="badge bg-success">+8</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center">
                            <span class="badge bg-secondary me-2">2</span>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Miguel Ángel</h6>
                                <small class="text-muted">38 trámites</small>
                            </div>
                            <span class="badge bg-danger">-2</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center bg-light">
                            <span class="badge bg-warning me-2">3</span>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ Auth::user()->name }} (Tú)</h6>
                                <small class="text-muted">{{ $stats['ranking']['total'] }} trámites</small>
                            </div>
                            <span class="badge bg-success">{{ $stats['ranking']['variacion'] }}</span>
                        </div>
                        <!-- ... otros items del ranking ... -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de progreso con datos reales
        var ctx = document.getElementById('progresoChart').getContext('2d');
        var progresoChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completados', 'Pendientes'],
                datasets: [{
                    data: [{{ $stats['completados_hoy'] }}, {{ $stats['pendientes'] }}],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
@endsection