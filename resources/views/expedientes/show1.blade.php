@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Expediente: {{ $expediente->numero_pedimento }}</h1>
            <div>
                <a href="{{ route('expedientes.downloadAll', $expediente) }}" class="btn btn-success">
                    Descargar todos los documentos (ZIP)
                </a>
                <a href="{{ route('expedientes.edit', $expediente) }}" class="btn btn-warning">Editar</a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Información del Expediente</div>
                    <div class="card-body">
                        <p><strong>Cliente:</strong> {{ $expediente->cliente->nombre_empresa }}</p>
                        <p><strong>Patente:</strong> {{ $expediente->patente->numero_patente }}</p>
                        <p><strong>Aduana:</strong> {{ $expediente->aduana->nombre_aduana }}</p>
                        <p><strong>Categoría:</strong> {{ $expediente->categoria }}</p>
                        <p><strong>Estado:</strong> {{ $expediente->estado }}</p>
                        <p><strong>Fecha de Pago:</strong> {{ $expediente->fecha_pago_pedimento->format('d/m/Y') }}</p>
                        <p><strong>Documentador:</strong> {{ $expediente->documentador->name }}</p>
                        <p><strong>Observaciones:</strong> {{ $expediente->observaciones ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Documentos</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#agregarDocumentoModal">
                    Agregar Documento
                </button>
            </div>
            <div class="card-body">
                @if($expediente->documentos->isEmpty())
                    <p>No hay documentos registrados para este expediente.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expediente->documentos as $documento)
                                    <tr>
                                        <td>{{ $documento->nombre_documento }}</td>
                                        
                                        <td>{{ $documento->fecha_documento ? $documento->fecha_documento->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('documentos.download', $documento) }}"
                                                class="btn btn-sm btn-success">Descargar</a>
                                            <form action="{{ route('documentos.destroy', $documento) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('¿Eliminar este documento?')">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                        <div class="form-text">Tamaño máximo: 20MB. El nombre del archivo se usará como nombre del documento.</div>
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
    </div>
@endsection