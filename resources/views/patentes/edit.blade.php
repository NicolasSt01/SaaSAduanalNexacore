@extends('layouts.app')

@section('title', 'Editar Patente')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Editar Patente: {{ $patente->numero_patente }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('patentes.update', $patente) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Campo Número de Patente (readonly) -->
                        <div class="mb-3">
                            <label for="numero_patente" class="form-label">Número de Patente</label>
                            <input type="text" class="form-control" id="numero_patente" 
                                   value="{{ $patente->numero_patente }}" readonly>
                            <input type="hidden" name="numero_patente" value="{{ $patente->numero_patente }}">
                        </div>
                        
                        <!-- Campo Nombre Agente Aduanal (requerido) -->
                        <div class="mb-3">
                            <label for="nombre_agente_aduanal" class="form-label">Nombre del Agente Aduanal *</label>
                            <input type="text" class="form-control" id="nombre_agente_aduanal" 
                                   name="nombre_agente_aduanal" 
                                   value="{{ old('nombre_agente_aduanal', $patente->nombre_agente_aduanal) }}" required>
                        </div>
                        
                        <!-- Campo RFC (opcional) -->
                        <div class="mb-3">
                            <label for="rfc_agente" class="form-label">RFC del Agente</label>
                            <input type="text" class="form-control" id="rfc_agente" 
                                   name="rfc_agente" 
                                   value="{{ old('rfc_agente', $patente->rfc_agente) }}">
                        </div>
                        
                        <!-- Sección para asignar aduanas -->
                        <div class="mb-3">
                            <label class="form-label">Aduanas asignadas</label>
                            <div class="border p-3">
                                @foreach($aduanas as $aduana)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="aduanas[]" 
                                           value="{{ $aduana->id }}"
                                           id="aduana_{{ $aduana->id }}"
                                           {{ in_array($aduana->id, $aduanaSeleccionadas) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="aduana_{{ $aduana->id }}">
                                        {{ $aduana->clave_aduana }} - {{ $aduana->nombre_aduana }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="{{ route('patentes.index') }}" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection