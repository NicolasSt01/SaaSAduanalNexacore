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
    <div class="container-fluid py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('finanzas.index', ['year' => $year, 'semana' => $semana]) }}">
                        <i class="fas fa-home me-1"></i>Resumen
                    </a>
                </li>
                <li class="breadcrumb-item active">{{ $cliente->nombre_empresa }} - Patente {{ $patente->numero_patente }}
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-2 fw-bold">
                            <i class="fas fa-building me-2"></i>{{ $cliente->nombre_empresa }}
                        </h3>
                        <p class="mb-0">
                            <i class="fas fa-file-contract me-2"></i>Patente: {{ $patente->numero_patente }}
                            <span class="ms-3"><i class="fas fa-calendar me-1"></i>Semana {{ $semana }} del
                                {{ $year }}</span>
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('finanzas.index', ['year' => $year, 'semana' => $semana]) }}"
                            class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Expedientes -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-folder-open fa-2x text-primary mb-2"></i>
                        <h3 class="fw-bold mb-0">{{ $expedientes->count() }}</h3>
                        <small class="text-muted">Total Pedimentos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h3 class="fw-bold mb-0">{{ $expedientes->where('tiene_factura', true)->count() }}</h3>
                        <small class="text-muted">Facturados</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-file-alt fa-2x text-info mb-2"></i>
                        <h3 class="fw-bold mb-0">{{ $expedientes->sum('total_tramites') }}</h3>
                        <small class="text-muted">Total Trámites</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                        <h3 class="fw-bold mb-0">{{ $expedientes->sum('rojos') }}</h3>
                        <small class="text-muted">Total Rojos</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Expedientes en Cards -->
        <div class="row">
            @forelse($expedientes as $exp)
                    @php
                        $cardClass = '';
                        $estadoIcon = '';
                        $estadoTexto = '';
                        $estadoBadge = '';

                        if ($exp['tiene_factura']) {
                            $factura = $exp['factura'];
                            $cardClass = 'facturado';
                            $estadoIcon = 'fa-check-circle text-success';
                            $estadoTexto = 'Facturado';
                            $estadoBadge = $factura->estado_badge_class;
                        } else {
                            $cardClass = '';
                            $estadoIcon = 'fa-clock text-warning';
                            $estadoTexto = 'Pendiente';
                            $estadoBadge = 'bg-secondary';
                        }
                    @endphp

                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card expediente-card {{ $cardClass }} border-0 shadow-sm h-100">
                            <!-- Header del Card -->
                            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-file-invoice me-2 text-primary"></i>
                                    Pedim. #{{ $exp['expediente_numero'] }}
                                </h6>
                                <span class="badge {{ $estadoBadge }}">
                                    <i class="fas {{ $estadoIcon }} me-1"></i>
                                    {{ $exp['tiene_factura'] ? $exp['factura']->estado_texto : 'Sin factura' }}
                                </span>
                            </div>

                            <div class="card-body">
                                <!-- Fechas -->
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="stat-box">
                                            <small class="text-muted d-block"><i
                                                    class="fas fa-calendar-alt me-1"></i>Apertura</small>
                                            <strong
                                                class="small">{{ $exp['fecha_apertura'] ? \Carbon\Carbon::parse($exp['fecha_apertura'])->format('d/m/Y') : 'N/A' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-box">
                                            <small class="text-muted d-block"><i
                                                    class="fas fa-calendar-check me-1"></i>Cierre</small>
                                            <strong
                                                class="small">{{ $exp['fecha_cierre'] ? \Carbon\Carbon::parse($exp['fecha_cierre'])->format('d/m/Y') : 'N/A' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Estadísticas -->
                                <div class="row g-2 mb-3">
                                    <div class="col-4">
                                        <div class="stat-box text-center">
                                            <h5 class="mb-0 fw-bold text-success">{{ $exp['total_tramites'] }}</h5>
                                            <small class="text-muted">Trámites</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box text-center">
                                            <h5 class="mb-0 fw-bold text-danger">{{ $exp['rojos'] }}</h5>
                                            <small class="text-muted">Rojos</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-box text-center">
                                            <h5 class="mb-0 fw-bold text-warning">{{ $exp['sobrepesos'] }}</h5>
                                            <small class="text-muted">Sobrepesos</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info de Factura si existe -->
                                @if($exp['tiene_factura'])
                                    <div class="alert alert-success py-2 mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><i
                                                        class="fas fa-file-invoice me-1"></i>{{ $exp['factura']->numero_factura }}</strong>
                                                <br>
                                                <small class="text-muted">Monto:
                                                    ${{ number_format($exp['factura']->monto_total, 2) }}</small>
                                                @if($exp['factura']->monto_adicionales > 0)
                                                    <br><small class="text-muted">Adicionales:
                                                        ${{ number_format($exp['factura']->monto_adicionales, 2) }}</small>
                                                @endif
                                            </div>
                                            <span class="badge {{ $exp['factura']->estado_badge_class }}">
                                                {{ $exp['factura']->estado_texto }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Documentos -->
                                    @if($exp['factura']->documentos->count() > 0)
                                        <div class="mb-3">
                                            <small class="text-muted fw-bold">Documentos:</small>
                                            @foreach($exp['factura']->documentos as $doc)
                                                <div class="documento-item d-flex justify-content-between align-items-center">
                                                    <span class="small">
                                                        <i
                                                            class="fas {{ $doc->esPdf() ? 'fa-file-pdf text-danger' : 'fa-file-code text-warning' }} me-1"></i>
                                                        {{ $doc->tipo_documento }}
                                                    </span>
                                                    <a href="{{ route('finanzas.documento.descargar', $doc->id) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-warning py-2 mb-3">
                                        <small><i class="fas fa-info-circle me-1"></i>Sin factura registrada</small>
                                    </div>
                                @endif

                                <!-- Acciones -->
                                <div class="d-grid gap-2">
                                    <button type="button"
                                        class="btn btn-sm {{ $exp['tiene_factura'] ? 'btn-outline-primary' : 'btn-primary' }} btn-gestionar-factura"
                                        data-expediente-id="{{ $exp['pedimento_id'] }}"
                                        data-expediente-numero="{{ $exp['expediente_numero'] }}"
                                        data-tramites="{{ $exp['total_tramites'] }}" data-rojos="{{ $exp['rojos'] }}"
                                        data-sobrepesos="{{ $exp['sobrepesos'] }}"
                                        data-tiene-factura="{{ $exp['tiene_factura'] ? 'true' : 'false' }}"
                                        data-factura-id="{{ $exp['tiene_factura'] ? $exp['factura']->id : '' }}">
                                        <i class="fas {{ $exp['tiene_factura'] ? 'fa-edit' : 'fa-file-invoice-dollar' }} me-1"></i>
                                        {{ $exp['tiene_factura'] ? 'Editar Factura' : 'Crear Factura' }}
                                    </button>

                                    <a href="{{ route('finanzas.detalle.expediente', [
                    'expedienteId' => $exp['pedimento_id'],
                    'year' => $year,
                    'semana' => $semana
                ]) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye me-1"></i>Ver Operaciones
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No se encontraron expedientes para esta patente en la semana seleccionada.
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Gestionar Factura -->
    <div class="modal fade" id="modalGestionarFactura" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-invoice me-2"></i>
                        <span id="modalTitulo">Gestionar Factura</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formFactura">
                    <div class="modal-body">
                        <input type="hidden" id="pedimento_id" name="pedimento_id">
                        <input type="hidden" id="factura_id" name="factura_id">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="semana" value="{{ $semana }}">

                        <!-- Info del Expediente -->
                        <div class="alert alert-light border">
                            <strong>Pedimento:</strong> <span id="infoExpediente"></span>
                        </div>

                        <div class="row g-3">
                            <!-- Número de Factura -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Número de Factura <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="numero_factura" name="numero_factura" required>
                            </div>

                            <!-- Fecha de Factura -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha de Factura <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_factura" name="fecha_factura" required>
                            </div>

                            <!-- Monto Total -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Monto Total</label>
                                <input type="number" class="form-control" id="monto_total" name="monto_total" min="0">
                            </div>

                            <!-- Monto Adicionales -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Conceptos Adicionales</label>
                                <input type="number" class="form-control" id="monto_adicionales" name="monto_adicionales"
                                    min="0" value="0">
                            </div>

                            <!-- Conceptos (solo lectura, calculados automáticamente) -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Cantidad Trámites</label>
                                <input type="number" class="form-control" id="cantidad_tramites" name="cantidad_tramites"
                                    readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Cantidad Rojos</label>
                                <input type="number" class="form-control" id="cantidad_rojos" name="cantidad_rojos"
                                    readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Cantidad Sobrepesos</label>
                                <input type="number" class="form-control" id="cantidad_sobrepesos"
                                    name="cantidad_sobrepesos" readonly>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="facturada">Facturada</option>
                                    <option value="pagada">Pagada</option>
                                    <option value="complemento_pago">Complemento de Pago</option>
                                </select>
                            </div>

                            <!-- Notas -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notas Adicionales</label>
                                <textarea class="form-control" id="notas_adicionales" name="notas_adicionales"
                                    rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar Factura
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{--<!-- Modal Subir Documentos -->
    <div class="modal fade" id="modalSubirDocumento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-upload me-2"></i>Subir Documento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSubirDocumento" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="doc_factura_id" name="factura_id">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipo de Documento <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" name="tipo_documento" required>
                                <option value="">Seleccionar...</option>
                                <option value="factura_pdf">Factura PDF</option>
                                <option value="factura_xml">Factura XML</option>
                                <option value="complemento_pago_pdf">Complemento de Pago PDF</option>
                                <option value="complemento_pago_xml">Complemento de Pago XML</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Archivo <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="archivo" accept=".pdf,.xml" required>
                            <small class="text-muted">Formatos permitidos: PDF, XML (Máx. 10MB)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i>Subir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>--}}
    <!-- Modal Subir Documentos (Múltiple con Drag & Drop) -->
    <div class="modal fade" id="modalSubirDocumento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-upload me-2"></i>Subir Documentos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSubirDocumento" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="doc_factura_id" name="factura_id">

                        <!-- Zona de Drag & Drop -->
                        <div class="drag-drop-zone mb-3" id="dragDropZone">
                            <div class="drag-drop-content">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <h6>Arrastra archivos aquí</h6>
                                <p class="text-muted mb-2">o</p>
                                <label for="fileInput" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-folder-open me-1"></i>Seleccionar Archivos
                                </label>
                                <input type="file" id="fileInput" class="d-none" accept=".pdf,.xml" multiple>
                                <small class="d-block mt-2 text-muted">PDF, XML (Máx. 10MB por archivo)</small>
                            </div>
                        </div>

                        <!-- Lista de Archivos Seleccionados -->
                        <div id="filesList" class="files-list"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmit" disabled>
                            <i class="fas fa-upload me-1"></i>Subir <span id="fileCount"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
        $(document).ready(function () {
            const modalFactura = new bootstrap.Modal(document.getElementById('modalGestionarFactura'));
            const modalDocumento = new bootstrap.Modal(document.getElementById('modalSubirDocumento'));

            // Abrir modal para gestionar factura
            $('.btn-gestionar-factura').click(function () {
                const expedienteId = $(this).data('expediente-id');
                const expedienteNumero = $(this).data('expediente-numero');
                const tramites = $(this).data('tramites');
                const rojos = $(this).data('rojos');
                const sobrepesos = $(this).data('sobrepesos');
                const tieneFactura = $(this).data('tiene-factura') === 'true';
                const facturaId = $(this).data('factura-id');

                // Resetear formulario
                $('#formFactura')[0].reset();

                // Llenar datos básicos
                $('#pedimento_id').val(expedienteId);
                //$('#infoExpediente').text(`#${expedienteNumero} - ${tramites} trámites, ${rojos} rojos, ${sobrepesos} sobrepesos`);
                $('#infoExpediente').text(`#${expedienteNumero} - ${tramites} trámites, ${rojos} rojos`);
                $('#cantidad_tramites').val(tramites);
                $('#cantidad_rojos').val(rojos);
                $('#cantidad_sobrepesos').val(sobrepesos);

                if (tieneFactura && facturaId) {
                    $('#modalTitulo').text('Editar Factura - Pedimento. #' + expedienteNumero);
                    $('#factura_id').val(facturaId);
                    cargarDatosFactura(facturaId);
                } else {
                    $('#modalTitulo').text('Crear Factura - Pedimento. #' + expedienteNumero);
                    $('#factura_id').val('');
                    $('#fecha_factura').val(new Date().toISOString().split('T')[0]);
                }

                modalFactura.show();
            });

            // Guardar factura (AJAX)
            $('#formFactura').submit(function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                $.ajax({
                    url: '{{ route("finanzas.factura.guardar") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // Cerrar modal de factura con API Bootstrap
                                modalFactura.hide();

                                // Pasar el ID de la factura creada al modal de documentos
                                $('#doc_factura_id').val(response.factura.id);

                                // Mostrar modal de subir documentos
                                modalDocumento.show();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al guardar la factura.'
                        });
                    }
                });
            });

            // Cargar datos de factura existente
            function cargarDatosFactura(facturaId) {
                $.ajax({
                    url: `/finanzas/factura/${facturaId}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            const factura = response.factura;
                            $('#numero_factura').val(factura.numero_factura);
                            $('#fecha_factura').val(factura.fecha_factura);
                            $('#monto_total').val(factura.monto_total);
                            $('#monto_adicionales').val(factura.monto_adicionales);
                            $('#estado').val(factura.estado);
                            $('#notas_adicionales').val(factura.notas_adicionales);
                        }
                    }
                });
            }

            // Subir documento (AJAX)
            $('#formSubirDocumento_Original').on('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                $.ajax({
                    url: '{{ route("finanzas.factura.documento.subir") }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Documento subido',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                modalDocumento.hide();
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al subir el documento'
                        });
                    }
                });
            });
        });
        // Subir documentos múltiples (AJAX)
        $('#formSubirDocumento1').on('submit', function (e) {
            e.preventDefault();

            if (selectedFiles.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Debes seleccionar al menos un archivo.'
                });
                return;
            }

            // Validar que todos tengan tipo asignado
            if (selectedFiles.some(f => !f.tipo)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Debes seleccionar el tipo de documento para todos los archivos.'
                });
                return;
            }

            const formData = new FormData();
            const facturaId = $('#doc_factura_id').val();

            // Agregar factura_id
            formData.append('factura_id', facturaId);

            // Agregar cada archivo con su tipo
            selectedFiles.forEach((item, index) => {
                formData.append(`archivos[${index}]`, item.file);
                formData.append(`tipos[${index}]`, item.tipo);
            });

            // Deshabilitar botón de submit
            const btnSubmit = $('#btnSubmit');
            const btnText = btnSubmit.html();
            btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Subiendo...');

            $.ajax({
                url: '{{ route("finanzas.factura.documento.subir") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Documentos subidos!',
                            text: response.message || `${selectedFiles.length} documento(s) subido(s) correctamente`,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            modalDocumento.hide();
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Error al subir los documentos'
                        });
                    }
                },
                error: function (xhr) {
                    let errorMsg = 'Error al subir los documentos';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                },
                complete: function () {
                    // Restaurar botón
                    btnSubmit.prop('disabled', false).html(btnText);
                }
            });
        });
        //Subir documentos multiples (AJAX)-Modificado 27-octubre-2025
        // Subir documentos múltiples (AJAX)
        // Definir la variable global si no existe
        //if (typeof window.selectedFiles === 'undefined') {
        //    window.selectedFiles = [];
        //}
        // Al inicio de tu archivo JavaScript, asegura que selectedFiles esté definido
        if (typeof selectedFiles === 'undefined') {
            var selectedFiles = [];
        }

        // Agrega este console.log para verificar qué hay en selectedFiles
        console.log('=== DEBUG SELECTED FILES ===');
        console.log('selectedFiles array:', selectedFiles);
        console.log('selectedFiles length:', selectedFiles.length);
        console.log('selectedFiles contenido:');
        selectedFiles.forEach((file, index) => {
            console.log(`Archivo ${index}:`, {
                id: file.id,
                fileName: file.file ? file.file.name : 'No file object',
                tipo: file.tipo,
                fileObject: file.file
            });
        });

        // Subir documentos múltiples (AJAX)
        /*$('#formSubirDocumento').on('submit', function (e) {
            e.preventDefault();

            console.log('=== INICIANDO ENVÍO ===');
            console.log('selectedFiles antes de validar:', selectedFiles);

            if (!selectedFiles || selectedFiles.length === 0) {
                console.error('selectedFiles está vacío o no definido');
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Debes seleccionar al menos un archivo.'
                });
                return;
            }

            // Validar que todos tengan tipo asignado
            const archivosSinTipo = selectedFiles.filter(f => !f.tipo);
            if (archivosSinTipo.length > 0) {
                console.error('Archivos sin tipo:', archivosSinTipo);
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Debes seleccionar el tipo de documento para todos los archivos.'
                });
                return;
            }

            const formData = new FormData();
            const facturaId = $('#doc_factura_id').val();

            formData.append('factura_id', facturaId);

            // Agregar archivos (CORREGIDO)
            selectedFiles.forEach((item, index) => {
                formData.append('archivos[]', item.file);
                formData.append('tipos[]', item.tipo);
            });

            // Verificar FormData
            console.log('FormData contenido:');
            for (let [key, value] of formData.entries()) {
                if (value instanceof File) {
                    console.log(`${key}:`, value.name, `(${value.size} bytes)`);
                } else {
                    console.log(`${key}:`, value);
                }
            }

            // Deshabilitar botón
            const btnSubmit = $('#btnSubmit');
            const btnText = btnSubmit.html();
            btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Subiendo...');

            $.ajax({
                url: '{{ route("finanzas.factura.documento.subir") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    console.log('Respuesta del servidor:', response);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Limpiar selectedFiles después del éxito
                            selectedFiles = [];
                            modalDocumento.hide();
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error en AJAX:', xhr.responseJSON || error);
                    let errorMsg = 'Error al subir los documentos';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                },
                complete: function () {
                    btnSubmit.prop('disabled', false).html(btnText);
                }
            });
        });*/
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dragDropZone = document.getElementById('dragDropZone');
            const fileInput = document.getElementById('fileInput');
            const filesList = document.getElementById('filesList');
            const formSubirDocumento = document.getElementById('formSubirDocumento');
            const btnSubmit = document.getElementById('btnSubmit');
            const fileCount = document.getElementById('fileCount');

            let selectedFiles = [];

            // Eventos Drag & Drop
            dragDropZone.addEventListener('click', () => fileInput.click());

            dragDropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dragDropZone.classList.add('drag-over');
            });

            dragDropZone.addEventListener('dragleave', () => {
                dragDropZone.classList.remove('drag-over');
            });

            dragDropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dragDropZone.classList.remove('drag-over');
                handleFiles(e.dataTransfer.files);
            });

            fileInput.addEventListener('change', (e) => {
                handleFiles(e.target.files);
            });

            function handleFiles(files) {
                const maxSize = 10 * 1024 * 1024; // 10MB

                Array.from(files).forEach(file => {
                    // Validar extensión
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (ext !== 'pdf' && ext !== 'xml') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Archivo no válido',
                            text: `${file.name} no es un archivo PDF o XML`,
                            timer: 2000
                        });
                        return;
                    }

                    // Validar tamaño
                    if (file.size > maxSize) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Archivo muy grande',
                            text: `${file.name} excede el tamaño máximo de 10MB`,
                            timer: 2000
                        });
                        return;
                    }

                    // Evitar duplicados
                    if (selectedFiles.some(f => f.file.name === file.name && f.file.size === file.size)) {
                        return;
                    }

                    const fileId = Date.now() + Math.random();
                    selectedFiles.push({
                        id: fileId,
                        file: file,
                        tipo: ''
                    });

                    renderFile(fileId, file);
                });

                updateSubmitButton();
            }

            function renderFile(fileId, file) {
                const ext = file.name.split('.').pop().toLowerCase();
                const sizeKB = (file.size / 1024).toFixed(2);

                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.dataset.fileId = fileId;

                fileItem.innerHTML = `
                <div class="file-info">
                    <div class="file-icon ${ext}">
                        <i class="fas fa-file-${ext === 'pdf' ? 'pdf' : 'code'}"></i>
                    </div>
                    <div class="file-details">
                        <p class="file-name" title="${file.name}">${file.name}</p>
                        <p class="file-size">${sizeKB} KB</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-file" onclick="removeFile(${fileId})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div>
                    <label class="form-label fw-semibold small mb-1">Tipo de Documento <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm tipo-documento" data-file-id="${fileId}" required>
                        <option value="">Seleccionar...</option>
                        <option value="factura_pdf">Factura PDF</option>
                        <option value="factura_xml">Factura XML</option>
                        <option value="complemento_pago_pdf">Complemento de Pago PDF</option>
                        <option value="complemento_pago_xml">Complemento de Pago XML</option>
                    </select>
                </div>
            `;

                filesList.appendChild(fileItem);

                // Event listener para el select
                fileItem.querySelector('.tipo-documento').addEventListener('change', function () {
                    const file = selectedFiles.find(f => f.id === fileId);
                    if (file) {
                        file.tipo = this.value;
                        updateSubmitButton();
                    }
                });
            }

            window.removeFile = function (fileId) {
                selectedFiles = selectedFiles.filter(f => f.id !== fileId);
                const fileItem = document.querySelector(`[data-file-id="${fileId}"]`);
                if (fileItem) {
                    fileItem.remove();
                }
                updateSubmitButton();
            };

            function updateSubmitButton() {
                const allHaveType = selectedFiles.length > 0 && selectedFiles.every(f => f.tipo !== '');
                btnSubmit.disabled = !allHaveType;
                fileCount.textContent = selectedFiles.length > 0 ? `(${selectedFiles.length})` : '';
            }

            // Submit del formulario con AJAX
            formSubirDocumento.addEventListener('submit', function (e) {
                e.preventDefault();

                if (selectedFiles.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Debes seleccionar al menos un archivo.'
                    });
                    return;
                }

                // Validar que todos tengan tipo asignado
                if (selectedFiles.some(f => !f.tipo)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Debes seleccionar el tipo de documento para todos los archivos.'
                    });
                    return;
                }

                const formData = new FormData();
                const facturaId = document.getElementById('doc_factura_id').value;
                formData.append('factura_id', facturaId);

                // CORRECCIÓN: Agregar archivos y tipos correctamente
                selectedFiles.forEach((item, index) => {
                    formData.append(`archivos[${index}]`, item.file);
                    formData.append(`tipos[${index}]`, item.tipo);
                });

                // Deshabilitar botón
                const btnText = btnSubmit.innerHTML;
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Subiendo...';

                // Petición AJAX con fetch
                fetch('{{ route("finanzas.factura.documento.subir") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Documentos subidos!',
                                text: data.message || `${selectedFiles.length} documento(s) subido(s) correctamente`,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                const modal = bootstrap.Modal.getInstance(document.getElementById('modalSubirDocumento'));
                                modal.hide();
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Error al subir los documentos'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al subir los archivos'
                        });
                    })
                    .finally(() => {
                        // Restaurar botón
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = btnText;
                    });
            });

            function resetForm() {
                selectedFiles = [];
                filesList.innerHTML = '';
                fileInput.value = '';
                updateSubmitButton();
            }

            // Limpiar al cerrar el modal
            document.getElementById('modalSubirDocumento').addEventListener('hidden.bs.modal', function () {
                resetForm();
            });
        });
    </script>


@endsection

@section('scripts')

@endsection