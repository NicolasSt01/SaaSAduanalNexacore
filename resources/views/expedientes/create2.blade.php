@extends('layouts.app')

@section('title', 'Crear Nuevo Expediente')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Nuevo Expediente</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('expedientes.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3">
                            <!-- Cliente -->
                            <div class="col-md-6">
                                <label for="cliente_id" class="form-label">Cliente *</label>
                                <select class="form-select" id="cliente_id" name="cliente_id" required>
                                    <option value="">Seleccionar Cliente</option>
                                    @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombre_empresa }} ({{ $cliente->rfc }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Patente -->
                            <div class="col-md-6">
                                <label for="patente_id" class="form-label">Patente *</label>
                                <select class="form-select" id="patente_id" name="patente_id" required>
                                    <option value="">Seleccionar Patente</option>
                                    @foreach($patentes as $patente)
                                    <option value="{{ $patente->id }}" {{ old('patente_id') == $patente->id ? 'selected' : '' }}>
                                        {{ $patente->numero_patente }} - {{ $patente->nombre_agente_aduanal }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Aduana -->
                            <div class="col-md-6">
                                <label for="aduana_id" class="form-label">Aduana *</label>
                                <select class="form-select" id="aduana_id" name="aduana_id" required>
                                    <option value="">Seleccionar Aduana</option>
                                    @foreach($aduanas as $aduana)
                                    <option value="{{ $aduana->id }}" {{ old('aduana_id') == $aduana->id ? 'selected' : '' }}>
                                        {{ $aduana->clave_aduana }} - {{ $aduana->nombre_aduana }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Documentador -->
                            <div class="col-md-6">
                                <label for="usuario_registro_id" class="form-label">Documentador *</label>
                                <select class="form-select" id="usuario_registro_id" name="usuario_registro_id" required>
                                    <option value="">Seleccionar Documentador</option>
                                    @foreach($documentadores as $documentador)
                                    <option value="{{ $documentador->id }}" {{ old('usuario_registro_id') == $documentador->id ? 'selected' : '' }}>
                                        {{ $documentador->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Número de Pedimento -->
                            <div class="col-md-6">
                                <label for="numero_pedimento" class="form-label">Número de Pedimento *</label>
                                <input type="text" class="form-control" id="numero_pedimento" name="numero_pedimento" 
                                       value="{{ old('numero_pedimento') }}" required>
                            </div>
                            
                            <!-- Fecha de Pago -->
                            <div class="col-md-6">
                                <label for="fecha_pago_pedimento" class="form-label">Fecha de Pago *</label>
                                <input type="date" class="form-control" id="fecha_pago_pedimento" 
                                       name="fecha_pago_pedimento" value="{{ old('fecha_pago_pedimento') }}" required>
                            </div>
                            
                            <!-- Categoría -->
                            <div class="col-md-6">
                                <label for="categoria" class="form-label">Categoría *</label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="">Seleccionar Categoría</option>
                                    <option value="Importacion" {{ old('categoria') == 'Importacion' ? 'selected' : '' }}>Importación</option>
                                    <option value="Operacion" {{ old('categoria') == 'Operacion' ? 'selected' : '' }}>Exportación</option>
                                    <option value="Rectificaciones" {{ old('categoria') == 'Rectificaciones' ? 'selected' : '' }}>Rectificaciones</option>
                                </select>
                            </div>
                            
                            <!-- Estado -->
                            <div class="col-md-6">
                                <label for="estado" class="form-label">Estado *</label>
                                <select class="form-select" id="estado" name="estado" required>
                                    <option value="En proceso" {{ old('estado') == 'En proceso' ? 'selected' : '' }}>En proceso</option>
                                    <option value="Completado" {{ old('estado') == 'Completado' ? 'selected' : '' }}>Completado</option>
                                    <option value="Archivado" {{ old('estado') == 'Archivado' ? 'selected' : '' }}>Archivado</option>
                                </select>
                            </div>
                            
                            <!-- Observaciones -->
                            <div class="col-12">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" 
                                          rows="3">{{ old('observaciones') }}</textarea>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> Guardar
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
@endsection