@extends('layouts.app')

@section('customcss')
    <style>
/* Estilos para el toggle switch */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #28a745;
}

input:checked + .slider:before {
    transform: translateX(26px);
}
</style>

@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-plus-circle"></i> Nueva Exportación
                    </h4>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h5 class="alert-heading">
                                <i class="fas fa-exclamation-triangle"></i> Por favor corrige los siguientes errores:
                            </h5>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('operaciones.admin.store') }}" method="POST" id="formOperacion">
                        @csrf

                        {{-- Información General --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-info-circle text-primary"></i> Información General
                                </h5>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('fecha') is-invalid @enderror" 
                                       id="fecha" 
                                       name="fecha" 
                                       value="{{ old('fecha', date('Y-m-d')) }}" 
                                       required>
                                @error('fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="referencia" class="form-label"># Referencia</label>
                                <input type="text" 
                                       class="form-control @error('referencia') is-invalid @enderror" 
                                       id="referencia" 
                                       name="referencia" 
                                       value="{{ old('referencia') }}" 
                                       placeholder="Ej: REF-2024-001">
                                @error('referencia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="cliente_id" class="form-label">Cliente <span class="text-danger">*</span></label>
                                <select class="form-select @error('cliente_id') is-invalid @enderror" 
                                        id="cliente_id" 
                                        name="cliente_id" 
                                        required>
                                    <option value="">Seleccione un cliente</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->nombre_empresa }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cliente_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="importador_id" class="form-label">Importador <span class="text-danger">*</span></label>
                                <select class="form-select @error('importador_id') is-invalid @enderror" 
                                        id="importador_id" 
                                        name="importador_id" 
                                        required>
                                    <option value="">Seleccione un importador</option>
                                    @foreach($importadores as $importador)
                                        <option value="{{ $importador->id }}" {{ old('importador_id') == $importador->id ? 'selected' : '' }}>
                                            {{ $importador->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('importador_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Producto y Ubicación --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-box text-success"></i> Producto y Ubicación
                                </h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="nombre_producto" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nombre_producto') is-invalid @enderror" 
                                       id="nombre_producto" 
                                       name="nombre_producto" 
                                       value="{{ old('nombre_producto') }}" 
                                       placeholder="Ej: Aguacate Hass"
                                       required>
                                @error('nombre_producto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="bodega_id" class="form-label">Bodega <span class="text-danger">*</span></label>
                                <select class="form-select @error('bodega_id') is-invalid @enderror" 
                                        id="bodega_id" 
                                        name="bodega_id" 
                                        required>
                                    <option value="">Seleccione una bodega</option>
                                    @foreach($bodegas as $bodega)
                                        <option value="{{ $bodega->id }}" {{ old('bodega_id') == $bodega->id ? 'selected' : '' }}>
                                            {{ $bodega->nombre_bodega }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bodega_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Documentación Aduanal --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-file-alt text-warning"></i> Documentación Aduanal
                                </h5>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="num_factura" class="form-label">Número de Factura</label>
                                <input type="text" 
                                       class="form-control @error('num_factura') is-invalid @enderror" 
                                       id="num_factura" 
                                       name="num_factura" 
                                       value="{{ old('num_factura') }}" 
                                       placeholder="Ej: FAC-2024-001">
                                @error('num_factura')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="aduana_id" class="form-label">Aduana <span class="text-danger">*</span></label>
                                <select class="form-select @error('aduana_id') is-invalid @enderror" 
                                        id="aduana_id" 
                                        name="aduana_id" 
                                        required>
                                    <option value="">Seleccione una aduana</option>
                                    @foreach($aduanas as $aduana)
                                        <option value="{{ $aduana->id }}" {{ old('aduana_id') == $aduana->id ? 'selected' : '' }}>
                                            {{ $aduana->nombre_aduana }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('aduana_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="patente_id" class="form-label">Patente</label>
                                <select class="form-select @error('patente_id') is-invalid @enderror" 
                                        id="patente_id" 
                                        name="patente_id">
                                    <option value="">Seleccione una patente</option>
                                    @foreach($patentes as $patente)
                                        <option value="{{ $patente->id }}" {{ old('patente_id') == $patente->id ? 'selected' : '' }}>
                                            {{ $patente->numero_patente }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('patente_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="pedimento_id" class="form-label">Expediente <span class="text-danger">*</span></label>
                                <select class="form-select @error('pedimento_id') is-invalid @enderror" 
                                        id="pedimento_id" 
                                        name="pedimento_id" 
                                        required>
                                    <option value="">Seleccione un expediente</option>
                                    @foreach($expedientes as $expediente)
                                        <option value="{{ $expediente->id }}" {{ old('pedimento_id') == $expediente->id ? 'selected' : '' }}>
                                            {{ $expediente->numero_pedimento }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pedimento_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="num_thermo" class="form-label">Número de Thermo</label>
                                <input type="text" 
                                       class="form-control @error('num_thermo') is-invalid @enderror" 
                                       id="num_thermo" 
                                       name="num_thermo" 
                                       value="{{ old('num_thermo') }}" 
                                       placeholder="Ej: TH-123456">
                                @error('num_thermo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="codigo_alpha" class="form-label">Código Alpha</label>
                                <input type="text" 
                                       class="form-control @error('codigo_alpha') is-invalid @enderror" 
                                       id="codigo_alpha" 
                                       name="codigo_alpha" 
                                       value="{{ old('codigo_alpha') }}" 
                                       placeholder="Ej: ABCD1234">
                                @error('codigo_alpha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="num_doda" class="form-label">Número de DODA</label>
                                <input type="text" 
                                       class="form-control @error('num_doda') is-invalid @enderror" 
                                       id="num_doda" 
                                       name="num_doda" 
                                       value="{{ old('num_doda') }}" 
                                       placeholder="Ej: DODA-2024-001">
                                @error('num_doda')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="modulacion" class="form-label">Modulación <span class="text-danger">*</span></label>
                                <select class="form-select @error('modulacion') is-invalid @enderror" 
                                        id="modulacion" 
                                        name="modulacion" 
                                        required>
                                    <option value="">Seleccione una opción</option>
                                    <option value="Desaduanamiento Libre" {{ old('modulacion') == 'Desaduanamiento Libre' ? 'selected' : '' }}>
                                        Desaduanamiento Libre
                                    </option>
                                    <option value="Reconocimiento Aduanero Concluido" {{ old('modulacion') == 'Reconocimiento Aduanero Concluido' ? 'selected' : '' }}>
                                        Reconocimiento Aduanero Concluido
                                    </option>
                                </select>
                                @error('modulacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Asignación y Control --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-users text-info"></i> Asignación y Control
                                </h5>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="usuario_registro_id" class="form-label">Documentador (Quien Registra) <span class="text-danger">*</span></label>
                                <select class="form-select @error('usuario_registro_id') is-invalid @enderror" 
                                        id="usuario_registro_id" 
                                        name="usuario_registro_id" 
                                        required>
                                    <option value="">Seleccione un documentador</option>
                                    @foreach($usuarios as $usuario)
                                        <option value="{{ $usuario->id }}" {{ old('usuario_registro_id', auth()->id()) == $usuario->id ? 'selected' : '' }}>
                                            {{ $usuario->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('usuario_registro_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="usuario_cierre_id" class="form-label">Asignado A <span class="text-danger">*</span></label>
                                <select class="form-select @error('usuario_cierre_id') is-invalid @enderror" 
                                        id="usuario_cierre_id" 
                                        name="usuario_cierre_id" 
                                        required>
                                    <option value="">Seleccione un usuario</option>
                                    @foreach($usuarios as $usuario)
                                        <option value="{{ $usuario->id }}" {{ old('usuario_cierre_id') == $usuario->id ? 'selected' : '' }}>
                                            {{ $usuario->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('usuario_cierre_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="prioridad" class="form-label">Prioridad</label>
                                <select class="form-select @error('prioridad') is-invalid @enderror" 
                                        id="prioridad" 
                                        name="prioridad">
                                    <option value="">Sin prioridad</option>
                                    <option value="Baja" {{ old('prioridad') == 'Baja' ? 'selected' : '' }}>Baja</option>
                                    <option value="Media" {{ old('prioridad') == 'Media' ? 'selected' : '' }}>Media</option>
                                    <option value="Alta" {{ old('prioridad') == 'Alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="Urgente" {{ old('prioridad') == 'Urgente' ? 'selected' : '' }}>Urgente</option>
                                </select>
                                @error('prioridad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select @error('estado') is-invalid @enderror" 
                                        id="estado" 
                                        name="estado">
                                    <option value="pendiente" {{ old('estado', 'Pendiente') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="proceso" {{ old('estado') == 'proceso' ? 'selected' : '' }}>En Proceso</option>
                                    <option value="terminado" {{ old('estado') == 'terminado' ? 'selected' : '' }}>Completado</option>
                                    <option value="Cancelado" {{ old('estado') == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Permiso de Sobrepeso</label>
                                <div class="d-flex align-items-center">
                                    <label class="switch me-2">
                                        <input type="checkbox" id="sobrepesoToggle" name="sobrepeso" 
                                            value="1" {{ old('sobrepeso') ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                    <span id="sobrepesoStatus">{{ old('sobrepeso') ? 'Activado' : 'Desactivado' }}</span>
                                </div>
                                <small class="form-text text-muted">Activar si el trámite aplica para sobrepeso</small>
                                @error('sobrepeso')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Observaciones --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-comment text-secondary"></i> Observaciones
                                </h5>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                          id="observaciones" 
                                          name="observaciones" 
                                          rows="4" 
                                          placeholder="Ingrese cualquier observación relevante...">{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('operaciones.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Exportación
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sobrepeso status text
    const sobrepesoToggle = document.getElementById('sobrepesoToggle');
    const sobrepesoStatus = document.getElementById('sobrepesoStatus');
    
    if (sobrepesoToggle && sobrepesoStatus) {
        sobrepesoToggle.addEventListener('change', function() {
            sobrepesoStatus.textContent = this.checked ? 'Activado' : 'Desactivado';
        });
    }
    
    // Validación adicional del formulario
    const form = document.getElementById('formOperacion');
    
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Por favor complete todos los campos obligatorios marcados con *');
        }
    });
});
</script>
@endsection