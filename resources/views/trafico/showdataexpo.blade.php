@extends('layouts.app')

@section('customcss')
<style>
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 30px;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: #0d6efd;
}
input:checked + .slider:before {
    transform: translateX(30px);
}
.info-section {
    background-color: #f8f9fa;
}
</style>
<style>
    .drop-zone {
    border: 2px dashed #0d6efd;
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
}

.drop-zone:hover {
    background-color: #e9ecef;
    border-color: #0b5ed7;
}

.drop-zone.dragover {
    background-color: #cfe2ff;
    border-color: #0b5ed7;
    border-style: solid;
}

.file-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
    border: 1px solid #dee2e6;
}

.file-item-info {
    display: flex;
    align-items: center;
    flex: 1;
}

.file-item-icon {
    font-size: 24px;
    margin-right: 12px;
    color: #0d6efd;
}

.file-item-details {
    flex: 1;
}

.file-item-name {
    font-weight: 500;
    margin-bottom: 2px;
}

.file-item-size {
    font-size: 12px;
    color: #6c757d;
}

.file-item-remove {
    color: #dc3545;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.file-item-remove:hover {
    background-color: #f8d7da;
}

.file-type-input {
    width: 150px;
    font-size: 0.875rem;
}
</style>

@endsection

@section('content')
        <div class="container-fluid px-4">
            <!-- Header con breadcrumb y acciones -->
            <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
                <div>

                    <h1 class="h3 mb-0">Operación: <strong>Ref#{{ $operacion->referencia }}</strong></h1>
                </div>
                <div class="btn-group">
                    <a href="{{ route('trafico.nuevaexpo') }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus"></i> Nuevo Trámite
                    </a>
                    
                    <a href="{{ route('operaciones.edit', $operacion) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>

                </div>
            </div>

            <!-- Alertas -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> Por favor, corrige los errores en el formulario.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- Información principal -->
                <!-- Información principal -->
    <div class="col-xl-8 col-lg-7">
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Información de la Exportación</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Primera columna -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Cliente</label>
                            <p class="mb-0 fs-6">{{ $operacion->cliente->nombre_empresa ?? 'No especificado' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Número de Factura</label>
                            <p class="mb-0 fs-6">{{ $operacion->num_factura ?? 'No especificado' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Nombre del Producto</label>
                            <p class="mb-0 fs-6">{{ $operacion->nombre_producto ?? 'No especificado' }}</p>
                        </div>
                    </div>

                    <!-- Segunda columna -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Aduana</label>
                            <p class="mb-0 fs-6">{{ $operacion->aduana->nombre_aduana ?? 'No especificado' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Número de Thermo</label>
                            <div class="d-flex align-items-center gap-2">
                                <p class="mb-0 fs-6">{{ $operacion->num_thermo ?? 'No asignado' }}</p>
                                @if(in_array(auth()->user()->role, ['Trafico', 'admin']))
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" 
                                        data-bs-target="#actualizarThermoModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Código Alpha</label>
                            <div class="d-flex align-items-center gap-2">
                                <p class="mb-0 fs-6">{{ $operacion->codigo_alpha ?? 'No asignado' }}</p>
                                @if(in_array(auth()->user()->role, ['Trafico', 'admin']))
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" 
                                        data-bs-target="#actualizarAlphaModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Bodega</label>
                            <div class="d-flex align-items-center gap-2">
                                <p class="mb-0 fs-6">{{ $operacion->bodega->nombre_bodega ?? 'No asignado' }}</p>
                                @if(in_array(auth()->user()->role, ['Trafico', 'admin']))
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" 
                                        data-bs-target="#modalBodega">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de estado y fecha -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Estado</label>
                            @php
    $estadoColors = [
        'pendiente' => 'warning',
        'completado' => 'success',
        'rechazado' => 'danger',
        'en_revision' => 'info'
    ];
    $color = $estadoColors[$operacion->estado] ?? 'secondary';
                            @endphp
                            <p class="mb-0 fs-6"><span class="badge bg-{{ $color }}">{{ ucfirst($operacion->estado) }}</span></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Fecha</label>
                            <p class="mb-0 fs-6">{{ optional($operacion->created_at)->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Sección de información adicional -->
                <div class="info-section mt-3 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Usuario dio de alta</label>
                                <p class="mb-0 fs-6">{{ $operacion->documentador->name ?? 'Usuario no especificado' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Permiso de Sobrepeso</label>
                                <div class="d-flex align-items-center">
                                    <label class="switch me-2">
                                        <input type="checkbox" id="sobrepesoToggle" name="sobrepeso" 
                                            {{ $operacion->sobrepeso ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                    <span id="sobrepesoStatus">{{ $operacion->sobrepeso ? 'Activado' : 'Desactivado' }}</span>
                                </div>
                                <small class="form-text text-muted">Activar si aplica para sobrepeso</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="mt-3">
                    <label class="form-label text-muted small mb-1">Observaciones</label>
                    <p class="mb-0 fs-6">{{ $operacion->observaciones ?? 'Ninguna' }}</p>
                </div>
            </div>
        </div>
    </div>

                <!-- Panel de estadísticas -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Documentos</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Total de documentos</span>
                                <span class="badge bg-primary rounded-pill">{{ $operacion->documentos->count() }}</span>
                            </div>

                            @php
    $tiposDocumentos = $operacion->documentos->groupBy('tipo_documento');
                            @endphp

                            @foreach($tiposDocumentos as $tipo => $documentos)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">{{ $tipo }}</span>
                                    <span class="badge bg-secondary rounded-pill">{{ $documentos->count() }}</span>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ ($documentos->count() / $operacion->documentos->count()) * 100 }}%;"
                                        aria-valuenow="{{ ($documentos->count() / $operacion->documentos->count()) * 100 }}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de documentos -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Documentos</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#agregarDocumentoModal">
                        <i class="fas fa-plus me-1"></i> Agregar
                    </button>
                </div>
                <div class="card-body">
                    @if($operacion->documentos->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-3 text-muted">No hay documentos registrados.</p>
                            <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal"
                                data-bs-target="#agregarDocumentoModal">
                                <i class="fas fa-plus me-1"></i> Agregar primer documento
                            </button>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Nombre</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($operacion->documentos as $documento)
                                        <tr>
                                            <td><span class="badge bg-light text-dark">{{ $documento->tipo_documento }}</span></td>
                                            <td>{{ $documento->nombre_documento }}</td>
                                            <td>{{ $documento->fecha_documento ? $documento->fecha_documento->format('d/m/Y') : 'N/A' }}
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('documentos.download', $documento) }}"
                                                        class="btn btn-outline-success"><i class="fas fa-download"></i></a>
                                                    <a href="#" class="btn btn-outline-primary" title="Vista previa"
                                                        data-bs-toggle="modal" data-bs-target="#previewModal"
                                                        data-documento-id="{{ $documento->id }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <form action="{{ route('documentos.destroy', $documento) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger"
                                                            onclick="return confirm('¿Eliminar documento?')">
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
                    @endif
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4 mb-4">

                <div class="btn-group">
                    <a href="{{ route('trafico.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-check me-1"></i> Guardar
                    </a>

                </div>
            </div>
        </div>

        <!-- Modal para agregar documento -->
        <div class="modal fade" id="agregarDocumentoModal_Original" tabindex="-1" aria-labelledby="agregarDocumentoModalLabel_Original"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Agregar Documento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('documentos_operacion.store2', $operacion) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="tipo_documento" class="form-label">Tipo de documento</label>
                                <input type="text" name="tipo_documento" id="tipo_documento" class="form-control text-lowercase"
                                    required>
                                <small class="text-muted">Ejemplo: factura, carta_porte, etc.</small>
                            </div>
                            <!-- Campo oculto para la fecha (se establecerá en el controlador) -->
                            <input type="hidden" name="fecha_documento" value="{{ date('Y-m-d') }}">

                            <input type="hidden" name="operacion_id" value="{{ $operacion->id }}">

                            <div class="mb-3">
                                <label for="archivo" class="form-label">Archivo PDF *</label>
                                <input type="file" class="form-control" id="archivo" name="archivo" accept=".*" required
                                    onchange="document.getElementById('nombre_documento').value = this.files[0]?.name.split('.').slice(0, -1).join('.')">
                            </div>
                            <!-- Campo oculto para el nombre del documento (se llenará automáticamente) -->
                            <input type="hidden" id="nombre_documento" name="nombre_documento">
                            <input type="hidden" name="fecha_documento" value="{{ date('Y-m-d') }}">

                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea name="observaciones" id="observaciones" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Documento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Modal para agregar documento con drag & drop -->
    <div class="modal fade" id="agregarDocumentoModal" tabindex="-1" aria-labelledby="agregarDocumentoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Documentos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('documentos_operacion.store2', $operacion) }}" method="POST"
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
                                <p class="text-muted mt-3 mb-0"><small>Puedes subir múltiples archivos a la vez</small></p>
                            </div>
                        </div>

                        <!-- Lista de archivos seleccionados -->
                        <div id="fileList" class="mt-3"></div>

                        <input type="hidden" name="operacion_id" value="{{ $operacion->id }}">
                        <input type="hidden" name="fecha_documento" value="{{ date('Y-m-d') }}">

                        <div class="mt-3">
                            <label for="tipo_documento_global" class="form-label">Tipo de documento (para todos)</label>
                            <input type="text" id="tipo_documento_global" class="form-control" value="otros"
                                placeholder="otros">
                            <small class="text-muted">Se aplicará a todos los archivos seleccionados</small>
                        </div>

                        <div class="mt-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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
                <h5 class="modal-title" id="previewModalTitle">Vista previa del documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- IFRAME para vista previa -->
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
        <!-- Modal para actualizar número de thermo -->
        <div class="modal fade" id="actualizarThermoModal" tabindex="-1" aria-labelledby="actualizarThermoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Actualizar Número de Thermo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formActualizarThermo" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="campo" value="num_thermo">
                    <input type="hidden" name="operacion_id" value="{{ $operacion->id }}">
                    
                    <div class="mb-3">
                        <label for="nuevo_thermo" class="form-label">Número de Thermo</label>
                        <input type="text" class="form-control" id="nuevo_thermo" name="valor" 
                               value="{{ $operacion->num_thermo ?? '' }}" 
                               placeholder="Ingrese el número de thermo">
                        <div class="form-text">Actual: <strong>{{ $operacion->num_thermo ?? 'No asignado' }}</strong></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacion_thermo" class="form-label">Observación (opcional)</label>
                        <textarea class="form-control" id="observacion_thermo" name="observacion" 
                                  rows="2" placeholder="Motivo del cambio..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarThermo">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
        </div>

        <!-- Modal para actualizar código alpha -->
        <div class="modal fade" id="actualizarAlphaModal" tabindex="-1" aria-labelledby="actualizarAlphaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Actualizar Código Alpha</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formActualizarAlpha" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="campo" value="codigo_alpha">
                    <input type="hidden" name="operacion_id" value="{{ $operacion->id }}">
                    
                    <div class="mb-3">
                        <label for="nuevo_alpha" class="form-label">Código Alpha</label>
                        <input type="text" class="form-control" id="nuevo_alpha" name="valor" 
                               value="{{ $operacion->codigo_alpha ?? '' }}" 
                               placeholder="Ingrese el código alpha">
                        <div class="form-text">Actual: <strong>{{ $operacion->codigo_alpha ?? 'No asignado' }}</strong></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacion_alpha" class="form-label">Observación (opcional)</label>
                        <textarea class="form-control" id="observacion_alpha" name="observacion" 
                                  rows="2" placeholder="Motivo del cambio..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarAlpha">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
        </div>


        <!-- Modal simple para bodega -->
<div class="modal fade" id="modalBodegaOLD" tabindex="-1" aria-labelledby="modalBodegaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar Bodega</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('operaciones.asignarBodega', $operacion->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bodega_id" class="form-label">Seleccionar Bodega</label>
                        <select class="form-control" id="bodega_id" name="bodega_id">
                            <option value="">-- Seleccione una bodega --</option>
                            @foreach($bodegas as $bodega)
                                <option value="{{ $bodega->id }}" 
                                    {{ $operacion->bodega_id == $bodega->id ? 'selected' : '' }}>
                                    {{ $bodega->nombre_bodega }} 
                                    @if($bodega->codigo)
                                        ({{ $bodega->codigo }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observacion" class="form-label">Observación (opcional)</label>
                        <textarea class="form-control" id="observacion" name="observacion" rows="2"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <strong>Actual:</strong> {{ $operacion->bodega->nombre ?? 'No asignado' }}
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

     <!-- Modal para bodega -->
<div class="modal fade" id="modalBodega" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar Bodega</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <!-- USA LA RUTA CON EL PARÁMETRO -->
            <form action="{{ route('operaciones.asignarBodega', $operacion->id) }}" method="POST" id="formAsignarBodega">
                @csrf
                <!-- NO NECESITAS @method('PUT') porque es POST -->
                <div class="modal-body">
                    <!-- Solo estos campos si tu método los requiere -->
                    <input type="hidden" name="campo" value="bodega_id">
                    <input type="hidden" name="operacion_id" value="{{ $operacion->id }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Bodega</label>
                        <select class="form-control" name="valor" id="selectBodega">
                            <option value="">-- Sin bodega --</option>
                            @foreach($bodegas ?? [] as $bodega)
                                <option value="{{ $bodega->id }}" 
                                    {{ $operacion->bodega_id == $bodega->id ? 'selected' : '' }}>
                                    {{ $bodega->nombre_bodega }}
                                    @if($bodega->tax_id)
                                        ({{ $bodega->tax_id }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observación (opcional)</label>
                        <textarea class="form-control" name="observacion" rows="2"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <strong>Actual:</strong> {{ $operacion->bodega->nombre ?? 'No asignada' }}
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>


        <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('sobrepesoToggle');
        const status = document.getElementById('sobrepesoStatus');

        if (toggle) {
            toggle.addEventListener('change', function() {
                status.textContent = this.checked ? 'Activado' : 'Desactivado';

                // Enviar el cambio al servidor mediante AJAX
                fetch('{{ route("operaciones.updateSobrepeso", $operacion->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        sobrepeso: this.checked
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Estado de sobrepeso actualizado correctamente');
                    } else {
                        console.error('Error al actualizar el estado de sobrepeso');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        }
    });
    </script>


    <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Autocompletar nombre del documento desde el nombre del archivo
                const archivoInput = document.getElementById('archivo');
                const nombreDocumentoInput = document.getElementById('nombre_documento');

                if (archivoInput && nombreDocumentoInput) {
                    archivoInput.addEventListener('change', function () {
                        if (this.files && this.files[0]) {
                            const fileName = this.files[0].name;
                            // Eliminar la extensión del archivo
                            const baseName = fileName.split('.').slice(0, -1).join('.');
                            nombreDocumentoInput.value = baseName;
                        }
                    });
                }


            });
        </script>

            
<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewModal = document.getElementById('previewModal');
    if (previewModal) {
        previewModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget; // el botón que abrió el modal
            const documentoId = button.getAttribute('data-documento-id');

            // enlace de descarga
            const downloadLink = document.getElementById('downloadPreview');
            downloadLink.href = `/documentos/${documentoId}/download`;

            // cargar el PDF en el iframe
            const iframe = document.getElementById('pdf-iframe');
            iframe.src = `/documentos/${documentoId}/preview#toolbar=0`; 
            // ^ esta ruta la tienes que crear en Laravel tal como te puse antes
        });
    }
});
</script>

<!-- Script de formulario modal de archivos-->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('archivo');
    const fileList = document.getElementById('fileList');
    const submitBtn = document.getElementById('submitBtn');
    const fileCount = document.getElementById('fileCount');
    const uploadForm = document.getElementById('uploadForm');
    
    let selectedFiles = [];

    // Hacer clic en la zona de drop
    dropZone.addEventListener('click', function(e) {
        if (e.target === this || e.target.closest('.drop-zone-content')) {
            fileInput.click();
        }
    });

    // Prevenir comportamiento por defecto
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Resaltar zona cuando se arrastra sobre ella
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, function() {
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, function() {
            dropZone.classList.remove('dragover');
        }, false);
    });

    // Manejar el drop
    dropZone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    });

    // Manejar selección de archivos
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        const filesArray = Array.from(files);
        
        filesArray.forEach(file => {
            // Evitar duplicados
            const isDuplicate = selectedFiles.some(f => 
                f.name === file.name && f.size === file.size
            );
            
            if (!isDuplicate) {
                selectedFiles.push(file);
            }
        });
        
        updateFileList();
        updateSubmitButton();
    }

    function updateFileList() {
        fileList.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            
            const fileSize = formatFileSize(file.size);
            const fileIcon = getFileIcon(file.name);
            
            fileItem.innerHTML = `
                <div class="file-item-info">
                    <div class="file-item-icon">
                        <i class="${fileIcon}"></i>
                    </div>
                    <div class="file-item-details">
                        <div class="file-item-name">${file.name}</div>
                        <div class="file-item-size">${fileSize}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" 
                           class="form-control form-control-sm file-type-input" 
                           placeholder="Tipo de documento"
                           data-file-index="${index}"
                           id="tipo_${index}">
                    <span class="file-item-remove" data-index="${index}">
                        <i class="fas fa-times"></i>
                    </span>
                </div>
            `;
            
            fileList.appendChild(fileItem);
        });
        
        // Event listeners para eliminar archivos
        document.querySelectorAll('.file-item-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                removeFile(index);
            });
        });
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        updateFileList();
        updateSubmitButton();
    }

    function updateSubmitButton() {
        fileCount.textContent = selectedFiles.length;
        submitBtn.disabled = selectedFiles.length === 0;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    function getFileIcon(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        const iconMap = {
            'pdf': 'fas fa-file-pdf text-danger',
            'doc': 'fas fa-file-word text-primary',
            'docx': 'fas fa-file-word text-primary',
            'xls': 'fas fa-file-excel text-success',
            'xlsx': 'fas fa-file-excel text-success',
            'jpg': 'fas fa-file-image text-info',
            'jpeg': 'fas fa-file-image text-info',
            'png': 'fas fa-file-image text-info',
            'zip': 'fas fa-file-archive text-warning',
            'rar': 'fas fa-file-archive text-warning'
        };
        return iconMap[extension] || 'fas fa-file text-secondary';
    }

    // Manejar el envío del formulario
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Eliminar el input file original del FormData
        formData.delete('archivos[]');
        
        const tipoGlobal = document.getElementById('tipo_documento_global').value || 'otros';
        
        // Agregar cada archivo con su tipo de documento
        selectedFiles.forEach((file, index) => {
            formData.append('archivos[]', file);
            
            const tipoInput = document.getElementById(`tipo_${index}`);
            const tipoDocumento = tipoInput && tipoInput.value ? tipoInput.value : tipoGlobal;
            formData.append('tipos_documento[]', tipoDocumento);
        });
        
        // Deshabilitar botón y mostrar loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Subiendo...';
        
        // Enviar con fetch
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cerrar modal y recargar página
                bootstrap.Modal.getInstance(document.getElementById('agregarDocumentoModal')).hide();
                location.reload();
            } else {
                alert('Error al subir archivos: ' + (data.message || 'Error desconocido'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Subir <span id="fileCount">' + selectedFiles.length + '</span> archivo(s)';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al subir archivos');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Subir <span id="fileCount">' + selectedFiles.length + '</span> archivo(s)';
        });
    });

    // Limpiar al cerrar el modal
    document.getElementById('agregarDocumentoModal').addEventListener('hidden.bs.modal', function() {
        selectedFiles = [];
        fileList.innerHTML = '';
        fileInput.value = '';
        updateSubmitButton();
        document.getElementById('tipo_documento_global').value = 'otros';
        document.getElementById('observaciones').value = '';
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar los formularios
    const formThermo = document.getElementById('formActualizarThermo');
    const formAlpha = document.getElementById('formActualizarAlpha');
    
    if (formThermo) {
        formThermo.addEventListener('submit', function(e) {
            e.preventDefault();
            actualizarCampo(this, 'thermo');
        });
    }
    
    if (formAlpha) {
        formAlpha.addEventListener('submit', function(e) {
            e.preventDefault();
            actualizarCampo(this, 'alpha');
        });
    }
    
    function actualizarCampo(form, tipo) {
    const formData = new FormData(form);
    const btnGuardar = form.querySelector('button[type="submit"]');
    const originalText = btnGuardar.innerHTML;
    
    console.log('Enviando datos para actualizar campo:', {
        operacion_id: formData.get('operacion_id'),
        campo: formData.get('campo'),
        valor: formData.get('valor'),
        observacion: formData.get('observacion')
    });
    
    // Mostrar loading
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
    
    fetch('{{ route('operaciones.actualizarCampo') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        console.log('Respuesta recibida, status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Datos de respuesta:', data);
        if (data.success) {
            // Mostrar mensaje de éxito
            showAlert('success', data.message);
            
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
            modal.hide();
            
            // Recargar la página después de un breve delay
            setTimeout(() => {
                location.reload();
            }, 1500);
            
        } else {
            showAlert('danger', data.message || 'Error al actualizar');
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        showAlert('danger', 'Error de conexión');
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = originalText;
    });
}

    function showAlert(type, message) {
        // Crear alerta
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insertar después del header
        const container = document.querySelector('.container-fluid');
        container.insertBefore(alert, container.firstChild);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    
    // Limpiar formularios cuando se cierren los modales
    document.getElementById('actualizarThermoModal')?.addEventListener('hidden.bs.modal', function() {
        const form = document.getElementById('formActualizarThermo');
        const btn = document.getElementById('btnGuardarThermo');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar';
        }
    });
    
    document.getElementById('actualizarAlphaModal')?.addEventListener('hidden.bs.modal', function() {
        const form = document.getElementById('formActualizarAlpha');
        const btn = document.getElementById('btnGuardarAlpha');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar';
        }
    });
});
</script>


<!-- Este es el javascript del modal de bodegas-->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formBodega = document.getElementById('formAsignarBodega3');
    
    if (formBodega) {
        formBodega.addEventListener('submit', function(e) {
            e.preventDefault();
            actualizarBodega(this);
        });
    }
    
    function actualizarBodega(form) {
        const formData = new FormData(form);
        const btnSubmit = form.querySelector('button[type="submit"]');
        const originalText = btnSubmit.innerHTML;
        
        // Mostrar loading
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
        
        fetch('{{ route("operaciones.actualizarCampo") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar alerta de éxito
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container-fluid').prepend(alert);
                
                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
                modal.hide();
                
                // Recargar después de 1.5 segundos
                setTimeout(() => {
                    location.reload();
                }, 1500);
                
            } else {
                alert('Error: ' + data.message);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalText;
        });
    }
});
</script>

@endsection

@push('scripts')

    
@endpush