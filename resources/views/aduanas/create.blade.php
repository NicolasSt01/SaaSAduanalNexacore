@extends('layouts.app')

@section('title', 'Registrar Nueva Aduana')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Nueva Aduana</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('aduanas.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="clave_aduana" class="form-label">Clave de Aduana *</label>
                                <input type="text" class="form-control" id="clave_aduana" name="clave_aduana" required>
                                <small class="form-text text-muted">Ejemplo: 640 para Veracruz</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="nombre_aduana" class="form-label">Nombre de Aduana *</label>
                                <input type="text" class="form-control" id="nombre_aduana" name="nombre_aduana" required>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> Registrar
                                </button>
                                <a href="{{ route('aduanas.index') }}" class="btn btn-secondary">
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