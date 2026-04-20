@extends('layouts.app')

@section('title', 'Detalles de Factura')

@section('customcss')
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

    .file-icon.img {
        background-color: #dbeafe;
        color: #1d4ed8;
    }

    .file-icon.other {
        background-color: #f3e8ff;
        color: #7e22ce;
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
    <div class="row">
        <div class="col-12">
            @if(isset($factura) && $factura)
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detalles de Factura #{{ $factura->numero_factura }}</h4>
                    <div>
                        {{--<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarArchivo">
                            <i class="fas fa-plus me-2"></i>Agregar Archivos
                        </button>--}}
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Información Principal -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Información de la Factura</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Número de Factura:</th>
                                    <td>{{ $factura->numero_factura }}</td>
                                </tr>
                                <tr>
                                    <th>Fecha de Factura:</th>
                                    <td>{{ \Carbon\Carbon::parse($factura->fecha_factura)->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Año/Semana:</th>
                                    <td>{{ $factura->year }} - Semana {{ $factura->semana }}</td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        <span class="badge bg-{{ $factura->estado == 'pagada' ? 'success' : ($factura->estado == 'pendiente' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($factura->estado) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Registrado por:</th>
                                    <td>{{ $factura->usuario->name }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Información del Cliente</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Expediente:</th>
                                    <td>{{ $factura->expediente->numero_pedimento }}</td>
                                </tr>
                                <tr>
                                    <th>Cliente:</th>
                                    <td>{{ $factura->cliente->nombre_empresa }}</td>
                                </tr>
                                <tr>
                                    <th>Patente:</th>
                                    <td>{{ $factura->patente->numero_patente }}</td>
                                </tr>
                                <tr>
                                    <th>Cantidad Sobrepesos:</th>
                                    <td>{{ $factura->cantidad_sobrepesos }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Resumen de Trámites -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Resumen de Trámites</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $factura->cantidad_tramites }}</h3>
                                            <p class="mb-0">Total Trámites</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $factura->cantidad_rojos }}</h3>
                                            <p class="mb-0">Trámites en Rojo</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-dark">
                                        <div class="card-body text-center">
                                            <h3>{{ $factura->cantidad_sobrepesos }}</h3>
                                            <p class="mb-0">Sobrepesos</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h3>${{ number_format($factura->monto_total, 2) }}</h3>
                                            <p class="mb-0">Monto Total</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Montos -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Desglose de Montos</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Monto Total:</th>
                                    <td class="text-end">${{ number_format($factura->monto_total, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Monto Adicionales:</th>
                                    <td class="text-end">${{ number_format($factura->monto_adicionales, 2) }}</td>
                                </tr>
                                <tr class="table-success">
                                    <th><strong>Total General:</strong></th>
                                    <td class="text-end"><strong>${{ number_format($factura->monto_total + $factura->monto_adicionales, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Notas Adicionales -->
                    @if($factura->notas_adicionales)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Notas Adicionales</h5>
                            <div class="card">
                                <div class="card-body">
                                    {{ $factura->notas_adicionales }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- SECCIÓN DE DOCUMENTOS -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-paperclip me-2"></i>
                                        Archivos Adjuntos
                                    </h5>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarArchivo">
                                        <i class="fas fa-plus me-2"></i>Agregar Archivos
                                    </button>
                                </div>
                                <div class="card-body">
                                    @if(isset($factura->documentos) && count($factura->documentos) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th><i class="fas fa-file me-2"></i>Nombre del Archivo</th>
                                                        <th><i class="fas fa-tag me-2"></i>Tipo</th>
                                                        <th><i class="fas fa-hdd me-2"></i>Tamaño</th>
                                                        <th><i class="fas fa-calendar me-2"></i>Fecha de Carga</th>
                                                        <th class="text-center"><i class="fas fa-cog me-2"></i>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($factura->documentos as $archivo)
                                                        <tr>
                                                            <td>
                                                                @php
                                                                    $ext = pathinfo($archivo->nombre_documento, PATHINFO_EXTENSION);
                                                                    $iconClass = 'fa-file';
                                                                    $textClass = 'text-secondary';
                                                                    
                                                                    if (in_array(strtolower($ext), ['pdf'])) {
                                                                        $iconClass = 'fa-file-pdf';
                                                                        $textClass = 'text-danger';
                                                                    } elseif (in_array(strtolower($ext), ['xml'])) {
                                                                        $iconClass = 'fa-file-code';
                                                                        $textClass = 'text-warning';
                                                                    } elseif (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) {
                                                                        $iconClass = 'fa-file-image';
                                                                        $textClass = 'text-success';
                                                                    }
                                                                @endphp
                                                                <i class="fas {{ $iconClass }} {{ $textClass }} me-2"></i>
                                                                <strong>{{ $archivo->nombre_documento }}</strong>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-secondary">{{ strtoupper($archivo->tipo_documento) }}</span>
                                                            </td>
                                                            <td>{{ $archivo->tamano_formateado }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($archivo->created_at)->format('d/m/Y H:i') }}</td>
                                                            <td class="text-center">
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="{{ route('documentos.download', $archivo) }}"
                                                                        class="btn btn-outline-success" title="Descargar">
                                                                        <i class="fas fa-download"></i>
                                                                    </a>
                                                                    <a href="#" class="btn btn-outline-primary" title="Vista previa"
                                                                        data-bs-toggle="modal" data-bs-target="#previewModal"
                                                                        data-documento-id="{{ $archivo->id }}">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                    <form action="{{ route('documentos.destroy', $archivo) }}" method="POST"
                                                                        class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar"
                                                                            onclick="return confirm('¿Estás seguro de eliminar este documento?')">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </button>
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
                                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No hay archivos adjuntos para esta factura.</p>
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarArchivo">
                                                <i class="fas fa-plus me-2"></i>Agregar Primer Archivo
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para subir archivos (Drag & Drop) -->
            <div class="modal fade" id="modalAgregarArchivo" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-upload me-2"></i>Subir Documentos - Factura #{{ $factura->numero_factura }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="formSubirDocumento" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="factura_id" value="{{ $factura->id }}">
                            <div class="modal-body">
                                <!-- Zona de Drag & Drop -->
                                <div class="drag-drop-zone mb-3" id="dragDropZone">
                                    <div class="drag-drop-content">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                        <h6>Arrastra archivos aquí</h6>
                                        <p class="text-muted mb-2">o</p>
                                        <label for="fileInput" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-folder-open me-1"></i>Seleccionar Archivos
                                        </label>
                                        <input type="file" id="fileInput" class="d-none" accept=".pdf,.xml,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx" multiple>
                                        <small class="d-block mt-2 text-muted">PDF, XML, imágenes, documentos (Máx. 10MB por archivo)</small>
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

            <!-- Modal para vista previa -->
            <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="previewModalTitle">Vista previa del documento</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <iframe id="pdf-iframe" src="" width="100%" height="600" style="border:none;"></iframe>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <a href="#" id="downloadPreview" class="btn btn-primary">
                                <i class="fas fa-download me-1"></i> Descargar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @else
            <div class="alert alert-danger">
                <h4>Error: Factura no encontrada</h4>
                <p>La factura que buscas no existe o no se pudo cargar.</p>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Volver</a>
            </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            
            // Determinar clase del ícono
            let iconClass = 'fa-file';
            let iconBgClass = 'other';
            
            if (ext === 'pdf') {
                iconClass = 'fa-file-pdf';
                iconBgClass = 'pdf';
            } else if (ext === 'xml') {
                iconClass = 'fa-file-code';
                iconBgClass = 'xml';
            } else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                iconClass = 'fa-file-image';
                iconBgClass = 'img';
            }

            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.dataset.fileId = fileId;

            fileItem.innerHTML = `
                <div class="file-info">
                    <div class="file-icon ${iconBgClass}">
                        <i class="fas ${iconClass}"></i>
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
                        <option value="documento_adjunto">Documento Adjunto</option>
                        <option value="evidencia">Evidencia</option>
                        <option value="otro">Otro</option>
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
            const facturaId = document.querySelector('input[name="factura_id"]').value;
            formData.append('factura_id', facturaId);

            // Agregar archivos y tipos
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
                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarArchivo'));
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
        document.getElementById('modalAgregarArchivo').addEventListener('hidden.bs.modal', function () {
            resetForm();
        });

        // Vista previa de documentos
        const previewModal = document.getElementById('previewModal');
        if (previewModal) {
            previewModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const documentoId = button.getAttribute('data-documento-id');

                const downloadLink = document.getElementById('downloadPreview');
                downloadLink.href = `/documentos/${documentoId}/download`;

                const iframe = document.getElementById('pdf-iframe');
                iframe.src = `/documentos/${documentoId}/preview#toolbar=0`;
            });
        }
    });
</script>
@endsection