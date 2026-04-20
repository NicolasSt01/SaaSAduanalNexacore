@extends('layouts.app')

@section('title', 'Registrar Nueva Exportación')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Nueva Exportación</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('operaciones.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <!-- Primera fila -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control @error('fecha') is-invalid @enderror" 
                                           id="fecha" name="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" required>
                                    <label for="fecha">Fecha</label>
                                    @error('fecha')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

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
                                    @error('cliente_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Segunda fila -->
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
                                    @error('importador_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('nombre_producto') is-invalid @enderror" 
                                           id="nombre_producto" name="nombre_producto" value="{{ old('nombre_producto') }}" required>
                                    <label for="nombre_producto">Nombre del Producto</label>
                                    @error('nombre_producto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tercera fila -->
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
                                    @error('bodega_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('num_factura') is-invalid @enderror" 
                                           id="num_factura" name="num_factura" value="{{ old('num_factura') }}" required>
                                    <label for="num_factura">Número de Factura</label>
                                    @error('num_factura')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Cuarta fila -->
                            <div class="col-md-4">
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
                                    @error('aduana_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
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
                                    @error('patente_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select class="form-select @error('pedimento_id') is-invalid @enderror" 
                                            id="pedimento_id" name="pedimento_id" required>
                                        <option value="">Seleccione un expediente</option>
                                        @foreach($expedientes as $expediente)
                                            <option value="{{ $expediente->id }}" {{ old('pedimento_id') == $expediente->id ? 'selected' : '' }}>
                                                {{ $expediente->numero_pedimento }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="pedimento_id">Expediente</label>
                                    @error('pedimento_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Quinta fila -->
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('num_thermo') is-invalid @enderror" 
                                           id="num_thermo" name="num_thermo" value="{{ old('num_thermo') }}">
                                    <label for="num_thermo">Número Thermo</label>
                                    @error('num_thermo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('codigo_alpha') is-invalid @enderror" 
                                           id="codigo_alpha" name="codigo_alpha" value="{{ old('codigo_alpha') }}">
                                    <label for="codigo_alpha">Código Alpha</label>
                                    @error('codigo_alpha')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('num_doda') is-invalid @enderror" 
                                           id="num_doda" name="num_doda" value="{{ old('num_doda') }}">
                                    <label for="num_doda">Número DODA</label>
                                    @error('num_doda')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('modulacion') is-invalid @enderror" 
                                           id="modulacion" name="modulacion" value="{{ old('modulacion') }}">
                                    <label for="modulacion">Modulación</label>
                                    @error('modulacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Sexta fila -->
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <select class="form-select @error('usuario_registro_id') is-invalid @enderror" 
                                            id="usuario_registro_id" name="usuario_registro_id" required>
                                        <option value="">Seleccione un documentador</option>
                                        @foreach($documentadores as $documentador)
                                            <option value="{{ $documentador->id }}" {{ old('usuario_registro_id') == $documentador->id ? 'selected' : '' }}>
                                                {{ $documentador->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="usuario_registro_id">Documentador</label>
                                    @error('usuario_registro_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('operaciones.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Guardar Exportación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection