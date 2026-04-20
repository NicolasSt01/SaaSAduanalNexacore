@extends('layouts.app')

@section('title', 'Trabajar en Exportación')

@section('customcss')
    <style>
        .drop-zone {
            border: 3px dashed #cbd5e0;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
            cursor: pointer;
        }

        .drop-zone:hover {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }

        .drop-zone.drag-over {
            border-color: #0d6efd;
            background-color: #e7f1ff;
            transform: scale(1.02);
        }

        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }

        .file-item:hover {
            background-color: #e9ecef;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .file-info {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .file-icon {
            width: 40px;
            height: 40px;
            background-color: #0d6efd;
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }

        .file-details {
            flex: 1;
        }

        .file-name {
            font-weight: 500;
            margin-bottom: 2px;
            color: #2d3748;
        }

        .file-size {
            font-size: 0.875rem;
            color: #718096;
        }

        .file-remove {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.2s ease;
        }

        .file-remove:hover {
            background-color: #dc3545;
            color: white;
        }

        /* Nuevos estilos para campos pendientes */
        .info-item {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .badge-pending {
            background: linear-gradient(45deg, #ffc107, #ff9800);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">
        {{-- Alerta si faltan datos --}}
        @if(empty($operacion->bodega_id) || empty($operacion->patente_id) || empty($operacion->num_thermo) || empty($operacion->codigo_alpha))
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Atención:</strong> Esta operación tiene campos pendientes de completar desde Tráfico.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row">
            <!-- Columna izquierda -->
            <div class="col-md-8 mb-4">
                <!-- Información de la Exportación -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-file-export me-2"></i>Trabajando en Exportación
                            </h5>
                            <span class="badge bg-light text-dark">
                                Ref: {{ $operacion->referencia ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Información del Cliente -->
                            <div class="col-md-6">
                                <div class="info-item">
                                    <strong><i class="fas fa-building text-primary me-2"></i>Empresa:</strong>
                                    <br>{{ $operacion->cliente->nombre_empresa }}
                                </div>
                                <div class="info-item">
                                    <strong><i class="fas fa-user text-primary me-2"></i>Contacto:</strong>
                                    <br>{{ $operacion->cliente->contacto_principal ?? 'N/A' }}
                                </div>
                                <div class="info-item">
                                    <strong><i class="fas fa-truck text-primary me-2"></i>Importador:</strong>
                                    <br>{{ $operacion->importador->nombre ?? 'N/A' }}
                                </div>
                            </div>

                            <!-- Información del Producto -->
                            <div class="col-md-6">
                                <div class="info-item">
                                    <strong><i class="fas fa-box text-success me-2"></i>Producto:</strong>
                                    <br>{{ $operacion->nombre_producto }}
                                </div>
                                <div class="info-item">
                                    <strong><i class="fas fa-file-invoice text-success me-2"></i>Factura:</strong>
                                    <br>{{ $operacion->num_factura }}
                                </div>
                                <div class="info-item">
                                    <strong><i class="fas fa-calendar text-success me-2"></i>Fecha:</strong>
                                    <br>{{ \Carbon\Carbon::parse($operacion->fecha)->format('d/m/Y') }}
                                </div>
                            </div>

                            <!-- Información de Logística -->
                            <div class="col-md-6 mt-3">
                                <div class="info-item">
                                    <strong><i class="fas fa-warehouse text-info me-2"></i>Bodega:</strong>
                                    <br>
                                    @if($operacion->bodega)
                                        {{ $operacion->bodega->nombre_bodega }}
                                    @else
                                        <span class="badge bg-warning text-dark badge-pending">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Pendiente
                                        </span>
                                    @endif
                                </div>
                                <div class="info-item">
                                    <strong><i class="fas fa-landmark text-info me-2"></i>Aduana:</strong>
                                    <br>{{ $operacion->aduana->nombre_aduana }}
                                </div>
                                <div class="info-item">
                                    <strong><i class="fas fa-id-card text-info me-2"></i>Patente:</strong>
                                    <br>
                                    @if($operacion->patente)
                                        {{ $operacion->patente->numero_patente }}
                                    @else
                                        <span class="badge bg-info text-dark">
                                            <i class="fas fa-link me-1"></i>Se asignará con el pedimento
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Seguimiento -->
                            <div class="col-md-6 mt-3">
                                <div class="info-item">
                                    <strong><i class="fas fa-thermometer-half text-warning me-2"></i>Thermo:</strong>
                                    <br>
                                    @if($operacion->num_thermo)
                                        {{ $operacion->num_thermo }}
                                    @else
                                        <span class="badge bg-warning text-dark badge-pending">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Pendiente
                                        </span>
                                    @endif
                                </div>
                                <div class="info-item">
                                    <strong><i class="fas fa-barcode text-warning me-2"></i>Alpha:</strong>
                                    <br>
                                    @if($operacion->codigo_alpha)
                                        {{ $operacion->codigo_alpha }}
                                    @else
                                        <span class="badge bg-warning text-dark badge-pending">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Pendiente
                                        </span>
                                    @endif
                                </div>
                                <div class="info-item">
                                    <strong><i class="fas fa-flag text-warning me-2"></i>Estado:</strong>
                                    <br>
                                    <span class="badge bg-{{ $operacion->estado == 'proceso' ? 'warning' : ($operacion->estado == 'completado' ? 'success' : 'info') }}">
                                        {{ ucfirst($operacion->estado) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Prioridad y fecha -->
                            <div class="col-12 mt-3 pt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Prioridad:</strong>
                                        <span class="badge 
                                            @if($operacion->prioridad == 'urgente') bg-danger
                                            @elseif($operacion->prioridad == 'media') bg-warning
                                            @else bg-primary @endif">
                                            {{ ucfirst($operacion->prioridad ?? 'Normal') }}
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Creado: {{ $operacion->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha -->
            <div class="col-md-4 mb-4">
                <!-- Formulario completar exportación -->
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks me-2"></i>Completar Exportación
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('documentador.completar', ['id' => $operacion->id]) }}" method="POST" id="operacionForm">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="pedimento_id" class="form-label">
                                    <i class="fas fa-file-alt me-1 text-primary"></i>
                                    Expediente/Pedimento *
                                </label>
                                <select class="form-select" id="pedimento_id" name="pedimento_id" required>
                                    <option value="">-- Seleccione un expediente --</option>
                                    @foreach($expedientes as $expediente)
                                        <option value="{{ $expediente->id }}" 
                                                data-patente="{{ $expediente->patente_id ?? '' }}"
                                                {{ old('pedimento_id', $operacion->pedimento_id) == $expediente->id ? 'selected' : '' }}>
                                            {{ $expediente->numero_pedimento }} 
                                            @if($expediente->patente)
                                                - Patente: {{ $expediente->patente->numero_patente }}
                                            @endif
                                            - {{ $expediente->tipo_expediente }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    La patente se actualizará automáticamente
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="num_doda" class="form-label">
                                    <i class="fas fa-hashtag me-1 text-primary"></i>
                                    Número DODA *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="num_doda" 
                                       name="num_doda"
                                       value="{{ old('num_doda', $operacion->num_doda) }}"
                                       placeholder="Ingrese el número DODA"
                                       >
                            </div>

                            <!-- Botones -->
                            <div class="d-flex gap-2 mb-3">
                                <!-- Botón Actualizar Información -->
                                <button type="submit" 
                                        onclick="setFormAction('actualizar')"
                                        class="btn btn-primary flex-fill">
                                    <i class="fas fa-save me-2"></i>Actualizar
                                </button>

                                <!-- Botón Cargar Archivo -->
                                <button type="button" 
                                        class="btn btn-success flex-fill" 
                                        data-bs-toggle="modal"
                                        data-bs-target="#agregarDocumentoModal">
                                    <i class="fas fa-upload me-2"></i>Archivos
                                </button>
                            </div>

                            <div class="d-grid">
                                <!-- Botón Marcar como Completado -->
                                <button type="submit" 
                                        onclick="setFormAction('completar')"
                                        class="btn btn-success btn-lg">
                                    <i class="fas fa-check-circle me-2"></i>Marcar como Completado
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Listado de documentos -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-file me-2"></i>Documentos Adjuntos
                            </h5>
                            <span class="badge bg-light text-dark">
                                {{ $documentos->count() }} documento(s)
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($documentos && count($documentos) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th><i class="fas fa-file-alt me-1"></i>Nombre</th>
                                            <th><i class="fas fa-tag me-1"></i>Tipo</th>
                                            <th><i class="fas fa-calendar me-1"></i>Fecha</th>
                                            <th><i class="fas fa-clock me-1"></i>Subido</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($documentos as $documento)
                                            <tr>
                                                <td>
                                                    <i class="fas fa-file-pdf text-danger me-2"></i>
                                                    {{ $documento->nombre_documento }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        {{ $documento->tipo_documento ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $documento->fecha_documento ? $documento->fecha_documento->format('d/m/Y') : 'N/A' }}
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $documento->created_at->format('d/m/Y H:i') }}
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('documentos.download', $documento->id) }}"
                                                           class="btn btn-outline-success" 
                                                           title="Descargar">
                                                            <i class="fas fa-download"></i>
                                                        </a>

                                                        <a href="#" 
                                                           class="btn btn-outline-primary" 
                                                           title="Vista previa"
                                                           data-bs-toggle="modal" 
                                                           data-bs-target="#previewModal"
                                                           data-documento-id="{{ $documento->id }}">
                                                            <i class="fas fa-eye"></i>
                                                        </a>

                                                        <form action="{{ route('documentos.destroy', $documento->id) }}" 
                                                              method="POST" 
                                                              class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-outline-danger"
                                                                    onclick="return confirm('¿Eliminar este documento?')" 
                                                                    title="Eliminar">
                                                                <i class="fas fa-trash"></i>
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
                                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                                <p class="text-muted">No hay documentos adjuntos todavía.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarDocumentoModal">
                                    <i class="fas fa-plus me-2"></i>Agregar primer documento
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar documento con drag & drop -->
    <div class="modal fade" id="agregarDocumentoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-cloud-upload-alt me-2"></i>Agregar Documentos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('documentos_operacion.store', $operacion) }}" method="POST"
                    enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <div class="modal-body">
                        <!-- Zona de Drag & Drop -->
                        <div class="drop-zone" id="dropZone">
                            <div class="drop-zone-content">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <p class="mb-2"><strong>Arrastra archivos aquí</strong></p>
                                <p class="text-muted mb-3">o</p>
                                <label for="archivo" class="btn btn-primary">
                                    <i class="fas fa-folder-open me-2"></i>Seleccionar archivos
                                </label>
                                <input type="file" class="d-none" id="archivo" name="archivos[]" accept="*/*" multiple>
                                <p class="text-muted mt-3 mb-0">
                                    <small>Puedes subir múltiples archivos a la vez</small>
                                </p>
                            </div>
                        </div>

                        <!-- Lista de archivos seleccionados -->
                        <div id="fileList" class="mt-3"></div>

                        <input type="hidden" name="operacion_id" value="{{ $operacion->id }}">
                        <input type="hidden" name="fecha_documento" value="{{ date('Y-m-d') }}">

                        <div class="mt-3">
                            <label for="tipo_documento_global" class="form-label">
                                Tipo de documento (para todos)
                            </label>
                            <input type="text" 
                                   id="tipo_documento_global" 
                                   class="form-control" 
                                   value="otros"
                                   placeholder="otros">
                            <small class="text-muted">Se aplicará a todos los archivos seleccionados</small>
                        </div>

                        <div class="mt-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" 
                                      id="observaciones" 
                                      class="form-control" 
                                      rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="fas fa-upload me-2"></i>Subir <span id="fileCount">0</span> archivo(s)
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
                    <h5 class="modal-title">Vista previa del documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
@endsection

@push('scripts')
<script>
    // Función para cambiar la acción del formulario
    function setFormAction(action) {
        const form = document.getElementById('operacionForm');
        if (action === 'actualizar') {
            form.action = "{{ route('documentador.actualizardata', ['id' => $operacion->id]) }}";
        } else if (action === 'completar') {
            form.action = "{{ route('documentador.completar', ['id' => $operacion->id]) }}";
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Manejo de vista previa
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

        // Drag & Drop functionality
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('archivo');
        const fileList = document.getElementById('fileList');
        const submitBtn = document.getElementById('submitBtn');
        const fileCount = document.getElementById('fileCount');
        const modal = document.getElementById('agregarDocumentoModal');

        let selectedFiles = [];

        dropZone.addEventListener('click', function (e) {
            if (e.target.id !== 'archivo') {
                fileInput.click();
            }
        });

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, function () {
                dropZone.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, function () {
                dropZone.classList.remove('drag-over');
            });
        });

        dropZone.addEventListener('drop', function (e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        });

        fileInput.addEventListener('change', function (e) {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            const filesArray = Array.from(files);
            selectedFiles = [...selectedFiles, ...filesArray];
            updateFileList();
            updateSubmitButton();
        }

        function updateFileList() {
            fileList.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                const fileExtension = file.name.split('.').pop().toUpperCase();

                fileItem.innerHTML = `
                    <div class="file-info">
                        <div class="file-icon">
                            <i class="fas fa-file"></i>
                        </div>
                        <div class="file-details">
                            <div class="file-name">${file.name}</div>
                            <div class="file-size">${formatFileSize(file.size)} • ${fileExtension}</div>
                        </div>
                    </div>
                    <button type="button" class="file-remove" onclick="removeFile(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                fileList.appendChild(fileItem);
            });
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        window.removeFile = function (index) {
            selectedFiles.splice(index, 1);
            updateFileList();
            updateSubmitButton();
        }

        function updateSubmitButton() {
            if (selectedFiles.length > 0) {
                submitBtn.disabled = false;
                fileCount.textContent = selectedFiles.length;
            } else {
                submitBtn.disabled = true;
                fileCount.textContent = '0';
            }
        }

        modal.addEventListener('hidden.bs.modal', function () {
            selectedFiles = [];
            fileList.innerHTML = '';
            fileInput.value = '';
            updateSubmitButton();
        });

        document.getElementById('uploadForm').addEventListener('submit', function (e) {
            const dt = new DataTransfer();
            selectedFiles.forEach(file => {
                dt.items.add(file);
            });
            fileInput.files = dt.files;

            const tipoDocumento = document.getElementById('tipo_documento_global').value || 'otros';
            const tipoInput = document.createElement('input');
            tipoInput.type = 'hidden';
            tipoInput.name = 'tipo_documento';
            tipoInput.value = tipoDocumento;
            this.appendChild(tipoInput);
        });
    });
</script>
@endpush