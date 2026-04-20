@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg rounded-3">
                <div class="card-header text-center bg-primary text-white">
                    <h4>Editar Cliente</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nombre_empresa" class="form-label">Nombre de la Empresa</label>
                            <input type="text" 
                                   class="form-control @error('nombre_empresa') is-invalid @enderror" 
                                   id="nombre_empresa" 
                                   name="nombre_empresa" 
                                   value="{{ old('nombre_empresa', $cliente->nombre_empresa) }}" 
                                   required>
                            @error('nombre_empresa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="rfc" class="form-label">RFC</label>
                            <input type="text" 
                                   class="form-control @error('rfc') is-invalid @enderror" 
                                   id="rfc" 
                                   name="rfc" 
                                   value="{{ old('rfc', $cliente->rfc) }}">
                            @error('rfc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tax_id" class="form-label">Tax ID</label>
                            <input type="text" 
                                   class="form-control @error('tax_id') is-invalid @enderror" 
                                   id="tax_id" 
                                   name="tax_id" 
                                   value="{{ old('tax_id', $cliente->tax_id) }}">
                            @error('tax_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="correo_contacto_principal" class="form-label">Correo de Contacto</label>
                            <input type="email" 
                                   class="form-control @error('correo_contacto_principal') is-invalid @enderror" 
                                   id="correo_contacto_principal" 
                                   name="correo_contacto_principal" 
                                   value="{{ old('correo_contacto_principal', $cliente->correo_contacto_principal) }}" 
                                   required>
                            @error('correo_contacto_principal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="telefono_contacto" class="form-label">Teléfono</label>
                            <input type="text" 
                                   class="form-control @error('telefono_contacto') is-invalid @enderror" 
                                   id="telefono_contacto" 
                                   name="telefono_contacto" 
                                   value="{{ old('telefono_contacto', $cliente->telefono_contacto) }}">
                            @error('telefono_contacto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="direccion_fiscal" class="form-label">Dirección Fiscal</label>
                            <input type="text" 
                                   class="form-control @error('direccion_fiscal') is-invalid @enderror" 
                                   id="direccion_fiscal" 
                                   name="direccion_fiscal" 
                                   value="{{ old('direccion_fiscal', $cliente->direccion_fiscal) }}">
                            @error('direccion_fiscal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="persona_contacto" class="form-label">Persona de Contacto</label>
                            <input type="text" 
                                   class="form-control @error('persona_contacto') is-invalid @enderror" 
                                   id="persona_contacto" 
                                   name="persona_contacto" 
                                   value="{{ old('persona_contacto', $cliente->persona_contacto) }}">
                            @error('persona_contacto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-secondary w-45">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-success w-45">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
