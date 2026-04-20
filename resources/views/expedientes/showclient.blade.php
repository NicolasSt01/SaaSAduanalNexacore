@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <!-- Header con breadcrumb y acciones -->
        <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
            <div>

                <h1 class="h3 mb-0">Expediente: <strong>{{ $expediente->numero_pedimento }}</strong></h1>
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
            <div class="col-xl-8 col-lg-7">
                <div class="card mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Información del
                            Expediente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Número de Pedimento</label>
                                    <p class="mb-0 fs-6">{{ $expediente->numero_pedimento }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Cliente</label>
                                    <p class="mb-0 fs-6">{{ $expediente->cliente->nombre_empresa }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Patente</label>
                                    <p class="mb-0 fs-6">{{ $expediente->patente->numero_patente }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Aduana</label>
                                    <p class="mb-0 fs-6">{{ $expediente->aduana->nombre_aduana }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Categoría</label>
                                    <p class="mb-0 fs-6">
                                        <span class="badge bg-info text-light">{{ $expediente->categoria }}</span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Estado</label>
                                    <p class="mb-0 fs-6">
                                        @php
                                            $estadoColors = [
                                                'pendiente' => 'warning',
                                                'Cerrado' => 'success',
                                                'rechazado' => 'danger',
                                                'en_revision' => 'info'
                                            ];
                                            $color = $estadoColors[$expediente->estado] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ ucfirst($expediente->estado) }}</span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Fecha de Pago</label>
                                    <p class="mb-0 fs-6">
                                        {{ optional($expediente->fecha_pago_pedimento)->format('d/m/Y') ?? '-' }}</p>
                                </div>
                                
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label text-muted small mb-1">Observaciones</label>
                            <p class="mb-0 fs-6">{{ $expediente->observaciones ?? 'Ninguna' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de estadísticas -->
            <div class="col-xl-4 col-lg-5">
                <div class="card mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Estadísticas de
                            Documentos</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Total de documentos</span>
                            <span class="badge bg-primary rounded-pill">{{ $expediente->documentos->count() }}</span>
                        </div>

                        @php
                            $tiposDocumentos = $expediente->documentos->groupBy('tipo_documento');
                        @endphp

                        @foreach($tiposDocumentos as $tipo => $documentos)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">{{ $tipo }}</span>
                                <span class="badge bg-secondary rounded-pill">{{ $documentos->count() }}</span>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: {{ ($documentos->count() / $expediente->documentos->count()) * 100 }}%;"
                                    aria-valuenow="{{ ($documentos->count() / $expediente->documentos->count()) * 100 }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        @endforeach

                        <div class="mt-4 text-center">
                            <a href="{{ route('expedientes.downloadAll', $expediente) }}"
                                class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> Descargar todos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de documentos -->
        {{--<div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Documentos del Expediente</h5>
                
            </div>
            <div class="card-body">
                @if($expediente->documentos->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">No hay documentos registrados para este expediente.</p>
                        
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Nombre</th>
                                    <th>Fecha</th>
                                    <th>Tamaño</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expediente->documentos as $documento)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $documento->tipo_documento }}</span>
                                        </td>
                                        <td>{{ $documento->nombre_documento }}</td>
                                        <td>{{ $documento->fecha_documento ? $documento->fecha_documento->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td>
                                            @if($documento->archivo_path && file_exists(storage_path('app/' . $documento->archivo_path)))
                                                {{ round(filesize(storage_path('app/' . $documento->archivo_path)) / 1024, 1) }} KB
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('documentos.download', $documento) }}"
                                                    class="btn btn-outline-success" title="Descargar">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a href="#" class="btn btn-outline-primary" title="Vista previa"
                                                    data-bs-toggle="modal" data-bs-target="#previewModal"
                                                    data-documento-id="{{ $documento->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>--}}

        <!-- Tabla de documentos agrupados por operación -->
<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Documentos del Expediente</h5>
        
    </div>
    <div class="card-body">
        @if($expediente->documentos->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-folder-open text-muted" style="font-size: 3rem;"></i>
                <p class="mt-3 text-muted">No hay documentos registrados para este expediente.</p>
                
            </div>
        @else
            @php
                // Agrupar documentos por operacion_id
                $documentosPorOperacion = $expediente->documentos->groupBy('operacion_id');
            @endphp

            <div class="accordion" id="documentosAccordion">
                @foreach($documentosPorOperacion as $exportacionId => $documentos)
                    @php
                        // Obtener información de la operación si existe
                        $exportacion = $documentos->first()->exportacion ?? null;
                        $operacionNombre = $exportacion ? $exportacion->nombre_operacion ?? "Operación #{$exportacionId}" : "Sin operación asignada";
                        $accordionId = "collapse-" . ($exportacionId ?? 'sin-operacion');
                    @endphp
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-{{ $accordionId }}">
                            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#{{ $accordionId }}" 
                                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
                                    aria-controls="{{ $accordionId }}">
                                <div class="d-flex align-items-center w-100">
                                    <i class="fas fa-folder-open me-2 text-primary"></i>
                                    <span class="fw-semibold">{{ $operacionNombre }}</span>
                                    <span class="badge bg-secondary ms-auto me-2">{{ $documentos->count() }} documento(s)</span>
                                </div>
                            </button>
                        </h2>
                        <div id="{{ $accordionId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                             aria-labelledby="heading-{{ $accordionId }}" data-bs-parent="#documentosAccordion">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Nombre</th>
                                                <th>Fecha</th>
                                                <th>Tamaño</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($documentos as $documento)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-light text-dark">{{ $documento->tipo_documento }}</span>
                                                    </td>
                                                    <td>{{ $documento->nombre_documento }}</td>
                                                    <td>{{ $documento->fecha_documento ? $documento->fecha_documento->format('d/m/Y') : 'N/A' }}</td>
                                                    <td>
                                                        @if($documento->archivo_path && file_exists(storage_path('app/' . $documento->archivo_path)))
                                                            {{ round(filesize(storage_path('app/' . $documento->archivo_path)) / 1024, 1) }} KB
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('documentos.download', $documento) }}"
                                                                class="btn btn-outline-success" title="Descargar">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                            <a href="#" class="btn btn-outline-primary" title="Vista previa"
                                                                data-bs-toggle="modal" data-bs-target="#previewModal"
                                                                data-documento-id="{{ $documento->id }}">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Información adicional de la operación (opcional) -->
                                @if($exportacion)
                                    <div class="p-3 bg-light border-top">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Operación: {{ $exportacion->id ?? 'N/A' }} | 
                                            Fecha: {{ $exportacion->fecha ? $exportacion->fecha->format('d/m/Y') : 'N/A' }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Botón para expandir/contraer todos -->
            <div class="text-center mt-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleAllAccordions">
                    <i class="fas fa-expand-arrows-alt me-1"></i> Expandir/Contraer todo
                </button>
            </div>
        @endif
    </div>
</div>


    </div>

    <!-- Modal para agregar documento simplificado -->
    <div class="modal fade" id="agregarDocumentoModal" tabindex="-1" aria-labelledby="agregarDocumentoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="agregarDocumentoModalLabel">Agregar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('documentos.store', $expediente) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <!-- Campo oculto para el tipo de documento (valor fijo: Otros) -->
                        <input type="hidden" name="tipo_documento" value="Otro">

                        <!-- Campo oculto para la fecha (se establecerá en el controlador) -->
                        <input type="hidden" name="fecha_documento" value="{{ date('Y-m-d') }}">

                        <div class="mb-3">
                            <label for="archivo" class="form-label">Archivo PDF *</label>
                            <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf" required
                                onchange="document.getElementById('nombre_documento').value = this.files[0]?.name.split('.').slice(0, -1).join('.')">
                            <div class="form-text">Tamaño máximo: 20MB. El nombre del archivo se usará como nombre del
                                documento.</div>
                        </div>

                        <!-- Campo oculto para el nombre del documento (se llenará automáticamente) -->
                        <input type="hidden" id="nombre_documento" name="nombre_documento">

                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones (opcional)</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
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

    <!-- Modal para cerrar firma y colocar fecha de pago -->
    <div class="modal fade" id="cerrarFirmaModal" tabindex="-1" aria-labelledby="cerrarFirmaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="cerrarFirmaModalLabel">Cerrar Firma y Actualizar Pago</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>

                <form action="{{ route('expedientes.cerrarFirma', $expediente) }}" method="POST">
                    @csrf
                    @method('POST') {{-- O el método que uses para actualizar --}}
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="estado" class="form-label">Nuevo estado</label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="En proceso" @selected($expediente->estado == 'En proceso')>En Proceso</option>
                                <option value="Abierto" @selected($expediente->estado == 'Abierto')>Abierto</option>
                                <option value="Cerrado" @selected($expediente->estado == 'Cerrado')>Cerrado</option>
                                <option value="Cancelado" @selected($expediente->estado == 'Cancelado')>Cancelado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_pago_pedimento" class="form-label">Fecha de Pago</label>
                            <input type="date" class="form-control" id="fecha_pago_pedimento" name="fecha_pago_pedimento"
                                value="{{ old('fecha_pago_pedimento', optional($expediente->fecha_pago_pedimento ?? now())->format('Y-m-d')) }}">
                        </div>
                        <div class="mb-3">
                            <label for="fecha_cierre" class="form-label">Fecha de Cierre</label>
                            <input type="date" class="form-control" id="fecha_cierre" name="fecha_cierre"
                                value="{{ old('fecha_cierre', optional($expediente->fecha_cierre ?? now())->format('Y-m-d')) }}">
                        </div>
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones (opcional)</label>
                            <textarea class="form-control" id="observaciones" name="observaciones"
                                rows="2">{{ old('observaciones', $expediente->observaciones) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>

            </div>
        </div>
    </div>





    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad para expandir/contraer todos los acordeones
    const toggleButton = document.getElementById('toggleAllAccordions');
    if (toggleButton) {
        let allExpanded = true; // Asumimos que el primer acordeón está expandido por defecto
        
        toggleButton.addEventListener('click', function() {
            const accordionCollapses = document.querySelectorAll('#documentosAccordion .accordion-collapse');
            const accordionButtons = document.querySelectorAll('#documentosAccordion .accordion-button');
            
            accordionCollapses.forEach(function(collapse) {
                const bsCollapse = new bootstrap.Collapse(collapse, {
                    toggle: false
                });
                
                if (allExpanded) {
                    bsCollapse.hide();
                } else {
                    bsCollapse.show();
                }
            });
            
            // Actualizar el estado de los botones
            accordionButtons.forEach(function(button) {
                if (allExpanded) {
                    button.classList.add('collapsed');
                    button.setAttribute('aria-expanded', 'false');
                } else {
                    button.classList.remove('collapsed');
                    button.setAttribute('aria-expanded', 'true');
                }
            });
            
            allExpanded = !allExpanded;
            
            // Actualizar el texto del botón
            const icon = toggleButton.querySelector('i');
            if (allExpanded) {
                icon.className = 'fas fa-compress-arrows-alt me-1';
            } else {
                icon.className = 'fas fa-expand-arrows-alt me-1';
            }
        });
    }
});
</script>

@endsection

@push('scripts')

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

            // Manejar la vista previa
            
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

@endpush