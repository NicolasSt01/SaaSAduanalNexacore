@extends('layouts.app')

@section('title', 'Asignar Exportación')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white py-2">
                        <h5 class="mb-0">Asignar Exportación</h5>
                    </div>
                    <div class="card-body">
                        <!-- Información de la Exportación -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6>Información de la Exportación</h6>
                            <p><strong>Cliente:</strong> {{ $operacion->cliente->nombre_empresa }}</p>
                            <p><strong>Producto:</strong> {{ $operacion->nombre_producto }}</p>
                            <p><strong>Factura:</strong> {{ $operacion->num_factura }}</p>
                            <p><strong>Estado actual:</strong> 
                                <span class="badge bg-{{ $operacion->estado == 'pendiente' ? 'warning' : 'info' }}">
                                    {{ ucfirst($operacion->estado) }}
                                </span>
                            </p>
                        </div>

                        <!-- Formulario de Asignación -->
                        <form action="{{ route('operaciones.asignar', $operacion->id) }}" method="POST">
                            @csrf

                            <!-- Documentador -->
                            <div class="mb-3">
                                <label for="usuario_cierre_id" class="form-label">Seleccionar Documentador:</label>
                                <select class="form-select" id="usuario_cierre_id" name="usuario_cierre_id" required>
                                    <option value="">-- Seleccione un documentador --</option>
                                    @foreach($documentadores as $documentador)
                                        <option value="{{ $documentador->id }}" 
                                            {{ old('usuario_cierre_id') == $documentador->id ? 'selected' : '' }}>
                                            {{ $documentador->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('usuario_cierre_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Prioridad -->
                            <div class="mb-3">
                                <label for="prioridad" class="form-label">Prioridad:</label>
                                <select class="form-select" id="prioridad" name="prioridad" required>
                                    <option value="regular" {{ old('prioridad') == 'regular' ? 'selected' : '' }}>Regular</option>
                                    <option value="media" {{ old('prioridad') == 'media' ? 'selected' : '' }}>Media</option>
                                    <option value="urgente" {{ old('prioridad') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                </select>
                                @error('prioridad')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Comentarios -->
                            <div class="mb-4">
                                <label for="comentarios" class="form-label">Comentarios/Instrucciones:</label>
                                <textarea class="form-control" id="comentarios" name="comentarios" 
                                    rows="4" placeholder="Instrucciones específicas para el documentador">{{ old('comentarios') }}</textarea>
                                @error('comentarios')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('operaciones.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-1"></i> Asignar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection