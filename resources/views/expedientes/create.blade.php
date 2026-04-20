@extends('layouts.app')

@section('title', 'Crear Expediente')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg rounded-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Crear Expediente</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('expedientes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        {{-- Cliente --}}
                        <div class="mb-3">
                            <label for="cliente_id" class="form-label">Cliente</label>
                            <select name="cliente_id" id="cliente_id" class="form-select @error('cliente_id') is-invalid @enderror" required>
                                <option value="">Seleccione un cliente</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                                @endforeach
                            </select>
                            @error('cliente_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Patente --}}
                        <div class="mb-3">
                            <label for="patente_id" class="form-label">Patente</label>
                            <select name="patente_id" id="patente_id" class="form-select @error('patente_id') is-invalid @enderror" required>
                                <option value="">Seleccione una patente</option>
                                @foreach($patentes as $patente)
                                    <option value="{{ $patente->id }}">{{ $patente->numero }}</option>
                                @endforeach
                            </select>
                            @error('patente_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Aduana --}}
                        <div class="mb-3">
                            <label for="aduana_id" class="form-label">Aduana</label>
                            <select name="aduana_id" id="aduana_id" class="form-select @error('aduana_id') is-invalid @enderror" required>
                                <option value="">Seleccione una aduana</option>
                                @foreach($aduanas as $aduana)
                                    <option value="{{ $aduana->id }}">{{ $aduana->nombre }}</option>
                                @endforeach
                            </select>
                            @error('aduana_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row g-4">

                        
                        <!-- Categoría -->
                                <div class="col-md-6">
                                    <label for="categoria" class="form-label">Categoría *</label>
                                    <select class="form-select" id="categoria" name="categoria" required>
                                        <option value="">Seleccionar Categoría</option>
                                        <option value="Importacion" {{ (old('categoria', $expediente->categoria ?? '') == 'Importacion') ? 'selected' : '' }}>Importación</option>
                                        <option value="Exportacion" {{ (old('categoria', $expediente->categoria ?? '') == 'Exportacion') ? 'selected' : '' }}>Exportación</option>
                                        <option value="Rectificaciones" {{ (old('categoria', $expediente->categoria ?? '') == 'Rectificaciones') ? 'selected' : '' }}>Rectificaciones</option>
                                    </select>
                                </div>
                                <!-- Clave Pedimento -->
                                <div class="col-md-6">
                                    <label for="clave_pedimento" class="form-label">Clave Pedimento *</label>
                                    <select class="form-select" id="clave_pedimento" name="clave_pedimento" required>
                                        <option value="">Seleccionar Clave</option>
                                        <option value="H1" {{ (old('clave_pedimento', $expediente->clave_pedimento ?? '') == 'H1') ? 'selected' : '' }}>H1</option>
                                        <option value="A1" {{ (old('clave_pedimento', $expediente->clave_pedimento ?? '') == 'A1') ? 'selected' : '' }}>A1</option>
                                        <option value="RT" {{ (old('clave_pedimento', $expediente->clave_pedimento ?? '') == 'RT') ? 'selected' : '' }}>RT</option>
                                    </select>
                                </div>
                        </div>

                        {{-- Tipo de Expediente --}}
                        <div class="mb-3">
                            <label for="tipo_expediente" class="form-label">Tipo de Expediente</label>
                            <select name="tipo_expediente" id="tipo_expediente" class="form-select" required>
                                <option value="">Seleccione tipo</option>
                                <option value="Unico">Único</option>
                                <option value="Consolidado">Consolidado</option>
                            </select>
                        </div>
                        

                        {{-- Número de Pedimento --}}
                        <div class="mb-3">
                            <label for="numero_pedimento" class="form-label">Número de Pedimento</label>
                            <input type="text" class="form-control @error('numero_pedimento') is-invalid @enderror" id="numero_pedimento" name="numero_pedimento" value="{{ old('numero_pedimento') }}" required>
                            @error('numero_pedimento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Fechas --}}
                        <div class="row" id="fechas-unico" style="display:none;">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_pago_pedimento" class="form-label">Fecha de Pago</label>
                                <input type="date" name="fecha_pago_pedimento" id="fecha_pago_pedimento" class="form-control">
                            </div>
                        </div>

                        <div class="row" id="fechas-consolidado" style="display:none;">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_apertura" class="form-label">Fecha de Apertura</label>
                                <input type="date" name="fecha_apertura" id="fecha_apertura" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_cierre" class="form-label">Fecha de Cierre</label>
                                <input type="date" name="fecha_cierre" id="fecha_cierre" class="form-control">
                            </div>
                        </div>

                        {{-- Observaciones --}}
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" rows="3" class="form-control">{{ old('observaciones') }}</textarea>
                        </div>

                        {{-- Documentos --}}
                        {{--<div class="mb-3">
                            <label for="documentos" class="form-label">Documentos</label>
                            <input type="file" name="documentos[]" multiple class="form-control">
                        </div>--}}

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('expedientes.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Crear Expediente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script para mostrar/ocultar fechas según tipo --}}
<script>
    document.getElementById('tipo_expediente').addEventListener('change', function() {
        document.getElementById('fechas-unico').style.display = (this.value === 'Unico') ? 'flex' : 'none';
        document.getElementById('fechas-consolidado').style.display = (this.value === 'Consolidado') ? 'flex' : 'none';
    });
</script>

@endsection
