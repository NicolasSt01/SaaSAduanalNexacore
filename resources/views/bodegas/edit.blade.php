@extends('layouts.app')

@section('title', 'Editar Bodega')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Editar Bodega</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('bodegas.update', $bodega) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nombre_bodega" class="form-label">Nombre de la Bodega</label>
                            <input type="text" class="form-control @error('nombre_bodega') is-invalid @enderror" 
                                   id="nombre_bodega" name="nombre_bodega" 
                                   value="{{ old('nombre_bodega', $bodega->nombre_bodega) }}" required>
                            @error('nombre_bodega')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="domicilio" class="form-label">Domicilio</label>
                            <textarea class="form-control @error('domicilio') is-invalid @enderror" 
                                      id="domicilio" name="domicilio" 
                                      rows="3" required>{{ old('domicilio', $bodega->domicilio) }}</textarea>
                            @error('domicilio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tax_id" class="form-label">Tax ID</label>
                            <input type="text" class="form-control @error('tax_id') is-invalid @enderror" 
                                   id="tax_id" name="tax_id" 
                                   value="{{ old('tax_id', $bodega->tax_id) }}" required>
                            @error('tax_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Equivalente al RFC en México</small>
                        </div>

                        <div class="mb-3">
                            <label for="contacto" class="form-label">Número de Contacto</label>
                            <input type="text" class="form-control @error('contacto') is-invalid @enderror" 
                                   id="contacto" name="contacto" 
                                   value="{{ old('contacto', $bodega->contacto) }}" required>
                            @error('contacto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('bodegas.show', $bodega) }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection