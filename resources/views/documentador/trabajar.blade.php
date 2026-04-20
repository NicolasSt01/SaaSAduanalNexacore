@extends('layouts.app')

@section('title', 'Trabajar en Exportación')

@section('customcss')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: #3b82f6;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --bg-secondary: #f1f5f9;
            --bg-tertiary: #f8fafc;
            --border-color: #e2e8f0;
            --border-dark: #cbd5e1;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --success: #10b981;
            --success-light: #d1fae5;
            --success-dark: #059669;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --warning-dark: #d97706;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --danger-dark: #dc2626;
            --info: #3b82f6;
            --info-light: #dbeafe;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        /* Layout & Cards */
        .card-section {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: var(--transition);
        }
        .card-header {
            padding: 1rem 1.25rem;
            background: var(--bg-tertiary);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header h5 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Info Items */
        .info-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .info-value {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        /* Badges & Status */
        .badge-status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .bg-pending { background: var(--warning-light); color: var(--warning-dark); }
        .bg-completado { background: var(--success-light); color: var(--success-dark); }
        .bg-proceso { background: var(--info-light); color: var(--info); }
        
        .pulse {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            animation: pulse-kf 1.5s infinite;
        }
        @keyframes pulse-kf {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        /* Table Style */
        .compact-table th {
            background: var(--bg-tertiary);
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        .compact-table td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        /* Drop Zone */
        .drop-zone {
            border: 2px dashed var(--border-dark);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            background: var(--bg-tertiary);
            transition: var(--transition);
            cursor: pointer;
        }
        .drop-zone:hover, .drop-zone.drag-over {
            border-color: var(--primary);
            background-color: #eff6ff;
        }

        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background-color: var(--bg-secondary);
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        /* Buttons Custom */
        .btn-modern {
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            border-radius: 8px;
            transition: var(--transition);
        }
        .btn-primary-modern {
            background: var(--primary);
            border: none;
            color: white;
        }
        .btn-primary-modern:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        /* Modals */
        .modal-content {
            border-radius: 16px;
            border: none;
            overflow: hidden;
        }
        .modal-header {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            padding: 1.25rem;
        }
        .btn-close { filter: brightness(0) invert(1); }

        /* Buttons */
        .btn {
            padding: 0.6rem 1.25rem;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 8px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .btn-success {
            background-color: var(--success);
            border: none;
        }

        .btn-success:hover {
            background-color: var(--success-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .btn-secondary {
            background-color: white;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
        }

        .btn-secondary:hover {
            background-color: var(--bg-secondary);
            border-color: var(--border-dark);
            color: var(--text-primary);
        }

        .btn-lg {
            padding: 0.8rem 1.5rem;
            font-size: 0.95rem;
        }



    </style>
@endsection

@section('content')
    <div class="container-fluid py-4 px-4">
        {{-- Alerta Modernizada --}}
        @if(empty($operacion->bodega_id) || empty($operacion->patente_id) || empty($operacion->num_thermo) || empty($operacion->codigo_alpha))
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 mb-4" role="alert" style="background: var(--warning-light); color: var(--warning-dark); border-radius: 12px;">
            <div style="background: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <strong class="d-block">Acción Requerida</strong>
                <span style="font-size: 0.85rem;">Existen campos críticos pendientes de completar por Tráfico.</span>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" style="filter: none;"></button>
        </div>
        @endif

        <div class="row g-4">
            <!-- Detalle de Exportación -->
            <div class="col-lg-8">
                <div class="card-section">
                    <div class="card-header" style="background: #eff6ff; border-bottom-color: #bfdbfe;">
                        <h5>
                            <i class="fas fa-file-export text-primary"></i>
                            Gestión de Exportación
                        </h5>
                        <span class="badge-status bg-blue" style="background: #dbeafe; color: #1e40af;">
                            REF: {{ $operacion->referencia ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Cliente & Org -->
                            <div class="col-md-6">
                                <h6 class="text-xs fw-bold text-primary text-uppercase mb-3" style="letter-spacing: 0.05em;">Entidades Relacionadas</h6>
                                
                                <div class="info-group">
                                    <div class="info-label"><i class="fas fa-building"></i> Empresa Cliente</div>
                                    <div class="info-value">{{ $operacion->cliente->nombre_empresa }}</div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label"><i class="fas fa-user-tie"></i> Contacto Principal</div>
                                    <div class="info-value">{{ $operacion->cliente->contacto_principal ?? 'N/A' }}</div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label"><i class="fas fa-truck-loading"></i> Importador</div>
                                    <div class="info-value">{{ $operacion->importador->nombre ?? 'N/A' }}</div>
                                </div>
                            </div>

                            <!-- Mercancía & Fechas -->
                            <div class="col-md-6">
                                <h6 class="text-xs fw-bold text-success text-uppercase mb-3" style="letter-spacing: 0.05em;">Detalles de la Carga</h6>
                                
                                <div class="info-group">
                                    <div class="info-label"><i class="fas fa-box"></i> Descripción Producto</div>
                                    <div class="info-value">{{ $operacion->nombre_producto }}</div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label"><i class="fas fa-file-invoice-dollar"></i> Factura Comercial</div>
                                    <div class="info-value fw-bold fs-6">{{ $operacion->num_factura }}</div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label"><i class="fas fa-calendar-alt"></i> Fecha de Operación</div>
                                    <div class="info-value">{{ \Carbon\Carbon::parse($operacion->fecha)->format('d/m/Y') }}</div>
                                </div>
                            </div>

                            <div class="col-12"><hr class="my-4" style="border-color: var(--border-color);"></div>

                            <!-- Logística -->
                            <div class="col-md-6">
                                <h6 class="text-xs fw-bold text-info text-uppercase mb-3" style="letter-spacing: 0.05em;">Aduana & Logística</h6>
                                
                                <div class="info-group">
                                    <div class="info-label"><i class="fas fa-warehouse"></i> Bodega Asignada</div>
                                    <div class="info-value">
                                        @if($operacion->bodega)
                                            {{ $operacion->bodega->nombre_bodega }}
                                        @else
                                            <span class="badge-status bg-pending"><div class="pulse"></div> Pendiente</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label"><i class="fas fa-landmark"></i> Aduana de Despacho</div>
                                    <div class="info-value">{{ $operacion->aduana->nombre_aduana }}</div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label"><i class="fas fa-id-badge"></i> Patente Aduanal</div>
                                    <div class="info-value">
                                        @if($operacion->patente)
                                            {{ $operacion->patente->numero_patente }}
                                        @else
                                            <span class="badge-status bg-blue" style="background: var(--info-light);"><i class="fas fa-link me-1"></i>Vía Pedimento</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Seguimiento -->
                            <div class="col-md-6">
                                <h6 class="text-xs fw-bold text-warning text-uppercase mb-3" style="letter-spacing: 0.05em;">Tracking Interno</h6>
                                
                                <div class="info-group">
                                    <div class="info-label"><i class="fas fa-thermometer-half"></i> No. de Thermo</div>
                                    <div class="info-value">
                                        @if($operacion->num_thermo)
                                            {{ $operacion->num_thermo }}
                                        @else
                                            <span class="badge-status bg-pending"><div class="pulse"></div> Pendiente</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label"><i class="fas fa-barcode"></i> Código Alpha</div>
                                    <div class="info-value">
                                        @if($operacion->codigo_alpha)
                                            {{ $operacion->codigo_alpha }}
                                        @else
                                            <span class="badge-status bg-pending"><div class="pulse"></div> Pendiente</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label"><i class="fas fa-flag"></i> Estado Actual</div>
                                    <div class="info-value">
                                        <span class="badge-status bg-{{ $operacion->estado }}">
                                            {{ ucfirst($operacion->estado) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <span class="text-xs text-muted fw-bold text-uppercase">Prioridad:</span>
                                <span class="badge-status @if($operacion->prioridad == 'urgente') bg-red @elseif($operacion->prioridad == 'media') bg-yellow @else bg-blue @endif" style="font-size: 0.75rem;">
                                    {{ ucfirst($operacion->prioridad ?? 'Normal') }}
                                </span>
                            </div>
                            <small class="text-muted"><i class="far fa-clock me-1"></i> Creado: {{ $operacion->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario y Acciones -->
            <div class="col-lg-4">
                <div class="card-section shadow-md" style="border-top: 4px solid var(--primary);">
                    <div class="card-header bg-white">
                        <h5 style="color: var(--primary);"><i class="fas fa-check-circle"></i> Cierre Documental</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('documentador.completar', ['id' => $operacion->id]) }}" method="POST" id="operacionForm">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="pedimento_id" class="form-label">Expediente / Pedimento <span class="text-danger">*</span></label>
                                <select class="form-select shadow-none" id="pedimento_id" name="pedimento_id" required>
                                    <option value="">-- Seleccionar Pedimento --</option>
                                    @foreach($expedientes as $expediente)
                                        <option value="{{ $expediente->id }}" 
                                                data-patente="{{ $expediente->patente_id ?? '' }}"
                                                {{ old('pedimento_id', $operacion->pedimento_id) == $expediente->id ? 'selected' : '' }}>
                                            {{ $expediente->numero_pedimento }} 
                                            @if($expediente->patente) [Patente: {{ $expediente->patente->numero_patente }} {{ $expediente->clave_pedimento }} |{{ $expediente->tipo_expediente }}] @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="mt-2 text-primary d-flex align-items-center gap-2" style="font-size: 0.75rem; font-weight: 500;">
                                    <i class="fas fa-info-circle"></i> La patente se actualizará automáticamente.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="num_doda" class="form-label">Número DODA / PITA <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="num_doda" name="num_doda"
                                       value="{{ old('num_doda', $operacion->num_doda) }}"
                                       placeholder="Ingrese el número DODA">
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <button type="submit" onclick="setFormAction('actualizar')" class="btn btn-modern btn-secondary w-100">
                                        <i class="fas fa-save me-2"></i>Guardar
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-modern btn-secondary w-100" data-bs-toggle="modal" data-bs-target="#agregarDocumentoModal">
                                        <i class="fas fa-file-upload me-2 text-success"></i>Archivos
                                    </button>
                                </div>
                            </div>

                            <button type="submit" onclick="setFormAction('completar')" class="btn btn-modern btn-success btn-lg w-100 mt-2 shadow-sm">
                                <i class="fas fa-flag-checkered me-2"></i>FINALIZAR OPERACIÓN
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Sidebar estadístico -->
                <div class="card-section">
                    <div class="card-header">
                        <h5><i class="fas fa-clipboard-list text-muted"></i> Resumen</h5>
                    </div>
                    <div class="card-body py-1">
                        <div class="d-flex justify-content-between py-2 border-bottom border-dashed">
                            <span class="text-muted">Documentos</span>
                            <span class="fw-bold">{{ $documentos->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Estado</span>
                            <span class="text-primary fw-bold text-uppercase" style="font-size: 0.7rem;">{{ $operacion->estado }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Documentos -->
            <div class="col-12">
                <div class="card-section">
                    <div class="card-header bg-white">
                        <h5 class="m-0"><i class="fas fa-folder-open text-muted"></i> Expediente Digital</h5>
                        <span class="badge bg-light text-muted">{{ $documentos->count() }} archivos</span>
                    </div>
                    <div class="card-body p-0">
                        @if($documentos && count($documentos) > 0)
                            <div class="table-responsive">
                                <table class="table compact-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Archivo</th>
                                            <th>Tipo / Categoría</th>
                                            <th>Emisión</th>
                                            <th>Carga</th>
                                            <th class="text-center">Operaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($documentos as $documento)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div style="width: 36px; height: 36px; background: #fee2e2; color: #dc2626; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </div>
                                                        <span class="fw-600 text-primary">{{ $documento->nombre_documento }}</span>
                                                    </div>
                                                </td>
                                                <td><span class="badge-status bg-gray">{{ $documento->tipo_documento ?? 'N/A' }}</span></td>
                                                <td>{{ $documento->fecha_documento ? $documento->fecha_documento->format('d/m/Y') : 'N/A' }}</td>
                                                <td><small class="text-muted">{{ $documento->created_at->diffForHumans() }}</small></td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm rounded bg-white border">
                                                        <a href="{{ route('documentos.download', $documento->id) }}" class="btn btn-white px-3" title="Descargar"><i class="fas fa-download text-success"></i></a>
                                                        <a href="#" class="btn btn-white px-3" data-bs-toggle="modal" data-bs-target="#previewModal" data-documento-id="{{ $documento->id }}" title="Ver"><i class="fas fa-eye text-primary"></i></a>
                                                        <form action="{{ route('documentos.destroy', $documento->id) }}" method="POST" class="d-inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-white px-3" onclick="return confirm('¿Eliminar documento?')" title="Borrar"><i class="fas fa-trash-alt text-danger"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-light mb-3"></i>
                                <p class="text-muted">Aún no se han adjuntado documentos a esta exportación.</p>
                                <button class="btn btn-modern btn-primary-modern" data-bs-toggle="modal" data-bs-target="#agregarDocumentoModal">
                                    <i class="fas fa-plus me-2"></i>Subir Documentos
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Documentos -->
    <div class="modal fade" id="agregarDocumentoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-cloud-upload-alt me-2"></i>Agregar Documentación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('documentos_operacion.store', $operacion) }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="drop-zone" id="dropZone">
                            <i class="fas fa-upload fa-3x text-primary-light mb-3"></i>
                            <h6 class="fw-bold">Arrastra archivos para cargarlos</h6>
                            <p class="text-muted small">o haz clic para seleccionar explorer</p>
                            <label for="archivo" class="btn btn-sm btn-outline-primary mt-2">Seleccionar Archivos</label>
                            <input type="file" class="d-none" id="archivo" name="archivos[]" multiple>
                        </div>

                        <div id="fileList" class="mt-4"></div>

                        <input type="hidden" name="operacion_id" value="{{ $operacion->id }}">
                        <input type="hidden" name="fecha_documento" value="{{ date('Y-m-d') }}">

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label text-xs fw-bold">Tipo de Documento</label>
                                <input type="text" id="tipo_documento_global" class="form-control" value="otros">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-xs fw-bold">Observaciones</label>
                                <textarea name="observaciones" id="observaciones" class="form-control" rows="1"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary-modern btn-modern px-4" id="submitBtn" disabled>
                            Subir <span id="fileCount">0</span> archivo(s)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Preview -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content overflow-hidden">
                <div class="modal-header">
                    <h5 class="modal-title">Vista Previa de Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="pdf-iframe" src="" width="100%" height="700" style="border:none;"></iframe>
                </div>
                <div class="modal-footer bg-light">
                    <a href="#" id="downloadPreview" class="btn btn-primary-modern btn-modern">Descargar Documento</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function setFormAction(action) {
        const form = document.getElementById('operacionForm');
        if (action === 'actualizar') {
            form.action = "{{ route('documentador.actualizardata', ['id' => $operacion->id]) }}";
        } else if (action === 'completar') {
            form.action = "{{ route('documentador.completar', ['id' => $operacion->id]) }}";
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const previewModal = document.getElementById('previewModal');
        if (previewModal) {
            previewModal.addEventListener('show.bs.modal', function (event) {
                const b = event.relatedTarget;
                const id = b.getAttribute('data-documento-id');
                document.getElementById('downloadPreview').href = `/documentos/${id}/download`;
                document.getElementById('pdf-iframe').src = `/documentos/${id}/preview#toolbar=0`;
            });
        }

        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('archivo');
        const fileList = document.getElementById('fileList');
        const submitBtn = document.getElementById('submitBtn');
        const fileCount = document.getElementById('fileCount');
        const modal = document.getElementById('agregarDocumentoModal');

        let selectedFiles = [];

        dropZone.addEventListener('click', () => fileInput.click());
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(e => dropZone.addEventListener(e, (ev) => { ev.preventDefault(); ev.stopPropagation(); }));
        ['dragenter', 'dragover'].forEach(e => dropZone.addEventListener(e, () => dropZone.classList.add('drag-over')));
        ['dragleave', 'drop'].forEach(e => dropZone.addEventListener(e, () => dropZone.classList.remove('drag-over')));

        dropZone.addEventListener('drop', (e) => handleFiles(e.dataTransfer.files));
        fileInput.addEventListener('change', (e) => handleFiles(e.target.files));

        function handleFiles(files) {
            selectedFiles = [...selectedFiles, ...Array.from(files)];
            updateFileList();
        }

        function updateFileList() {
            fileList.innerHTML = '';
            selectedFiles.forEach((f, i) => {
                const div = document.createElement('div');
                div.className = 'file-item';
                div.innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-file text-primary"></i>
                        <div><strong class="text-xs d-block">${f.name}</strong><span class="text-muted" style="font-size:0.65rem;">${(f.size/1024).toFixed(1)} KB</span></div>
                    </div>
                    <button type="button" class="btn btn-sm text-danger" onclick="removeFile(${i})"><i class="fas fa-times"></i></button>
                `;
                fileList.appendChild(div);
            });
            submitBtn.disabled = selectedFiles.length === 0;
            fileCount.textContent = selectedFiles.length;
        }

        window.removeFile = (i) => { selectedFiles.splice(i, 1); updateFileList(); };

        document.getElementById('uploadForm').addEventListener('submit', function() {
            const dt = new DataTransfer();
            selectedFiles.forEach(f => dt.items.add(f));
            fileInput.files = dt.files;
        });
    });
</script>
@endpush
