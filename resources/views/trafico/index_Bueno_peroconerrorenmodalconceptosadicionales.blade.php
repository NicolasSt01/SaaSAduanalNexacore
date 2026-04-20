@extends('layouts.app')

@section('title', 'Seguimiento de Tráfico - Lista por Aduana')

@section('customcss')
    <style>
        .list-card {
            border-radius: 10px;
            border: 1px solid #e9ecef;
            background: #fff;
        }

        .thermo-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        }

        .aduana-header {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.08), rgba(14, 165, 233, 0.04));
            border-left: 4px solid rgba(99, 102, 241, 0.2);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .thermo-meta {
            font-size: .875rem;
            color: #6c757d;
        }

        .tooltip-inner {
            max-width: 420px;
            text-align: left;
        }

        .badge-state {
            min-width: 90px;
        }

        .tooltip-inner {
            max-width: 300px !important;
            background-color: #fff !important;
            color: #333 !important;
            border: 1px solid #ddd !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            text-align: left;
            padding: 0 !important;
        }

        .tooltip-card {
            background: #fff;
            border-radius: 8px;
        }

        .tooltip-item strong {
            font-size: 0.9rem;
        }

        .tooltip-item small {
            font-size: 0.75rem;
        }

        .tooltip-arrow::before {
            border-top-color: #fff !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">



        {{-- ============================================ --}}
        {{-- SEGUNDO DIV: 70% Saludo | 30% Botones --}}
        {{-- ============================================ --}}
        <div class="row mb-4">
            {{-- 70% Izquierda: Sección Saludo --}}
            <div class="col-lg-8">
                <h2 class="h4 mb-1">Hola, {{ auth()->user()->name }}! 👋</h2>
                <p class="text-muted mb-0">Dashboard de tráfico en tiempo real.</p>

                {{--<div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 mb-1">Hola, {{ auth()->user()->name }}! 👋</h2>
                        <p class="text-muted mb-0">Dashboard de tráfico en tiempo real.</p>
                    </div>
                </div>--}}
            </div>

            {{-- 30% Derecha: Botones Nuevo trámite y Actualizar modulación --}}
            <div class="col-lg-4">
                <a href="{{ route('trafico.nuevaexpo') }}" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus"></i> Nuevo Trámite
                </a>
                <a href="{{ route('operaciones.actualizarmodulacion2') }}" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-sync"></i> Actualizar Modulación
                </a>
                {{--<div class="card shadow-sm">
                    <div class="card-body d-flex flex-column gap-2">
                        <a href="{{ route('trafico.nuevaexpo') }}" class="btn btn-primary shadow-sm">
                            <i class="fas fa-plus"></i> Nuevo Trámite
                        </a>
                        <a href="{{ route('operaciones.actualizarmodulacion2') }}" class="btn btn-secondary shadow-sm">
                            <i class="fas fa-sync"></i> Actualizar Modulación
                        </a>
                    </div>
                </div>--}}
            </div>
        </div>

        {{-- ============================================ --}}
{{-- SECCIÓN DE REGISTROS PENDIENTES (INCOMPLETOS) --}}
{{-- INSERTAR DESPUÉS DEL DIV DE BOTONES Y ANTES DEL ROW DE 65%-35% --}}
{{-- ============================================ --}}

@if($stats['incompletos'] > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-warning">
            <div class="card-header bg-warning bg-opacity-10 border-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Operaciones Pendientes de Completar
                        <span class="badge bg-warning text-dark ms-2">{{ $stats['incompletos'] }}</span>
                    </h6>
                    <button class="btn btn-sm btn-outline-warning" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#collapsePendientes" aria-expanded="false">
                        <i class="fas fa-chevron-down"></i> Ver registros
                    </button>
                </div>
            </div>
            
            <div class="collapse" id="collapsePendientes">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 100px;">Referencia</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Importador</th>
                                    <th>Producto</th>
                                    <th>Factura</th>
                                    <th class="text-center">Faltantes</th>
                                    <th class="text-center" style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registrosIncompletos as $incompleto)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $incompleto->referencia }}</span>
                                    </td>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($incompleto->fecha)->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $incompleto->cliente->nombre_empresa ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        {{ $incompleto->importador->nombre ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($incompleto->nombre_producto, 30) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $incompleto->num_factura }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                                            @if(empty($incompleto->num_thermo))
                                                <span class="badge bg-warning text-dark" title="Falta Thermo">
                                                    <i class="fas fa-thermometer-half"></i>
                                                </span>
                                            @endif
                                            @if(empty($incompleto->codigo_alpha))
                                                <span class="badge bg-warning text-dark" title="Falta Código Alpha">
                                                    <i class="fas fa-barcode"></i>
                                                </span>
                                            @endif
                                            @if(empty($incompleto->bodega_id))
                                                <span class="badge bg-warning text-dark" title="Falta Bodega">
                                                    <i class="fas fa-warehouse"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('trafico.operaciones.show', $incompleto->id) }}" 
                                           class="btn btn-sm btn-warning rounded-pill px-3"
                                           title="Actualizar información">
                                            <i class="fas fa-edit me-1"></i> Actualizar
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer bg-light text-center py-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Completa estos registros para que aparezcan agrupados por camión
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

        {{-- ============================================ --}}
        {{-- TERCER DIV: 65% Trámites | 35% Resumen + Gráfica --}}
        {{-- ============================================ --}}
        <div class="row">
            {{-- 65% Izquierda: Sección Trámites del Día --}}
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        {{-- Filtros --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Trámites del día</h5>
                            <button class="btn btn-sm btn-outline-secondary" type="button" id="toggleFilters">
                                <i class="fas fa-filter"></i> Mostrar filtros
                            </button>
                        </div>

                        <div class="card shadow-sm mb-4 filter-section" id="filterCard" style="display: none;">
                            <div class="card-body">
                                <form method="GET" action="{{ route('trafico.index') }}">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="estado" class="form-label small">Estado</label>
                                            <select class="form-select" id="estado" name="estado">
                                                <option value="">Todos</option>
                                                <option value="DESADUANAMIENTO LIBRE" {{ request('estado') == 'DESADUANAMIENTO LIBRE' ? 'selected' : '' }}>Verdes</option>
                                                <option value="RECONOCIMIENTO ADUANERO CONCLUIDO" {{ request('estado') == 'RECONOCIMIENTO ADUANERO CONCLUIDO' ? 'selected' : '' }}>Rojos</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="thermo" class="form-label small">No. Thermo</label>
                                            <input type="text" class="form-control" id="thermo"
                                                placeholder="Número de Thermo" name="thermo"
                                                value="{{ request('thermo') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="alpha" class="form-label small">Código Alpha</label>
                                            <input type="text" class="form-control" id="alpha" placeholder="Código Alpha"
                                                name="alpha" value="{{ request('alpha') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="doda" class="form-label small">No. DODA</label>
                                            <input type="text" class="form-control" id="doda" placeholder="Número DODA"
                                                name="doda" value="{{ request('doda') }}">
                                        </div>
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
                                            <a href="{{ route('trafico.index') }}"
                                                class="btn btn-sm btn-outline-secondary">Limpiar</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Lista de Trámites Agrupados por Aduana --}}
                        @php
                            $byAduana = [];
                            foreach ($thermos as $numThermo => $registros) {
                                $adu = $registros->first()->aduana->nombre_aduana ?? 'SIN ADUANA';
                                if (!isset($byAduana[$adu]))
                                    $byAduana[$adu] = [];
                                $byAduana[$adu][$numThermo] = $registros;
                            }
                        @endphp

                        @if(count($byAduana) === 0)
                            <div class="alert alert-info">No hay operaciones registradas para el día de hoy.</div>
                        @endif

                        @foreach($byAduana as $aduana => $thermosList)
                            <div class="aduana-header mb-3">
                                <strong>{{ $aduana }}</strong>
                                <div class="small text-muted">{{ count($thermosList) }} trailer(es)</div>
                            </div>

                            <ul class="list-group list-card mb-4">
                                @foreach($thermosList as $numThermo => $registros)
                                    @php
                                        $first = $registros->first();
                                        $estado = ucfirst($first->modulacion ?? 'Sin Modulación');
                                        $isOverweight = $first->sobrepeso ?? false;
                                        $color = match (strtoupper($first->modulacion)) {
                                            'DESADUANAMIENTO LIBRE' => 'success',
                                            'RECONOCIMIENTO ADUANERO CONCLUIDO' => 'danger',
                                            'RECONOCIMIENTO ADUANERO' => 'danger',
                                            default => 'secondary'
                                        };
                                        [$thermoValue, $alphaValue] = explode('|', $numThermo);
                                        $modalId = $thermoValue . '_' . $alphaValue;

                                        $tooltipHtml = '<div class="p-2 tooltip-card">';
                                        foreach ($registros->take(5) as $r) {
                                            $tooltipHtml .= '
                                                                                                                                                                                                                                                                                                                <div class="tooltip-item mb-2">
                                                                                                                                                                                                                                                                                                                    <strong>' . e($r->cliente->nombre_empresa) . '</strong><br>
                                                                                                                                                                                                                                                                                                                    <span class="text-primary fw-bold">Factura: ' . e($r->num_factura) . '</span><br>
                                                                                                                                                                                                                                                                                                                    <small class="text-muted">Operación: ' . e($r->id) . '</small>
                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                <hr class="my-1">';
                                        }
                                        if ($registros->count() > 5) {
                                            $tooltipHtml .= '<div class="text-muted small">+ ' . ($registros->count() - 5) . ' más...</div>';
                                        }
                                        $tooltipHtml .= '</div>';
                                    @endphp

                                    <li
                                        class="list-group-item d-flex align-items-center justify-content-between thermo-item p-3 border-0">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="pe-2">
                                                <i class="fas fa-truck-moving fa-2x text-muted"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">
                                                    Economico <strong>{{ $numThermo }}</strong>
                                                    @if($isOverweight)
                                                        <i class="fas fa-exclamation-triangle ms-2 text-danger"
                                                            title="Permiso de sobrepeso"></i>
                                                    @endif
                                                </div>
                                                <div class="thermo-meta mt-1">
                                                    <span class="me-2"><i class="fas fa-code"></i>
                                                        {{ $first->codigo_alpha ?? 'Sin Alpha' }}</span>
                                                    <span class="me-2"><i class="fas fa-tag"></i>
                                                        {{ $first->expediente->numero_pedimento ?? 'SIN PEDIMENTO' }}</span>
                                                </div>
                                                <div class="thermo-meta mt-1">
                                                    <small class="text-muted">{{ $registros->count() }} factura(s) — Aduana:
                                                        {{ $aduana }}</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="tooltip"
                                                data-bs-html="true" data-bs-placement="top"
                                                title="{!! htmlspecialchars($tooltipHtml, ENT_QUOTES, 'UTF-8') !!}">
                                                <i class="fas fa-file-invoice"></i>
                                            </button>

                                            {{--<span class="badge bg-{{ $color }} badge-state me-2">{{ $estado }}</span>--}}
                                            <span class="badge bg-{{ $color }} badge-state me-2" style="cursor: pointer"
                                                data-bs-toggle="modal" data-bs-target="#modalModulacion{{ $modalId }}">
                                                {{ $estado }}
                                            </span>

                                            <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#updateStatusModal{{ $modalId }}" title="Registrar estatus">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </button>

                                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#detalleThermoModal{{ $modalId }}">
                                                <i class="fas fa-eye"></i> Ver Detalles
                                            </button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 35% Derecha: Resumen de Jornada + Gráfica --}}
            <div class="col-lg-4">
                {{-- Sección Resumen de Jornada --}}
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
                                    <h4 class="mb-0 text-warning">{{ $stats['incompletos'] }}</h4>
                                    <small class="text-muted">Por Completar</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección Gráfica de distribución de modulaciones --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light text-dark">
                        <h6 class="mb-0">📈 Distribución de Modulaciones</h6>
                    </div>
                    <div class="card-body">
                        @if ($leyendaModulacion)
                            <div class="alert alert-info text-center mt-4">{{ $leyendaModulacion }}</div>
                        @else
                            <div class="chart-container" style="height: 300px;">
                                <canvas id="modulacionDoughnut"></canvas>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODALES --}}
    {{-- ============================================ --}}
    {{--@forelse($thermos as $numThermo => $registros)--}}
    @forelse($thermos as $grupoKey => $registros)
        @php
            [$thermo, $alpha] = explode('|', $grupoKey);
            $modalId = $thermo . '_' . $alpha; // limpio para IDs HTML
            $first = $registros->first();
        @endphp
        
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




        {{-- MODAL DINÁMICO DE DETALLES (REDISEÑADO) --}}
        <div class="modal fade" id="detalleThermoModal{{ $modalId }}" tabindex="-1" aria-hidden="true"
            data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content rounded-4 shadow-lg">
                    <div class="modal-header border-0 rounded-top-4 py-4 px-4"
                        style="background: linear-gradient(45deg, #1e3a8a, #3b82f6); color: white;">
                        <h5 class="modal-title fw-bold">Detalles del Economico: **{{ $first->num_thermo ."|".$first->codigo_alpha }}**</h5>
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
                                    <button type="button" class="btn btn-warning rounded-pill px-4" data-bs-toggle="modal"
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
                                            <div
                                                class="d-flex justify-content-between align-items-center bg-light rounded-pill px-3 py-2 mb-2 shadow-sm">
                                                <span class="small">
                                                    <i class="fas fa-tag text-success me-2"></i>
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $concepto->tipo_concepto)) }}</strong>
                                                </span>
                                                <div class="d-flex align-items-center gap-2">
                                                    {{--<span class="badge bg-success">${{ number_format($concepto->monto, 2)
                                                        }}</span>--}}
                                                    <form action="{{ route('conceptos.destroy', $concepto->id) }}" method="POST"
                                                        onsubmit="return confirm('¿Eliminar este concepto?')" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-danger rounded-circle p-1"
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
                                        data-bs-target="#conceptosModal{{ $modalId }}">
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
                                            <span class="badge bg-light text-dark fw-normal">{{ $exp->estado }}</span>
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
                                <div class="overflow-auto rounded p-3" style="max-height: 400px; background-color: #ffffff;">
                                    @forelse($registros as $exp)
                                        <div class="card shadow-sm mb-3 rounded-3 border-0">
                                            <div
                                                class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-2">
                                                <h6 class="mb-0 fw-bold">Operación REF#{{ $exp->referencia }}</h6>
                                                <div>
                                                    {{-- Botón Ver detalles --}}
                                                    <a href="{{ route('trafico.operaciones.show', $exp->id) }}"
                                                        class="btn btn-sm btn-outline-primary me-2 rounded-pill">
                                                        <i class="fas fa-eye"></i> Ver detalles
                                                    </a>

                                                    {{-- Botón Agregar archivo --}}
                                                    {{--<button type="button" class="btn btn-primary btn-sm rounded-pill"
                                                        data-bs-toggle="modal" data-bs-target="#uploadModal{{ $exp->id }}">
                                                        <i class="fas fa-plus"></i> Agregar archivo
                                                    </button>--}}
                                                </div>

                                                {{--<button type="button" class="btn btn-primary btn-sm rounded-pill"
                                                    data-bs-toggle="modal" data-bs-target="#uploadModal{{ $exp->id }}">
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
                                                                    <span class="small text-truncate me-2">{{ $doc->nombre_documento }}</span>
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
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL ANIDADO PARA AGREGAR CONCEPTOS ADICIONALES --}}
        <div class="modal fade" id="conceptosModal_OLD{{ $modalId }}" tabindex="-1" aria-hidden="true"
            data-bs-backdrop="static">
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

        {{-- MODAL ANIDADO PARA AGREGAR CONCEPTOS ADICIONALES --}}
        <div class="modal fade" id="conceptosModal{{ $modalId }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 shadow-lg">
                    <div class="modal-header bg-success text-white rounded-top-4">
                        <h5 class="modal-title">
                            <i class="fas fa-dollar-sign me-2"></i>Conceptos Adicionales - Economico {{ $numThermo }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form action="{{ route('conceptos.store') }}" method="POST" enctype="multipart/form-data"
                            id="formConcepto{{ $numThermo }}">
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
                            </div>

                            {{-- Monto por defecto oculto --}}
                            <input type="hidden" name="monto" value="0">

                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-comment-dots me-1 text-success"></i>Descripción
                                </label>
                                <textarea class="form-control" name="descripcion" rows="2"
                                    placeholder="Agrega detalles adicionales (opcional)"></textarea>
                                <small class="text-muted">Ejemplo: "Reacomodo de 2 tarimas"</small>
                            </div>

                            {{-- NUEVO: Campo para subir archivo --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-paperclip me-1 text-success"></i>Adjuntar Archivo (Opcional)
                                </label>
                                <input type="file" class="form-control" name="archivo" id="archivoConcepto{{ $numThermo }}"
                                    accept=".pdf,.jpg,.jpeg,.png,.xlsx,.xls,.doc,.docx">
                                <small class="text-muted">Formatos permitidos: PDF, Imágenes, Excel, Word (máx. 50MB)</small>

                                {{-- Vista previa del archivo --}}
                                <div id="previewArchivo{{ $numThermo }}" class="mt-3" style="display: none;">
                                    <div class="card border-success">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div id="iconoArchivo{{ $numThermo }}" class="me-3 fs-2"></div>
                                                    <div>
                                                        <div class="fw-bold" id="nombreArchivo{{ $numThermo }}"></div>
                                                        <small class="text-muted">
                                                            <span id="tipoArchivo{{ $numThermo }}"></span> •
                                                            <span id="tamanoArchivo{{ $numThermo }}"></span>
                                                        </small>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle"
                                                    onclick="quitarArchivo{{ $numThermo }}()" title="Quitar archivo">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

        <script>
            // Script para vista previa del archivo
            document.getElementById('archivoConcepto{{ $numThermo }}').addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    mostrarPreview{{ $numThermo }}(file);
                }
            });

            function mostrarPreview{{ $numThermo }}(file) {
                const preview = document.getElementById('previewArchivo{{ $numThermo }}');
                const icono = document.getElementById('iconoArchivo{{ $numThermo }}');
                const nombre = document.getElementById('nombreArchivo{{ $numThermo }}');
                const tipo = document.getElementById('tipoArchivo{{ $numThermo }}');
                const tamano = document.getElementById('tamanoArchivo{{ $numThermo }}');

                // Obtener extensión del archivo
                const extension = file.name.split('.').pop().toLowerCase();

                // Iconos según tipo de archivo
                const iconos = {
                    'pdf': '<i class="fas fa-file-pdf text-danger"></i>',
                    'jpg': '<i class="fas fa-file-image text-primary"></i>',
                    'jpeg': '<i class="fas fa-file-image text-primary"></i>',
                    'png': '<i class="fas fa-file-image text-primary"></i>',
                    'xlsx': '<i class="fas fa-file-excel text-success"></i>',
                    'xls': '<i class="fas fa-file-excel text-success"></i>',
                    'doc': '<i class="fas fa-file-word text-info"></i>',
                    'docx': '<i class="fas fa-file-word text-info"></i>',
                    'default': '<i class="fas fa-file text-secondary"></i>'
                };

                icono.innerHTML = iconos[extension] || iconos['default'];
                nombre.textContent = file.name;
                tipo.textContent = extension.toUpperCase();
                tamano.textContent = formatBytes(file.size);

                preview.style.display = 'block';
            }

            function quitarArchivo{{ $numThermo }}() {
                document.getElementById('archivoConcepto{{ $numThermo }}').value = '';
                document.getElementById('previewArchivo{{ $numThermo }}').style.display = 'none';
            }

            function formatBytes(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            }
        </script>


        {{-- MODAL ANIDADO para agregar documentos --}}
        @foreach($registros as $exp)
            <div class="modal fade" id="uploadModal{{ $exp->id }}" tabindex="-1" aria-labelledby="uploadModalLabel{{ $exp->id }}"
                aria-hidden="true" data-bs-backdrop="static">
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
                            <form action="{{ route('documentos_operacion.store', ['expediente' => $exp->id]) }}" method="POST"
                                enctype="multipart/form-data">
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
                                    <input type="file" class="form-control" id="archivo{{ $exp->id }}" name="archivo" accept=".*"
                                        required
                                        onchange="document.getElementById('nombre_documento{{ $exp->id }}').value = this.files[0]?.name.split('.').slice(0, -1).join('.')">
                                </div>

                                <input type="hidden" id="nombre_documento{{ $exp->id }}" name="nombre_documento">
                                <input type="hidden" name="fecha_documento" value="{{ date('Y-m-d') }}">

                                <div class="mb-3">
                                    <label for="observaciones{{ $exp->id }}" class="form-label">Observaciones</label>
                                    <textarea name="observaciones" id="observaciones{{ $exp->id }}" class="form-control"></textarea>
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
        <div class="modal fade" id="updateModulacionModal{{ $modalId }}" tabindex="-1"
            aria-labelledby="updateModulacionModalLabel{{ $numThermo }}" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 shadow-lg">
                    <div class="modal-header bg-warning text-dark rounded-top-4">
                        <h5 class="modal-title" id="updateModulacionModalLabel{{ $numThermo }}">Actualizar
                            Estado de Modulación</h5>
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form action="{{ route('operaciones.updatemodulacion', ['num_thermo' => $numThermo]) }}"
                            method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="modulacion_status" class="form-label">Nuevo Estado de
                                    Modulación</label>
                                <select class="form-select rounded-pill" name="modulacion" id="modulacion_status" required>
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
        <div class="modal fade" id="updateStatusModal{{ $modalId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 8px; border: 1px solid #e5e5e5;">
                    <form action="{{ route('recorridos.store') }}" method="POST">
                        @csrf
                        <!-- Header -->
                        <div class="modal-header py-2" style="background-color: #fafafa; border-bottom: 1px solid #e5e5e5;">
                            <h6 class="modal-title mb-0" style="font-weight: 600; color: #333;">
                                <i class="fas fa-truck me-2 text-warning"></i>
                                Actualizar Estatus <small class="text-muted">({{ $numThermo }})</small>
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                <input type="text" class="form-control form-control-sm" id="ubicacion" name="ubicacion"
                                    placeholder="Ej: CD. Valles" required>
                            </div>

                            <div class="mb-2">
                                <label for="observacion" class="form-label mb-1">
                                    <i class="fas fa-sticky-note me-1 text-warning"></i>Observación (Opcional)
                                </label>
                                <textarea class="form-control form-control-sm" id="observacion" name="observacion" rows="2"
                                    placeholder="Agrega cualquier observación relevante..."></textarea>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer py-2" style="background-color: #fafafa; border-top: 1px solid #e5e5e5;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
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

        <!-- Modal Detalle de Modulación -->
        <div class="modal fade" id="modalModulacion{{ $modalId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content" style="border-radius: 10px; overflow: hidden;">

                    <!-- HEADER -->
                    <div class="p-4 text-center" style="background-color: #1a365d;">
                        <img src="https://salassys.com/wp-content/uploads/2025/11/white-2.png" alt="Logo Crosspoint" width="150"
                            class="mb-2">
                    </div>

                    <!-- STATUS CARD -->
                    <div class="text-center p-4"
                        style="background-color: {{ $color == 'green' ? '#10b981' : ($color == 'red' ? '#dc2626' : '#6b7280') }};">

                        <!-- Circle Icon -->
                        <div style="width: 70px; height: 70px; 
                                    background-color: rgba(255,255,255,0.2);
                                    border-radius: 50%; 
                                    margin: 0 auto 15px;
                                    display: flex; justify-content:center; align-items:center;
                                    font-size: 32px; color: #fff;">
                            {{ $color == 'green' ? '✓' : '!' }}
                        </div>

                        <h3 class="text-white fw-bold mb-1">{{ $estado }}</h3>
                        <p class="text-white-50 mb-0" style="font-size: 14px;">
                            @if($color == 'green')
                                Desaduanamiento Libre.
                            @elseif($color == 'red')
                                Reconocimiento Aduanero.
                            @else
                                La operación sigue en proceso.
                            @endif
                        </p>
                    </div>

                    <!-- BODY -->
                    <div class="p-4">

                        <!-- TARJETA DETALLES -->
                        <div class="p-3 mb-3" style="background-color: #f7fafc; border-radius: 8px; border: 1px solid #e2e8f0;">

                            <h5 class="fw-bold mb-3">
                                📋 Detalles del Economico
                            </h5>

                            <!-- Item -->
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-secondary fw-semibold">Económico:</span>
                                <span class="fw-bold">{{ $first->num_thermo }}</span>
                            </div>

                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-secondary fw-semibold">Código Alpha:</span>
                                <span class="fw-bold">{{ $first->codigo_alpha ?? 'N/A' }}</span>
                            </div>

                            

                        </div>

                        <!-- TARJETA FACTURAS -->
                        <div class="p-3" style="background-color: #ebf8ff; border-radius: 8px; border-left: 4px solid #3b82f6;">

                            <h6 class="fw-bold mb-2">📦 Facturas Asociadas</h6>

                            @foreach($registros as $r)
                                <div class="d-flex justify-content-between py-1 border-bottom">
                                    <span class="text-secondary">Factura:</span>
                                    <span class="fw-bold">{{ $r->num_factura }}</span>
                                </div>
                            @endforeach

                            @if (count($registros) == 0)
                                <p class="text-muted mb-0">No hay facturas registradas.</p>
                            @endif
                        </div>

                    </div>

                    <!-- FOOTER -->
                    <div class="p-3 text-end">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">
                            Cerrar
                        </button>
                    </div>

                </div>
            </div>
        </div>




    @empty






    @endforelse

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Toggle filtros
            const toggleFiltersBtn = document.getElementById('toggleFilters');
            const filterCard = document.getElementById('filterCard');

            if (toggleFiltersBtn && filterCard) {
                toggleFiltersBtn.addEventListener('click', function () {
                    if (filterCard.style.display === 'none' || filterCard.style.display === '') {
                        filterCard.style.display = 'block';
                        toggleFiltersBtn.innerHTML = '<i class="fas fa-times"></i> Ocultar filtros';
                    } else {
                        filterCard.style.display = 'none';
                        toggleFiltersBtn.innerHTML = '<i class="fas fa-filter"></i> Mostrar filtros';
                    }
                });
            }

            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Gráfica (si es necesaria)
            @if(isset($labelsModulacion) && isset($dataModulacion))
                const labelsModulacion = @json($labelsModulacion);
                const dataModulacion = @json($dataModulacion);
                const backgroundColorsModulacion = @json($backgroundColorsModulacion);

                if (dataModulacion.length > 0) {
                    const ctx = document.getElementById('modulacionDoughnut')?.getContext('2d');
                    if (ctx) {
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
                                    legend: { position: 'bottom' }
                                }
                            }
                        });
                    }
                }
            @endif
                                    });


        // ========================================================
        // 🔔 SISTEMA DE NOTIFICACIONES COMPLETO
        // ========================================================

        @if(auth()->user()->role === 'Trafico')
            let ultimaConsulta = new Date().toISOString();
            let notificacionesCargadas = false;

            console.log('🔔 Sistema de notificaciones iniciado');

            // Cargar notificaciones iniciales
            cargarNotificacionesCompletas();

            // Iniciar polling cada 10 segundos (más frecuente para pruebas)
            const pollingInterval = setInterval(() => {
                console.log('🔄 Consultando nuevas notificaciones...');
                consultarNuevasNotificaciones();
            }, 10000); // 10 segundos

            // Función para cargar todas las notificaciones
            function cargarNotificacionesCompletas() {
                console.log('📥 Cargando notificaciones...');
                fetch('{{ route('notificaciones.noLeidas') }}')
                    .then(response => response.json())
                    .then(data => {
                        console.log('✅ Notificaciones cargadas:', data);
                        actualizarBadgeGlobal(data.count);
                        mostrarNotificaciones(data.notificaciones);
                        notificacionesCargadas = true;
                    })
                    .catch(error => console.error('❌ Error al cargar notificaciones:', error));
            }

            // Función para consultar solo nuevas notificaciones
            function consultarNuevasNotificaciones_OLD() {
                fetch(`{{ route('notificaciones.nuevas') }}?ultima_consulta=${encodeURIComponent(ultimaConsulta)}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('📊 Respuesta de polling:', data);

                        if (data.nuevas && data.nuevas.length > 0) {
                            console.log('🆕 ¡Nuevas notificaciones detectadas!', data.nuevas.length);

                            // Actualizar timestamp
                            ultimaConsulta = data.timestamp;

                            // Actualizar badge
                            actualizarBadgeGlobal(data.count);

                            // Mostrar toasts
                            data.nuevas.forEach(notif => {
                                console.log('🎯 Mostrando toast para:', notif.titulo);
                                mostrarToast(notif);
                            });

                            // Recargar lista de notificaciones
                            cargarNotificacionesCompletas();

                            // Reproducir sonido (opcional)
                            reproducirSonidoNotificacion();
                        } else {
                            console.log('⏸️ No hay nuevas notificaciones');
                        }
                    })
                    .catch(error => console.error('❌ Error en polling:', error));
            }

            // Función para consultar solo nuevas notificaciones
            function consultarNuevasNotificaciones_OLD2() {
                const url = `{{ route('notificaciones.nuevas') }}?ultima_consulta=${encodeURIComponent(ultimaConsulta)}`;
                console.log('🌐 URL de consulta:', url);
                console.log('⏰ Última consulta guardada:', ultimaConsulta);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        console.log('📊 Respuesta COMPLETA de polling:', data);
                        console.log('📅 Timestamp actual del servidor:', data.timestamp);
                        console.log('🔢 Nuevas encontradas:', data.nuevas?.length || 0);
                        console.log('📋 Total no leídas:', data.count);

                        if (data.debug) {
                            console.log('🐛 Debug info:', data.debug);
                        }

                        if (data.nuevas && data.nuevas.length > 0) {
                            console.log('🆕 ¡NUEVAS NOTIFICACIONES!');
                            console.table(data.nuevas.map(n => ({
                                id: n.id,
                                titulo: n.titulo,
                                created_at: n.created_at
                            })));

                            // Actualizar timestamp ANTES de procesar
                            const nuevoTimestamp = data.timestamp;
                            console.log('🔄 Actualizando timestamp de', ultimaConsulta, 'a', nuevoTimestamp);
                            ultimaConsulta = nuevoTimestamp;

                            // Actualizar badge
                            actualizarBadgeGlobal(data.count);

                            // Mostrar toasts
                            data.nuevas.forEach(notif => {
                                console.log('🎯 Mostrando toast para:', notif.titulo);
                                mostrarToast(notif);
                            });

                            // Recargar lista de notificaciones
                            cargarNotificacionesCompletas();

                            // Reproducir sonido
                            reproducirSonidoNotificacion();
                        } else {
                            console.log('⏸️ No hay nuevas notificaciones (count total:', data.count, ')');
                        }
                    })
                    .catch(error => console.error('❌ Error en polling:', error));
            }

            // Versión simplificada - compara IDs en lugar de timestamps
            let ultimosIdsVistos = new Set();

            function consultarNuevasNotificaciones() {
                fetch('{{ route('notificaciones.noLeidas') }}')
                    .then(response => response.json())
                    .then(data => {
                        console.log('📊 Notificaciones actuales:', data.notificaciones.length);

                        // Actualizar badge
                        actualizarBadgeGlobal(data.count);

                        // Detectar notificaciones realmente nuevas
                        const notificacionesNuevas = data.notificaciones.filter(notif => {
                            return !ultimosIdsVistos.has(notif.id) && !notif.leida;
                        });

                        console.log('🆕 Notificaciones nuevas detectadas:', notificacionesNuevas.length);

                        if (notificacionesNuevas.length > 0) {
                            // Mostrar toasts para las nuevas
                            notificacionesNuevas.forEach(notif => {
                                console.log('🎯 Mostrando toast para:', notif.titulo);
                                mostrarToast(notif);
                                ultimosIdsVistos.add(notif.id);
                            });

                            // Actualizar lista
                            mostrarNotificaciones(data.notificaciones);

                            // Sonido
                            reproducirSonidoNotificacion();
                        }

                        // Actualizar el set de IDs vistos
                        data.notificaciones.forEach(notif => {
                            ultimosIdsVistos.add(notif.id);
                        });
                    })
                    .catch(error => console.error('❌ Error en polling:', error));
            }



            // Mostrar lista de notificaciones en el dropdown
            function mostrarNotificaciones(notificaciones) {
                const lista = document.getElementById('notificationList');
                if (!lista) return;

                console.log('📝 Mostrando', notificaciones.length, 'notificaciones en dropdown');

                if (notificaciones.length === 0) {
                    lista.innerHTML = `
                                                                <div class="empty-notifications">
                                                                    <i class="fas fa-bell-slash"></i>
                                                                    <p class="mb-0">No hay notificaciones</p>
                                                                </div>
                                                            `;
                    return;
                }

                lista.innerHTML = notificaciones.map(notif => {
                    const colorClass = notif.tipo === 'documento_subido' ? 'success' :
                        notif.tipo === 'operacion_completada' ? 'info' : 'warning';
                    const icon = notif.tipo === 'documento_subido' ? 'fa-file-upload' :
                        notif.tipo === 'operacion_completada' ? 'fa-check-circle' : 'fa-sync-alt';

                    return `
                                                                <div class="notification-item ${notif.leida ? '' : 'unread'}" 
                                                                     onclick="marcarComoLeida(${notif.id}, ${notif.operacion_id})">
                                                                    <div class="notification-icon ${colorClass}">
                                                                        <i class="fas ${icon}"></i>
                                                                    </div>
                                                                    <div class="notification-content">
                                                                        <div class="notification-title">${notif.titulo}</div>
                                                                        <div class="notification-message">${notif.mensaje}</div>
                                                                        <div class="notification-time">
                                                                            <i class="fas fa-clock me-1"></i>${formatearTiempo(notif.created_at)}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            `;
                }).join('');
            }

            // Mostrar toast de notificación
            function mostrarToast(notif) {
                console.log('🎨 Creando toast para:', notif.titulo);

                const container = document.getElementById('toastContainer');
                if (!container) {
                    console.error('❌ No se encontró el contenedor de toasts');
                    return;
                }

                const colorClass = notif.tipo === 'documento_subido' ? 'success' :
                    notif.tipo === 'operacion_completada' ? 'info' : 'warning';
                const icon = notif.tipo === 'documento_subido' ? 'fa-file-upload' :
                    notif.tipo === 'operacion_completada' ? 'fa-check-circle' : 'fa-sync-alt';

                const toast = document.createElement('div');
                toast.className = `toast-custom ${colorClass}`;
                toast.innerHTML = `
                                                            <div class="toast-icon ${colorClass}">
                                                                <i class="fas ${icon}"></i>
                                                            </div>
                                                            <div class="toast-content">
                                                                <div class="toast-title">${notif.titulo}</div>
                                                                <div class="toast-message">${notif.mensaje}</div>
                                                                <div class="toast-actions">
                                                                    <button class="btn btn-sm btn-primary" onclick="verDetalleNotificacion(${notif.id}, '${notif.datos?.num_thermo || ''}')">
                                                                        Ver ahora
                                                                    </button>
                                                                    <button class="btn btn-sm btn-outline-secondary" onclick="cerrarToast(this)">
                                                                        Descartar
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span class="toast-close" onclick="cerrarToast(this)">×</span>
                                                        `;

                container.appendChild(toast);
                console.log('✅ Toast agregado al DOM');

                // Auto-cerrar después de 7 segundos
                setTimeout(() => {
                    if (toast.parentElement) {
                        cerrarToast(toast);
                    }
                }, 7000);
            }

            // Cerrar toast
            window.cerrarToast = function (element) {
                const toast = element.closest ? element.closest('.toast-custom') : element;
                if (toast) {
                    toast.classList.add('hiding');
                    setTimeout(() => {
                        if (toast.parentElement) {
                            toast.remove();
                        }
                    }, 300);
                }
            }

            // Ver detalle de notificación
            window.verDetalleNotificacion = function (notifId, numThermo) {
                console.log('👁️ Ver detalle:', notifId, numThermo);

                // Marcar como leída
                marcarComoLeida(notifId);

                // Buscar y abrir el modal del thermo
                if (numThermo) {
                    const modal = document.getElementById(`detalleThermoModal${numThermo}`);
                    if (modal) {
                        const modalInstance = new bootstrap.Modal(modal);
                        modalInstance.show();
                        console.log('✅ Modal abierto');
                    } else {
                        console.warn('⚠️ No se encontró el modal para el thermo:', numThermo);
                    }
                }
            }

            // Marcar notificación como leída
            window.marcarComoLeida = function (notifId, operacionId = null) {
                console.log('✔️ Marcando como leída:', notifId);

                fetch(`/notificaciones/${notifId}/marcar-leida`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('✅ Marcada como leída');
                            actualizarBadgeGlobal(data.count);
                            cargarNotificacionesCompletas();
                        }
                    })
                    .catch(error => console.error('❌ Error al marcar como leída:', error));
            }

            // Formatear tiempo relativo
            function formatearTiempo(fechaISO) {
                const fecha = new Date(fechaISO);
                const ahora = new Date();
                const diff = Math.floor((ahora - fecha) / 1000);

                if (diff < 60) return 'Hace un momento';
                if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`;
                if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} horas`;
                if (diff < 604800) return `Hace ${Math.floor(diff / 86400)} días`;

                return fecha.toLocaleDateString('es-MX');
            }

            // Reproducir sonido de notificación (opcional)
            function reproducirSonidoNotificacion() {
                // Puedes agregar un audio aquí si quieres
                // const audio = new Audio('/sounds/notification.mp3');
                // audio.play().catch(e => console.log('No se pudo reproducir el sonido'));
            }

            // Limpiar interval cuando se sale de la página
            window.addEventListener('beforeunload', function () {
                clearInterval(pollingInterval);
            });

            console.log('✅ Sistema de notificaciones completamente inicializado');
        @endif



    </script>

@endsection