@extends('layouts.app')

@section('title', 'Seguimiento de Thermos - Tráfico')

@section('content')
    <div class="container-fluid py-3">
        <!-- Header -->
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Departamento de Trafico</h1>
                <span class="badge bg-primary">Departamento de Tráfico</span>
            </div>
        </div>

        <div class="row">
            <!-- Sección Principal -->
            <div class="col-lg-8 col-md-7">
                <!-- Filtros -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body py-2">
                        <form method="GET" action="{{ route('trafico.index') }}">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm" name="estado">
                                        <option value="">Todos los estados</option>
                                        <option value="frontera">En Frontera</option>
                                        <option value="verde">Verde</option>
                                        <option value="rojo">Rojo</option>
                                        <option value="liberado">Liberado</option>
                                        <option value="transito">En Tránsito</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control form-control-sm" placeholder="Número de Thermo"
                                        name="thermo">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control form-control-sm" placeholder="Código Alpha"
                                        name="alpha">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control form-control-sm" placeholder="Número DODA"
                                        name="doda">
                                </div>
                            </div>
                            <div class="row g-2 align-items-center mt-2">
                                <div class="col-md-4">
                                    <input type="text" class="form-control form-control-sm" placeholder="Cliente"
                                        name="cliente">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="Número de Pedimento" name="pedimento">
                                </div>
                                <div class="col-md-4 text-end">
                                    <button class="btn btn-sm btn-outline-secondary me-1">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                    <a href="{{ route('trafico.index') }}" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                    <a href="{{ route('trafico.nuevaexpo') }}" class="btn btn-success">+ Nuevo Trámite</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Cards de Thermos -->
                <div class="row" id="thermos-container">
                    @forelse($thermos as $numThermo => $registros)
                                    @php
                                        $first = $registros->first();
                                        $estado = ucfirst($first->modulacion ?? 'Sin Modulacion');
                                        $color = match (strtolower($first->modulacion)) {
                                            'verde' => 'success',
                                            'rojo' => 'danger',
                                            'frontera' => 'warning',
                                            'liberado' => 'info',
                                            'transito' => 'secondary',
                                            default => 'dark'
                                        };
                                    @endphp

                                    <div class="col-xxl-4 col-lg-6 col-md-6 mb-3">
                                        <div class="card h-100 shadow-sm border-start border-{{ $color }} border-3">
                                            <div class="card-body p-3">
                                                <!-- Header -->
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0">Thermo: <strong>{{ $numThermo }}</strong></h6>
                                                    <span class="badge bg-{{ $color }}">{{ $estado }}</span>
                                                </div>

                                                <p class="card-text small mb-2">
                                                    <i class="fas fa-code me-1 text-muted"></i>
                                                    <strong>Alpha:</strong> {{ $first->codigo_alpha }}
                                                </p>

                                                <!-- Listado de Facturas -->
                                                <div class="mb-2">
                                                    <p class="small mb-1"><strong>Facturas:</strong></p>
                                                    <ul class="list-unstyled small mb-2">
                                                        @foreach($registros->take(3) as $exp)
                                                            <li>
                                                                <i class="fas fa-file-invoice me-1 text-muted"></i>
                                                                {{ $exp->cliente->nombre_empresa }} - {{ $exp->num_factura }}
                                                            </li>
                                                        @endforeach
                                                        @if($registros->count() > 3)
                                                            <li class="text-muted">+ {{ $registros->count() - 3 }} más...</li>
                                                        @endif
                                                    </ul>
                                                </div>

                                                <!-- Información Adicional -->
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-tag me-1"></i>
                                                        {{ $first->expediente->numero_pedimento ?? 'SIN PEDIMENTO' }}
                                                    </span>
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-building me-1"></i>
                                                        {{ $first->aduana->nombre_aduana ?? 'SIN ADUANA' }}
                                                    </span>
                                                </div>

                                                <!-- Footer -->
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-hashtag me-1"></i>
                                                        {{ $first->num_doda ?? 'SIN DODA' }}
                                                    </span>
                                                    <div>
                                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                            data-bs-target="#detalleThermoModal{{ $numThermo }}">
                                                            <i class="fas fa-eye"></i> Detalles
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal dinámico -->
                                    <div class="modal fade" id="detalleThermoModal{{ $numThermo }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white py-2">
                                                    <h6 class="modal-title mb-0">Detalles del Thermo {{ $numThermo }}</h6>
                                                    <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <!-- Lado izquierdo: datos del transporte y facturas -->
                                                        <div class="col-lg-7">
                                                            <p><strong>Alpha:</strong> {{ $registros->first()->codigo_alpha }}</p>
                                                            <p><strong>DODA:</strong> {{ $registros->first()->num_doda ?? 'SIN DODA' }}</p>
                                                            <p><strong>Pedimento:</strong>
                                                                {{ $registros->first()->expediente->numero_pedimento ?? 'SIN PEDIMENTO' }}
                                                            </p>
                                                            <p><strong>Aduana:</strong>
                                                                {{ $registros->first()->aduana->nombre_aduana ?? 'SIN ADUANA' }}</p>
                                                            <p><strong>Modulacion:</strong>
                                                                <span class="badge bg-{{ match (strtolower($registros->first()->modulacion)) {
                            'verde' => 'success',
                            'rojo' => 'danger',
                            'frontera' => 'warning',
                            'liberado' => 'info',
                            'transito' => 'secondary',
                            default => 'dark'
                        } }}">{{ ucfirst($registros->first()->modulacion ?? 'Sin estado') }}</span>
                                                            </p>

                                                            <p><strong>Facturas:</strong></p>
                                                            <ul>
                                                                @foreach($registros as $exp)
                                                                    <li>{{ $exp->cliente->nombre_empresa }} - {{ $exp->num_factura }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>

                                                        <!-- Lado derecho: listado de documentos -->
                                                        <div class="col-lg-5">
                                                            <h6>Documentos asociados</h6>
                                                            <div class="overflow-auto border p-2" style="max-height: 400px;">
                                                                @forelse($registros as $exp)
                                                                    <div class="mb-3">
                                                                        <strong>Operación #{{ $exp->id }}</strong>
                                                                        @if($exp->documentos->isNotEmpty())
                                                                            @foreach($exp->documentos->groupBy('tipo_documento') as $tipo => $docs)
                                                                                <div class="mt-2">
                                                                                    <small class="text-muted">{{ ucfirst($tipo) }}</small>
                                                                                    @foreach($docs as $doc)
                                                                                        <div class="card mb-1 p-2 shadow-sm">
                                                                                            <div class="d-flex justify-content-between align-items-center">
                                                                                                <span class="small">{{ $doc->nombre_documento }}</span>
                                                                                                <a href="{{ route('documentos.download', $doc) }}"
                                                                                                    class="btn btn-sm btn-outline-primary">
                                                                                                    <i class="fas fa-download"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            @endforeach
                                                                        @else
                                                                            <p class="text-muted small">Sin documentos</p>
                                                                        @endif
                                                                    </div>
                                                                @empty
                                                                    <p class="text-muted small">No hay operaciones registradas.</p>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer py-2">
                                                    <button type="button" class="btn btn-sm btn-secondary"
                                                        data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                    @empty
                        <p class="text-muted">No hay operaciones registradas hoy.</p>
                    @endforelse
                </div>
            </div>

            <!-- Sidebar con Estadísticas -->
            <div class="col-lg-4 col-md-5">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0">Resumen de Thermos</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h4 class="mb-0 text-primary">{{ $stats['total'] }}</h4><small>Total</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h4 class="mb-0 text-warning">{{ $stats['frontera'] }}</h4><small>Frontera</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h4 class="mb-0 text-success">{{ $stats['verde'] }}</h4><small>Verdes</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h4 class="mb-0 text-danger">{{ $stats['rojo'] }}</h4><small>Rojos</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h4 class="mb-0 text-info">{{ $stats['liberado'] }}</h4><small>Liberados</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-2">
                                    <h4 class="mb-0 text-secondary">{{ $stats['transito'] }}</h4><small>Tránsito</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h6 class="mb-0">Distribución por Estado</h6>
                    </div>
                    <div class="card-body"><canvas id="estadosChart" height="200"></canvas></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctxEstados = document.getElementById('estadosChart').getContext('2d');
            new Chart(ctxEstados, {
                type: 'doughnut',
                data: {
                    labels: ['Frontera', 'Verdes', 'Rojos', 'Liberados', 'Tránsito'],
                    datasets: [{
                        data: [
                                {{ $stats['frontera'] }},
                                {{ $stats['verde'] }},
                                {{ $stats['rojo'] }},
                                {{ $stats['liberado'] }},
                            {{ $stats['transito'] }}
                        ],
                        backgroundColor: ['#ffc107', '#28a745', '#dc3545', '#17a2b8', '#6c757d']
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        });
    </script>
@endsection