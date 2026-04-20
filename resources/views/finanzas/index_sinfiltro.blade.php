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

        .documento-item {
            padding: 0.5rem;
            border-radius: 0.25rem;
            background-color: #f8f9fa;
            margin-bottom: 0.5rem;
        }
    </style>
    <style>
        .drag-drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            background-color: #f8fafc;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .drag-drop-zone:hover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }

        .drag-drop-zone.drag-over {
            border-color: #3b82f6;
            background-color: #dbeafe;
            border-style: solid;
        }

        .drag-drop-content i {
            opacity: 0.5;
        }

        .files-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .file-item {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 10px;
            background-color: #fff;
            transition: all 0.2s;
        }

        .file-item:hover {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .file-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 20px;
        }

        .file-icon.pdf {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .file-icon.xml {
            background-color: #fef3c7;
            color: #d97706;
        }

        .file-details {
            flex: 1;
            min-width: 0;
        }

        .file-name {
            font-weight: 500;
            margin: 0;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-size {
            font-size: 12px;
            color: #64748b;
            margin: 0;
        }

        .btn-remove-file {
            padding: 4px 8px;
            font-size: 12px;
        }
    </style>
@endsection

@section('content')
    <div class="container">

        {{-- ========================== FILTROS ================================ --}}
        <div class="card mb-4">
            <div class="card-header">
                <strong>Resumen Operativo – Finanzas</strong>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('finanzas.index') }}" class="row g-3">

                    {{-- Año --}}
                    <div class="col-md-3">
                        <label class="form-label">Año</label>
                        <select name="anio" class="form-select">
                            @for ($a = now()->year; $a >= now()->year - 5; $a--)
                                <option value="{{ $a }}" {{ $a == $anio ? 'selected' : '' }}>
                                    {{ $a }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- Semana --}}
                    <div class="col-md-3">
                        <label class="form-label">Semana</label>
                        <select name="semana" class="form-select">
                            <option value="">Todas</option>
                            @for ($s = 1; $s <= 53; $s++)
                                <option value="{{ $s }}" {{ $s == $semana ? 'selected' : '' }}>
                                    Semana {{ $s }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- Cliente --}}
                    <div class="col-md-4">
                        <label class="form-label">Cliente</label>
                        <select name="cliente_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id }}" {{ $clienteId == $c->id ? 'selected' : '' }}>
                                    {{ $c->nombre_empresa }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">
                            Filtrar
                        </button>
                    </div>

                </form>
            </div>
        </div>


        {{-- ========================== RESUMEN POR CLIENTE ================================ --}}
        @forelse ($resumen as $item)
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">

                        <h5 class="mb-0">
                            <strong>{{ $item['nombre_empresa'] }}</strong>
                        </h5>

                        {{-- Totales generales por cliente --}}
                        <div class="text-end small">
                            <span class="me-3"><strong>Patentes:</strong> {{ $item['totales']['patentes'] }}</span>
                            <span class="me-3"><strong>Pedimentos:</strong> {{ $item['totales']['pedimentos'] }}</span>
                            <span class="me-3"><strong>Remesas:</strong> {{ $item['totales']['remesas'] }}</span>
                            <span class="me-3"><strong>Rojos:</strong> {{ $item['totales']['rojos'] }}</span>
                            <span class="me-3"><strong>Sobrepesos:</strong> {{ $item['totales']['sobrepesos'] }}</span>
                            <span class="me-3"><strong>Taras:</strong> {{ $item['totales']['taras'] }}</span>
                            <span class="me-3"><strong>Adicionales:</strong> {{ $item['totales']['adicionales'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- ========================== MINI TABLA DETALLADA ================================ --}}
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Patente</th>
                                    <th>Pedimento</th>
                                    <th>Remesas</th>
                                    <th>Rojos</th>
                                    <th>Sobrepesos</th>
                                    <th>Taras</th>
                                    <th>Adicionales</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($item['detalle'] as $row)
                                {{-- DEBUG: Descomentar para ver qué contiene $row --}}
     
                                    <tr>
                                        <td>{{ $row['patente'] }}</td>
                                        <td>{{ $row['pedimento'] }}</td>
                                        <td>{{ $row['remesas'] }}</td>
                                        <td>{{ $row['rojos'] }}</td>
                                        <td>{{ $row['sobrepesos'] }}</td>
                                        <td>{{ $row['taras'] }}</td>
                                        <td>{{ $row['adicionales'] }}</td>

                                        {{-- BOTÓN FACTURAR --}}
                                        {{--<td class="text-center">
                                            <button class="btn btn-sm btn-success btnFacturar" data-id="{{ $row['id'] }}"
                                                data-bs-toggle="modal" data-bs-target="#modalFacturar">
                                                Facturar
                                            </button>
                                        </td>--}}
                                        <td class="text-center">
                                            {{--<button class="btn btn-sm btn-success btnFacturar" data-id="{{ $row['id'] }}"
                                                data-pedimento="{{ $row['pedimento'] }}" data-bs-toggle="modal"
                                                data-bs-target="#modalFacturar">
                                                Facturar
                                            </button>--}}
                                            {{--<button class="btn btn-sm btn-success btnFacturar" data-id="{{ $row['id'] }}"
                                                data-pedimento="{{ $row['pedimento'] }}" data-patente="{{ $row['patente'] }}"
                                                data-remesas="{{ $row['remesas'] }}" data-rojos="{{ $row['rojos'] }}"
                                                data-sobrepesos="{{ $row['sobrepesos'] }}" data-taras="{{ $row['taras'] }}"
                                                data-adicionales="{{ $row['adicionales'] }}" data-bs-toggle="modal"
                                                data-bs-target="#modalFacturar">
                                                <i class="fas fa-file-invoice"></i> Facturar
                                            </button>--}}
                                            @if ($row['pedimento'] && $row['pedimento'] !== 'N/A')
                                                <div class="btn-group" role="group">
                                                {{-- Botón Ver --}}
                                                <a href="{{ route('finanzas.detalle.expediente', $row['id']) }}"
                                                    class="btn btn-sm btn-info" title="Ver detalles">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>

                                                {{-- Botón Facturar --}}
                                                <button class="btn btn-sm btn-success btnFacturar" data-id="{{ $row['id'] }}"
                                                    data-pedimento="{{ $row['pedimento'] }}" data-patente="{{ $row['patente'] }}"
                                                    data-remesas="{{ $row['remesas'] }}" data-rojos="{{ $row['rojos'] }}"
                                                    data-sobrepesos="{{ $row['sobrepesos'] }}" data-taras="{{ $row['taras'] }}"
                                                    data-adicionales="{{ $row['adicionales'] }}" data-bs-toggle="modal"
                                                    data-bs-target="#modalFacturar" title="Facturar expediente">
                                                    <i class="fas fa-file-invoice"></i> Facturar
                                                </button>
                                            </div>
                                            @endif
                                            

                                        </td>
                                    </tr>
                                @endforeach

                                @if ($item['detalle']->isEmpty())
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-3">
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
            <div class="alert alert-info text-center">
                No hay información para los filtros seleccionados.
            </div>
        @endforelse
    </div>




    <!-- ============================
                 ESTILOS DEL MODAL MODERNIZADO
            ============================= -->
    <style>
        /* Paleta de Color Azul y Modernización */
        :root {
            --primary-color: #007bff;
            --primary-dark: #0056b3;
            --text-color: #2c3e50;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            --border-radius: 16px;
        }

        /* Modal personalizado */
        #modalFacturar .modal-dialog {
            max-width: 420px;
        }

        #modalFacturar .modal-content {
            border-radius: var(--border-radius);
            border: none;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        /* Encabezado estilo ticket */
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

        .ticket-header .btn-close:hover {
            opacity: 1;
        }

        /* Cuerpo del formulario */
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .input-field-modern {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            font-size: 1.0em;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .input-field-modern:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.15);
            background-color: white;
        }

        #monto_total {
            font-family: 'Courier New', monospace;
            text-align: right;
            font-weight: 700;
            font-size: 1.1em;
            color: var(--primary-color);
        }

        /* Sección de Código de Barras */
        .barcode-section {
            padding: 20px 25px 25px 25px;
            text-align: center;
            background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
            border-top: 2px dashed #dee2e6;
        }

        .barcode-label {
            font-size: 0.75em;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            margin-bottom: 10px;
            font-weight: 600;
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
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .barcode-number {
            font-family: 'Courier New', monospace;
            font-size: 0.95em;
            letter-spacing: 2px;
            color: #495057;
            margin-top: 8px;
            font-weight: 600;
        }

        /* Botones del footer */
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

        .btn-cancel-modern:hover {
            background-color: #dee2e6;
            transform: translateY(-1px);
        }

        .btn-save-modern {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-save-modern:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .btn-save-modern:active {
            transform: scale(0.98);
        }

        /* Animación de entrada del modal */
        #modalFacturar.show .modal-dialog {
            animation: slideInDown 0.4s ease-out;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Resumen de datos ocultos (opcional - para debugging) */
        .data-summary {
            background-color: #e7f3ff;
            border-left: 4px solid var(--primary-color);
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.85em;
        }

        .data-summary-hidden {
            display: none;
        }
    </style>

    <!-- ============================
                 MODAL FACTURAR MODERNIZADO
            ============================= -->
    <div class="modal fade" id="modalFacturar" tabindex="-1" aria-labelledby="modalFacturarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- HEADER ESTILO TICKET -->
                <div class="ticket-header">
                    <h5 id="modalFacturarLabel">Facturar Pedimento</h5>
                    <div class="pedimento-ref">
                        Ref: <strong id="labelPedimento">---</strong>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- FORMULARIO -->
                <form id="formFacturar">
                    @csrf

                    <!-- CAMPOS HIDDEN AUTOMÁTICOS -->
                    <input type="hidden" name="pedimento_id" id="pedimento_id">
                    <input type="hidden" name="year" id="factura_year">
                    <input type="hidden" name="semana" id="factura_week">
                    <input type="hidden" name="cantidad_tramites" id="cantidad_tramites">
                    <input type="hidden" name="cantidad_rojos" id="cantidad_rojos">
                    <input type="hidden" name="cantidad_sobrepesos" id="cantidad_sobrepesos">
                    <input type="hidden" name="cantidad_taras" id="cantidad_taras">
                    <input type="hidden" name="monto_adicionales" id="monto_adicionales">
                    <input type="hidden" name="estado" value="pendiente">

                    <!-- BODY DEL FORMULARIO -->
                    <div class="ticket-body">

                        <!-- Resumen de datos capturados (opcional - puedes ocultarlo) -->
                        <div class="data-summary data-summary-hidden" id="dataSummary">
                            <strong>📊 Datos capturados:</strong><br>
                            <small>
                                Año: <span id="display_year">-</span> |
                                Semana: <span id="display_week">-</span> |
                                Remesas: <span id="display_tramites">-</span> |
                                Rojos: <span id="display_rojos">-</span> |
                                Sobrepesos: <span id="display_sobrepesos">-</span> |
                                Taras: <span id="display_taras">-</span> |
                                Adicionales: $<span id="display_adicionales">-</span>
                            </small>
                        </div>

                        <!-- CAMPO 1: Número de Factura -->
                        <div class="form-group-modern">
                            <label for="numero_factura" class="label-modern">
                                <i class="fas fa-hashtag"></i> Número de Factura
                            </label>
                            <input type="text" class="input-field-modern" name="numero_factura" id="numero_factura"
                                placeholder="Ej: FAC-2025-001" required autocomplete="off">
                        </div>

                        <!-- CAMPO 2: Fecha de Factura -->
                        <div class="form-group-modern">
                            <label for="fecha_factura" class="label-modern">
                                <i class="fas fa-calendar-alt"></i> Fecha de Emisión
                            </label>
                            <input type="date" class="input-field-modern" name="fecha_factura" id="fecha_factura" required>
                        </div>

                        <!-- CAMPO 3: Monto Total -->
                        <div class="form-group-modern">
                            <label for="monto_total" class="label-modern">
                                <i class="fas fa-dollar-sign"></i> Monto Total
                            </label>
                            <input type="number" step="0.01" class="input-field-modern" name="monto_total" id="monto_total"
                                placeholder="0.00" required>
                        </div>

                        <!-- Campo opcional: Notas -->
                        <div class="form-group-modern">
                            <label for="notas_adicionales" class="label-modern">
                                <i class="fas fa-sticky-note"></i> Notas (opcional)
                            </label>
                            <textarea class="input-field-modern" name="notas_adicionales" id="notas_adicionales" rows="2"
                                placeholder="Observaciones adicionales..."
                                style="resize: vertical; min-height: 50px;"></textarea>
                        </div>

                    </div>

                    <!-- SECCIÓN DE CÓDIGO DE BARRAS -->
                    <div class="barcode-section">
                        <div class="barcode-label">Código de Registro</div>
                        <div class="barcode-strip"></div>
                        <div class="barcode-number" id="barcodeNumber">---</div>
                    </div>

                    <!-- FOOTER CON BOTONES -->
                    <div class="modal-footer modal-footer-modern">
                        <button type="button" class="btn btn-modern btn-cancel-modern" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        {{--<button type="button" class="btn btn-modern" id="btnDebugData"
                            style="background-color: #ffc107; color: #000;">
                            <i class="fas fa-bug"></i> Ver Datos (Debug)
                        </button>--}}
                        <button type="submit" class="btn btn-modern btn-save-modern" id="btnGuardarFactura">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- ============================
                 JAVASCRIPT MEJORADO
            ============================= -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // ==========================================
            // DETECTAR CLIC EN BOTÓN FACTURAR
            // ==========================================
            document.querySelectorAll(".btnFacturar").forEach(btn => {
                btn.addEventListener("click", function () {
                    // Capturar datos del botón
                    const id = this.dataset.id;
                    const pedimento = this.dataset.pedimento;
                    const patente = this.dataset.patente || '';
                    const remesas = this.dataset.remesas || 0;
                    const rojos = this.dataset.rojos || 0;
                    const sobrepesos = this.dataset.sobrepesos || 0;
                    const taras = this.dataset.taras || 0;
                    const adicionales = this.dataset.adicionales || 0;

                    console.log('📦 Datos capturados del botón:', {
                        id, pedimento, patente, remesas, rojos, sobrepesos, taras, adicionales
                    });

                    // Poblar campos hidden - CORREGIDO
                    document.getElementById("pedimento_id").value = id;
                    document.getElementById("cantidad_tramites").value = remesas;  // ✅ Remesas → cantidad_tramites
                    document.getElementById("cantidad_rojos").value = rojos;       // ✅ Rojos → cantidad_rojos
                    document.getElementById("cantidad_sobrepesos").value = sobrepesos; // ✅ Sobrepesos → cantidad_sobrepesos
                    document.getElementById("cantidad_taras").value = taras;       // ✅ Taras → cantidad_taras
                    document.getElementById("monto_adicionales").value = adicionales; // ✅ Adicionales → monto_adicionales

                    // Mostrar pedimento en el header
                    document.getElementById("labelPedimento").innerText = pedimento;

                    // Generar código de barras
                    document.getElementById("barcodeNumber").innerText = pedimento.toUpperCase();

                    // Calcular año y semana actual
                    const hoy = new Date();
                    const year = hoy.getFullYear();
                    const oneJan = new Date(hoy.getFullYear(), 0, 1);
                    const numberOfDays = Math.floor((hoy - oneJan) / (24 * 60 * 60 * 1000));
                    const week = Math.ceil((hoy.getDay() + 1 + numberOfDays) / 7);

                    document.getElementById("factura_year").value = year;
                    document.getElementById("factura_week").value = week;

                    // Establecer fecha actual por defecto
                    document.getElementById("fecha_factura").valueAsDate = hoy;

                    // Opcional: Mostrar resumen de datos (quita la clase 'data-summary-hidden' si quieres verlo)
                    document.getElementById("display_year").innerText = year;
                    document.getElementById("display_week").innerText = week;
                    document.getElementById("display_tramites").innerText = remesas;
                    document.getElementById("display_rojos").innerText = rojos;
                    document.getElementById("display_sobrepesos").innerText = sobrepesos;
                    document.getElementById("display_taras").innerText = taras;
                    document.getElementById("display_adicionales").innerText = adicionales;

                    // Limpiar campos visibles del formulario
                    document.getElementById("numero_factura").value = '';
                    document.getElementById("monto_total").value = '';
                    document.getElementById("notas_adicionales").value = '';

                    // Focus en el primer campo
                    setTimeout(() => {
                        document.getElementById("numero_factura").focus();
                    }, 300);
                });
            });

            // ==========================================
            // BOTÓN DEBUG - MOSTRAR DATOS
            // ==========================================
            /*document.getElementById("btnDebugData").addEventListener("click", function () {
                const formData = new FormData(document.getElementById("formFacturar"));

                let datosHTML = '<div style="text-align: left; font-family: monospace; font-size: 13px;">';
                datosHTML += '<strong style="color: #007bff; font-size: 16px;">📊 DATOS QUE SE ENVIARÁN:</strong><br><br>';

                const datos = {};
                for (let [key, value] of formData.entries()) {
                    datos[key] = value;

                    // Resaltar en rojo si es 0 o vacío
                    const isZeroOrEmpty = value === '0' || value === '' || value === null;
                    const color = isZeroOrEmpty ? 'red' : '#28a745';
                    const icon = isZeroOrEmpty ? '❌' : '✅';

                    datosHTML += `${icon} <strong>${key}:</strong> <span style="color: ${color};">${value || '(vacío)'}</span><br>`;
                }

                datosHTML += '<br><strong style="color: #007bff;">📦 Objeto completo:</strong><br>';
                datosHTML += '<pre style="background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto;">';
                datosHTML += JSON.stringify(datos, null, 2);
                datosHTML += '</pre></div>';

                console.log('🐛 DEBUG - Datos del formulario:', datos);

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '🐛 Debug de Datos',
                        html: datosHTML,
                        width: '600px',
                        confirmButtonText: 'Cerrar',
                        confirmButtonColor: '#007bff'
                    });
                } else {
                    // Fallback si no hay SweetAlert
                    alert('Ver consola (F12) para los datos completos');
                    console.table(datos);
                }
            });*/

            // ==========================================
            // GUARDAR FACTURA VÍA AJAX
            // ==========================================
            document.getElementById("formFacturar").addEventListener("submit", async function (e) {
                e.preventDefault();

                const btnGuardar = document.getElementById("btnGuardarFactura");
                const btnOriginalHTML = btnGuardar.innerHTML;

                try {
                    console.log('🟡 INICIANDO GUARDADO DE FACTURA...');

                    // Mostrar loading
                    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                    btnGuardar.disabled = true;

                    // Capturar datos del formulario
                    const formData = new FormData(this);

                    console.log('📋 DATOS DEL FORMULARIO:');
                    for (let [key, value] of formData.entries()) {
                        console.log(`   ${key}: ${value}`);
                    }

                    // Hacer petición fetch
                    const response = await fetch("{{ route('finanzas.factura.guardar') }}", {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "X-Requested-With": "XMLHttpRequest",
                            "Accept": "application/json"
                        }
                    });

                    console.log('📡 RESPONSE STATUS:', response.status);

                    // Parsear respuesta
                    const responseText = await response.text();
                    let data;

                    try {
                        data = JSON.parse(responseText);
                        console.log('📦 DATA JSON RECIBIDA:', data);
                    } catch (jsonError) {
                        console.error('❌ ERROR AL PARSEAR JSON:', responseText.substring(0, 500));

                        if (responseText.includes('<!DOCTYPE html>')) {
                            throw new Error('El servidor respondió con HTML. Revisa el Network tab en DevTools para más detalles.');
                        } else {
                            throw new Error(`Respuesta inesperada: ${responseText.substring(0, 200)}...`);
                        }
                    }

                    if (!response.ok) {
                        throw new Error(`Error HTTP ${response.status}: ${data.message || 'Error del servidor'}`);
                    }

                    if (data.success) {
                        console.log('✅ FACTURA GUARDADA EXITOSAMENTE');

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: "success",
                                title: "¡Factura guardada!",
                                text: data.message || "La factura se guardó correctamente",
                                confirmButtonColor: "#007bff",
                                confirmButtonText: "Aceptar"
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            alert('✅ Factura guardada correctamente');
                            location.reload();
                        }
                    } else {
                        throw new Error(data.message || "Error al guardar la factura");
                    }

                } catch (error) {
                    console.group('❌ ERROR CAPTURADO');
                    console.error('⏰ Timestamp:', new Date().toISOString());
                    console.error('💬 Mensaje:', error.message);
                    console.error('🔗 Stack:', error.stack);
                    console.groupEnd();

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: "error",
                            title: "Error al guardar",
                            html: `<div style="text-align: left; font-size: 14px;">
                                        <strong>Error:</strong><br>
                                        ${error.message.replace(/\n/g, '<br>')}
                                        <br><br>
                                        <small style="color: #666;">
                                        ⚠️ Revisa la consola (F12) para más detalles.
                                        </small>
                                    </div>`,
                            confirmButtonColor: "#007bff",
                            confirmButtonText: "Entendido"
                        });
                    } else {
                        alert('❌ Error: ' + error.message);
                    }

                } finally {
                    console.log('🔄 RESTAURANDO BOTÓN...');
                    btnGuardar.innerHTML = btnOriginalHTML;
                    btnGuardar.disabled = false;
                }
            });

        });
    </script>








    <script>
        function facturar(id) {
            // Más adelante abrimos un modal
            alert("Abrir modal para facturar: " + id);
        }
    </script>
    <!-- En tu head -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection