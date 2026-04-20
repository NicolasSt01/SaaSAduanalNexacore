@extends('layouts.app')

@section('title', 'Listado de Operaciones')

@section('content')
    <div class="container-fluid py-3">
        <!-- Header y Botón de Acción -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">Gestión de Operaciones</h1>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Resumen -->
        <div class="row mb-4">
            <div class="col-xxl-2 col-lg-3 col-md-6 mb-3">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-0">Total</h6>
                                <h4 class="mb-0">{{ $operaciones->total() }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-file-export fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-lg-3 col-md-6 mb-3">
                <div class="card bg-warning text-dark shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-0">Pendientes</h6>
                                <h4 class="mb-0">14</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-lg-3 col-md-6 mb-3">
                <div class="card bg-info text-white shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-0">En Proceso</h6>
                                <h4 class="mb-0">8</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-tasks fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-lg-3 col-md-6 mb-3">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-0">Completados</h6>
                                <h4 class="mb-0">26</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sección Principal de Cards -->
            <div class="col-lg-8 col-md-7">
                <!-- Filtros Compactos -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body py-2">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="filtroEstado">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente">Pendientes</option>
                                    <option value="asignado">Asignados</option>
                                    <option value="completado">Completados</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="filtroPrioridad">
                                    <option value="">Todas las prioridades</option>
                                    <option value="baja">Baja</option>
                                    <option value="media">Media</option>
                                    <option value="alta">Alta</option>
                                    <option value="urgente">Urgente</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" class="form-control form-control-sm" id="filtroFecha">
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" placeholder="Buscar..." id="busqueda">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <a href="{{ route('operaciones.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Nueva Exportación
                    </a>
                    <br>
                </div>

                <!-- Cards de Operaciones -->
                <div class="row" id="operaciones-container">
                    @forelse($operaciones as $operacion)
                        <div class="col-xxl-3 col-lg-4 col-md-6 col-sm-6 mb-3 operacion-card"
                            data-id="{{ $operacion->id }}">
                            <div class="card h-100 shadow-sm 
                        @if(($operacion->estado ?? 'pendiente') == 'terminado') border-start border-secondary border-3
                        @elseif(($operacion->prioridad ?? 'baja') == 'alta') border-start border-danger border-3
                        @elseif(($operacion->prioridad ?? 'baja') == 'media') border-start border-warning border-3
                        @elseif(($operacion->prioridad ?? 'baja') == 'urgente') border-start border-danger border-4
                        @elseif(($operacion->estado ?? 'pendiente') == 'completado') border-start border-success border-3
                        @else border-start border-primary border-3 
                        @endif">

                                <div class="card-body p-3">
                                    <!-- Header -->
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0 text-truncate">
                                            {{ $operacion->cliente->nombre_empresa }}
                                        </h6>
                                        <span class="badge estado-badge" id="estado-{{ $operacion->id }}">
                                            {{ ucfirst($operacion->estado ?? 'pendiente') }}
                                        </span>
                                    </div>

                                    <!-- Información Básica -->
                                    <p class="card-text small mb-1 text-truncate">
                                        <i class="fas fa-box me-1 text-muted"></i>
                                        {{ $operacion->nombre_producto }}
                                    </p>

                                    <p class="card-text small mb-1">
                                        <i class="fas fa-file-invoice me-1 text-muted"></i>
                                        <strong>Factura:</strong> {{ $operacion->num_factura }}
                                    </p>

                                    <p class="card-text small mb-2">
                                        <i class="fas fa-warehouse me-1 text-muted"></i>
                                        <strong>Bodega:</strong> {{ $operacion->bodega->nombre_bodega }}
                                    </p>

                                    <div class="card mb-3" data-operacion-id="{{ $operacion->id }}">
                                        <div class="card-body">
                                            <!-- Otra info de la exportación aquí -->

                                            <!-- Modulación siempre visible -->
                                            <div class="mb-2">
                                                <span class="badge 
                                                        @if($operacion->modulacion === 'Verde') bg-success
                                                        @elseif($operacion->modulacion === 'Rojo') bg-danger
                                                        @elseif(($operacion->estado ?? '') == 'terminado') bg-secondary
                                                        @else bg-dark @endif modulacion-span" id="modulacion-{{ $operacion->id }}">
                                                    Modulación: {{ $operacion->modulacion ?? 'Pendiente' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Botones de Acción -->
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <a href="{{ route('operaciones.show', $operacion) }}"
                                                class="btn btn-sm btn-outline-secondary" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('operaciones.edit', $operacion) }}"
                                                class="btn btn-sm btn-outline-secondary ms-1" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                        <div>
                                            @if(($operacion->estado ?? '') !== 'terminado')
                                                <a href="{{ route('operaciones.showAsignarForm', $operacion->id) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Asignar">
                                                    <i class="fas fa-user-plus"></i>
                                                </a>
                                            @else
                                                <a href="#" class="btn btn-sm btn-outline-dark" title="Ver Detalles">
                                                    <i class="fas fa-info-circle"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('operaciones.destroy', $operacion) }}" method="POST"
                                                class="d-inline ms-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                                    onclick="return confirm('¿Eliminar esta exportación?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No hay operaciones registradas</h5>
                                <p class="text-muted">Comienza agregando una nueva exportación</p>
                                <a href="{{ route('operaciones.create') }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-2"></i>Crear Exportación
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Paginación -->
                @if($operaciones->hasPages())
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $operaciones->links() }}
                    </div>
                @endif
            </div>

            <!-- Sidebar con Gráficos -->
            <div class="col-lg-4 col-md-5">
                <!-- Gráfico de Progreso -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0">Progreso de Trámites</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="progresoChart" height="200"></canvas>
                        <div class="mt-2 text-center small">
                            <span class="badge bg-success me-2">Completados: 26</span>
                            <span class="badge bg-warning me-2">Pendientes: 14</span>
                            <span class="badge bg-info">En proceso: 8</span>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Aduana -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0">Estado en Aduana</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="aduanaChart" height="200"></canvas>
                        <div class="mt-2 text-center small">
                            <span class="badge bg-success me-2">Verdes: 15</span>
                            <span class="badge bg-danger">Rojos: 4</span>
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
        document.addEventListener('DOMContentLoaded', function () {
            // Gráfico de Progreso
            const ctxProgreso = document.getElementById('progresoChart').getContext('2d');
            new Chart(ctxProgreso, {
                type: 'doughnut',
                data: {
                    labels: ['Completados', 'Pendientes', 'En proceso'],
                    datasets: [{
                        data: [26, 14, 8],
                        backgroundColor: ['#28a745', '#ffc107', '#17a2b8'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });

            // Gráfico de Aduana
            const ctxAduana = document.getElementById('aduanaChart').getContext('2d');
            new Chart(ctxAduana, {
                type: 'doughnut',
                data: {
                    labels: ['Verdes', 'Rojos'],
                    datasets: [{
                        data: [15, 4],
                        backgroundColor: ['#28a745', '#dc3545'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });

            // Filtros
            const filtroEstado = document.getElementById('filtroEstado');
            const filtroPrioridad = document.getElementById('filtroPrioridad');
            const filtroFecha = document.getElementById('filtroFecha');
            const busqueda = document.getElementById('busqueda');

            if (filtroEstado) filtroEstado.addEventListener('change', aplicarFiltros);
            if (filtroPrioridad) filtroPrioridad.addEventListener('change', aplicarFiltros);
            if (filtroFecha) filtroFecha.addEventListener('change', aplicarFiltros);
            if (busqueda) busqueda.addEventListener('input', aplicarFiltros);

            function aplicarFiltros() {
                console.log('Aplicando filtros...');
            }
        });
    </script>



    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function actualizarModulaciones() {
                document.querySelectorAll('[data-operacion-id]').forEach(function (card) {
                    let operacionId = card.getAttribute('data-operacion-id');
                    fetch(`/operaciones/${operacionId}/modulacion`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.modulacion) {
                                let span = card.querySelector('.modulacion-span');
                                if (span) {
                                    span.textContent = 'Modulación: ' + data.modulacion;
                                }
                            }
                        })
                        .catch(err => console.error('Error al actualizar modulación:', err));
                });
            }

            setInterval(actualizarModulaciones, 5000); // cada 5s
            actualizarModulaciones(); // primera vez al cargar
        });
    </script>




@endsection