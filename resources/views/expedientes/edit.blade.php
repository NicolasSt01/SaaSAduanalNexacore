@extends('layouts.app')

@section('title', 'Editar Expediente')
@section('customcss')
    <style>
/* Estilos adicionales para mejorar la apariencia */
.form-control, .form-select {
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
}

.card {
    border-radius: 12px;
}

.btn {
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
}

.btn-light:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.border-bottom {
    border-color: #e9ecef !important;
}

.form-text {
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .card-body {
        padding: 1.5rem !important;
    }
}
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header con breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            
            <h1 class="h3 mb-0 text-dark fw-bold">Editar Expediente</h1>
            <p class="text-muted mb-0">Modifica la información del expediente {{ $expediente->numero_pedimento }}</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <!-- Card principal -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('expedientes.update', $expediente) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Información General -->
                        <div class="mb-4">
                            <h5 class="text-dark mb-3 fw-semibold border-bottom pb-2">
                                <i class="fas fa-info-circle text-primary me-2"></i>Información General
                            </h5>
                            
                            <div class="row g-3">
                                <!-- Cliente -->
                                <div class="col-md-6">
                                    <label for="cliente_id" class="form-label text-dark fw-medium">Cliente</label>
                                    <select name="cliente_id" id="cliente_id" class="form-select border-light-subtle" required>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->id }}" {{ $expediente->cliente_id == $cliente->id ? 'selected' : '' }}>
                                                {{ $cliente->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Patente -->
                                <div class="col-md-6">
                                    <label for="patente_id" class="form-label text-dark fw-medium">Patente</label>
                                    <select name="patente_id" id="patente_id" class="form-select border-light-subtle" required>
                                        @foreach($patentes as $patente)
                                            <option value="{{ $patente->id }}" {{ $expediente->patente_id == $patente->id ? 'selected' : '' }}>
                                                {{ $patente->numero }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Aduana -->
                                <div class="col-md-6">
                                    <label for="aduana_id" class="form-label text-dark fw-medium">Aduana</label>
                                    <select name="aduana_id" id="aduana_id" class="form-select border-light-subtle" required>
                                        @foreach($aduanas as $aduana)
                                            <option value="{{ $aduana->id }}" {{ $expediente->aduana_id == $aduana->id ? 'selected' : '' }}>
                                                {{ $aduana->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Tipo de Expediente -->
                                <div class="col-md-6">
                                    <label for="tipo_expediente" class="form-label text-dark fw-medium">Tipo de Expediente</label>
                                    <select name="tipo_expediente" id="tipo_expediente" class="form-select border-light-subtle" required>
                                        <option value="Unico" {{ $expediente->tipo_expediente == 'Unico' ? 'selected' : '' }}>Único</option>
                                        <option value="Consolidado" {{ $expediente->tipo_expediente == 'Consolidado' ? 'selected' : '' }}>Consolidado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas -->
                        <div class="mb-4">
                            <h5 class="text-dark mb-3 fw-semibold border-bottom pb-2">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>Fechas
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="fecha_apertura" class="form-label text-dark fw-medium">Fecha de Apertura</label>
                                    <input type="date" name="fecha_apertura" id="fecha_apertura" 
                                           class="form-control border-light-subtle" 
                                           value="{{ $expediente->fecha_apertura ? $expediente->fecha_apertura->format('Y-m-d') : '' }}">
                                    <div class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>Fecha en que se abrió el expediente
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="fecha_cierre" class="form-label text-dark fw-medium">Fecha de Cierre</label>
                                    <input type="date" name="fecha_cierre" id="fecha_cierre" 
                                           class="form-control border-light-subtle" 
                                           value="{{ $expediente->fecha_cierre ? $expediente->fecha_cierre->format('Y-m-d') : '' }}">
                                    <div class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>Fecha en que se cerró el expediente (opcional)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado y Observaciones -->
                        <div class="mb-4">
                            <h5 class="text-dark mb-3 fw-semibold border-bottom pb-2">
                                <i class="fas fa-cog text-primary me-2"></i>Estado y Observaciones
                            </h5>
                            
                            <div class="row g-3">
                                <!-- Estado -->
                                <div class="col-md-6">
                                    <label for="estado" class="form-label text-dark fw-medium">Estado</label>
                                    <select name="estado" id="estado" class="form-select border-light-subtle">
                                        <option value="En proceso" {{ $expediente->estado == 'En proceso' ? 'selected' : '' }}>
                                            En proceso
                                        </option>
                                        <option value="Abierto" {{ $expediente->estado == 'Abierto' ? 'selected' : '' }}>
                                            Abierto
                                        </option>
                                        <option value="Cerrado" {{ $expediente->estado == 'Cerrado' ? 'selected' : '' }}>
                                            Cerrado
                                        </option>
                                        <option value="Cancelado" {{ $expediente->estado == 'Cancelado' ? 'selected' : '' }}>
                                            Cancelado
                                        </option>
                                    </select>
                                </div>

                                <!-- Observaciones (col completa) -->
                                <div class="col-12">
                                    <label for="observaciones" class="form-label text-dark fw-medium">Observaciones</label>
                                    <textarea name="observaciones" id="observaciones" rows="4" 
                                              class="form-control border-light-subtle" 
                                              placeholder="Ingresa observaciones adicionales sobre el expediente...">{{ $expediente->observaciones }}</textarea>
                                    <div class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>Información adicional relevante para este expediente
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end pt-3 border-top">
                            <a href="{{ route('expedientes.show', $expediente) }}" 
                               class="btn btn-light border text-dark d-flex align-items-center justify-content-center">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-success d-flex align-items-center justify-content-center">
                                <i class="fas fa-save me-2"></i>
                                Actualizar Expediente
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card con información adicional -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center text-muted">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>
                        <small>
                            <strong>Consejo:</strong> Asegúrate de que toda la información sea correcta antes de actualizar el expediente. 
                            Los cambios se reflejarán inmediatamente en el sistema.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection