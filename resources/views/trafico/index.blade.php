@extends('layouts.app')

@section('title', 'Mesa de Control - Tráfico')

@section('customcss')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --bg-body: #f8fafc;
            --border-color: #e2e8f0;
            --row-hover: #f1f5f9;
        }
$byAduana = [];
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            font-size: 0.85rem;
            overflow-x: hidden;
        }

        /* ----- LAYOUT ----- */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .content-area {
            flex: 1;
            padding: 1.5rem;
            margin-right: 320px;
            overflow-y: auto;
        }

        .sidebar-right {
            width: 320px;
            background: #fff;
            border-left: 1px solid var(--border-color);
            padding: 1.5rem;
            position: fixed;
            right: 0;
            top: 70px;
            /* 🔥 Ajustado para que empiece debajo del navbar */
            bottom: 0;
            height: calc(100vh - 60px);
            /* 🔥 Altura total menos el navbar */
            box-sizing: border-box;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.02);
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 900;
            /* 🔥 Menor que el z-index del navbar (usualmente 1000+) */
        }

        /* ----- HEADER ----- */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        /* ----- COMPACT LIST ----- */
        .aduana-section {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .aduana-header {
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
            font-weight: 700;
            color: #475569;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .aduana-header.priority {
            background: #eff6ff;
            color: #1e40af;
            border-bottom-color: #bfdbfe;
        }

        .compact-table {
            width: 100%;
            border-collapse: collapse;
        }

        .compact-table th {
            text-align: left;
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
        }

        .compact-table td {
            padding: 0.5rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .compact-table tr:hover {
            background-color: var(--row-hover);
        }

        /* Column Styles */
        .col-thermo {
            font-weight: 700;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .col-alpha {
            font-size: 0.75rem;
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            color: #475569;
            margin-left: 5px;
        }

        .col-client {
            font-weight: 500;
        }

        .col-factura {
            font-family: monospace;
            color: #64748b;
            font-size: 0.8rem;
        }

        /* Status Badges */
        .badge-status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .bg-green {
            background: #dcfce7;
            color: #166534;
        }

        .bg-red {
            background: #fee2e2;
            color: #991b1b;
        }

        .bg-yellow {
            background: #fef3c7;
            color: #92400e;
        }

        .pulse {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Action Buttons */
        .btn-ack {
            padding: 3px 10px;
            font-size: 0.7rem;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            background: white;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-ack:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
        }

        /* ----- SIDEBAR STATS ----- */
        .stat-item {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed #e2e8f0;
        }

        .stat-val {
            font-weight: 700;
            color: #1e293b;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.85rem;
        }

        /* ----- PROGRESS CHART ----- */
        .progress-widget {
            text-align: center;
            margin-top: 2rem;
        }

        .circular-chart {
            display: block;
            margin: 10px auto;
            max-width: 80%;
            max-height: 200px;
        }

        .circle-bg {
            fill: none;
            stroke: #eee;
            stroke-width: 3.8;
        }

        .circle {
            fill: none;
            stroke-width: 2.8;
            stroke-linecap: round;
        }

        .percentage {
            fill: #666;
            font-family: sans-serif;
            font-weight: bold;
            font-size: 0.5em;
            text-anchor: middle;
        }

        /* Warning Section */
        .warning-section {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .warning-header {
            padding: 0.75rem 1rem;
            background: #fef3c7;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            color: #92400e;
            transition: background 0.2s ease;
            user-select: none;
        }

        .warning-header:hover {
            background: #fde68a;
        }

        .warning-header i.fa-chevron-down {
            transition: transform 0.3s ease;
            color: #92400e;
        }

        .warning-header.expanded i.fa-chevron-down {
            transform: rotate(180deg);
        }

        .warning-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding: 0 1rem;
        }

        .warning-body.show {
            max-height: 2000px;
            /* Ajusta según necesites */
            padding: 1rem;
        }

        .warning-item {
            background: white;
            padding: 0.5rem;
            border-radius: 4px;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
        }

        .warning-section h6 {
            color: #92400e;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .warning-item {
            background: white;
            padding: 0.5rem;
            border-radius: 4px;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
        }

        /* 🔥 ACKNOWLEDGE FUNCTIONALITY STYLES */
        .history-section {
            margin-top: 0.5rem;
            border-top: 1px dashed var(--border-color);
        }

        .history-header {
            padding: 0.75rem 1rem;
            background: #f8fafc;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #64748b;
            transition: background 0.2s ease;
        }

        .history-header:hover {
            background: #f1f5f9;
        }

        .history-header i {
            transition: transform 0.3s ease;
        }

        .history-header.expanded i {
            transform: rotate(180deg);
        }

        .history-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .history-body.show {
            max-height: 1000px;
        }

        .history-body table {
            opacity: 0.7;
        }

        .history-body tr {
            background-color: #fafafa;
        }

        .btn-acknowledge {
            background: #10b981;
            color: white;
            border: none;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .btn-acknowledge:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
        }

        .btn-acknowledge:active {
            transform: translateY(0);
        }

        /* ----- FILTERS ----- */
        .filter-bar {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: none;
            /* Hidden by default */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .filter-bar.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .filter-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
        }

        .filter-input {
            width: 100%;
            padding: 0.4rem 0.75rem;
            font-size: 0.85rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: #1e293b;
            background-color: #f8fafc;
            transition: all 0.2s;
        }

        .filter-input:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-filter-toggle {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-filter-toggle:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .btn-filter-toggle.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Row animation when moving to history */
        @keyframes fadeToHistory {
            0% {
                opacity: 1;
                background-color: #fff;
            }

            50% {
                background-color: #d1fae5;
            }

            100% {
                opacity: 0;
                transform: translateX(-20px);
            }
        }

        .acknowledging {
            animation: fadeToHistory 0.5s ease forwards;
        }
    </style>
@endsection

@section('content')
    <div class="main-wrapper">
        {{-- MAIN CONTENT --}}
        <div class="content-area">
            {{-- Header con botón de nueva operación --}}
            <div class="page-header">
                <h4 class="page-title">
                    Tráfico
                </h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn-filter-toggle" id="toggleFilters">
                        <i class="fas fa-filter"></i> Filtros
                    </button>
                    <a href="{{ route('trafico.nuevaexpo') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nueva Operación
                    </a>
                </div>
            </div>

            {{-- Filter Bar --}}
            <form id="filterForm" class="filter-bar">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label>Estado</label>
                        <select name="estado" class="filter-input">
                            <option value="">Todos</option>
                            <option value="DESADUANAMIENTO LIBRE">Verdes</option>
                            <option value="RECONOCIMIENTO ADUANERO CONCLUIDO">Rojos</option>
                            <option value="0">Sin Modulación</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>No. Thermo</label>
                        <input type="text" name="thermo" class="filter-input" placeholder="Ej: 5312">
                    </div>
                    <div class="filter-group">
                        <label>Código Alpha</label>
                        <input type="text" name="alpha" class="filter-input" placeholder="Ej: AMZN">
                    </div>
                    <div class="filter-group">
                        <label>Desde</label>
                        <input type="date" name="fecha_desde" class="filter-input" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="filter-group">
                        <label>Hasta</label>
                        <input type="date" name="fecha_hasta" class="filter-input" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="filter-group">
                        <label>No. DODA</label>
                        <input type="text" name="doda" class="filter-input" placeholder="Ej: 123456">
                    </div>
                    <div class="filter-group">
                        <label>Cliente</label>
                        <input type="text" name="cliente" class="filter-input" placeholder="Nombre del cliente">
                    </div>
                    <div class="filter-group">
                        <label>No. Pedimento</label>
                        <input type="text" name="pedimento" class="filter-input" placeholder="Ej: 3040-...">
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3 gap-2">
                    <button type="reset" class="btn-ack" onclick="setTimeout(refreshDashboardData, 10)">Limpiar</button>
                    <button type="button" class="btn btn-primary btn-sm px-3"
                        onclick="refreshDashboardData()">Aplicar</button>
                </div>
            </form>

            {{-- Sección de Operaciones Incompletas --}}
            @if($stats['incompletos'] > 0)
                <div class="warning-section">
                    <div class="warning-header" onclick="toggleIncompletos()">
                        <span>
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Operaciones Pendientes de Completar
                            <span class="badge bg-warning text-dark ms-1"
                                id="incompletos-count">{{ $stats['incompletos'] }}</span>
                        </span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="warning-body" id="incompletos-body">
                        @foreach($registrosIncompletos as $incompleto)
                            <div class="warning-item">
                                <strong>{{ $incompleto->cliente->nombre_empresa ?? 'N/A' }}</strong> -
                                Factura: {{ $incompleto->num_factura }} -
                                <span class="text-muted">Ref: {{ $incompleto->referencia }}</span>
                                <a href="{{ route('trafico.operaciones.show', $incompleto->id) }}"
                                    class="btn btn-sm btn-warning btn-ack ms-2">
                                    <i class="fas fa-edit"></i> Completar
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Lista de Camiones por Aduana --}}
            @php
                 $byAduana = [];
                  foreach ($thermos as $numThermo => $registros) {
                       $adu = $registros->first()->aduana->nombre_aduana ?? 'SIN ADUANA';
                       if (!isset($byAduana[$adu]))
                          $byAduana[$adu] = [];
                     $byAduana[$adu][$numThermo] = $registros;
                   }
    
                   // 🔥 ORDENAR: REYNOSA primero, luego el resto alfabéticamente
                   uksort($byAduana, function($a, $b) {
                       $aUpper = strtoupper($a);
                       $bUpper = strtoupper($b);
        
                       // Si A es REYNOSA, va primero
                      if ($aUpper === 'REYNOSA') return -1;
                      // Si B es REYNOSA, va primero
                      if ($bUpper === 'REYNOSA') return 1;
                      // Si ninguno es REYNOSA, orden alfabético
                     return strcmp($aUpper, $bUpper);
                 });
            @endphp

            @forelse($byAduana as $aduana => $thermosList)
                @php $aduanaSlug = Str::slug($aduana); @endphp
                <div class="aduana-section" id="section-{{ $aduanaSlug }}">
                    <div class="aduana-header {{ strtoupper($aduana) === 'REYNOSA' ? 'priority' : '' }}">
                        <span>
                            <i class="fas fa-map-marker-alt me-2"></i>{{ strtoupper($aduana) }}
                        </span>
                        <span class="badge bg-secondary">{{ count($thermosList) }} unidades</span>
                    </div>

                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th style="width: 15%">Unidad</th>
                                <th style="width: 25%">Cliente</th>
                                <th style="width: 20%">Facturas</th>
                                <th style="width: 15%">Estatus</th>
                                <th style="width: 25%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $activas = [];
                                $historial = [];

                                foreach ($thermosList as $grupoKey => $registros) {
                                    if ($registros->first()->traffic_acknowledged) {
                                        $historial[$grupoKey] = $registros;
                                    } else {
                                        $activas[$grupoKey] = $registros;
                                    }
                                }
                            @endphp

                            @foreach($activas as $grupoKey => $registros)
                                @php
                                    $parts = explode('|', $grupoKey);
                                    $thermo = $parts[0] ?? '';
                                    $alpha = $parts[1] ?? '';
                                    $fechaRaw = $parts[2] ?? date('Y-m-d');
                                    $fecha = substr($fechaRaw, 0, 10);
                                    $modalId = str_replace([' ', '-', ':'], '_', $thermo . '_' . $alpha . '_' . $fecha);
                                    $first = $registros->first();

                                    $estadoOperacion = strtolower($first->estado ?? 'pendiente');
                                    $modulacion = $first->modulacion;

                                    $estadoMostrar = ucfirst($estadoOperacion);
                                    $color = match ($estadoOperacion) {
                                        'terminado' => 'green',
                                        'proceso', 'en proceso' => 'yellow',
                                        default => 'muted'
                                    };

                                    if ($estadoOperacion === 'terminado' && $modulacion) {
                                        $modulacionUpper = strtoupper($modulacion);
                                        if (
                                            $modulacionUpper === 'DESADUANAMIENTO LIBRE' ||
                                            str_contains($modulacionUpper, 'RECONOCIMIENTO')
                                        ) {
                                            $estadoMostrar = ucfirst($modulacion);
                                            $color = match (true) {
                                                $modulacionUpper === 'DESADUANAMIENTO LIBRE' => 'green',
                                                str_contains($modulacionUpper, 'RECONOCIMIENTO') => 'red',
                                                default => 'yellow'
                                            };
                                        }
                                    }

                                    $facturas = $registros->pluck('num_factura')->take(3)->implode(', ');
                                    if ($registros->count() > 3)
                                        $facturas .= '...';
                                @endphp
                                <tr>
                                    <td>
                                        <span class="col-thermo">{{ $thermo }}</span>
                                        <span class="col-alpha">{{ $alpha }}</span>
                                        <div class="small text-muted mt-1" style="font-size: 0.7rem;">
                                            <i class="far fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::parse($fecha)->format('m/d/Y') }}
                                        </div>
                                    </td>
                                    <td class="col-client">{{ $first->cliente->nombre_empresa ?? 'N/A' }}</td>
                                    <td class="col-factura">{{ $facturas }}</td>
                                    <td>
                                        <span class="badge-status bg-{{ $color }}" onclick="loadModulacionModal('{{ $thermo }}', '{{ $alpha }}', '{{ $fecha }}')">
                                            <span class="pulse"></span> {{ $estadoMostrar }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-ack" onclick="loadDetalleModal('{{ $thermo }}', '{{ $alpha }}', '{{ $fecha }}')">
                                            <i class="fas fa-eye me-1"></i> Ver Detalles
                                        </button>
                                        <button class="btn-ack ms-1" onclick="loadUbicacionModal('{{ $thermo }}', '{{ $alpha }}', '{{ $fecha }}')">
                                            <i class="fas fa-map-marker-alt"></i> Ubicación
                                        </button>

                                        @php
                                            $showAcknowledge = $estadoOperacion === 'terminado' && $modulacion &&
                                                (strtoupper($modulacion) === 'DESADUANAMIENTO LIBRE' ||
                                                    str_contains(strtoupper($modulacion), 'RECONOCIMIENTO'));
                                        @endphp

                                        @if($showAcknowledge)
                                            <button class="btn-acknowledge ms-1"
                                                onclick="acknowledgeOperation({{ $first->id }}, '{{ $modalId }}')">
                                                <i class="fas fa-check"></i> Enterado
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- 🔥 SECCIÓN DE HISTORIAL COLAPSABLE --}}
                    <div class="history-section" id="history-section-{{ $aduanaSlug }}"
                        style="{{ count($historial) == 0 ? 'display: none;' : '' }}">
                        <div class="history-header" onclick="toggleHistory('{{ $aduanaSlug }}')">
                            <span>
                                <i class="fas fa-history me-2"></i>
                                Historial
                                <span class="badge bg-secondary ms-1"
                                    id="history-count-{{ $aduanaSlug }}">{{ count($historial) }}</span>
                            </span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="history-body" id="history-body-{{ $aduanaSlug }}">
                            <table class="compact-table">
                                <tbody>
                                    @foreach($historial as $grupoKey => $registros)
                                        @php
                                            $parts = explode('|', $grupoKey);
                                            $thermo = $parts[0] ?? '';
                                            $alpha = $parts[1] ?? '';
                                            $fechaRaw = $parts[2] ?? date('Y-m-d');
                                            $fecha = substr($fechaRaw, 0, 10);
                                            $modalId = str_replace([' ', '-', ':'], '_', $thermo . '_' . $alpha . '_' . $fecha);
                                            $first = $registros->first();
                                            $modulacionUpper = strtoupper($first->modulacion ?? '');
                                            $color = match (true) {
                                                $modulacionUpper === 'DESADUANAMIENTO LIBRE' => 'green',
                                                str_contains($modulacionUpper, 'RECONOCIMIENTO') => 'red',
                                                default => 'yellow'
                                            };
                                            $facturas = $registros->pluck('num_factura')->take(3)->implode(', ');
                                            if ($registros->count() > 3)
                                                $facturas .= '...';
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="col-thermo">{{ $thermo }}</span>
                                                <span class="col-alpha">{{ $alpha }}</span>
                                                <div class="small text-muted mt-1" style="font-size: 0.7rem;">
                                                    <i class="far fa-calendar-alt me-1"></i>{{ \Carbon\Carbon::parse($fecha)->format('m/d/Y') }}
                                                </div>
                                            </td>
                                            <td class="col-client">{{ $first->cliente->nombre_empresa ?? 'N/A' }}</td>
                                            <td class="col-factura">{{ $facturas }}</td>
                                            <td>
                                                <span class="badge-status bg-{{ $color }}">
                                                    {{ ucfirst($first->modulacion) }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn-ack" onclick="loadDetalleModal('{{ $thermo }}', '{{ $alpha }}', '{{ $fecha }}')">
                                                    <i class="fas fa-eye me-1"></i> Ver Detalles
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No hay operaciones registradas para el día de hoy.
                </div>
            @endforelse
        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="sidebar-right">
            <h6 class="text-uppercase text-muted fw-bold mb-4">Métricas del Día</h6>

            <div class="stat-item">
                <span class="stat-label">Total Operaciones</span>
                <span class="stat-val">{{ $stats['total'] }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Pendientes</span>
                <span class="stat-val text-warning">{{ $stats['incompletos'] }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Verde (Libre)</span>
                <span class="stat-val text-success">{{ $verde }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Rojo (Reconocimiento)</span>
                <span class="stat-val text-danger">{{ $rojo }}</span>
            </div>

            <div class="progress-widget">
                <h5 class="mb-3">Progreso Global</h5>
                @php
                    $total = $stats['total'];
                    $completados = $verde + $rojo;
                    $porcentaje = $total > 0 ? round(($completados / $total) * 100) : 0;
                @endphp
                <svg viewBox="0 0 36 36" class="circular-chart">
                    <path class="circle-bg"
                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <path class="circle" stroke="#16a34a" stroke-dasharray="{{ $porcentaje }}, 100"
                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <text x="18" y="20.35" class="percentage">{{ $porcentaje }}%</text>
                </svg>
                <p class="small text-muted mt-2">{{ $completados }} de {{ $total }} camiones modulados</p>
            </div>

            @if(!$leyendaModulacion && count($dataModulacion) > 0)
                <div class="mt-4">
                    <h6 class="text-uppercase text-muted fw-bold mb-3">Distribución</h6>
                    <div style="height: 150px; position: relative;">
                        <canvas id="modulacionChart"></canvas>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODALES --}}
    {{-- ============================================ --}}
    {{-- ============================================ --}}
    {{-- MODAL ÚNICO DINÁMICO --}}
    {{-- ============================================ --}}

    {{-- MODAL DE DETALLES --}}
    <div class="modal fade" id="detalleThermoModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header border-0 rounded-top-4 py-4 px-4"
                    style="background: linear-gradient(45deg, #1e3a8a, #3b82f6); color: white;">
                    <h5 class="modal-title fw-bold" id="modalThermoTitle">
                        Cargando...
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-5" id="modalThermoBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL AGREGAR CONCEPTOS --}}
    <div class="modal fade" id="conceptosModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title" id="conceptosModalTitle">
                        <i class="fas fa-dollar-sign me-2"></i>Conceptos Adicionales
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="conceptosModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL ACTUALIZAR UBICACIÓN --}}
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 8px;">
                <div class="modal-header py-2" style="background-color: #fafafa;">
                    <h6 class="modal-title mb-0 fw-semibold" id="updateStatusModalTitle">
                        <i class="fas fa-truck me-2 text-warning"></i>
                        Actualizar Estatus
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="updateStatusModalBody">
                    <div class="text-center py-3">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DETALLE DE MODULACIÓN --}}
    <div class="modal fade" id="modalModulacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content" style="border-radius: 10px; overflow: hidden;" id="modalModulacionContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let modulacionChart = null;

        document.addEventListener('DOMContentLoaded', function () {
            // Toggle Filters
            document.getElementById('toggleFilters').addEventListener('click', function () {
                this.classList.toggle('active');
                const filterBar = document.getElementById('filterForm');
                filterBar.classList.toggle('show');
            });

            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Gráfica de modulación inicial
            @if(!$leyendaModulacion && count($dataModulacion) > 0)
                const ctx = document.getElementById('modulacionChart');
                if (ctx) {
                    modulacionChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: @json($labelsModulacion),
                            datasets: [{
                                data: @json($dataModulacion),
                                backgroundColor: @json($backgroundColorsModulacion),
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
                                        font: { size: 10 }
                                    }
                                }
                            }
                        }
                    });
                }
            @endif

            // 🔥 SISTEMA DE ACTUALIZACIÓN EN TIEMPO REAL
            startRealTimeUpdates();
        });

        // Función para iniciar actualizaciones automáticas
        function startRealTimeUpdates() {
            // Actualizar cada 10 segundos
            setInterval(function () {
                refreshDashboardData();
            }, 10000);
        }

        // Función para obtener datos actualizados via AJAX
        function refreshDashboardData() {
            // Recoger filtros del formulario
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams();

            formData.forEach((value, key) => {
                if (value) params.append(key, value);
            });

            fetch('{{ route("trafico.dashboard.ajax") }}?' + params.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStatistics(data.stats, data.verde, data.rojo);
                        updateTruckLists(data.byAduana);
                        updateIncompletos(data.incompletos);
                        console.log('Dashboard actualizado:', data.timestamp);
                    }
                })
                .catch(error => {
                    console.error('Error actualizando dashboard:', error);
                });
        }

        // Actualizar estadísticas del sidebar
        function updateStatistics(stats, verde, rojo) {
            // Actualizar valores de estadísticas
            const statItems = document.querySelectorAll('.stat-item');
            if (statItems[0]) statItems[0].querySelector('.stat-val').textContent = stats.total;
            if (statItems[1]) statItems[1].querySelector('.stat-val').textContent = stats.incompletos;
            if (statItems[2]) statItems[2].querySelector('.stat-val').textContent = verde;
            if (statItems[3]) statItems[3].querySelector('.stat-val').textContent = rojo;

            // Actualizar progreso circular
            const total = stats.total;
            const completados = verde + rojo;
            const porcentaje = total > 0 ? Math.round((completados / total) * 100) : 0;

            const circleProgress = document.querySelector('.circle');
            const percentageText = document.querySelector('.percentage');
            const progressDesc = document.querySelector('.progress-widget p');

            if (circleProgress) {
                circleProgress.setAttribute('stroke-dasharray', `${porcentaje}, 100`);
            }
            if (percentageText) {
                percentageText.textContent = `${porcentaje}%`;
            }
            if (progressDesc) {
                progressDesc.textContent = `${completados} de ${total} camiones modulados`;
            }
        }

        // Función para estandarizar slugs (idéntico a Str::slug de Laravel)
        function slugify(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-')           // Reemplazar espacios por -
                .replace(/[^\w\-]+/g, '')       // Remover caracteres no alfanuméricos
                .replace(/\-\-+/g, '-')         // Reemplazar múltiples - por uno solo
                .replace(/^-+/, '')             // Remover - al inicio
                .replace(/-+$/, '');            // Remover - al final
        }

        // Actualizar lista de camiones por aduana
        function updateTruckLists(byAduana) {
            const contentArea = document.querySelector('.content-area');

            for (const [aduana, data] of Object.entries(byAduana)) {
                const slug = slugify(aduana);
                let section = document.getElementById(`section-${slug}`);

                if (section) {
                    // Actualizar tabla activa
                    const tbody = section.querySelector('table:first-of-type tbody');
                    const badgeCount = section.querySelector('.aduana-header .badge');
                    if (badgeCount) badgeCount.textContent = `${data.activas.length} unidades`;
                    if (tbody) tbody.innerHTML = createTruckRowsHtml(data.activas, false);

                    // Actualizar historial
                    const historyTbody = section.querySelector('.history-body tbody');
                    const historyCount = document.getElementById(`history-count-${slug}`);
                    const historySection = document.getElementById(`history-section-${slug}`);

                    if (historyCount) historyCount.textContent = data.historial.length;
                    if (historyTbody) historyTbody.innerHTML = createTruckRowsHtml(data.historial, true);

                    if (historySection) {
                        historySection.style.display = data.historial.length > 0 ? 'block' : 'none';
                    }
                } else {
                    const newSection = createAduanaSection(aduana, data);
                    const alertInfo = contentArea.querySelector('.alert-info');
                    if (alertInfo) contentArea.insertBefore(newSection, alertInfo);
                    else contentArea.appendChild(newSection);
                }
            }
        }

        // Actualizar createTruckRowsHtml para usar el modal único
        function createTruckRowsHtml(trucks, isHistory) {
            let html = '';
            trucks.forEach(truck => {
                const badgeClass = `badge-status bg-${truck.color}`;

                const isCompleted = truck.estado.toLowerCase() === 'terminado' ||
                    truck.color === 'green' ||
                    truck.color === 'red';

                html += `
                                                        <tr>
                                                            <td>
                                                                <span class="col-thermo">${truck.thermo}</span>
                                                                <span class="col-alpha">${truck.alpha}</span>
                                                                <div class="small text-muted mt-1" style="font-size: 0.7rem;">
                                                                    <i class="far fa-calendar-alt me-1"></i>${truck.fecha ? (() => { const p = truck.fecha.substring(0,10).split('-'); return `${p[1]}/${p[2]}/${p[0]}`; })() : 'N/A'}
                                                                </div>
                                                            </td>
                                                            <td class="col-client">${truck.cliente}</td>
                                                            <td class="col-factura">${truck.facturas}</td>
                                                            <td>
                                                                <span class="${badgeClass}" 
                                                                      onclick="loadModulacionModal('${truck.thermo}', '${truck.alpha}', '${truck.fecha}')"
                                                                      style="cursor: pointer;">
                                                                    ${!isHistory ? '<span class="pulse"></span>' : ''} ${truck.estado}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button class="btn-ack" onclick="loadDetalleModal('${truck.thermo}', '${truck.alpha}', '${truck.fecha}')">
                                                                    <i class="fas fa-eye me-1"></i> Ver Detalles
                                                                </button>
                                                                ${!isHistory ? `
                                                                    <button class="btn-ack ms-1" onclick="loadUbicacionModal('${truck.thermo}', '${truck.alpha}', '${truck.fecha}')">
                                                                        <i class="fas fa-map-marker-alt"></i> Ubicación
                                                                    </button>
                                                                    ${isCompleted ? `
                                                                        <button class="btn-acknowledge ms-1" 
                                                                                onclick="acknowledgeOperation(${truck.id}, '${truck.modalId}')">
                                                                            <i class="fas fa-check"></i> Enterado
                                                                        </button>
                                                                    ` : ''}
                                                                ` : ''}
                                                            </td>
                                                        </tr>`;
            });
            return html;
        }

        // Función para cargar modal de detalles
        function loadDetalleModal(thermo, alpha, fecha) {
            const modal = new bootstrap.Modal(document.getElementById('detalleThermoModal'));
            document.getElementById('modalThermoTitle').textContent = `Detalles del Económico: ${thermo} | ${alpha}`;
            document.getElementById('modalThermoBody').innerHTML = `
                                                    <div class="text-center py-5">
                                                        <div class="spinner-border text-primary" role="status">
                                                            <span class="visually-hidden">Cargando...</span>
                                                        </div>
                                                    </div>`;

            modal.show();

            // 🔥 Usar la fecha específica del grupo
            const fechaDesde = fecha;
            const fechaHasta = fecha;

            const url = `/trafico/modal-detalle/${encodeURIComponent(thermo)}/${encodeURIComponent(alpha)}?fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        document.getElementById('modalThermoBody').innerHTML = data.html;
                    } else {
                        document.getElementById('modalThermoBody').innerHTML = `
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        ${data.message || 'No se encontraron datos'}
                                                    </div>`;
                    }
                })
                .catch(error => {
                    console.error('Error completo:', error);
                    document.getElementById('modalThermoBody').innerHTML = `
                                                <div class="alert alert-danger">
                                                    <i class="fas fa-times-circle me-2"></i>
                                                    Error al cargar los detalles
                                                </div>`;
                });

            /*fetch(`/trafico/modal-detalle/${thermo}/${alpha}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('modalThermoBody').innerHTML = data.html;
                    } else {
                        document.getElementById('modalThermoBody').innerHTML = `
                                <div class="alert alert-danger">Error al cargar los detalles</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('modalThermoBody').innerHTML = `
                            <div class="alert alert-danger">Error de conexión</div>`;
                });*/
        }

        // Función para cargar modal de ubicación
        function loadUbicacionModal(thermo, alpha, fecha) {
            const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
            document.getElementById('updateStatusModalTitle').innerHTML = `
                                                    <i class="fas fa-truck me-2 text-warning"></i>
                                                    Actualizar Estatus <small class="text-muted">(${thermo})</small>`;

            document.getElementById('updateStatusModalBody').innerHTML = `
                                                    <div class="text-center py-3">
                                                        <div class="spinner-border text-warning" role="status">
                                                            <span class="visually-hidden">Cargando...</span>
                                                        </div>
                                                    </div>`;

            modal.show();

            // 🔥 Usar la fecha específica del grupo
            const fechaDesde = fecha;
            const fechaHasta = fecha;

            const url = `/trafico/modal-ubicacion/${encodeURIComponent(thermo)}/${encodeURIComponent(alpha)}?fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        document.getElementById('updateStatusModalBody').innerHTML = data.html;
                    } else {
                        document.getElementById('updateStatusModalBody').innerHTML = `
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        ${data.message || 'No se encontraron datos'}
                                                    </div>`;
                    }
                })
                .catch(error => {
                    console.error('Error completo:', error);
                    document.getElementById('updateStatusModalBody').innerHTML = `
                                                <div class="alert alert-danger">
                                                    <i class="fas fa-times-circle me-2"></i>
                                                    Error al cargar los detalles
                                                </div>`;
                });
            ///---------------------------------------------------------------------

            /*fetch(`/trafico/modal-ubicacion/${thermo}/${alpha}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('updateStatusModalBody').innerHTML = data.html;
                    }
                })
                .catch(error => console.error('Error:', error));*/
        }

        // Función para cargar modal de modulación
        function loadModulacionModal(thermo, alpha, fecha) {
            const modal = new bootstrap.Modal(document.getElementById('modalModulacion'));
            document.getElementById('modalModulacionContent').innerHTML = `
                                                    <div class="text-center py-5">
                                                        <div class="spinner-border text-primary" role="status">
                                                            <span class="visually-hidden">Cargando...</span>
                                                        </div>
                                                    </div>`;

            modal.show();
            // 🔥 Usar la fecha específica del grupo
            const fechaDesde = fecha;
            const fechaHasta = fecha;

            const url = `/trafico/modal-modulacion/${encodeURIComponent(thermo)}/${encodeURIComponent(alpha)}?fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        document.getElementById('modalModulacionContent').innerHTML = data.html;
                    } else {
                        document.getElementById('modalModulacionContent').innerHTML = `
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        ${data.message || 'No se encontraron datos'}
                                                    </div>`;
                    }
                })
                .catch(error => {
                    console.error('Error completo:', error);
                    document.getElementById('modalModulacionContent').innerHTML = `
                                                <div class="alert alert-danger">
                                                    <i class="fas fa-times-circle me-2"></i>
                                                    Error al cargar los detalles
                                                </div>`;
                });
            ///---------------------------------------------------------------------

            /*fetch(`/trafico/modal-modulacion/${thermo}/${alpha}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('modalModulacionContent').innerHTML = data.html;
                    }
                })
                .catch(error => console.error('Error:', error));*/
        }
        // Crear sección de aduana completa (con historial)
        function createAduanaSection(aduana, data) {
            const slug = slugify(aduana);
            const isPriority = aduana.toUpperCase() === 'REYNOSA';
            const section = document.createElement('div');
            section.className = 'aduana-section';
            section.id = `section-${slug}`;

            const headerClass = isPriority ? 'aduana-header priority' : 'aduana-header';

            section.innerHTML = `
                                                                <div class="${headerClass}">
                                                                    <span>
                                                                        <i class="fas fa-map-marker-alt me-2"></i>${aduana.toUpperCase()}
                                                                    </span>
                                                                    <span class="badge bg-secondary">${data.activas.length} unidades</span>
                                                                </div>
                                                                <table class="compact-table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th style="width: 15%">Unidad</th>
                                                                            <th style="width: 25%">Cliente</th>
                                                                            <th style="width: 20%">Facturas</th>
                                                                            <th style="width: 15%">Estatus</th>
                                                                            <th style="width: 25%">Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        ${createTruckRowsHtml(data.activas, false)}
                                                                    </tbody>
                                                                </table>

                                                                <div class="history-section" id="history-section-${slug}" style="${data.historial.length === 0 ? 'display: none;' : ''}">
                                                                    <div class="history-header" onclick="toggleHistory('${slug}')">
                                                                        <span>
                                                                            <i class="fas fa-history me-2"></i>
                                                                            Historial 
                                                                            <span class="badge bg-secondary ms-1" id="history-count-${slug}">${data.historial.length}</span>
                                                                        </span>
                                                                        <i class="fas fa-chevron-down"></i>
                                                                    </div>
                                                                    <div class="history-body" id="history-body-${slug}">
                                                                        <table class="compact-table">
                                                                            <tbody>
                                                                                ${createTruckRowsHtml(data.historial, true)}
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>`;

            return section;
        }

        // Actualizar sección de incompletos
        // Actualizar sección de incompletos
        function updateIncompletos(incompletos) {
            const existingSection = document.querySelector('.warning-section');

            if (incompletos.length > 0) {
                // Guardar el estado actual (expandido o colapsado)
                const wasExpanded = existingSection ?
                    existingSection.querySelector('.warning-body')?.classList.contains('show') :
                    false;

                let bodyClass = wasExpanded ? 'warning-body show' : 'warning-body';
                let headerClass = wasExpanded ? 'warning-header expanded' : 'warning-header';

                let html = `
                <div class="${headerClass}" onclick="toggleIncompletos()">
                    <span>
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Operaciones Pendientes de Completar 
                        <span class="badge bg-warning text-dark ms-1" id="incompletos-count">${incompletos.length}</span>
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="${bodyClass}" id="incompletos-body">`;

                incompletos.forEach(inc => {
                    html += `
                    <div class="warning-item">
                        <strong>${inc.cliente}</strong> - 
                        Factura: ${inc.num_factura} - 
                        <span class="text-muted">Ref: ${inc.referencia}</span>
                        <a href="/trafico/operaciones/${inc.id}" 
                           class="btn btn-sm btn-warning btn-ack ms-2">
                            <i class="fas fa-edit"></i> Completar
                        </a>
                    </div>`;
                });

                html += '</div>';

                if (existingSection) {
                    existingSection.innerHTML = html;
                } else {
                    const section = document.createElement('div');
                    section.className = 'warning-section';
                    section.innerHTML = html;
                    const contentArea = document.querySelector('.content-area');
                    const filterBar = document.getElementById('filterForm');
                    if (filterBar) {
                        filterBar.after(section);
                    } else {
                        const pageHeader = contentArea.querySelector('.page-header');
                        pageHeader.after(section);
                    }
                }
            } else {
                // Si no hay incompletos, remover la sección
                if (existingSection) {
                    existingSection.remove();
                }
            }
        }
        // 🔥 FUNCIONES PARA "ENTERADO" (ACKNOWLEDGE)
        function acknowledgeOperation(id, modalId) {
            if (!confirm('¿Marcar esta operación como enterada? Se moverá al historial.')) return;

            const row = event.target.closest('tr');
            row.classList.add('acknowledging');

            fetch(`/trafico/acknowledge/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        setTimeout(() => {
                            moveToHistory(row);
                        }, 500);
                    } else {
                        row.classList.remove('acknowledging');
                        alert('Error al procesar: ' + (data.error || 'Desconocido'));
                    }
                })
                .catch(error => {
                    row.classList.remove('acknowledging');
                    console.error('Error:', error);
                    alert('Error de conexión');
                });
        }

        function moveToHistory(row) {
            const aduanaSection = row.closest('.aduana-section');
            const aduanaId = aduanaSection.id.replace('section-', ''); // Asumiendo que tenemos slugs

            // Si no tenemos ID de aduana directo del elemento, lo buscamos por el ID del historial
            const historyBody = aduanaSection.querySelector('.history-body tbody');
            const historyCount = aduanaSection.querySelector('[id^="history-count-"]');

            // Quitar el botón de enterado
            const btnAck = row.querySelector('.btn-acknowledge');
            if (btnAck) btnAck.remove();

            // Mover al historial
            historyBody.appendChild(row);
            row.classList.remove('acknowledging');

            // Actualizar contador
            if (historyCount) {
                historyCount.textContent = parseInt(historyCount.textContent) + 1;
                aduanaSection.querySelector('.history-section').style.display = 'block';
            }

            // Si la tabla principal quedó vacía, podríamos ocultar la aduana o poner un mensaje
            const mainTbody = aduanaSection.querySelector('table:first-of-type tbody');
            if (mainTbody.children.length === 0) {
                // Opcional: manejar tabla vacía
            }
        }

        function toggleHistory(slug) {
            const body = document.getElementById(`history-body-${slug}`);
            const header = body.previousElementSibling;

            body.classList.toggle('show');
            header.classList.toggle('expanded');
        }

        // Función para cargar modal de conceptos
        function loadConceptosModal(thermo, alpha, fecha) {
            const modal = new bootstrap.Modal(document.getElementById('conceptosModal'));
            document.getElementById('conceptosModalTitle').innerHTML = `
                                                <i class="fas fa-dollar-sign me-2"></i>Conceptos Adicionales - Económico ${thermo} | ${alpha}`;

            document.getElementById('conceptosModalBody').innerHTML = `
                                                <div class="text-center py-3">
                                                    <div class="spinner-border text-success" role="status">
                                                        <span class="visually-hidden">Cargando...</span>
                                                    </div>
                                                </div>`;

            modal.show();

            // 🔥 Usar la fecha específica del grupo
            const fechaDesde = fecha;
            const fechaHasta = fecha;

            const url = `/trafico/modal-conceptos/${encodeURIComponent(thermo)}/${encodeURIComponent(alpha)}?fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        document.getElementById('conceptosModalBody').innerHTML = data.html;
                    } else {
                        document.getElementById('conceptosModalBody').innerHTML = `
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        ${data.message || 'No se encontraron datos'}
                                                    </div>`;
                    }
                })
                .catch(error => {
                    console.error('Error completo:', error);
                    document.getElementById('conceptosModalBody').innerHTML = `
                                                <div class="alert alert-danger">
                                                    <i class="fas fa-times-circle me-2"></i>
                                                    Error al cargar los detalles
                                                </div>`;
                });
            ///---------------------------------------------------------------------
            /*fetch(`/trafico/modal-conceptos/${thermo}/${alpha}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('conceptosModalBody').innerHTML = data.html;
                    }
                })
                .catch(error => console.error('Error:', error));*/
        }

        // Manejar el submit del formulario de conceptos
        function handleConceptoSubmit_old(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cerrar modal de conceptos
                        bootstrap.Modal.getInstance(document.getElementById('conceptosModal')).hide();

                        // Mostrar mensaje de éxito
                        alert('Concepto agregado exitosamente');

                        // Recargar el modal de detalles si está abierto
                        const detalleModal = document.getElementById('detalleThermoModal');
                        if (detalleModal.classList.contains('show')) {
                            const title = document.getElementById('modalThermoTitle').textContent;
                            const match = title.match(/(\S+)\s*\|\s*(\S+)/);
                            if (match) {
                                loadDetalleModal(match[1], match[2]);
                            }
                        }
                    } else {
                        alert('Error al guardar: ' + (data.message || 'Desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });

            return false;
        }
        
        
        //-----------------------------------
        // Manejar el submit del formulario de conceptos
function handleConceptoSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    // Mostrar spinner en el botón
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal de conceptos
            bootstrap.Modal.getInstance(document.getElementById('conceptosModal')).hide();
            
            // Mostrar mensaje de éxito
            mostrarMensajeExito('Concepto agregado exitosamente');
            
            // Limpiar formulario
            form.reset();
            quitarArchivo();
            
            // Recargar el modal de detalles si está abierto
            const detalleModal = document.getElementById('detalleThermoModal');
            if (detalleModal && detalleModal.classList.contains('show')) {
                const title = document.getElementById('modalThermoTitle').textContent;
                const match = title.match(/(\S+)\s*\|\s*(\S+)/);
                
                // Buscar la fecha en el modal si es posible, o usar la actual
                // Como ya pasamos la fecha al cargar, lo ideal es guardarla o buscarla en un data attribute
                const url = document.querySelector('#modalThermoBody .btn-refresh-detalle')?.dataset.fecha || '';
                
                if (match) {
                    loadDetalleModal(match[1], match[2], url);
                }
            }
        } else {
            mostrarMensajeError('Error al guardar: ' + (data.message || 'Desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensajeError('Error al procesar la solicitud');
    })
    .finally(() => {
        // Restaurar botón
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
    
    return false;
}

// Funciones auxiliares para mostrar mensajes
function mostrarMensajeExito(mensaje) {
    // Puedes usar SweetAlert2, Toastr, o simplemente alert
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: mensaje,
            timer: 2000,
            showConfirmButton: false
        });
    } else {
        alert(mensaje);
    }
}

function mostrarMensajeError(mensaje) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: mensaje
        });
    } else {
        alert(mensaje);
    }
}
        
        
        
        
        //-----------------------------------

        // Función para toggle de incompletos
        function toggleIncompletos() {
            const body = document.getElementById('incompletos-body');
            const header = body.previousElementSibling;

            body.classList.toggle('show');
            header.classList.toggle('expanded');
        }


    </script>
@endsection