@extends('layouts.app')

@section('title', 'Registrar Nueva Exportación')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Nueva Exportación - Datos desde XML</h5>
                        <small class="text-white-50">Los campos marcados con * se llenaron automáticamente desde el
                            XML</small>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('exportaciones.store') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <!-- Primera fila -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" class="form-control @error('fecha') is-invalid @enderror"
                                            id="fecha" name="fecha"
                                            value="{{ old('fecha', $exportacionData['fecha'] ?? now()->format('Y-m-d')) }}"
                                            required>
                                        <label for="fecha">Fecha *</label>
                                        @error('fecha')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                

                                <!-- Segunda fila -->
                                

                               

                                <!-- Tercera fila -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select @error('bodega_id') is-invalid @enderror" id="bodega_id"
                                            name="bodega_id" required>
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
                                            id="num_factura" name="num_factura"
                                            value="{{ old('num_factura', $exportacionData['num_factura'] ?? '') }}" required
                                            placeholder="Número de Factura">
                                        <label for="num_factura">Número de Factura *</label>
                                        @error('num_factura')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        @if(!empty($exportacionData['uuid']))
                                            <small class="text-muted">UUID: {{ $exportacionData['uuid'] }}</small>
                                        @endif
                                    </div>
                                </div>

                                <!-- Cliente (Emisor) -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select @error('cliente_id') is-invalid @enderror"
                                            id="cliente_id" name="cliente_id" required>
                                            <option value="">Seleccione un cliente</option>
                                            @foreach($clientes as $cliente)
                                                                                <option value="{{ $cliente->id }}" {{ (old('cliente_id') == $cliente->id ||
                                                (isset($clienteMatch) && $clienteMatch->id == $cliente->id))
                                                ? 'selected' : '' }}>
                                                                                    {{ $cliente->nombre_empresa }}
                                                                                    @if(isset($clienteMatch) && $clienteMatch->id == $cliente->id)
                                                                                        (Coincidencia por RFC: {{ $exportacionData['emisor_rfc'] }})
                                                                                    @endif
                                                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="cliente_id">Cliente *</label>
                                        @error('cliente_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-info">
                                            Emisor en XML: {{ $exportacionData['emisor_nombre'] }} (RFC:
                                            {{ $exportacionData['emisor_rfc'] }})
                                            @if(!empty($exportacionData['domicilio_emisor']))
                                                <br>Domicilio: {{ $exportacionData['domicilio_emisor']['calle'] }}
                                                {{ $exportacionData['domicilio_emisor']['numero_exterior'] }},
                                                {{ $exportacionData['domicilio_emisor']['colonia'] }},
                                                {{ $exportacionData['domicilio_emisor']['municipio'] }},
                                                {{ $exportacionData['domicilio_emisor']['estado'] }}
                                            @endif
                                        </small>
                                    </div>
                                </div>

                                <!-- Importador (Receptor) -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select @error('importador_id') is-invalid @enderror"
                                            id="importador_id" name="importador_id" required>
                                            <option value="">Seleccione un importador</option>
                                            @foreach($importadores as $importador)
                                                                                <option value="{{ $importador->id }}" {{ (old('importador_id') == $importador->id ||
                                                (isset($importadorMatch) && $importadorMatch->id == $importador->id))
                                                ? 'selected' : '' }}>
                                                                                    {{ $importador->nombre }}
                                                                                    @if(isset($importadorMatch) && $importadorMatch->id == $importador->id)
                                                                                        (Coincidencia por nombre)
                                                                                    @endif
                                                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="importador_id">Importador *</label>
                                        @error('importador_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-info">
                                            Receptor en XML: {{ $exportacionData['receptor_nombre'] }} (RFC:
                                            {{ $exportacionData['receptor_rfc'] }})
                                            @if(!empty($exportacionData['domicilio_receptor']))
                                                <br>Domicilio: {{ $exportacionData['domicilio_receptor']['calle'] }}
                                                {{ $exportacionData['domicilio_receptor']['numero_exterior'] }},
                                                {{ $exportacionData['domicilio_receptor']['localidad'] }},
                                                {{ $exportacionData['domicilio_receptor']['estado'] }},
                                                {{ $exportacionData['domicilio_receptor']['pais'] }}
                                            @endif
                                        </small>
                                    </div>
                                </div>

                                <!-- Producto -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text"
                                            class="form-control @error('nombre_producto') is-invalid @enderror"
                                            id="nombre_producto" name="nombre_producto"
                                            value="{{ old('nombre_producto', $exportacionData['nombre_producto'] ?? '') }}"
                                            required>
                                        <label for="nombre_producto">Nombre del Producto *</label>
                                        @error('nombre_producto')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        @if(!empty($exportacionData['conceptos']))
                                            <small class="text-muted">Productos en factura:
                                                @foreach($exportacionData['conceptos'] as $concepto)
                                                    {{ $concepto['descripcion'] }} ({{ $concepto['cantidad'] }} x
                                                    ${{ number_format($concepto['valor_unitario'], 2) }}),
                                                @endforeach
                                            </small>
                                        @endif
                                    </div>
                                </div>

                                <!-- Datos Comercio Exterior -->
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control"
                                            value="{{ $exportacionData['comercio_exterior']['clave_pedimento'] ?? '' }}"
                                            readonly>
                                        <label>Clave Pedimento</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control"
                                            value="{{ $exportacionData['comercio_exterior']['incoterm'] ?? '' }}" readonly>
                                        <label>Incoterm</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control"
                                            value="${{ number_format($exportacionData['comercio_exterior']['total_usd'] ?? 0, 2) }} USD"
                                            readonly>
                                        <label>Total USD</label>
                                    </div>
                                </div>

                                <!-- Resto del formulario permanece igual -->
                                <!-- ... -->

                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="{{ route('exportaciones.index') }}" class="btn btn-secondary">
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