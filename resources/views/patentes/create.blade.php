@extends('layouts.app')

@section('title', 'Registrar Nueva Patente')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Nueva Patente Aduanal</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('patentes.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="numero_patente" class="form-label">Número de Patente *</label>
                                <input type="text" class="form-control" id="numero_patente" name="numero_patente" required>
                                <small class="form-text text-muted">Ejemplo: 1234-5678</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="rfc_agente" class="form-label">RFC del Agente</label>
                                <input type="text" class="form-control" id="rfc_agente" name="rfc_agente">
                            </div>
                            
                            <div class="col-12">
                                <label for="nombre_agente_aduanal" class="form-label">Nombre del Agente Aduanal *</label>
                                <input type="text" class="form-control" id="nombre_agente_aduanal" name="nombre_agente_aduanal" required>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> Registrar
                                </button>
                                <a href="{{ route('patentes.index') }}" class="btn btn-secondary">
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