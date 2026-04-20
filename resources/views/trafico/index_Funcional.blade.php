@extends('layouts.app')

@section('title', 'Seguimiento de Tráfico')

@section('customcss')
    <style>
        .card-modern {
            border-radius: 15px;
            border: 2px solid;
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
        }

        .card-modern:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .card-border-green {
            border-color: #28a745 !important;
        }

        .card-border-red {
            border-color: #dc3545 !important;
        }

        .filter-section {
            display: none;
            /* Se oculta por defecto */
        }
    </style>
@endsection

@section('content')


    <div class="container-fluid py-4">
        {{-- SECCIÓN DE BIENVENIDA Y TÍTULO --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Hola, {{ auth()->user()->name }}! 👋</h1>
                <p class="text-muted">Dashboard de tráfico en tiempo real.</p>
            </div>
            <div>
                <a href="{{ route('trafico.nuevaexpo') }}" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus"></i> Nuevo Trámite
                </a>
                <a href="{{ route('operaciones.actualizarmodulacion2') }}" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-sync"></i> Actualiza Modulacion
                </a>
            </div>

        </div>

        <div class="row">
            {{-- SECCIÓN PRINCIPAL: FILTROS Y CARDS --}}
            <div class="col-lg-8">
                {{-- Botón para mostrar/ocultar filtros --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Trámites del día</h5>
                    <button class="btn btn-sm btn-outline-secondary" type="button" id="toggleFilters">
                        <i class="fas fa-filter"></i> Mostrar filtros
                    </button>
                </div>

                {{-- Sección de filtros oculta --}}
                <div class="card shadow-sm mb-4 filter-section" id="filterCard">
                    <div class="card-body">
                        <form method="GET" action="{{ route('trafico.index') }}">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="estado" class="form-label small">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="">Todos</option>
                                        {{--<option value="frontera">En Frontera</option>--}}
                                        <option value="DESADUANAMIENTO LIBRE" {{ request('estado') == 'DESADUANAMIENTO LIBRE' ? 'selected' : '' }}>Verdes</option>
                                        <option value="RECONOCIMIENTO ADUANERO CONCLUIDO" {{ request('estado') == 'RECONOCIMIENTO ADUANERO CONCLUIDO' ? 'selected' : '' }}>
                                            Rojos</option>
                                        {{--<option value="transito">En Tránsito</option>
                                        <option value="retraso">Con Retraso</option>--}}
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="thermo" class="form-label small">No. Thermo</label>
                                    <input type="text" class="form-control" id="thermo" placeholder="Número de Thermo"
                                        name="thermo" value="{{ request('thermo') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="alpha" class="form-label small">Código Alpha</label>
                                    <input type="text" class="form-control" id="alpha" placeholder="Código Alpha"
                                        name="alpha" value="{{ request('alpha') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="doda" class="form-label small">No. DODA</label>
                                    <input type="text" class="form-control" id="doda" placeholder="Número DODA" name="doda"
                                        value="{{ request('doda') }}">
                                </div>

                                {{-- Nuevo filtro por rango de fechas --}}
                                <div class="col-md-3">
                                    <label for="fecha_desde" class="form-label small">Fecha Desde</label>
                                    <input type="date" class="form-control" id="fecha_desde" name="fecha_desde"
                                        value="{{ request('fecha_desde') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="fecha_hasta" class="form-label small">Fecha Hasta</label>
                                    <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta"
                                        value="{{ request('fecha_hasta') }}">
                                </div>

                                <div class="col-12 d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-sm btn-primary me-2">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                    <a href="{{ route('trafico.index') }}" class="btn btn-sm btn-outline-secondary">
                                        Limpiar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Contenedor de las Cards de Thermos --}}
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" id="thermos-container">
                    @forelse($thermos as $numThermo => $registros)
                        @php
                            $first = $registros->first();

                            $estado = ucfirst($first->modulacion ?? 'Sin Modulación');
                            $isOverweight = $first->sobrepeso ?? false; // Asume que 'sobrepeso' es un booleano
                            $color = match (strtoupper($first->modulacion)) {
                                'DESADUANAMIENTO LIBRE' => 'green',
                                'RECONOCIMIENTO ADUANERO CONCLUIDO' => 'red',
                                'RECONOCIMIENTO ADUANERO' => 'red',
                                default => 'muted' // Color neutro para otros estados
                            };

                        @endphp
                        <div class="col">
                            <div
                                class="card card-modern h-100 {{ $color === 'green' ? 'card-border-green' : ($color === 'red' ? 'card-border-red' : '') }} shadow-sm">
                                <div class="card-body d-flex flex-column p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title text-primary mb-0 d-flex align-items-center">
                                            <i class="fas fa-truck-moving me-2 text-muted"></i>
                                            <strong>{{ $numThermo }}</strong>
                                            @if ($isOverweight)
                                                <i class="fas fa-exclamation-triangle ms-2 text-danger"
                                                    title="Permiso de sobrepeso"></i>
                                            @endif
                                        </h6>
                                        <span
                                            class="badge bg-{{ $color === 'green' ? 'success' : ($color === 'red' ? 'danger' : 'secondary') }}">{{ $estado }}</span>
                                    </div>

                                    <div class="small text-muted mb-2">
                                        <i class="fas fa-code"></i> Alpha:
                                        {{ $first->codigo_alpha ?? 'Sin Alpha' }}
                                    </div>

                                    <div class="small text-muted mb-2">
                                        <i class="fas fa-tag"></i> Pedimento:
                                        {{ $first->expediente->numero_pedimento ?? 'SIN PEDIMENTO' }}
                                    </div>

                                    <ul class="list-unstyled mb-0">
                                        @foreach($registros->take(3) as $exp)
                                            <li>
                                                <i class="fas fa-circle"
                                                    style="font-size: 0.5rem; vertical-align: middle; color: #6c757d;"></i>
                                                <span class="small">{{ $exp->cliente->nombre_empresa }} -
                                                    {{ $exp->num_factura }}</span>
                                            </li>
                                        @endforeach
                                        @if($registros->count() > 3)
                                            <li class="text-muted small">+ {{ $registros->count() - 3 }} más...</li>
                                        @endif
                                    </ul>
                                    <div class="mt-auto pt-2 d-flex justify-content-end gap-2">
                                        {{-- Botón cuadrado para actualizar estatus --}}
                                        <button class="btn btn-warning btn-sm p-2" data-bs-toggle="modal"
                                            data-bs-target="#updateStatusModal{{ $numThermo }}"
                                            style="width:36px; height:36px; display:flex; align-items:center; justify-content:center;">
                                            <i class="fas fa-truck-pickup"></i>
                                        </button>

                                        {{-- Botón Ver Detalles (normal) --}}
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#detalleThermoModal{{ $numThermo }}">
                                            <i class="fas fa-eye"></i> Ver Detalles
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL DINÁMICO DE DETALLES (original) --}}
                        <div class="modal fade" id="detalleThermoModalOriginal{{ $numThermo }}" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white py-3">
                                        <h5 class="modal-title">Detalles del Thermo: **{{ $numThermo }}**</h5>
                                        <button type="button" class="btn-close btn-close-white"
                                            data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-lg-6 border-end">
                                                <h6>Información General</h6>
                                                <hr class="mt-2 mb-3">
                                                <ul class="list-unstyled">
                                                    <li><strong>Código Alpha:</strong> {{ $first->codigo_alpha }}</li>
                                                    <li><strong>DODA:</strong> {{ $first->num_doda ?? 'SIN DODA' }}</li>
                                                    <li><strong>Pedimento:</strong>
                                                        {{ $first->expediente->numero_pedimento ?? 'SIN PEDIMENTO' }}
                                                    </li>
                                                    <li><strong>Aduana:</strong>
                                                        {{ $first->aduana->nombre_aduana ?? 'SIN ADUANA' }}</li>
                                                    <li><strong>Modulación:</strong>
                                                        <span
                                                            class="badge bg-{{ $color === 'green' ? 'success' : ($color === 'red' ? 'danger' : 'secondary') }}">{{ $estado }}</span>
                                                    </li>
                                                </ul>
                                                <h6 class="mt-4">Facturas Asociadas</h6>
                                                <hr class="mt-2 mb-3">
                                                <ul class="list-group list-group-flush">
                                                    @foreach($registros as $exp)
                                                        <li
                                                            class="list-group-item d-flex justify-content-between align-items-center">
                                                            {{ $exp->cliente->nombre_empresa }} - {{ $exp->num_factura }}
                                                            <span class="badge bg-light text-dark">{{ $exp->estado }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                            <div class="col-lg-6">
                                                <h6>Documentos</h6>
                                                <hr class="mt-2 mb-3">
                                                <div class="overflow-auto border rounded p-3" style="max-height: 400px;">
                                                    @forelse($registros as $exp)
                                                        <div class="mb-3">
                                                            <strong>Operación #{{ $exp->id }}</strong>
                                                            @if($exp->documentos->isNotEmpty())
                                                                @foreach($exp->documentos->groupBy('tipo_documento') as $tipo => $docs)
                                                                    <div class="mt-2">
                                                                        <small class="text-muted">{{ ucfirst($tipo) }}</small>
                                                                        @foreach($docs as $doc)
                                                                            <div
                                                                                class="d-flex justify-content-between align-items-center border p-2 rounded mb-1 bg-light">
                                                                                <span
                                                                                    class="small text-truncate">{{ $doc->nombre_documento }}</span>
                                                                                <a href="{{ route('documentos.download', $doc) }}"
                                                                                    class="btn btn-sm btn-outline-primary ms-2">
                                                                                    <i class="fas fa-download"></i>
                                                                                </a>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <p class="text-muted small">Sin documentos.</p>
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
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL DINÁMICO DE DETALLES (REDISENADO Original al 10/13/2025) --}}
                        <div class="modal fade" id="detalleThermoModalOriginal2{{ $numThermo }}" tabindex="-1" aria-hidden="true"
                            data-bs-backdrop="static">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content rounded-4 shadow-lg">
                                    <div class="modal-header border-0 rounded-top-4 py-4 px-4"
                                        style="background: linear-gradient(45deg, #1e3a8a, #3b82f6); color: white;">
                                        <h5 class="modal-title fw-bold">Detalles del Thermo: **{{ $numThermo }}**</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                            aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body p-5">
                                        <div class="row">
                                            <!-- Información General y Botón de Modulación -->
                                            <div class="col-lg-6 border-end">
                                                <h6 class="text-primary fw-bold">
                                                    <i class="fas fa-info-circle me-2"></i> Información General
                                                </h6>
                                                <hr class="mt-2 mb-3">
                                                <ul class="list-unstyled mb-4">
                                                    <li><strong>Código Alpha:</strong> {{ $first->codigo_alpha }}</li>
                                                    <li><strong>DODA:</strong> {{ $first->num_doda ?? 'SIN DODA' }}</li>
                                                    <li><strong>Pedimento:</strong>
                                                        {{ $first->expediente->numero_pedimento ?? 'SIN PEDIMENTO' }}</li>
                                                    <li><strong>Aduana:</strong>
                                                        {{ $first->aduana->nombre_aduana ?? 'SIN ADUANA' }}</li>
                                                    <li><strong>Modulación:</strong>
                                                        <span
                                                            class="badge bg-{{ $color === 'green' ? 'success' : ($color === 'red' ? 'danger' : 'secondary') }}">{{ $estado }}</span>
                                                    </li>
                                                </ul>
                                                {{-- Botón para actualizar la modulación, solo si hay DODA y Pedimento --}}
                                                @if ($first->num_doda && $first->expediente->numero_pedimento)
                                                    <button type="button" class="btn btn-warning rounded-pill px-4"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#updateModulacionModal{{ $numThermo }}">
                                                        <i class="fas fa-sync-alt me-2"></i> Actualizar Modulación
                                                    </button>
                                                @endif

                                                <h6 class="mt-5 text-primary fw-bold">
                                                    <i class="fas fa-file-invoice me-2"></i> Facturas Asociadas
                                                </h6>
                                                <hr class="mt-2 mb-3">
                                                <ul class="list-group list-group-flush">
                                                    @forelse($registros as $exp)
                                                        <li
                                                            class="list-group-item d-flex justify-content-between align-items-center bg-light rounded-pill mb-2 p-3 shadow-sm">
                                                            <span>
                                                                {{ $exp->cliente->nombre_empresa }} - Factura
                                                                #{{ $exp->num_factura }}
                                                            </span>
                                                            <span
                                                                class="badge bg-light text-dark fw-normal">{{ $exp->estado }}</span>
                                                        </li>
                                                    @empty
                                                        <p class="text-muted small">No hay operaciones registradas.</p>
                                                    @endforelse
                                                </ul>
                                            </div>
                                            <!-- Documentos por Operación -->
                                            <div class="col-lg-6">
                                                <h6 class="text-primary fw-bold">
                                                    <i class="fas fa-folder-open me-2"></i> Documentos
                                                </h6>
                                                <hr class="mt-2 mb-3">
                                                <div class="overflow-auto rounded p-3"
                                                    style="max-height: 400px; background-color: #ffffff;">
                                                    @forelse($registros as $exp)
                                                        <div class="card shadow-sm mb-3 rounded-3 border-0">
                                                            <div
                                                                class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-2">
                                                                <h6 class="mb-0 fw-bold">Operación #{{ $exp->id }}</h6>
                                                                <button type="button" class="btn btn-primary btn-sm rounded-pill"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#uploadModal{{ $exp->id }}">
                                                                    <i class="fas fa-plus me-1"></i> Agregar archivo
                                                                </button>
                                                            </div>
                                                            <div class="card-body py-2">
                                                                @if($exp->documentos->isNotEmpty())
                                                                    @foreach($exp->documentos->groupBy('tipo_documento') as $tipo => $docs)
                                                                        <div class="mt-2">
                                                                            <small class="text-muted">{{ ucfirst($tipo) }}</small>
                                                                            @foreach($docs as $doc)
                                                                                <div
                                                                                    class="d-flex justify-content-between align-items-center border-bottom pb-2 pt-2">
                                                                                    <span
                                                                                        class="small text-truncate me-2">{{ $doc->nombre_documento }}</span>
                                                                                    <a href="{{ route('documentos.download', $doc) }}"
                                                                                        class="btn btn-sm btn-outline-primary rounded-pill">
                                                                                        <i class="fas fa-download"></i>
                                                                                    </a>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <p class="text-muted small mb-0">Sin documentos.</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="text-muted small">No hay operaciones registradas.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 py-3 d-flex justify-content-end">
                                        <button type="button" class="btn btn-secondary rounded-pill px-4"
                                            data-bs-dismiss="modal">
                                            <i class="fas fa-times me-2"></i> Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL DINÁMICO DE DETALLES (REDISEÑADO) --}}
                        <div class="modal fade" id="detalleThermoModal{{ $numThermo }}" tabindex="-1" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header border-0 rounded-top-4 py-4 px-4"
                style="background: linear-gradient(45deg, #1e3a8a, #3b82f6); color: white;">
                <h5 class="modal-title fw-bold">Detalles del Thermo: **{{ $numThermo }}**</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-5">
                <div class="row">
                    <!-- Información General y Botón de Modulación -->
                    <div class="col-lg-6 border-end">
                        <h6 class="text-primary fw-bold">
                            <i class="fas fa-info-circle me-2"></i> Información General
                        </h6>
                        <hr class="mt-2 mb-3">
                        <ul class="list-unstyled mb-4">
                            <li><strong>Código Alpha:</strong> {{ $first->codigo_alpha }}</li>
                            <li><strong>DODA:</strong> {{ $first->num_doda ?? 'SIN DODA' }}</li>
                            <li><strong>Pedimento:</strong>
                                {{ $first->expediente->numero_pedimento ?? 'SIN PEDIMENTO' }}</li>
                            <li><strong>Aduana:</strong>
                                {{ $first->aduana->nombre_aduana ?? 'SIN ADUANA' }}</li>
                            <li><strong>Modulación:</strong>
                                <span
                                    class="badge bg-{{ $color === 'green' ? 'success' : ($color === 'red' ? 'danger' : 'secondary') }}">{{ $estado }}</span>
                            </li>
                        </ul>
                        {{-- Botón para actualizar la modulación, solo si hay DODA y Pedimento --}}
                        @if ($first->num_doda && $first->expediente->numero_pedimento)
                            <button type="button" class="btn btn-warning rounded-pill px-4"
                                data-bs-toggle="modal"
                                data-bs-target="#updateModulacionModal{{ $numThermo }}">
                                <i class="fas fa-sync-alt me-2"></i> Actualizar Modulación
                            </button>
                        @endif

                        {{-- SECCIÓN DE CONCEPTOS ADICIONALES DEL CAMIÓN --}}
                        <div class="mt-4">
                            <h6 class="text-primary fw-bold">
                                <i class="fas fa-dollar-sign me-2"></i> Conceptos Adicionales del Camión
                            </h6>
                            <hr class="mt-2 mb-3">
                            
                            {{-- Mostrar conceptos ya registrados --}}
                            <div class="mb-3" id="conceptosLista{{ $numThermo }}">
                                @php
                                    $conceptosCamion = $first->conceptosAdicionales->where('ambito', 'camion');
                                @endphp
                                
                                @forelse($conceptosCamion as $concepto)
                                    <div class="d-flex justify-content-between align-items-center bg-light rounded-pill px-3 py-2 mb-2 shadow-sm">
                                        <span class="small">
                                            <i class="fas fa-tag text-success me-2"></i>
                                            <strong>{{ ucfirst(str_replace('_', ' ', $concepto->tipo_concepto)) }}</strong>
                                        </span>
                                        <div class="d-flex align-items-center gap-2">
                                            {{--<span class="badge bg-success">${{ number_format($concepto->monto, 2) }}</span>--}}
                                            <form action="{{ route('conceptos.destroy', $concepto->id) }}" method="POST" 
                                                  onsubmit="return confirm('¿Eliminar este concepto?')" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1" 
                                                        style="width: 24px; height: 24px; line-height: 1;">
                                                    <i class="fas fa-times" style="font-size: 0.7rem;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @if($concepto->descripcion)
                                        <small class="text-muted ms-4 d-block mb-2">
                                            <i class="fas fa-comment-dots me-1"></i>{{ $concepto->descripcion }}
                                        </small>
                                    @endif
                                @empty
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-info-circle me-1"></i>No hay conceptos adicionales registrados.
                                    </p>
                                @endforelse
                            </div>
                            
                            {{-- Botón para agregar conceptos --}}
                            <button class="btn btn-sm btn-success rounded-pill px-3" data-bs-toggle="modal" 
                                    data-bs-target="#conceptosModal{{ $numThermo }}">
                                <i class="fas fa-plus me-1"></i> Agregar Concepto
                            </button>
                        </div>

                        <h6 class="mt-5 text-primary fw-bold">
                            <i class="fas fa-file-invoice me-2"></i> Facturas Asociadas
                        </h6>
                        <hr class="mt-2 mb-3">
                        <ul class="list-group list-group-flush">
                            @forelse($registros as $exp)
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center bg-light rounded-pill mb-2 p-3 shadow-sm">
                                    <span>
                                        {{ $exp->cliente->nombre_empresa }} - Factura
                                        #{{ $exp->num_factura }}
                                    </span>
                                    <span
                                        class="badge bg-light text-dark fw-normal">{{ $exp->estado }}</span>
                                </li>
                            @empty
                                <p class="text-muted small">No hay operaciones registradas.</p>
                            @endforelse
                        </ul>
                    </div>
                    <!-- Documentos por Operación -->
                    <div class="col-lg-6">
                        <h6 class="text-primary fw-bold">
                            <i class="fas fa-folder-open me-2"></i> Documentos
                        </h6>
                        <hr class="mt-2 mb-3">
                        <div class="overflow-auto rounded p-3"
                            style="max-height: 400px; background-color: #ffffff;">
                            @forelse($registros as $exp)
                                <div class="card shadow-sm mb-3 rounded-3 border-0">
                                    <div
                                        class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-2">
                                        <h6 class="mb-0 fw-bold">Operación #{{ $exp->id }}</h6>
                                        <div>
                                                {{-- Botón Ver detalles --}}
                                                <a href="{{ route('trafico.operaciones.show', $exp->id) }}" 
                                                   class="btn btn-sm btn-outline-primary me-2 rounded-pill">
                                                    <i class="fas fa-eye"></i> Ver detalles
                                                </a>
                                                        
                                                {{-- Botón Agregar archivo --}}
                                                <button type="button" class="btn btn-primary btn-sm rounded-pill"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#uploadModal{{ $exp->id }}">
                                                    <i class="fas fa-plus"></i> Agregar archivo
                                                </button>
                                        </div>

                                        {{--<button type="button" class="btn btn-primary btn-sm rounded-pill"
                                            data-bs-toggle="modal"
                                            data-bs-target="#uploadModal{{ $exp->id }}">
                                            <i class="fas fa-plus me-1"></i> Agregar archivo
                                        </button>--}}
                                    </div>
                                    <div class="card-body py-2">
                                        @if($exp->documentos->isNotEmpty())
                                            @foreach($exp->documentos->groupBy('tipo_documento') as $tipo => $docs)
                                                <div class="mt-2">
                                                    <small class="text-muted">{{ ucfirst($tipo) }}</small>
                                                    @foreach($docs as $doc)
                                                        <div
                                                            class="d-flex justify-content-between align-items-center border-bottom pb-2 pt-2">
                                                            <span
                                                                class="small text-truncate me-2">{{ $doc->nombre_documento }}</span>
                                                            <a href="{{ route('documentos.download', $doc) }}"
                                                                class="btn btn-sm btn-outline-primary rounded-pill">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-muted small mb-0">Sin documentos.</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small">No hay operaciones registradas.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 py-3 d-flex justify-content-end">
                <button type="button" class="btn btn-secondary rounded-pill px-4"
                    data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
                        </div>

