@extends('layouts.app')

@section('title', 'Registrar Nueva Exportación')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h5 class="mb-0">Nueva Exportación</h5>
                    <a href="#" class="btn btn-light btn-sm">
                        <i class="fas fa-file-upload me-1"></i> Cargar por XML
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('operaciones.storetrafico') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">

                            <!-- Fecha -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control @error('fecha') is-invalid @enderror"
                                        id="fecha" name="fecha"
                                        value="{{ old('fecha', now()->format('Y-m-d')) }}" required>
                                    <label for="fecha">Fecha</label>
                                    @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Cliente -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select @error('cliente_id') is-invalid @enderror"
                                        id="cliente_id" name="cliente_id" required>
                                        <option value="">Seleccione un cliente</option>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                                {{ $cliente->nombre_empresa }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="cliente_id">Cliente</label>
                                    @error('cliente_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Importador -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select @error('importador_id') is-invalid @enderror"
                                        id="importador_id" name="importador_id" required>
                                        <option value="">Seleccione un importador</option>
                                        @foreach($importadores as $importador)
                                            <option value="{{ $importador->id }}" {{ old('importador_id') == $importador->id ? 'selected' : '' }}>
                                                {{ $importador->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="importador_id">Importador</label>
                                    @error('importador_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Producto -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('nombre_producto') is-invalid @enderror"
                                        id="nombre_producto" name="nombre_producto"
                                        value="{{ old('nombre_producto') }}" required>

                                    <label for="nombre_producto">Producto</label>
                                    @error('nombre_producto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Bodega -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select @error('bodega_id') is-invalid @enderror"
                                        id="bodega_id" name="bodega_id" required>
                                        <option value="">Seleccione una bodega</option>
                                        @foreach($bodegas as $bodega)
                                            <option value="{{ $bodega->id }}" {{ old('bodega_id') == $bodega->id ? 'selected' : '' }}>
                                                {{ $bodega->nombre_bodega }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="bodega_id">Bodega</label>
                                    @error('bodega_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Factura -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('num_factura') is-invalid @enderror"
                                        id="num_factura" name="num_factura"
                                        value="{{ old('num_factura') }}" required>
                                    <label for="num_factura">Número de Factura</label>
                                    @error('num_factura') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Aduana -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select @error('aduana_id') is-invalid @enderror"
                                        id="aduana_id" name="aduana_id" required>
                                        <option value="">Seleccione una aduana</option>
                                        @foreach($aduanas as $aduana)
                                            <option value="{{ $aduana->id }}" {{ old('aduana_id') == $aduana->id ? 'selected' : '' }}>
                                                {{ $aduana->nombre_aduana }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="aduana_id">Aduana</label>
                                    @error('aduana_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Patente -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select @error('patente_id') is-invalid @enderror"
                                        id="patente_id" name="patente_id" required>
                                        <option value="">Seleccione una patente</option>
                                        @foreach($patentes as $patente)
                                            <option value="{{ $patente->id }}" {{ old('patente_id') == $patente->id ? 'selected' : '' }}>
                                                {{ $patente->numero_patente }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="patente_id">Patente</label>
                                    @error('patente_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Thermo -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('num_thermo') is-invalid @enderror"
                                        id="num_thermo" name="num_thermo"
                                        value="{{ old('num_thermo') }}">
                                    <label for="num_thermo">Número Thermo</label>
                                    @error('num_thermo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Código Alpha -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('codigo_alpha') is-invalid @enderror"
                                        id="codigo_alpha" name="codigo_alpha"
                                        value="{{ old('codigo_alpha') }}">
                                    <label for="codigo_alpha">Código Alpha</label>
                                    @error('codigo_alpha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Documentador (oculto, siempre usuario actual) -->
                            <input type="hidden" name="usuario_registro_id" value="{{ auth()->id() }}">
                            <!-- Campos que el usuario de trafico no captura-->
                             <input type="hidden" name="pedimento_id" value="">
                             <input type="hidden" name="num_doda" value="">
                             <input type="hidden" name="modulacion" value="">
                            

                        </div>

                        <!-- Documentos relacionados -->
                        <div class="card mt-4 mb-4">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file-alt me-2 text-primary"></i> Documentos de la Exportación
                                </h5>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#agregarDocumentoModal">
                                    <i class="fas fa-plus me-1"></i> Agregar
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="text-center py-5">
                                    <i class="fas fa-folder-open text-muted" style="font-size: 3rem;"></i>
                                    <p class="mt-3 text-muted">Aún no se han agregado documentos a esta exportación.</p>
                                </div>
                            </div>
                        </div>


                        <!-- Footer -->
                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('trafico.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal para agregar documento -->
<div class="modal fade" id="agregarDocumentoModal" tabindex="-1" aria-labelledby="agregarDocumentoModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agregarDocumentoModalLabel">Agregar Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('documentos.store', $expediente) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="archivo" class="form-label">Archivo PDF *</label>
                        <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf" required
                            onchange="document.getElementById('nombre_documento').value = this.files[0]?.name.split('.').slice(0, -1).join('.')">
                        <div class="form-text">
                            Tamaño máximo: 20MB. El nombre del archivo se usará como nombre del documento.
                        </div>
                    </div>

                    <!-- Nombre del documento oculto -->
                    <input type="hidden" id="nombre_documento" name="nombre_documento">

                    <div class="mb-3">
                        <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                        <input type="text" class="form-control" id="tipo_documento" name="tipo_documento"
                               placeholder="ej. pedimento, factura" oninput="this.value = this.value.toLowerCase()">
                    </div>

                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Documento</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
