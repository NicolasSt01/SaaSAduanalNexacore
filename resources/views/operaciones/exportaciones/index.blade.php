@extends('layouts.app')

@section('title', 'Listado de Operaciones')

@section('content')
    <style>
        :root {
            --verde-discreto: #d4edda;
            --verde-borde: #28a745;
            --verde-texto: #155724;
            --rojo-discreto: #f8d7da;
            --rojo-borde: #dc3545;
            --rojo-texto: #721c24;
            --gris-discreto: #e9ecef;
            --gris-borde: #6c757d;
            --gris-texto: #495057;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-wrapper {
            max-height: 650px;
            overflow-y: auto;
            overflow-x: auto;
        }

        .table-wrapper::-webkit-scrollbar {
            width: 12px;
            height: 12px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
            border: 2px solid #f1f1f1;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .operaciones-table {
            width: 100%;
            margin: 0;
            font-size: 0.875rem;
        }

        .operaciones-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #2c3e50;
        }

        .operaciones-table thead th {
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 1rem 0.75rem;
            border: none;
            white-space: nowrap;
        }

        .operaciones-table tbody tr {
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }

        .operaciones-table tbody tr:hover {
            transform: translateX(2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .operaciones-table tbody td {
            padding: 0.85rem 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f4;
        }

        /* Estilos según modulación */
        .row-verde {
            background-color: var(--verde-discreto);
            border-left-color: var(--verde-borde) !important;
        }

        .row-verde:hover {
            background-color: #c3e6cb;
        }

        .row-rojo {
            background-color: var(--rojo-discreto);
            border-left-color: var(--rojo-borde) !important;
        }

        .row-rojo:hover {
            background-color: #f5c6cb;
        }

        .row-gris {
            background-color: var(--gris-discreto);
            border-left-color: var(--gris-borde) !important;
        }

        .row-gris:hover {
            background-color: #dee2e6;
        }

        .badge-modulacion {
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
            display: inline-block;
        }

        .badge-verde {
            background-color: var(--verde-borde);
            color: white;
        }

        .badge-rojo {
            background-color: var(--rojo-borde);
            color: white;
        }

        .badge-gris {
            background-color: var(--gris-borde);
            color: white;
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.7rem;
            flex-shrink: 0;
        }

        .user-name {
            font-weight: 500;
            color: #2c3e50;
            white-space: nowrap;
        }

        .btn-action {
            padding: 0.35rem 0.5rem;
            border-radius: 5px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            border: none;
            margin: 0 0.1rem;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .actions-cell {
            white-space: nowrap;
            text-align: center;
        }

        .search-filters {
            padding: 1rem;
            background: white;
            border-bottom: 2px solid #e9ecef;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .loading-more {
            text-align: center;
            padding: 1.5rem;
            color: #3498db;
            display: none;
        }

        .badge-estado {
            font-size: 0.7rem;
            padding: 0.3rem 0.6rem;
        }
    </style>

    <div class="container-fluid py-3">
        <!-- Header y Botón de Acción -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">Gestión de Operaciones</h1>
                    <a href="{{ route('trafico.nuevaexpo') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Nueva Exportación
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Resumen -->
        <div class="row g-3 mb-4">
            <div class="col-xxl-2 col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Total</h6>
                                <h3 class="mb-0">{{ $totalHoy }}</h3>
                            </div>
                            <div class="bg-primary text-white rounded-circle p-3">
                                <i class="fas fa-file-export"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Pendientes</h6>
                                <h3 class="mb-0">{{ $pendientesHoy }}</h3>
                            </div>
                            <div class="bg-danger text-white rounded-circle p-3">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">En Proceso</h6>
                                <h3 class="mb-0">{{ $enProcesoHoy }}</h3>
                            </div>
                            <div class="bg-warning text-dark rounded-circle p-3">
                                <i class="fas fa-tasks"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-2 col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm border-0 h-100 rounded-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Completados</h6>
                                <h3 class="mb-0">{{ $completadasHoy }}</h3>
                            </div>
                            <div class="bg-success text-white rounded-circle p-3">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sección Principal de Lista -->
            <div class="col-lg-8 col-md-7">
                <!-- Filtros Compactos -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body py-2">
                        <form action="{{ route('operaciones.index') }}" method="GET" class="row g-2 align-items-center">
                            <div class="col-md-2 col-sm-6">
                                <select class="form-select form-select-sm" name="estado" id="filtroEstado">
                                    <option value="">Todos los estados</option>
                                    <option value="Pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                                    <option value="En Proceso" {{ request('estado') == 'proceso' ? 'selected' : '' }}>En Proceso</option>
                                    <option value="Completado" {{ request('estado') == 'terminado' ? 'selected' : '' }}>Completados</option>
                                </select>
                            </div>

                            <div class="col-md-2 col-sm-6">
                                <select class="form-select form-select-sm" name="prioridad" id="filtroPrioridad">
                                    <option value="">Todas las prioridades</option>
                                    <option value="Regular" {{ request('prioridad') == 'regular' ? 'selected' : '' }}>Baja</option>
                                    <option value="Media" {{ request('prioridad') == 'media' ? 'selected' : '' }}>Media</option>
                                    <option value="Urgente" {{ request('prioridad') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                </select>
                            </div>

                            <div class="col-md-2 col-sm-6">
                                <input type="date" class="form-control form-control-sm" name="fecha" id="filtroFecha" value="{{ request('fecha') }}">
                            </div>

                            <div class="col-md-3 col-sm-6">
                                <input type="text" class="form-control form-control-sm" name="busqueda" id="busqueda" placeholder="Buscar..." value="{{ request('busqueda') }}">
                            </div>

                            <div class="col-md-2 col-sm-6">
                                <input type="text" class="form-control form-control-sm" name="bodega" id="bodega" placeholder="Bodega..." value="{{ request('bodega') }}">
                            </div>

                            <div class="col-md-1 col-sm-12 d-flex">
                                <button class="btn btn-primary btn-sm w-100" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de Operaciones -->
                <div class="table-container">
                    <div class="table-wrapper" id="tableWrapper">
                        <table class="operaciones-table table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>#Referencia</th>
                                    <th>Cliente</th>
                                    <th>Producto</th>
                                    <th>Factura</th>
                                    <th>Bodega</th>
                                    <th>Estado</th>
                                    <th>Modulación</th>
                                    <th>Asignado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @forelse($operaciones as $operacion)
                                    @php
                                        $modulacionClass = 'row-gris';
                                        $badgeClass = 'badge-gris';
                                        $modulacionLower = strtolower($operacion->modulacion ?? '');
                                        
                                        if ($modulacionLower === 'desaduanamiento libre') {
                                            $modulacionClass = 'row-verde';
                                            $badgeClass = 'badge-verde';
                                        } elseif (str_contains($modulacionLower, 'reconocimiento aduanero')) {
                                            $modulacionClass = 'row-rojo';
                                            $badgeClass = 'badge-rojo';
                                        }

                                        // Color de estado
                                        switch ($operacion->estado ?? 'pendiente') {
                                            case 'terminado':
                                            case 'completado':
                                                $estadoColor = 'success';
                                                break;
                                            case 'pendiente':
                                                $estadoColor = 'secondary';
                                                break;
                                            default:
                                                $estadoColor = 'primary';
                                        }
                                    @endphp
                                    <tr class="{{ $modulacionClass }}" data-index="{{ $loop->index }}">
                                        <td>{{ $operacion->fecha->format('d/m/Y') }}</td>
                                        <td><strong>{{ Str::limit($operacion->referencia ?? 'N/A', 50) }}</strong></td>
                                        <td><strong>{{ Str::limit($operacion->cliente->nombre_empresa ?? 'N/A', 20) }}</strong></td>
                                        <td>{{ Str::limit($operacion->nombre_producto, 25) }}</td>
                                        <td><span class="badge bg-info text-white">{{ $operacion->num_factura }}</span></td>
                                        <td>{{ Str::limit($operacion->bodega->nombre_bodega ?? 'N/A', 15) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $estadoColor }} text-white badge-estado">
                                                {{ ucfirst($operacion->estado ?? 'pendiente') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-modulacion {{ $badgeClass }}">
                                                {{ $operacion->modulacion ? Str::limit($operacion->modulacion, 20) : 'Pendiente' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($operacion->asignado)
                                                <div class="user-cell">
                                                    <div class="user-avatar">
                                                        {{ strtoupper(substr($operacion->asignado->name, 0, 2)) }}
                                                    </div>
                                                    <span class="user-name">{{ Str::limit($operacion->asignado->name, 15) }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted small">Sin asignar</span>
                                            @endif
                                        </td>
                                        <td class="actions-cell">
                                            <a href="{{ route('operaciones.show', $operacion) }}" 
                                               class="btn btn-action btn-outline-secondary btn-sm" 
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('operaciones.edit', $operacion) }}" 
                                               class="btn btn-action btn-outline-warning btn-sm" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(($operacion->estado ?? '') !== 'terminado')
                                                <a href="{{ route('operaciones.showAsignarForm', $operacion->id) }}" 
                                                   class="btn btn-action btn-outline-primary btn-sm" 
                                                   title="Asignar">
                                                    <i class="fas fa-user-plus"></i>
                                                </a>
                                            @else
                                                <button class="btn btn-action btn-outline-info btn-sm" 
                                                        title="Información"
                                                        onclick="mostrarInfo({{ $operacion->id }})">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            @endif
                                            <form action="{{ route('operaciones.destroy', $operacion) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('¿Está seguro de eliminar esta exportación?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-action btn-outline-danger btn-sm" 
                                                        title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="empty-state">
                                            <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                                            <h5 class="text-muted">No hay operaciones registradas</h5>
                                            <p class="text-muted">Comienza agregando una nueva exportación</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Loading para scroll infinito -->
                        <div class="loading-more" id="loadingMore">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">Cargando más registros...</p>
                        </div>
                    </div>
                </div>

                <!-- Paginación -->
                {{--@if($operaciones->hasPages())
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $operaciones->links() }}
                    </div>
                @endif--}}
            </div>

            <!-- Sidebar con Gráficos -->
            <div class="col-lg-4 col-md-5">
                <!-- Gráfico de Progreso -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-center" style="height:240px">
                            <canvas id="prioridadChart"></canvas>
                        </div>
                        <p class="text-center mt-2 mb-0">
                            Total de trámites hoy: <strong>{{ $totalHoy }}</strong>
                        </p>
                    </div>
                </div>

                <!-- Gráfico de Aduana -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0">Estado en Aduana</h6>
                        <a href="{{ route('operaciones.actualizarmodulacion') }}" class="btn btn-sm btn-outline-primary"
                            title="Actualizar modulaciones">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar Modulacion
                        </a>
                    </div>

                    <div class="card-body">
                        @if ($leyendaModulacion)
                            <div class="alert alert-info text-center mt-4">
                                {{ $leyendaModulacion }}
                            </div>
                        @else
                            <div class="chart-container" style="height: 300px;">
                                <canvas id="modulacionPieChart"></canvas>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Gráfico Polar
            const totalHoy = {{ $totalHoy }};
            const ctx = document.getElementById('prioridadChart').getContext('2d');

            new Chart(ctx, {
                type: 'polarArea',
                data: {
                    labels: @json($labelsPrioridad),
                    datasets: [{
                        label: 'Trámites de hoy',
                        data: @json($dataPrioridad),
                        backgroundColor: [
                            'rgba(13,110,253,0.6)',
                            'rgba(25,135,84,0.6)',
                            'rgba(220,53,69,0.6)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            min: 0,
                            max: totalHoy,
                            ticks: {
                                stepSize: Math.ceil(totalHoy / 5),
                                backdropColor: 'transparent'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Progreso de Trámites del Día'
                        }
                    }
                }
            });

            // Gráfico de Modulación
            const labelsModulacion = @json($labelsModulacion);
            const dataModulacion = @json($dataModulacion);
            const backgroundColorsModulacion = @json($backgroundColorsModulacion);
            const hasData = dataModulacion.length > 0;

            if (hasData) {
                const ctxModulacion = document.getElementById('modulacionPieChart').getContext('2d');
                new Chart(ctxModulacion, {
                    type: 'pie',
                    data: {
                        labels: labelsModulacion,
                        datasets: [{
                            data: dataModulacion,
                            backgroundColor: backgroundColorsModulacion,
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
                            }
                        }
                    }
                });
            }

            // Scroll infinito
            const tableWrapper = document.getElementById('tableWrapper');
            const loadingMore = document.getElementById('loadingMore');
            let loading = false;
            let page = 2;

            tableWrapper.addEventListener('scroll', function() {
                if (loading) return;

                const scrollTop = tableWrapper.scrollTop;
                const scrollHeight = tableWrapper.scrollHeight;
                const clientHeight = tableWrapper.clientHeight;

                if (scrollTop + clientHeight >= scrollHeight - 50) {
                    loadMore();
                }
            });

            function loadMore() {
                // Implementar carga AJAX si se necesita
                loading = true;
                loadingMore.style.display = 'block';

                setTimeout(() => {
                    loading = false;
                    loadingMore.style.display = 'none';
                }, 1000);
            }

            // Animación de entrada
            const rows = document.querySelectorAll('.operaciones-table tbody tr');
            rows.forEach((row, index) => {
                if (index < 20) {
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-10px)';
                    setTimeout(() => {
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '1';
                        row.style.transform = 'translateX(0)';
                    }, index * 20);
                }
            });
        });

        function mostrarInfo(operacionId) {
            alert('Información adicional de exportación #' + operacionId);
        }
    </script>
@endsection