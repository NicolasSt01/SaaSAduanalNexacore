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
.list-tramite {
    transition: all 0.2s ease;
    border-left: 4px solid transparent;
}
.list-tramite:hover {
    background-color: #f8f9fa;
}
.list-tramite.mis-tramites {
    background-color: #e7f3ff;
    border-left-color: #0d6efd;
}
.list-tramite.disponible {
    border-left-color: #28a745;
}
.list-tramite.asignado-otro {
    background-color: #f8f9fa;
    border-left-color: #6c757d;
}
</style>
@endsection

@section('content')
    <div class="container-fluid py-4">
        {{-- Alertas de Éxito/Error --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <!-- Header de Bienvenida -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm bg-white border-0 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h3 mb-1 text-gray-800">Hola, <strong>{{ Auth::user()->name }}</strong> 👋</h1>
                                <p class="mb-0 text-muted">Documentador | Último acceso: {{ now()->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-light text-primary fs-6 p-2 rounded-pill">
                                    Rendimiento: {{ $stats['efectividad'] }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row g-4 mb-4">
            {{-- Card de Tramites Asignados Hoy --}}
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

            {{-- Cards de Completados y Pendientes --}}
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

            {{-- Card de Ranking Semanal --}}
            <div class="col-xl-3 col-md-6">
                <div class="card card-modern h-100">
                    <div class="card-body d-flex flex-column p-4 justify-content-center">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <i class="fas fa-trophy fa-3x text-info"></i>
                            <h2 class="mb-0 display-4 text-info">#{{ $stats['ranking_posicion'] ?? '-' }}</h2>
                        </div>
                        <h6 class="text-muted mb-0">Ranking Semanal</h6>
                        @php
                            $miRanking = $rankingSemanal->firstWhere('is_current_user', true);
                        @endphp
                        <small class="text-muted">
                            Con {{ $miRanking->total_tramites ?? 0 }} trámites esta semana
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Sección de Operaciones Asignadas (LISTA) -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow-sm h-100 border-0 rounded-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center p-3">
                        <h5 class="mb-0 text-gray-800">Trámites del Día</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showCompletedSwitch"
                                onchange="document.getElementById('filterForm').submit();" {{ request('show_completed') ? 'checked' : '' }}>
                            <label class="form-check-label" for="showCompletedSwitch">Mostrar Terminadas</label>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <form id="filterForm" action="{{ route('documentador.dashboard') }}" method="GET" class="d-none">
                            <input type="hidden" name="show_completed" value="{{ request('show_completed') ? '' : '1' }}">
                        </form>

                        @if($operaciones->isEmpty())
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No hay trámites para hoy</h5>
                                <p class="text-muted">No existen operaciones programadas para el día de hoy</p>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                {{-- MIS TRÁMITES --}}
                                @if($tramitesPropios->isNotEmpty())
                                    <div class="list-group-item bg-primary text-white fw-bold">
                                        <i class="fas fa-user-check me-2"></i>Mis Trámites Asignados ({{ $tramitesPropios->count() }})
                                    </div>
                                    @foreach($tramitesPropios as $operacion)
                                        @include('documentador.partials.tramite-item', ['operacion' => $operacion, 'tipo' => 'propio'])
                                    @endforeach
                                @endif

                                {{-- TRÁMITES DISPONIBLES --}}
                                @if($tramitesDisponibles->isNotEmpty())
                                    <div class="list-group-item bg-success text-white fw-bold">
                                        <i class="fas fa-clipboard-list me-2"></i>Trámites Disponibles ({{ $tramitesDisponibles->count() }})
                                    </div>
                                    @foreach($tramitesDisponibles as $operacion)
                                        @include('documentador.partials.tramite-item', ['operacion' => $operacion, 'tipo' => 'disponible'])
                                    @endforeach
                                @endif

                                {{-- TRÁMITES ASIGNADOS A OTROS --}}
                                @if($tramitesOtros->isNotEmpty())
                                    <div class="list-group-item bg-secondary text-white fw-bold">
                                        <i class="fas fa-users me-2"></i>Asignados a Otros Documentadores ({{ $tramitesOtros->count() }})
                                    </div>
                                    @foreach($tramitesOtros as $operacion)
                                        @include('documentador.partials.tramite-item', ['operacion' => $operacion, 'tipo' => 'otro'])
                                    @endforeach
                                @endif
                            </div>

                            {{-- Paginación --}}
                            <div class="d-flex justify-content-center p-3">
                                {{ $operaciones->links('pagination::bootstrap-5') }}
                            </div>
                        @endif
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
                            <span class="badge bg-success me-3 rounded-pill">
                                <i class="fas fa-check-circle"></i> Completados: {{ $stats['completados_hoy'] }}
                            </span>
                            <span class="badge bg-warning me-3 rounded-pill">
                                <i class="fas fa-clock"></i> Pendientes: {{ $stats['pendientes'] }}
                            </span>
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
                                <li class="list-group-item d-flex align-items-center px-0 py-2 {{ $rankItem->is_current_user ? 'bg-light rounded-3' : '' }}">
                                    <span class="badge bg-{{ $loop->index === 0 ? 'primary' : ($loop->index === 1 ? 'info' : ($loop->index === 2 ? 'warning' : 'secondary')) }} rounded-pill me-3">
                                        {{ $loop->index + 1 }}
                                    </span>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 text-gray-800">{{ $rankItem->name }} {{ $rankItem->is_current_user ? '(Tú)' : '' }}</h6>
                                        <small class="text-muted">{{ $rankItem->total_tramites }} trámites</small>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
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