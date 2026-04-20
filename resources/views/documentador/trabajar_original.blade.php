@extends('layouts.app')

@section('title', 'Trabajar en Exportación')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Información de la Exportación -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Trabajando en Exportación</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Información del Cliente -->
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-building me-2"></i>Información del Cliente
                                    </h6>
                                    <p class="mb-1"><strong>Empresa:</strong> {{ $operacion->cliente->nombre_empresa }}</p>
                                    <p class="mb-0"><strong>Contacto:</strong> {{ $operacion->cliente->contacto_principal ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Producto -->
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-box me-2"></i>Información del Producto
                                    </h6>
                                    <p class="mb-1"><strong>Producto:</strong> {{ $operacion->nombre_producto }}</p>
                                    <p class="mb-0"><strong>Factura:</strong> {{ $operacion->num_factura }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Logística -->
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-truck me-2"></i>Información Logística
                                    </h6>
                                    <p class="mb-1"><strong>Bodega:</strong> {{ $operacion->bodega->nombre_bodega }}</p>
                                    <p class="mb-1"><strong>Aduana:</strong> {{ $operacion->aduana->nombre_aduana }}</p>
                                    <p class="mb-0"><strong>Patente:</strong> {{ $operacion->patente->numero_patente }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Seguimiento -->
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-info-circle me-2"></i>Información de Seguimiento
                                    </h6>
                                    <p class="mb-1"><strong>Thermo:</strong> {{ $operacion->num_thermo ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>Alpha:</strong> {{ $operacion->codigo_alpha ?? 'N/A' }}</p>
                                    <p class="mb-0"><strong>Estado:</strong> 
                                        <span class="badge bg-{{ $operacion->estado == 'proceso' ? 'warning' : 'info' }}">
                                            {{ ucfirst($operacion->estado) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Prioridad -->
                        <div class="col-12 mb-3">
                            <div class="card 
                                @if($operacion->prioridad == 'urgente') border-danger
                                @elseif($operacion->prioridad == 'media') border-warning
                                @else border-primary @endif">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-exclamation-circle me-2"></i>Prioridad
                                    </h6>
                                    <span class="badge 
                                        @if($operacion->prioridad == 'urgente') bg-danger
                                        @elseif($operacion->prioridad == 'media') bg-warning
                                        @else bg-primary @endif fs-6">
                                        Prioridad: {{ ucfirst($operacion->prioridad) }}
                                    </span>
                                    <p class="mt-2 mb-0 small">
                                        <strong>Fecha de creación:</strong> 
                                        {{ $operacion->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario para Completar -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Completar Exportación</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('documentador.completar', $operacion->id) }}" method="POST">
                        @csrf

                        <!-- Expediente -->
                        <div class="mb-3">
                            <label for="pedimento_id" class="form-label">Seleccionar Expediente/Pedimento *</label>
                            <select class="form-select" id="pedimento_id" name="pedimento_id" required>
                                <option value="">-- Seleccione un expediente --</option>
                                @foreach($expedientes as $expediente)
                                    <option value="{{ $expediente->id }}" 
                                        {{ old('pedimento_id') == $expediente->id ? 'selected' : '' }}>
                                        {{ $expediente->numero_pedimento }} - {{ $expediente->tipo_expediente }} - {{ $expediente->categoria }} 
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Número DODA -->
                        <div class="mb-3">
                            <label for="num_doda" class="form-label">Número DODA *</label>
                            <input type="text" class="form-control" id="num_doda" name="num_doda" 
                                value="{{ old('num_doda') }}" required placeholder="Ingrese el número DODA">
                        </div>

                        <!-- Comentarios -->
                        <div class="mb-3">
                            <label for="comentarios" class="form-label">Comentarios (Opcional)</label>
                            <textarea class="form-control" id="comentarios" name="comentarios" 
                                rows="3" placeholder="Observaciones o comentarios adicionales">{{ old('comentarios') }}</textarea>
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle me-2"></i>Marcar como Completado
                            </button>
                            <a href="{{ route('documentador.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información de Tiempo 
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Tiempo de Trabajo</h6>
                </div>
                <div class="card-body text-center">
                    <h4 id="tiempoTranscurrido">00:00:00</h4>
                    <small class="text-muted">Tiempo transcurrido en esta operación</small>
                </div>
            </div>-->
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Contador de tiempo
        let startTime = new Date().getTime();
        
        function updateTimer() {
            let currentTime = new Date().getTime();
            let elapsedTime = currentTime - startTime;
            
            let hours = Math.floor(elapsedTime / (1000 * 60 * 60));
            let minutes = Math.floor((elapsedTime % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((elapsedTime % (1000 * 60)) / 1000);
            
            document.getElementById('tiempoTranscurrido').textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        setInterval(updateTimer, 1000);
        
        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const expediente = document.getElementById('pedimento_id').value;
            const doda = document.getElementById('num_doda').value;
            
            if (!expediente || !doda) {
                e.preventDefault();
                alert('Por favor complete todos los campos obligatorios');
            }
        });
    });
</script>
@endsection