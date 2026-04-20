@extends('layouts.app')

@section('content')
    @php
        // Valores por defecto seguros (evitan "Undefined variable")
        $pedimentosMes = $pedimentosMes ?? 0;
        $pedimentosVerde = $pedimentosVerde ?? 0;
        $pedimentosRojo = $pedimentosRojo ?? 0;

        // Aseguramos que labels/values sean arrays o Collections, si no lo son los volvemos array vacío.
        $chartLabels = (isset($labels) && (is_array($labels) || $labels instanceof \Illuminate\Support\Collection))
            ? $labels
            : [];

        $chartValues = (isset($values) && (is_array($values) || $values instanceof \Illuminate\Support\Collection))
            ? $values
            : [];

        // Operaciones y pedimentos recientes: si no vienen, usar array vacío para foreach/forelse
        $operacionesHoy = (isset($operacionesHoy) && ($operacionesHoy instanceof \Illuminate\Support\Collection || is_array($operacionesHoy)))
            ? $operacionesHoy
            : [];

        $pedimentosRecientes = (isset($pedimentosRecientes) && ($pedimentosRecientes instanceof \Illuminate\Support\Collection || is_array($pedimentosRecientes)))
            ? $pedimentosRecientes
            : [];
    @endphp

    <div class="container py-4">

        <!-- Bienvenida -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow border-0 rounded-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h4 mb-1">Bienvenido(a), <span
                                    class="text-primary fw-bold">{{ Auth::user()->name }}</span></h1>
                            <p class="text-muted mb-0">Aquí puedes consultar el estado de tus pedimentos y analíticas</p>
                        </div>
                        <i class="bi bi-graph-up text-primary display-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Métricas rápidas -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Pedimentos este mes</h6>
                                <h3 class="mb-0">{{ $pedimentosMes }}</h3>
                                {{--<small class="text-success">+0% vs mes anterior</small>--}}
                            </div>
                            <div class="bg-primary text-white rounded-circle p-3">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Operaciones en verde</h6>
                                <h3 class="mb-0">{{ $pedimentosVerde }}</h3>
                                <small class="text-muted">último mes</small>
                            </div>
                            <div class="bg-success text-white rounded-circle p-3">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Operaciones en rojo</h6>
                                <h3 class="mb-0">{{ $pedimentosRojo }}</h3>
                                <small class="text-muted">último mes</small>
                            </div>
                            <div class="bg-danger text-white rounded-circle p-3">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico + operaciones -->
        <div class="row g-3 mb-4">
            {{--<div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-header bg-white border-0 fw-bold">Evolución de Operaciones</div>
                    <div class="card-body">
                        @if(count($chartLabels) && count($chartValues))
                        <canvas id="operacionesChart" height="120"></canvas>
                        @else
                        <div class="d-flex align-items-center justify-content-center" style="height:200px;">
                            <div class="text-center text-muted">
                                <i class="bi bi-graph-up" style="font-size:28px;"></i>
                                <div>No hay datos para mostrar en el gráfico</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>--}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Evolución de Operaciones</span>
                        <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse"
                            data-bs-target="#filtroFechas" aria-expanded="false">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                    </div>

                    <!-- Filtro de fechas colapsable -->
                    <div class="collapse" id="filtroFechas">
                        <div class="card-body border-bottom bg-light">
                            <form method="GET" action="{{ route('cliente.admindashboard') }}" class="row g-3">
                                <div class="col-md-4">
                                    <label for="fecha_inicio" class="form-label small">Fecha Inicio</label>
                                    <input type="date" class="form-control form-control-sm" id="fecha_inicio"
                                        name="fecha_inicio"
                                        value="{{ request('fecha_inicio', $fechaInicio->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="fecha_fin" class="form-label small">Fecha Fin</label>
                                    <input type="date" class="form-control form-control-sm" id="fecha_fin" name="fecha_fin"
                                        value="{{ request('fecha_fin', $fechaFin->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-search"></i> Aplicar
                                    </button>
                                    <a href="{{ route('cliente.admindashboard') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-x-circle"></i> Limpiar
                                    </a>
                                </div>
                            </form>

                            <!-- Períodos predefinidos -->
                            <div class="mt-3">
                                <small class="text-muted">Períodos rápidos:</small>
                                <div class="btn-group btn-group-sm mt-1" role="group">
                                    <a href="{{ route('cliente.admindashboard') }}?fecha_inicio={{ now()->subMonths(3)->startOfMonth()->format('Y-m-d') }}&fecha_fin={{ now()->endOfMonth()->format('Y-m-d') }}"
                                        class="btn btn-outline-secondary btn-sm">3m</a>
                                    <a href="{{ route('cliente.admindashboard') }}?fecha_inicio={{ now()->subMonths(6)->startOfMonth()->format('Y-m-d') }}&fecha_fin={{ now()->endOfMonth()->format('Y-m-d') }}"
                                        class="btn btn-outline-secondary btn-sm">6m</a>
                                    <a href="{{ route('cliente.admindashboard') }}?fecha_inicio={{ now()->subYear()->startOfYear()->format('Y-m-d') }}&fecha_fin={{ now()->endOfYear()->format('Y-m-d') }}"
                                        class="btn btn-outline-secondary btn-sm">Este año</a>
                                    <a href="{{ route('cliente.admindashboard') }}?fecha_inicio={{ now()->subYear()->startOfYear()->format('Y-m-d') }}&fecha_fin={{ now()->subYear()->endOfYear()->format('Y-m-d') }}"
                                        class="btn btn-outline-secondary btn-sm">Año ant.</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if(count($labels) && count($values))
                            <canvas id="operacionesChart" height="120"></canvas>
                        @else
                            <div class="d-flex align-items-center justify-content-center" style="height:200px;">
                                <div class="text-center text-muted">
                                    <i class="bi bi-graph-up" style="font-size:28px;"></i>
                                    <div>No hay datos para mostrar en el gráfico</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center fw-bold">
                        Operaciones del día
                        <a href="{{ route('cliente.operacionescliente') }}" class="btn btn-sm btn-outline-primary">Ver
                            todos</a>
                    </div>
                    <div class="card-body p-0">
                        @forelse($operacionesHoy as $op)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Factura #{{ $op->num_factura }}</strong><br>
                                    <small class="text-muted">Aduana: {{ $op->aduana->nombre_aduana ?? 'N/A' }}</small>
                                </div>
                                @if($op->modulacion === 'DESADUANAMIENTO LIBRE')
                                    <span class="badge bg-success">Verde</span>
                                @elseif(in_array($op->modulacion, ['RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO']))
                                    <span class="badge bg-danger">Rojo</span>
                                @else
                                    <span class="badge bg-secondary">{{ $op->modulacion ?? 'Pendiente' }}</span>
                                @endif
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">
                                No hay operaciones registradas para hoy.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de pedimentos -->
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center fw-bold">
                Pedimentos recientes
                <div>
                    {{--<button class="btn btn-sm btn-outline-secondary">Filtrar</button>--}}

                    <a href="{{ route('expedientes.indexcliente') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>N° Pedimento</th>

                                <th>Aduana</th>

                                <th>Categoría</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pedimentosRecientes as $ped)
                                <tr>
                                    <td>{{ $ped->numero_pedimento }}</td>

                                    <td>{{ $ped->aduana->nombre_aduana ?? 'N/A' }}</td>

                                    <td>{{ $ped->categoria }}</td>
                                    <td>
                                        <span class="badge 
                                                @if($ped->estado === 'En proceso') bg-warning
                                                @elseif($ped->estado === 'Abierto') bg-primary
                                                @elseif($ped->estado === 'Cerrado') bg-success
                                                @elseif($ped->estado === 'Cancelado') bg-danger
                                                @else bg-secondary
                                                @endif">
                                            {{ $ped->estado }}
                                        </span>
                                    </td>
                                    <td><a href="{{ route('expedientes.showclient', $ped->id) }}"
                                            class="btn btn-sm btn-outline-primary">Detalle</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No hay pedimentos recientes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



    </div>

    <!-- Script gráfico -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const labels = @json($chartLabels);
            const values = @json($chartValues);

            if (Array.isArray(labels) && labels.length > 0 && Array.isArray(values) && values.length > 0) {
                const ctx = document.getElementById('operacionesChart2').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Operaciones',
                            data: values,
                            fill: true,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            tension: 0.4,
                            pointBackgroundColor: '#0d6efd',
                            pointBorderColor: '#fff'
                        }]
                    },
                    options: {
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const labels = @json($chartLabels);
            const values = @json($chartValues);

            if (Array.isArray(labels) && labels.length > 0 && Array.isArray(values) && values.length > 0) {
                const ctx = document.getElementById('operacionesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Operaciones',
                            data: values,
                            fill: true,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0)',
                            tension: 0.4,
                            pointBackgroundColor: 'rgba(13, 110, 253, 0.5)',
                            pointBorderColor: '#0d6efd',
                            // Se agregan las siguientes líneas
                            pointStyle: 'circle',
                            pointRadius: 10, // Ajusta el tamaño del punto
                            pointHoverRadius: 15 // Tamaño del punto al pasar el cursor

                        }]
                    },
                    options: {
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        });
    </script>

@endsection