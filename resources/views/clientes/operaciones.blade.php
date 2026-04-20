@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <!-- Filtro de fechas -->
        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-4">
                <label class="form-label">Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>

        <!-- Métricas -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100 rounded-3 text-center">
                    <div class="card-body">
                        <h6 class="text-muted">Total operaciones</h6>
                        <h3 class="fw-bold">{{ $total }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100 rounded-3 text-center">
                    <div class="card-body">
                        <h6 class="text-muted">Verdes</h6>
                        <h3 class="fw-bold text-success">{{ $verdes }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100 rounded-3 text-center">
                    <div class="card-body">
                        <h6 class="text-muted">Rojos</h6>
                        <h3 class="fw-bold text-danger">{{ $rojos }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100 rounded-3 text-center">
                    <div class="card-body">
                        <h6 class="text-muted">Hoy</h6>
                        <h3 class="fw-bold text-primary">{{ $hoy }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards + Gráfico -->
        <div class="row g-3 mb-4">
            <!-- Cards de operaciones -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-header bg-white border-0">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <span class="fw-bold">Operaciones</span>
                                <span class="badge bg-primary rounded-pill ms-2">{{ $operaciones->total() }} total</span>
                            </div>
                            <div class="col-md-6">
                                <!-- Buscador -->
                                <form method="GET" action="{{ route('cliente.operacionescliente') }}" class="d-flex">
                                    <input type="hidden" name="fecha_inicio" value="{{ request('fecha_inicio') }}">
                                    <input type="hidden" name="fecha_fin" value="{{ request('fecha_fin') }}">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" placeholder="Buscar por # factura..."
                                            name="busqueda" value="{{ request('busqueda') }}">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="bi bi-search"></i>
                                        </button>
                                        @if(request('busqueda'))
                                            <a href="{{ route('cliente.operacionescliente') }}?fecha_inicio={{ request('fecha_inicio') }}&fecha_fin={{ request('fecha_fin') }}"
                                                class="btn btn-outline-danger">
                                                <i class="bi bi-x"></i>
                                            </a>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        @forelse($operaciones as $op)
                            <div class="card mb-2 shadow-sm border-0">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Factura #{{ $op->num_factura }}</strong><br>
                                        <small class="text-muted">{{ $op->aduana->nombre_aduana ?? 'N/A' }} |
                                            {{ $op->fecha?->format('d/m/Y') }}</small>
                                    </div>
                                    @if($op->modulacion === 'DESADUANAMIENTO LIBRE')
                                        <span class="badge bg-success rounded-pill">
                                            <i class="bi bi-check-circle me-1"></i>Verde
                                        </span>
                                    @elseif(in_array($op->modulacion, ['RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO']))
                                        <span class="badge bg-danger rounded-pill">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Rojo
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill">{{ $op->modulacion ?? 'Pendiente' }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                @if(request('busqueda'))
                                    <p class="text-muted mt-2 mb-0">No se encontraron operaciones con "{{ request('busqueda') }}"
                                    </p>
                                @else
                                    <p class="text-muted mt-2 mb-0">No hay operaciones en este rango de fechas</p>
                                @endif
                            </div>
                        @endforelse
                    </div>

                    <!-- Paginador mejorado -->
                    @if($operaciones->hasPages())
                        <div class="card-footer bg-light border-0">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        Mostrando <strong>{{ $operaciones->firstItem() }}</strong> a
                                        <strong>{{ $operaciones->lastItem() }}</strong> de
                                        <strong>{{ $operaciones->total() }}</strong> operaciones
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <nav aria-label="Navegación de operaciones">
                                        <ul class="pagination pagination-sm justify-content-end mb-0">
                                            {{-- Botón Primera Página --}}
                                            @if ($operaciones->currentPage() > 3)
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="{{ $operaciones->appends(request()->query())->url(1) }}">1</a>
                                                </li>
                                                @if ($operaciones->currentPage() > 4)
                                                    <li class="page-item disabled">
                                                        <span class="page-link">...</span>
                                                    </li>
                                                @endif
                                            @endif

                                            {{-- Botón Anterior --}}
                                            @if ($operaciones->onFirstPage())
                                                <li class="page-item disabled">
                                                    <span class="page-link"><i class="bi bi-chevron-left"></i></span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="{{ $operaciones->appends(request()->query())->previousPageUrl() }}">
                                                        <i class="bi bi-chevron-left"></i>
                                                    </a>
                                                </li>
                                            @endif

                                            {{-- Páginas del rango actual --}}
                                            @php
                                                $start = max(1, $operaciones->currentPage() - 2);
                                                $end = min($operaciones->lastPage(), $operaciones->currentPage() + 2);
                                            @endphp

                                            @for ($page = $start; $page <= $end; $page++)
                                                @if ($page == $operaciones->currentPage())
                                                    <li class="page-item active">
                                                        <span class="page-link">{{ $page }}</span>
                                                    </li>
                                                @else
                                                    <li class="page-item">
                                                        <a class="page-link"
                                                            href="{{ $operaciones->appends(request()->query())->url($page) }}">{{ $page }}</a>
                                                    </li>
                                                @endif
                                            @endfor

                                            {{-- Botón Siguiente --}}
                                            @if ($operaciones->hasMorePages())
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="{{ $operaciones->appends(request()->query())->nextPageUrl() }}">
                                                        <i class="bi bi-chevron-right"></i>
                                                    </a>
                                                </li>
                                            @else
                                                <li class="page-item disabled">
                                                    <span class="page-link"><i class="bi bi-chevron-right"></i></span>
                                                </li>
                                            @endif

                                            {{-- Botón Última Página --}}
                                            @if ($operaciones->currentPage() < $operaciones->lastPage() - 2)
                                                @if ($operaciones->currentPage() < $operaciones->lastPage() - 3)
                                                    <li class="page-item disabled">
                                                        <span class="page-link">...</span>
                                                    </li>
                                                @endif
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="{{ $operaciones->appends(request()->query())->url($operaciones->lastPage()) }}">{{ $operaciones->lastPage() }}</a>
                                                </li>
                                            @endif
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Gráfico -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-header bg-white border-0 fw-bold">Comparativo Rojos / Verdes</div>
                    <div class="card-body">
                        <canvas id="comparativoChart" height="200"></canvas>
                    </div>
                </div>
            </div>

        </div>
        <!-- Nueva sección de gráficas -->
        <div class="row g-3 mt-8">
            <!-- Pastel por Bodega -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-header bg-white border-0 fw-bold">Distribución por Bodega</div>
                    <div class="card-body">
                        <canvas id="chartBodegas" height="200"></canvas>
                    </div>
                </div>
            </div>
            <!-- Barra horizontal por Aduana -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-header bg-white border-0 fw-bold">Operaciones por Aduana</div>
                    <div class="card-body">
                        <canvas id="chartAduanas" height="200"></canvas>
                    </div>
                </div>
            </div>
            <!--Agregar metricas adicionales -->
            <!-- Sobrepesos: Cuantas operaciones tuvieron sobrepeso en el periodo.
             Tiempos promedio de despacho: Tiempo entre "fecha" y "fecha de modulacion" (o cierre). Muestra si mis operaciones estan fluyendo rapido o se estan tardando.
             Costo promedio por operacion: (Cuando tengamos ligado facturacion) Permite ver al cliente el gasto total en el periodo.
             Grafica linear de operaciones por dia en el rango de fechas: permite ver si mi flujo va subiendo o bajando.
        -->

        </div>
    </div>

    <!-- Script gráfico -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('comparativoChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Verdes', 'Rojos'],
                    datasets: [{
                        data: [{{ $verdes }}, {{ $rojos }}],
                        backgroundColor: ['#198754', '#dc3545']
                    }]
                },
                options: {
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // 🔹 Barra horizontal por Aduana
            new Chart(document.getElementById('chartAduanas'), {
                type: 'bar',
                data: {
                    labels: @json($aduanasLabels),
                    datasets: [{
                        label: 'Operaciones',
                        data: @json($aduanasTotals),
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.6)',
                    }]
                },
                options: {
                    indexAxis: 'x', // HORIZONTAL
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { beginAtZero: true }
                    }
                }
            });

            // 🔹 Pastel por Bodega
            new Chart(document.getElementById('chartBodegas'), {
                type: 'pie',
                data: {
                    labels: @json($bodegasLabels),
                    datasets: [{
                        data: @json($bodegasTotals),
                        backgroundColor: [
                            '#7209B7', '#3A86FF', '#38B000', '#FF006E', '#FB5607', '#8338EC'
                        ]
                    }]
                },
                options: {
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    </script>
@endsection