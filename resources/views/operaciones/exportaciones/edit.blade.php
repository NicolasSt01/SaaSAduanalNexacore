@extends('layouts.app') 

@section('title', 'Editar Exportación')

@section('customcss')
<style>
/* Estilo base reutilizado con color verde */
.form-control, .form-select {
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.1);
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
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">Editar Exportación</h1>
            <p class="text-muted mb-0">Modifica la información de la exportación #{{ $operacion->id }}</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <!-- Card principal -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('operaciones.update', $operacion) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Información General -->
                        <div class="mb-4">
                            <h5 class="text-dark mb-3 fw-semibold border-bottom pb-2">
                                <i class="fas fa-info-circle text-success me-2"></i>Información General
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="fecha" class="form-label text-dark fw-medium">Fecha</label>
                                    <input type="date" id="fecha" name="fecha" class="form-control border-light-subtle"
                                        value="{{ old('fecha', $operacion->fecha->format('Y-m-d')) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="cliente_id" class="form-label text-dark fw-medium">Cliente</label>
                                    <select id="cliente_id" name="cliente_id" class="form-select border-light-subtle" required>
                                        <option value="">Seleccione un cliente</option>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->id }}" {{ old('cliente_id', $operacion->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                                {{ $cliente->nombre_empresa }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="importador_id" class="form-label text-dark fw-medium">Importador</label>
                                    <select id="importador_id" name="importador_id" class="form-select border-light-subtle" required>
                                        <option value="">Seleccione un importador</option>
                                        @foreach($importadores as $importador)
                                            <option value="{{ $importador->id }}" {{ old('importador_id', $operacion->importador_id) == $importador->id ? 'selected' : '' }}>
                                                {{ $importador->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles del Producto -->
                        <div class="mb-4">
                            <h5 class="text-dark mb-3 fw-semibold border-bottom pb-2">
                                <i class="fas fa-box text-success me-2"></i>Detalles del Producto
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nombre_producto" class="form-label text-dark fw-medium">Nombre del Producto</label>
                                    <input type="text" id="nombre_producto" name="nombre_producto"
                                           class="form-control border-light-subtle"
                                           value="{{ old('nombre_producto', $operacion->nombre_producto) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="num_factura" class="form-label text-dark fw-medium">Número de Factura</label>
                                    <input type="text" id="num_factura" name="num_factura"
                                           class="form-control border-light-subtle"
                                           value="{{ old('num_factura', $operacion->num_factura) }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Aduana, Patente y Expediente -->
                        <div class="mb-4">
                            <h5 class="text-dark mb-3 fw-semibold border-bottom pb-2">
                                <i class="fas fa-warehouse text-success me-2"></i>Aduana, Patente y Expediente
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="aduana_id" class="form-label text-dark fw-medium">Aduana</label>
                                    <select id="aduana_id" name="aduana_id" class="form-select border-light-subtle" required>
                                        <option value="">Seleccione una aduana</option>
                                        @foreach($aduanas as $aduana)
                                            <option value="{{ $aduana->id }}" {{ old('aduana_id', $operacion->aduana_id) == $aduana->id ? 'selected' : '' }}>
                                                {{ $aduana->nombre_aduana }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="patente_id" class="form-label text-dark fw-medium">Patente</label>
                                    <select id="patente_id" name="patente_id" class="form-select border-light-subtle" >
                                        <option value="">Seleccione una patente</option>
                                        @foreach($patentes as $patente)
                                            <option value="{{ $patente->id }}" {{ old('patente_id', $operacion->patente_id) == $patente->id ? 'selected' : '' }}>
                                                {{ $patente->numero_patente }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="pedimento_id" class="form-label text-dark fw-medium">Expediente</label>
                                    <select id="pedimento_id" name="pedimento_id" class="form-select border-light-subtle" >
                                        <option value="">Seleccione un expediente</option>
                                        @foreach($expedientes as $expediente)
                                            <option value="{{ $expediente->id }}" {{ old('pedimento_id', $operacion->pedimento_id) == $expediente->id ? 'selected' : '' }}>
                                                {{ $expediente->numero_pedimento }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Códigos e Identificadores -->
                        <div class="mb-4">
                            <h5 class="text-dark mb-3 fw-semibold border-bottom pb-2">
                                <i class="fas fa-barcode text-success me-2"></i>Códigos e Identificadores
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="num_thermo" class="form-label text-dark fw-medium">Número Thermo</label>
                                    <input type="text" id="num_thermo" name="num_thermo" 
                                           class="form-control border-light-subtle"
                                           value="{{ old('num_thermo', $operacion->num_thermo) }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="codigo_alpha" class="form-label text-dark fw-medium">Código Alpha</label>
                                    <input type="text" id="codigo_alpha" name="codigo_alpha"
                                           class="form-control border-light-subtle"
                                           value="{{ old('codigo_alpha', $operacion->codigo_alpha) }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="num_doda" class="form-label text-dark fw-medium">Número DODA</label>
                                    <input type="text" id="num_doda" name="num_doda"
                                           class="form-control border-light-subtle"
                                           value="{{ old('num_doda', $operacion->num_doda) }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="modulacion" class="form-label text-dark fw-medium">Modulación</label>
                                    <input type="text" id="modulacion" name="modulacion"
                                           class="form-control border-light-subtle"
                                           value="{{ old('modulacion', $operacion->modulacion) }}">
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end pt-3 border-top">
                            <a href="{{ route('operaciones.show', $operacion) }}" 
                               class="btn btn-light border text-dark d-flex align-items-center justify-content-center">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success d-flex align-items-center justify-content-center">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Consejo -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center text-muted">
                        <i class="fas fa-lightbulb me-2 text-success"></i>
                        <small>
                            <strong>Consejo:</strong> Verifica los datos de cliente, producto y aduana antes de guardar los cambios. 
                            La información se actualizará inmediatamente en el sistema.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