{{-- MODAL ANIDADO PARA AGREGAR CONCEPTOS ADICIONALES --}}
<div class="modal fade" id="conceptosModal{{ $numThermo }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title">
                    <i class="fas fa-dollar-sign me-2"></i>Conceptos Adicionales - Thermo {{ $numThermo }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('conceptos.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="operacion_id" value="{{ $first->id }}">
                    <input type="hidden" name="ambito" value="camion">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-tag me-1 text-success"></i>Tipo de Concepto *
                        </label>
                        <input type="text" class="form-control" name="tipo_concepto" 
                               placeholder="Ejemplo: Reacomodo Tarimas, Maniobras, etc." required>
                        <small class="text-muted">Puedes escribir libremente el concepto que desees registrar.</small>
                        {{--<select class="form-select" name="tipo_concepto" id="tipoConcepto{{ $numThermo }}" required>
                            <option value="">Selecciona un concepto...</option>
                            <option value="sobrepeso">Sobrepeso</option>
                            <option value="reacomodo_tarimas">Reacomodo de Tarimas</option>
                            <option value="maniobras_especiales">Maniobras Especiales</option>
                            <option value="reentarimado">Reentarimado</option>
                            <option value="custodia">Custodia</option>
                            <option value="almacenaje_extra">Almacenaje Extra</option>
                            <option value="pernocta">Pernocta</option>
                            <option value="otro">Otro</option>
                        </select>--}}
                    </div>
                    {{-- Monto por defecto oculto --}}
                    <input type="hidden" name="monto" value="0">

                    {{--<div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-dollar-sign me-1 text-success"></i>Monto *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" class="form-control" name="monto" 
                                   placeholder="0.00" required>
                        </div>
                    </div>--}}
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-comment-dots me-1 text-success"></i>Descripción
                        </label>
                        <textarea class="form-control" name="descripcion" rows="2" 
                                  placeholder="Agrega detalles adicionales (opcional)"></textarea>
                        <small class="text-muted">Ejemplo: "Reacomodo de 2 tarimas"</small>
                    </div>
                    
                    <div class="alert alert-info py-2 px-3 small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Nota:</strong> Este concepto se cobrará una sola vez por camión, 
                        independientemente del número de operaciones.
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            <i class="fas fa-check me-1"></i>Guardar Concepto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


                        {{-- MODAL ANIDADO para agregar documentos --}}
                        @foreach($registros as $exp)
                            <div class="modal fade" id="uploadModal{{ $exp->id }}" tabindex="-1"
                                aria-labelledby="uploadModalLabel{{ $exp->id }}" aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 shadow-lg">
                                        <div class="modal-header bg-primary text-white rounded-top-4">
                                            <h5 class="modal-title" id="uploadModalLabel{{ $exp->id }}">Cargar Documento para
                                                Operación #{{ $exp->id }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            {{-- Formulario para cargar documentos, adaptado de tu ejemplo --}}
                                            <form action="{{ route('documentos_operacion.store', ['expediente' => $exp->id]) }}"
                                                method="POST" enctype="multipart/form-data">
                                                @csrf
                                                {{-- Campo oculto para el ID de la exportación, como en tu modal original --}}
                                                <input type="hidden" name="operacion_id" value="{{ $exp->id }}">

                                                <div class="mb-3">
                                                    <label for="tipo_documento{{ $exp->id }}" class="form-label">Tipo de
                                                        documento</label>
                                                    <input type="text" name="tipo_documento" id="tipo_documento{{ $exp->id }}"
                                                        class="form-control text-lowercase" required>
                                                    <small class="text-muted">Ejemplo: factura, carta_porte, etc.</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="archivo{{ $exp->id }}" class="form-label">Archivo *</label>
                                                    <input type="file" class="form-control" id="archivo{{ $exp->id }}"
                                                        name="archivo" accept=".*" required
                                                        onchange="document.getElementById('nombre_documento{{ $exp->id }}').value = this.files[0]?.name.split('.').slice(0, -1).join('.')">
                                                </div>

                                                <input type="hidden" id="nombre_documento{{ $exp->id }}" name="nombre_documento">
                                                <input type="hidden" name="fecha_documento" value="{{ date('Y-m-d') }}">

                                                <div class="mb-3">
                                                    <label for="observaciones{{ $exp->id }}"
                                                        class="form-label">Observaciones</label>
                                                    <textarea name="observaciones" id="observaciones{{ $exp->id }}"
                                                        class="form-control"></textarea>
                                                </div>

                                                <div class="d-flex justify-content-end mt-4">
                                                    <button type="button" class="btn btn-secondary me-2 rounded-pill"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill">Guardar
                                                        Documento</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- MODAL ANIDADO para actualizar modulación --}}
                        <div class="modal fade" id="updateModulacionModal{{ $numThermo }}" tabindex="-1"
                            aria-labelledby="updateModulacionModalLabel{{ $numThermo }}" aria-hidden="true"
                            data-bs-backdrop="static">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 shadow-lg">
                                    <div class="modal-header bg-warning text-dark rounded-top-4">
                                        <h5 class="modal-title" id="updateModulacionModalLabel{{ $numThermo }}">Actualizar
                                            Estado de Modulación</h5>
                                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <form
                                            action="{{ route('operaciones.updatemodulacion', ['num_thermo' => $numThermo]) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-3">
                                                <label for="modulacion_status" class="form-label">Nuevo Estado de
                                                    Modulación</label>
                                                <select class="form-select rounded-pill" name="modulacion"
                                                    id="modulacion_status" required>
                                                    <option value="">Seleccione un estado...</option>
                                                    <option value="DESADUANAMIENTO LIBRE">Verde</option>
                                                    <option value="RECONOCIMIENTO ADUANERO CONCLUIDO">Rojo</option>
                                                    <option value="0">Sin Modular</option>
                                                </select>
                                            </div>
                                            <div class="d-flex justify-content-end mt-4">
                                                <button type="button" class="btn btn-secondary me-2 rounded-pill"
                                                    data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-warning rounded-pill">Actualizar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>



                        {{-- NUEVO MODAL PARA ACTUALIZAR ESTATUS DEL CAMIÓN --}}
                        <div class="modal fade" id="updateStatusModal{{ $numThermo }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" style="border-radius: 8px; border: 1px solid #e5e5e5;">
                                    <form action="{{ route('recorridos.store') }}" method="POST">
                                        @csrf
                                        <!-- Header -->
                                        <div class="modal-header py-2"
                                            style="background-color: #fafafa; border-bottom: 1px solid #e5e5e5;">
                                            <h6 class="modal-title mb-0" style="font-weight: 600; color: #333;">
                                                <i class="fas fa-truck me-2 text-warning"></i>
                                                Actualizar Estatus <small class="text-muted">({{ $numThermo }})</small>
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>

                                        <!-- Body -->
                                        <div class="modal-body" style="background-color: #fff;">
                                            <input type="hidden" name="operacion_id" value="{{ $first->id }}">

                                            <div class="mb-3">
                                                <label for="estatus" class="form-label mb-1">
                                                    <i class="fas fa-map-marker-alt me-1 text-warning"></i>Estatus
                                                </label>
                                                <select class="form-select form-select-sm" id="estatus" name="estatus" required>
                                                    <option value="">Selecciona un estatus</option>
                                                    <option value="transito">En Tránsito</option>
                                                    <option value="retraso">Retrasado</option>
                                                    <option value="frontera">En Frontera</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="ubicacion" class="form-label mb-1">
                                                    <i class="fas fa-location-dot me-1 text-warning"></i>Ubicación
                                                </label>
                                                <input type="text" class="form-control form-control-sm" id="ubicacion"
                                                    name="ubicacion" placeholder="Ej: CD. Valles" required>
                                            </div>

                                            <div class="mb-2">
                                                <label for="observacion" class="form-label mb-1">
                                                    <i class="fas fa-sticky-note me-1 text-warning"></i>Observación (Opcional)
                                                </label>
                                                <textarea class="form-control form-control-sm" id="observacion"
                                                    name="observacion" rows="2"
                                                    placeholder="Agrega cualquier observación relevante..."></textarea>
                                            </div>
                                        </div>

                                        <!-- Footer -->
                                        <div class="modal-footer py-2"
                                            style="background-color: #fafafa; border-top: 1px solid #e5e5e5;">
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                data-bs-dismiss="modal">
                                                <i class="fas fa-times me-1"></i>Cancelar
                                            </button>
                                            <button type="submit" class="btn btn-sm btn-warning text-white">
                                                <i class="fas fa-check me-1"></i>Guardar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                    @empty
                        <div class="col-12">
                            <div class="alert alert-info text-center" role="alert">
                                No hay operaciones registradas para el día de hoy.
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Paginación --}}
                <div class="d-flex justify-content-center mt-4">
                    {{-- Asume que $thermos es una colección paginada. Debes implementarlo en tu controlador --}}
                    {{-- {{ $thermos->links('pagination::bootstrap-5') }} --}}
                </div>
            </div>

            {{-- SIDEBAR CON ESTADÍSTICAS Y GRÁFICA --}}
            <div class="col-lg-4">
                {{-- Sección de Estadísticas --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light text-dark">
                        <h6 class="mb-0">📊 Resumen de la Jornada</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center g-3">
                            <div class="col-6">
                                <div class="p-3 border rounded">
                                    <h4 class="mb-0 text-primary">{{ $stats['total'] }}</h4>
                                    <small class="text-muted">Total del día</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border rounded">
                                    <h4 class="mb-0 text-success">{{ $verde }}</h4>
                                    <small class="text-muted">Modulación Verde</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border rounded">
                                    <h4 class="mb-0 text-danger">{{ $rojo }}</h4>
                                    <small class="text-muted">Modulación Roja</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border rounded">
                                    <h4 class="mb-0 text-warning">{{ $stats['frontera'] }}</h4>
                                    <small class="text-muted">En Frontera</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección para la Gráfica --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light text-dark">
                        <h6 class="mb-0">📈 Distribución de Modulaciones</h6>
                    </div>
                    <div class="card-body">
                        @if ($leyendaModulacion)
                            <div class="alert alert-info text-center mt-4">
                                {{ $leyendaModulacion }}
                            </div>
                        @else
                            <div class="chart-container" style="height: 300px;">
                                <canvas id="modulacionDoughnut"></canvas>
                            </div>
                        @endif
                    </div>

                </div>

                {{-- Espacio para futura gráfica --}}
                {{--<div class="card shadow-sm mb-4">
                    <div class="card-header bg-light text-dark">
                        <h6 class="mb-0">Espacio para Gráfica Adicional</h6>
                    </div>
                    <div class="card-body text-center text-muted">
                        <i class="fas fa-chart-line fa-3x my-3"></i>
                        <p class="mb-0">Aquí se podrá colocar una gráfica futura.</p>
                    </div>
                </div>--}}
            </div>
        </div>
    </div>

    {{-- Scripts de FontAwesome y Bootstrap --}}
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    {{--
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>--}}

    {{-- Script para la Gráfica con Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const labelsModulacion = @json($labelsModulacion);
            const dataModulacion = @json($dataModulacion);
            const backgroundColorsModulacion = @json($backgroundColorsModulacion);

            if (dataModulacion.length > 0) {
                const ctx = document.getElementById('modulacionDoughnut').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
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
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        let label = context.label || '';
                                        if (label) label += ': ';
                                        if (context.parsed !== null) label += context.parsed;
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                // Opcional: mostrar mensaje si no hay datos
                document.getElementById('modulacionDoughnut').replaceWith(
                    document.createTextNode('No hay datos para graficar')
                );
            }
        });


    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Lógica para mostrar/ocultar filtros
            const toggleFiltersBtn = document.getElementById('toggleFilters');
            const filterCard = document.getElementById('filterCard');
            const filterIcon = toggleFiltersBtn.querySelector('i');
            const filterText = toggleFiltersBtn;

            toggleFiltersBtn.addEventListener('click', function () {
                if (filterCard.style.display === 'none' || filterCard.style.display === '') {
                    filterCard.style.display = 'block';
                    filterIcon.classList.remove('fa-filter');
                    filterIcon.classList.add('fa-times');
                    filterText.innerHTML = '<i class="fas fa-times"></i> Ocultar filtros';
                } else {
                    filterCard.style.display = 'none';
                    filterIcon.classList.remove('fa-times');
                    filterIcon.classList.add('fa-filter');
                    filterText.innerHTML = '<i class="fas fa-filter"></i> Mostrar filtros';
                }
            });


        });
    </script>


@endsection