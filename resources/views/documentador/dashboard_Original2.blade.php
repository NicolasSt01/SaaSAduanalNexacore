@extends('layouts.app')

@section('title', 'Dashboard - Documentador')
@section('customcss')
<style>
.card-modern {
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    transition: transform .15s ease-in-out, box-shadow .15s ease-in-out;
}
.card-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
</style>
@endsection

@section('content')
    <div class="container-fluid py-4">
        <!-- Header de Bienvenida -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm bg-white border-0 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h3 mb-1 text-gray-800">Hola, <strong>{{ Auth::user()->name }}</strong> 👋</h1>
                                <p class="mb-0 text-muted">Documentador | Último acceso: {{ now()->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <div class="d-flex align-items-center">
                                <button class="btn btn-sm btn-outline-primary shadow-sm me-2" data-bs-toggle="modal"
                                    data-bs-target="#tomarTramiteModal">
                                    <i class="fas fa-plus me-1"></i> Tomar Trámite
                                </button>
                                <span class="badge bg-light text-primary fs-6 p-2 rounded-pill">Rendimiento:
                                    {{ $stats['efectividad'] }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row g-4 mb-4">
            {{-- Card de Tramites Asignados Hoy (Prioridad 1) --}}
            <div class="col-xl-3 col-md-6">
                <div class="card card-modern h-100">
                    <div class="card-body d-flex flex-column p-4 justify-content-center">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <i class="fas fa-file-alt fa-3x text-primary"></i>
                            <h2 class="mb-0 display-4 text-primary">{{ $stats['total_hoy'] }}</h2>
                        </div>
                        <h6 class="text-muted mb-0">Trámites Asignados Hoy</h6>
                    </div>
                </div>
            </div>

            

            {{-- Cards de Completados y Pendientes (Prioridad 3) --}}
            <div class="col-xl-3 col-md-6">
                <div class="card card-modern h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Completados</h6>
                                <h2 class="mb-0">{{ $stats['completados_hoy'] }}</h2>
                                <small class="text-success">{{ $stats['efectividad'] }}% de efectividad</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-modern h-100">
                    <div class="card-body p-4">
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

            {{-- Card de Ranking Semanal (Prioridad 2) --}}
            <div class="col-xl-3 col-md-6">
                <div class="card card-modern h-100">
                    <div class="card-body d-flex flex-column p-4 justify-content-center">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <i class="fas fa-trophy fa-3x text-info"></i>
                            <h2 class="mb-0 display-4 text-info">#{{ $stats['ranking']['posicion'] }}</h2>
                        </div>
                        <h6 class="text-muted mb-0">Ranking Semanal</h6>
                        <small class="text-muted">Con {{ $stats['ranking']['total'] }} trámites</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Sección de Operaciones Asignadas -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow-sm h-100 border-0 rounded-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center p-3">
                        <h5 class="mb-0 text-gray-800">Operaciones Asignadas</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showCompletedSwitch"
                                onchange="document.getElementById('filterForm').submit();" {{ request('show_completed') ? 'checked' : '' }}>
                            <label class="form-check-label" for="showCompletedSwitch">Mostrar Terminadas</label>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <form id="filterForm" action="{{ route('documentador.dashboard') }}" method="GET" class="d-none">
                            <input type="hidden" name="show_completed" value="{{ request('show_completed') ? '' : '1' }}">
                        </form>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
                            @forelse($operaciones as $operacion)
                                @php
                                    $priority = strtolower($operacion->prioridad ?? 'regular');
                                    $estado = strtolower($operacion->estado ?? '');

                                    $borderColor = match ($estado) {
                                        'completado' => 'border-secondary',
                                        default => match ($priority) {
                                                'urgente' => 'border-danger',
                                                'media' => 'border-warning',
                                                default => 'border-primary'
                                            }
                                    };
                                @endphp
                                <div class="col">
                                    <div class="card h-100 border-2 rounded-3 shadow-sm {{ $borderColor }}">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0 text-gray-800">
                                                    {{ $operacion->cliente->nombre_empresa }}</h6>
                                                <span
                                                    class="badge rounded-pill bg-{{ $borderColor === 'border-secondary' ? 'secondary' : match ($priority) { 'urgente' => 'danger', 'media' => 'warning', default => 'primary'} }}">{{ ucfirst($operacion->estado) }}</span>
                                            </div>
                                            <div class="small text-muted mb-2">
                                                <strong>Producto:</strong> {{ $operacion->nombre_producto }}
                                            </div>
                                            <div class="small text-muted mb-2">
                                                <strong>Factura:</strong> {{ $operacion->num_factura }}
                                            </div>
                                            @if($operacion->num_thermo)
                                                <div class="small text-muted mb-2">
                                                    <strong>Thermo:</strong> {{ $operacion->num_thermo }}
                                                </div>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                                <span class="badge bg-light text-dark">#{{ $operacion->id }}</span>
                                                @if($operacion->estado == 'completado')
                                                    <a href="{{ route('documentador.trabajar', $operacion->id) }}"
                                                        class="btn btn-sm btn-outline-secondary rounded-pill">
                                                        <i class="fas fa-info-circle me-1"></i> Info
                                                    </a>
                                                @else
                                                    <a href="{{ route('documentador.trabajar', $operacion->id) }}"
                                                        class="btn btn-sm btn-primary rounded-pill">
                                                        <i class="fas fa-tools me-1"></i> Trabajar
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col">
                                    <div class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No hay trámites asignados</h5>
                                        <p class="text-muted">No tienes operaciones asignadas para hoy</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        {{-- Paginación --}}
                        <div class="d-flex justify-content-center mt-4">
                            {{ $operaciones->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Gráficos y Ranking -->
            <div class="col-xl-4 col-lg-5">
                <!-- Gráfico de Progreso -->
                <div class="card shadow-sm mb-4 border-0 rounded-3">
                    <div class="card-header bg-white p-3">
                        <h5 class="mb-0 text-gray-800">Progreso del Día</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-container" style="position:relative; height:200px;">
                            <canvas id="progresoChart"></canvas>
                        </div>
                        <div class="mt-4 text-center">
                            <span class="badge bg-success me-3 rounded-pill"><i class="fas fa-check-circle"></i>
                                Completados: {{ $stats['completados_hoy'] }}</span>
                            <span class="badge bg-warning me-3 rounded-pill"><i class="fas fa-clock"></i> Pendientes:
                                {{ $stats['pendientes'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Ranking de Documentadores -->
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-gray-800">Ranking Semanal</h5>
                        <span class="badge bg-primary rounded-pill">Top 10</span>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-group list-group-flush">
                            @foreach($rankingSemanal->take(10) as $rankItem)
                                <li
                                    class="list-group-item d-flex align-items-center px-0 py-2 {{ $rankItem->is_current_user ? 'bg-light rounded-3' : '' }}">
                                    <span
                                        class="badge bg-{{ $loop->index === 0 ? 'primary' : ($loop->index === 1 ? 'info' : ($loop->index === 2 ? 'warning' : 'secondary')) }} rounded-pill me-3">{{ $loop->index + 1 }}</span>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 text-gray-800">{{ $rankItem->name }}
                                            {{ $rankItem->is_current_user ? '(Tú)' : '' }}</h6>
                                        <small class="text-muted">{{ $rankItem->total_tramites }} trámites</small>
                                    </div>
                                    @if($rankItem->is_current_user && $stats['ranking']['variacion'] !== 'n/a')
                                        <span
                                            class="badge rounded-pill bg-{{ $stats['ranking']['variacion'] > 0 ? 'success' : 'danger' }}">{{ $stats['ranking']['variacion'] > 0 ? '+' : '' }}{{ $stats['ranking']['variacion'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Tomar Trámite -->
    <div class="modal fade" id="tomarTramiteModal" tabindex="-1" aria-labelledby="tomarTramiteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title" id="tomarTramiteModalLabel">Tomar un Trámite</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p>¿Estás seguro de que deseas tomar el siguiente trámite disponible?</p>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('documentador.tomar_tramite') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary rounded-pill">
                            Sí, Tomar Trámite
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gráfico de progreso con datos reales
            var ctx = document.getElementById('progresoChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Completados', 'Pendientes'],
                    datasets: [{
                        data: [{{ $stats['completados_hoy'] }}, {{ $stats['pendientes'] }}],
                        backgroundColor: ['#28a745', '#ffc107'],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 14
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

@endsection

@section('scripts')

@endsection