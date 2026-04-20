@extends('layouts.app')

@section('title', 'Registrar Nuevo Cliente')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Nuevo Cliente</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('clientes.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="nombre_empresa" class="form-label">Nombre de la Empresa *</label>
                                <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="rfc" class="form-label">RFC *</label>
                                <input type="text" class="form-control" id="rfc" name="rfc" >
                                <small class="form-text text-muted">Formato: ABC123456XYZ</small>
                            </div>

                            <div class="col-md-6">
                                <label for="tax_id" class="form-label">Tax ID</label>
                                <input type="text" class="form-control" id="tax_id" name="tax_id" >
                                <small class="form-text text-muted">Formato: ABC123456XYZ</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="correo_contacto_principal" class="form-label">Correo Electrónico *</label>
                                <input type="email" class="form-control" id="correo_contacto_principal" name="correo_contacto_principal" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="telefono_contacto" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono_contacto" name="telefono_contacto">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="persona_contacto" class="form-label">Persona de Contacto</label>
                                <input type="text" class="form-control" id="persona_contacto" name="persona_contacto">
                            </div>
                            
                            <div class="col-12">
                                <label for="direccion_fiscal" class="form-label">Dirección Fiscal</label>
                                <input type="text" class="form-control" id="direccion_fiscal" name="direccion_fiscal">
                            </div>
                            
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
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