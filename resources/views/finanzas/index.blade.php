@extends('layouts.app')
@section('customcss')
    <style>
        .expediente-card {
            transition: all 0.3s ease;
            border-left: 4px solid #6c757d;
        }

        .expediente-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .expediente-card.facturado {
            border-left-color: #28a745;
            background-color: #f0f9f4;
        }

        .expediente-card.parcial {
            border-left-color: #ffc107;
            background-color: #fffbf0;
        }

        .stat-box {
            padding: 0.75rem;
            border-radius: 0.5rem;
            background-color: #f8f9fa;
        }

        /* 🆕 ESTILOS PARA FILTROS MEJORADOS */
        .filter-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .filter-card .card-header {
            background: transparent;
            border: none;
            color: white;
            padding: 1.5rem;
        }

        .filter-card .card-body {
            background: white;
            border-radius: 0 0 15px 15px;
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            padding: 0.65rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .btn-filter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.65rem 2rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-clear {
            background: #6c757d;
            border: none;
            padding: 0.65rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            color: white;
        }

        .btn-clear:hover {
            background: #5a6268;
            color: white;
        }

        /* 🆕 BADGES DE ESTADO */
        .filter-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            background: #e7f3ff;
            color: #0056b3;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .filter-badge i {
            margin-right: 0.3rem;
        }

        /* 🆕 TABLA MEJORADA */
        .table-modern {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table-modern thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .table-modern thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table-modern tbody tr {
            transition: all 0.2s ease;
        }

        .table-modern tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
        }

        .table-modern tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        /* 🆕 CARD DE CLIENTE MEJORADA */
        .client-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .client-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .client-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
        }

        .client-header h5 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .totals-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }

        .total-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
        }

        .total-item strong {
            font-weight: 700;
        }

        /* 🆕 BOTONES DE ACCIÓN MEJORADOS */
        .btn-action-group {
            display: flex;
            gap: 0.5rem;
        }

        .btn-view {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-invoice {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-invoice:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.4);
            color: white;
        }

        /* 🆕 EMPTY STATE MEJORADO */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .empty-state h4 {
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #6c757d;
        }

        /* ESTILOS DEL MODAL (mantener los existentes) */
        :root {
            --primary-color: #667eea;
            --primary-dark: #764ba2;
            --text-color: #2c3e50;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            --border-radius: 16px;
        }

        #modalFacturar .modal-dialog {
            max-width: 420px;
        }

        #modalFacturar .modal-content {
            border-radius: var(--border-radius);
            border: none;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .ticket-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 30px 20px 25px 20px;
            text-align: center;
            position: relative;
        }

        .ticket-header h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1.5em;
            letter-spacing: 0.5px;
        }

        .ticket-header .pedimento-ref {
            font-size: 0.9em;
            opacity: 0.95;
            margin-top: 8px;
            font-weight: 400;
        }

        .ticket-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        .ticket-body {
            padding: 30px 25px 20px 25px;
            background-color: var(--card-bg);
        }

        .form-group-modern {
            margin-bottom: 20px;
        }

        .label-modern {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9em;
            color: var(--primary-dark);
        }

        .input-field-modern {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            font-size: 1.0em;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .input-field-modern:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            background-color: white;
        }

        .barcode-section {
            padding: 20px 25px 25px 25px;
            text-align: center;
            background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
            border-top: 2px dashed #dee2e6;
        }

        .barcode-strip {
            height: 60px;
            width: 100%;
            margin: 12px 0;
            background: repeating-linear-gradient(to right,
                    #2c3e50, #2c3e50 2px,
                    transparent 2px, transparent 4px);
            border-radius: 6px;
            opacity: 0.85;
        }

        .modal-footer-modern {
            padding: 20px 25px;
            background-color: #f8f9fa;
            border-top: none;
            gap: 10px;
        }

        .btn-modern {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.0em;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-cancel-modern {
            background-color: #e9ecef;
            color: #495057;
        }

        .btn-save-modern {
            background-color: var(--primary-color);
            color: white;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-4">

        {{-- ========================== FILTROS MEJORADOS ================================ --}}
        <div class="card filter-card mb-4">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-filter me-2"></i>
                    Resumen Operativo – Finanzas
                </h4>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('finanzas.index') }}" id="formFiltros">
                    <div class="row g-3">

                        {{-- 🆕 BÚSQUEDA POR NÚMERO DE PEDIMENTO --}}
                        <div class="col-12">
                            <div class="alert alert-info border-0" style="background: #e7f3ff; border-radius: 10px;">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Tip:</strong> Busca por número de pedimento para encontrar un pedimento específico, 
                                o usa los filtros de año/semana/cliente para ver listados completos.
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                <i class="fas fa-search me-2"></i>
                                Buscar por Número de Pedimento
                            </label>
                            <input 
                                type="text" 
                                name="numero_pedimento" 
                                class="form-control" 
                                placeholder="Ej: 24-47-3807-6002476"
                                value="{{ $numeroPedimento ?? '' }}"
                                id="inputPedimento"
                            >
                            <small class="text-muted">
                                Si buscas por pedimento, se ignorarán los filtros de año y semana.
                            </small>
                        </div>

                        <div class="col-12"><hr></div>

                        {{-- Año --}}
                        <div class="col-md-3">
                            <label class="form-label">
                                <i class="fas fa-calendar me-2"></i>
                                Año Fiscal
                            </label>
                            <select name="anio" class="form-select" id="selectAnio">
                                @for ($a = now()->year; $a >= now()->year - 5; $a--)
                                    <option value="{{ $a }}" {{ $a == $anio ? 'selected' : '' }}>
                                        {{ $a }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        {{-- Semana --}}
                        <div class="col-md-3">
                            <label class="form-label">
                                <i class="fas fa-calendar-week me-2"></i>
                                Semana Fiscal
                            </label>
                            <select name="semana" class="form-select" id="selectSemana">
                                <option value="">Todas las semanas</option>
                                @for ($s = 1; $s <= 53; $s++)
                                    <option value="{{ $s }}" {{ $s == $semana ? 'selected' : '' }}>
                                        Semana {{ str_pad($s, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        {{-- Cliente --}}
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-building me-2"></i>
                                Cliente
                            </label>
                            <select name="cliente_id" class="form-select" id="selectCliente">
                                <option value="">Todos los clientes</option>
                                @foreach ($clientes as $c)
                                    <option value="{{ $c->id }}" {{ $clienteId == $c->id ? 'selected' : '' }}>
                                        {{ $c->nombre_empresa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Botones --}}
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-filter btn-primary flex-grow-1">
                                <i class="fas fa-search me-2"></i>
                                Buscar
                            </button>
                            <a href="{{ route('finanzas.index') }}" class="btn btn-clear" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>

                    </div>

                    {{-- 🆕 MOSTRAR FILTROS ACTIVOS --}}
                    @if($numeroPedimento || $clienteId || $semana)
                        <div class="mt-3">
                            <strong class="me-2">Filtros activos:</strong>
                            
                            @if($numeroPedimento)
                                <span class="filter-badge">
                                    <i class="fas fa-file-alt"></i>
                                    Pedimento: {{ $numeroPedimento }}
                                </span>
                            @endif

                            @if($clienteId)
                                <span class="filter-badge">
                                    <i class="fas fa-building"></i>
                                    Cliente: {{ $clientes->find($clienteId)->nombre_empresa ?? 'N/A' }}
                                </span>
                            @endif

                            @if($semana)
                                <span class="filter-badge">
                                    <i class="fas fa-calendar-week"></i>
                                    Año: {{ $anio }} - Semana: {{ $semana }}
                                </span>
                            @else
                                <span class="filter-badge">
                                    <i class="fas fa-calendar"></i>
                                    Año: {{ $anio }}
                                </span>
                            @endif
                        </div>
                    @endif
                </form>
            </div>
        </div>


        {{-- ========================== RESUMEN POR CLIENTE ================================ --}}
        @forelse ($resumen as $item)
            <div class="client-card">
                <div class="client-header">
                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                        <div>
                            <h5>
                                <i class="fas fa-building me-2"></i>
                                {{ $item['nombre_empresa'] }}
                            </h5>
                            <div class="totals-summary">
                                <div class="total-item">
                                    <i class="fas fa-id-card me-1"></i>
                                    <strong>Patentes:</strong> {{ $item['totales']['patentes'] }}
                                </div>
                                <div class="total-item">
                                    <i class="fas fa-file-alt me-1"></i>
                                    <strong>Pedimentos:</strong> {{ $item['totales']['pedimentos'] }}
                                </div>
                                <div class="total-item">
                                    <i class="fas fa-boxes me-1"></i>
                                    <strong>Remesas:</strong> {{ $item['totales']['remesas'] }}
                                </div>
                                <div class="total-item">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <strong>Rojos:</strong> {{ $item['totales']['rojos'] }}
                                </div>
                                <div class="total-item">
                                    <i class="fas fa-weight me-1"></i>
                                    <strong>Sobrepesos:</strong> {{ $item['totales']['sobrepesos'] }}
                                </div>
                                <div class="total-item">
                                    <i class="fas fa-balance-scale me-1"></i>
                                    <strong>Taras:</strong> {{ $item['totales']['taras'] }}
                                </div>
                                <div class="total-item">
                                    <i class="fas fa-plus-circle me-1"></i>
                                    <strong>Adicionales:</strong> {{ $item['totales']['adicionales'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================== TABLA DETALLADA ================================ --}}
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-id-card me-2"></i>Aduana</th>
                                    <th><i class="fas fa-id-card me-2"></i>Patente</th>
                                    <th><i class="fas fa-file-alt me-2"></i>Pedimento</th>
                                    <th><i class="fas fa-calendar me-2"></i>Fecha</th>
                                    <th class="text-center"><i class="fas fa-boxes me-2"></i>Remesas</th>
                                    <th class="text-center"><i class="fas fa-exclamation-triangle me-2"></i>Rojos</th>
                                    <th class="text-center"><i class="fas fa-weight me-2"></i>Sobrepesos</th>
                                    <th class="text-center"><i class="fas fa-balance-scale me-2"></i>Taras</th>
                                    <th class="text-center"><i class="fas fa-plus-circle me-2"></i>Adicionales</th>
                                    <th class="text-center"><i class="fas fa-cog me-2"></i>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($item['detalle'] as $row)
                                    <tr>
                                        <td><strong>{{ $row['aduana'] }}</strong></td>
                                        <td><strong>{{ $row['patente'] }}</strong></td>
                                        <td>
                                            @if($row['tiene_pedimento_pagado'])
                                                <i class="fas fa-check-circle text-success me-1"></i>
                                            @endif
                                            <span class="badge bg-primary">{{ $row['pedimento'] }}</span>
                                        </td>
                                        <td>
                                            @if($row['fecha'])
                                                {{ \Carbon\Carbon::parse($row['fecha'])->format('d/m/Y') }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $row['remesas'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($row['rojos'] > 0)
                                                <span class="badge bg-danger">{{ $row['rojos'] }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($row['sobrepesos'] > 0)
                                                <span class="badge bg-warning text-dark">{{ $row['sobrepesos'] }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($row['taras'] > 0)
                                                <span class="badge bg-secondary">{{ $row['taras'] }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($row['adicionales'] > 0)
                                                <span class="badge bg-success">{{ $row['adicionales'] }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if ($row['pedimento'] && $row['pedimento'] !== 'N/A')
                                                <div class="btn-action-group justify-content-center">
                                                    <a href="{{ route('finanzas.detalle.expediente', $row['id']) }}"
                                                        class="btn btn-sm btn-view" title="Ver detalles">
                                                        <i class="fas fa-eye me-1"></i> Ver
                                                    </a>

                                                    <button class="btn btn-sm btn-invoice btnFacturar" 
                                                        data-id="{{ $row['id'] }}"
                                                        data-pedimento="{{ $row['pedimento'] }}" 
                                                        data-patente="{{ $row['patente'] }}"
                                                        data-remesas="{{ $row['remesas'] }}" 
                                                        data-rojos="{{ $row['rojos'] }}"
                                                        data-sobrepesos="{{ $row['sobrepesos'] }}" 
                                                        data-taras="{{ $row['taras'] }}"
                                                        data-adicionales="{{ $row['adicionales'] }}" 
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalFacturar" 
                                                        title="Facturar expediente">
                                                        <i class="fas fa-file-invoice me-1"></i> Facturar
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-muted small">Sin pedimento</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($item['detalle']->isEmpty())
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay registros para este cliente.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h4>No se encontraron resultados</h4>
                <p>No hay información para los filtros seleccionados. Intenta ajustar tus criterios de búsqueda.</p>
            </div>
        @endforelse
    </div>

    {{-- ========================== MODAL FACTURAR (SIN CAMBIOS) ================================ --}}
    <div class="modal fade" id="modalFacturar" tabindex="-1" aria-labelledby="modalFacturarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="ticket-header">
                    <h5 id="modalFacturarLabel">Facturar Pedimento</h5>
                    <div class="pedimento-ref">
                        Ref: <strong id="labelPedimento">---</strong>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formFacturar">
                    @csrf
                    <input type="hidden" name="pedimento_id" id="pedimento_id">
                    <input type="hidden" name="year" id="factura_year">
                    <input type="hidden" name="semana" id="factura_week">
                    <input type="hidden" name="cantidad_tramites" id="cantidad_tramites">
                    <input type="hidden" name="cantidad_rojos" id="cantidad_rojos">
                    <input type="hidden" name="cantidad_sobrepesos" id="cantidad_sobrepesos">
                    <input type="hidden" name="cantidad_taras" id="cantidad_taras">
                    <input type="hidden" name="monto_adicionales" id="monto_adicionales">
                    <input type="hidden" name="estado" value="pendiente">

                    <div class="ticket-body">
                        <div class="form-group-modern">
                            <label for="numero_factura" class="label-modern">
                                <i class="fas fa-hashtag"></i> Número de Factura
                            </label>
                            <input type="text" class="input-field-modern" name="numero_factura" id="numero_factura"
                                placeholder="Ej: FAC-2025-001" required autocomplete="off">
                        </div>

                        <div class="form-group-modern">
                            <label for="fecha_factura" class="label-modern">
                                <i class="fas fa-calendar-alt"></i> Fecha de Emisión
                            </label>
                            <input type="date" class="input-field-modern" name="fecha_factura" id="fecha_factura" required>
                        </div>

                        <div class="form-group-modern">
                            <label for="monto_total" class="label-modern">
                                <i class="fas fa-dollar-sign"></i> Monto Total
                            </label>
                            <input type="number" step="0.01" class="input-field-modern" name="monto_total" id="monto_total"
                                placeholder="0.00" required>
                        </div>

                        <div class="form-group-modern">
                            <label for="notas_adicionales" class="label-modern">
                                <i class="fas fa-sticky-note"></i> Notas (opcional)
                            </label>
                            <textarea class="input-field-modern" name="notas_adicionales" id="notas_adicionales" rows="2"
                                placeholder="Observaciones adicionales..." style="resize: vertical;"></textarea>
                        </div>
                    </div>

                    <div class="barcode-section">
                        <div class="barcode-label">Código de Registro</div>
                        <div class="barcode-strip"></div>
                        <div class="barcode-number" id="barcodeNumber">---</div>
                    </div>

                    <div class="modal-footer modal-footer-modern">
                        <button type="button" class="btn btn-modern btn-cancel-modern" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-modern btn-save-modern" id="btnGuardarFactura">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================== JAVASCRIPT ================================ --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // ==========================================
            // 🆕 LÓGICA DE FILTROS INTELIGENTES
            // ==========================================
            const inputPedimento = document.getElementById('inputPedimento');
            const selectAnio = document.getElementById('selectAnio');
            const selectSemana = document.getElementById('selectSemana');
            const selectCliente = document.getElementById('selectCliente');

            // Cuando se escribe en el campo de pedimento, deshabilitar otros filtros
            inputPedimento.addEventListener('input', function() {
                const hasPedimento = this.value.trim() !== '';
                
                selectAnio.disabled = hasPedimento;
                selectSemana.disabled = hasPedimento;
                selectCliente.disabled = hasPedimento;

                if (hasPedimento) {
                    selectAnio.style.opacity = '0.5';
                    selectSemana.style.opacity = '0.5';
                    selectCliente.style.opacity = '0.5';
                } else {
                    selectAnio.style.opacity = '1';
                    selectSemana.style.opacity = '1';
                    selectCliente.style.opacity = '1';
                }
            });

            // ==========================================
            // DETECTAR CLIC EN BOTÓN FACTURAR
            // ==========================================
            document.querySelectorAll(".btnFacturar").forEach(btn => {
                btn.addEventListener("click", function () {
                    const id = this.dataset.id;
                    const pedimento = this.dataset.pedimento;
                    const patente = this.dataset.patente || '';
                    const remesas = this.dataset.remesas || 0;
                    const rojos = this.dataset.rojos || 0;
                    const sobrepesos = this.dataset.sobrepesos || 0;
                    const taras = this.dataset.taras || 0;
                    const adicionales = this.dataset.adicionales || 0;

                    console.log('📦 Datos capturados:', {
                        id, pedimento, patente, remesas, rojos, sobrepesos, taras, adicionales
                    });

                    // Poblar campos hidden
                    document.getElementById("pedimento_id").value = id;
                    document.getElementById("cantidad_tramites").value = remesas;
                    document.getElementById("cantidad_rojos").value = rojos;
                    document.getElementById("cantidad_sobrepesos").value = sobrepesos;
                    document.getElementById("cantidad_taras").value = taras;
                    document.getElementById("monto_adicionales").value = adicionales;

                    // Mostrar pedimento en el header
                    document.getElementById("labelPedimento").innerText = pedimento;
                    document.getElementById("barcodeNumber").innerText = pedimento.toUpperCase();

                    // Calcular año y semana actual
                    const hoy = new Date();
                    const year = hoy.getFullYear();
                    const oneJan = new Date(hoy.getFullYear(), 0, 1);
                    const numberOfDays = Math.floor((hoy - oneJan) / (24 * 60 * 60 * 1000));
                    const week = Math.ceil((hoy.getDay() + 1 + numberOfDays) / 7);

                    document.getElementById("factura_year").value = year;
                    document.getElementById("factura_week").value = week;
                    document.getElementById("fecha_factura").valueAsDate = hoy;

                    // Limpiar campos visibles
                    document.getElementById("numero_factura").value = '';
                    document.getElementById("monto_total").value = '';
                    document.getElementById("notas_adicionales").value = '';

                    setTimeout(() => document.getElementById("numero_factura").focus(), 300);
                });
            });

            // ==========================================
            // GUARDAR FACTURA VÍA AJAX
            // ==========================================
            document.getElementById("formFacturar").addEventListener("submit", async function (e) {
                e.preventDefault();

                const btnGuardar = document.getElementById("btnGuardarFactura");
                const btnOriginalHTML = btnGuardar.innerHTML;

                try {
                    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                    btnGuardar.disabled = true;

                    const formData = new FormData(this);

                    const response = await fetch("{{ route('finanzas.factura.guardar') }}", {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "X-Requested-With": "XMLHttpRequest",
                            "Accept": "application/json"
                        }
                    });

                    const responseText = await response.text();
                    let data;

                    try {
                        data = JSON.parse(responseText);
                    } catch (jsonError) {
                        console.error('❌ ERROR AL PARSEAR JSON:', responseText.substring(0, 500));
                        throw new Error('El servidor respondió con formato inválido');
                    }

                    if (!response.ok) {
                        throw new Error(data.message || 'Error del servidor');
                    }

                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "¡Factura guardada!",
                            text: data.message || "La factura se guardó correctamente",
                            confirmButtonColor: "#667eea",
                            confirmButtonText: "Aceptar"
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || "Error al guardar la factura");
                    }

                } catch (error) {
                    console.error('❌ ERROR:', error);

                    Swal.fire({
                        icon: "error",
                        title: "Error al guardar",
                        html: `<div style="text-align: left;">
                                    <strong>Error:</strong><br>
                                    ${error.message}
                                </div>`,
                        confirmButtonColor: "#667eea"
                    });

                } finally {
                    btnGuardar.innerHTML = btnOriginalHTML;
                    btnGuardar.disabled = false;
                }
            });

        });
    </script>
@endsection