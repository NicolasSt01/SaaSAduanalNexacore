@extends('layouts.app')

@section('title', isset($expediente) ? 'Editar Expediente' : 'Crear Nuevo Expediente')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($expediente) ? 'Editar' : 'Crear' }} Expediente</h3>
                </div>
                <div class="card-body">
                    <form action="{{ isset($expediente) ? route('expedientes.update', $expediente) : route('expedientes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($expediente))
                            @method('PUT')
                        @endif
                        
                        <!-- Sección de datos del expediente -->
                        <div class="row g-3">
                            <!-- Cliente -->
                            <div class="col-md-6">
                                <label for="cliente_id" class="form-label">Cliente *</label>
                                <select class="form-select" id="cliente_id" name="cliente_id" required>
                                    <option value="">Seleccionar Cliente</option>
                                    @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" 
                                        {{ (old('cliente_id', $expediente->cliente_id ?? '') == $cliente->id) ? 'selected' : '' }}>
                                        {{ $cliente->nombre_empresa }} ({{ $cliente->rfc }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Resto de campos del expediente... -->
                            <!-- (Mantén aquí todos los otros campos de tu formulario) -->
                        </div>
                        
                        <!-- Sección de documentos -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h4>Documentos</h4>
                                
                                @if(isset($expediente) && $expediente->documentos->isNotEmpty())
                                <div class="mb-4">
                                    <h5>Documentos existentes</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Tipo</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($expediente->documentos as $documento)
                                                <tr>
                                                    <td>{{ $documento->nombre_documento }}</td>
                                                    <td>{{ $documento->tipo_documento ?? 'N/A' }}</td>
                                                    <td>
                                                        <a href="{{ route('documentos.download', $documento) }}" class="btn btn-sm btn-success" title="Descargar">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-danger delete-documento" 
                                                            data-id="{{ $documento->id }}" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
                                
                                <div id="documentos-container">
                                    <div class="documento-item mb-3 border p-3">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Nombre Documento *</label>
                                                <input type="text" name="documentos[0][nombre]" class="form-control" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Tipo Documento</label>
                                                <select name="documentos[0][tipo]" class="form-select">
                                                    <option value="">Seleccionar...</option>
                                                    <option value="Factura">Factura</option>
                                                    <option value="Pedimento">Pedimento</option>
                                                    <option value="Carta Porte">Carta Porte</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Archivo (PDF) *</label>
                                                <input type="file" name="documentos[0][archivo]" class="form-control" accept=".pdf" required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger remove-documento" style="display: none;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="button" id="add-documento" class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-plus"></i> Agregar otro documento
                                </button>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> {{ isset($expediente) ? 'Actualizar' : 'Guardar' }}
                                </button>
                                <a href="{{ route('expedientes.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar documento -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro que deseas eliminar este documento?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteDocumentForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejo de documentos dinámicos
        let docCounter = 1;
        
        // Agregar nuevo documento
        document.getElementById('add-documento').addEventListener('click', function() {
            const container = document.getElementById('documentos-container');
            const template = document.querySelector('.documento-item').cloneNode(true);
            
            // Actualizar índices
            const newIndex = docCounter++;
            template.innerHTML = template.innerHTML.replace(/\[0\]/g, `[${newIndex}]`);
            
            // Limpiar valores
            template.querySelectorAll('input').forEach(input => {
                if (input.type !== 'hidden') input.value = '';
            });
            template.querySelector('select').selectedIndex = 0;
            
            // Mostrar botón de eliminar
            template.querySelector('.remove-documento').style.display = 'block';
            template.querySelector('.remove-documento').addEventListener('click', function() {
                container.removeChild(template);
            });
            
            container.appendChild(template);
        });
        
        // Manejar botones de eliminar existentes
        document.querySelectorAll('.remove-documento').forEach(btn => {
            btn.style.display = 'none'; // Ocultar el primero
        });
        
        // Eliminar documentos existentes
        document.querySelectorAll('.delete-documento').forEach(btn => {
            btn.addEventListener('click', function() {
                const documentId = this.getAttribute('data-id');
                const form = document.getElementById('deleteDocumentForm');
                form.action = `/documentos/${documentId}`;
                
                const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                modal.show();
            });
        });
    });
</script>
@endpush