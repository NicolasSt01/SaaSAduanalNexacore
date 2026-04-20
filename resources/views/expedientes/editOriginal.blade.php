@extends('layouts.app')

@section('title', 'Editar Expediente')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg rounded-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Editar Expediente</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('expedientes.update', $expediente) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        {{-- Cliente --}}
                        <div class="mb-3">
                            <label for="cliente_id" class="form-label">Cliente</label>
                            <select name="cliente_id" id="cliente_id" class="form-select" required>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ $expediente->cliente_id == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombre_empresa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Patente --}}
                        <div class="mb-3">
                            <label for="patente_id" class="form-label">Patente</label>
                            <select name="patente_id" id="patente_id" class="form-select" required>
                                @foreach($patentes as $patente)
                                    <option value="{{ $patente->id }}" {{ $expediente->patente_id == $patente->id ? 'selected' : '' }}>
                                        {{ $patente->numero_patente }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Aduana --}}
                        <div class="mb-3">
                            <label for="aduana_id" class="form-label">Aduana</label>
                            <select name="aduana_id" id="aduana_id" class="form-select" required>
                                @foreach($aduanas as $aduana)
                                    <option value="{{ $aduana->id }}" {{ $expediente->aduana_id == $aduana->id ? 'selected' : '' }}>
                                        {{ $aduana->nombre_aduana }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tipo Expediente --}}
                        <div class="mb-3">
                            <label for="tipo_expediente" class="form-label">Tipo de Expediente</label>
                            <select name="tipo_expediente" id="tipo_expediente" class="form-select" required>
                                <option value="Unico" {{ $expediente->tipo_expediente == 'Unico' ? 'selected' : '' }}>Único</option>
                                <option value="Consolidado" {{ $expediente->tipo_expediente == 'Consolidado' ? 'selected' : '' }}>Consolidado</option>
                            </select>
                        </div>

                        {{-- Fechas --}}
                        <div class="row" id="fechas-unico" style="display: {{ $expediente->tipo_expediente == 'Unico' ? 'flex' : 'none' }};">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_pago_pedimento" class="form-label">Fecha de Pago</label>
                                <input type="date" name="fecha_pago_pedimento" id="fecha_pago_pedimento" class="form-control" value="{{ $expediente->fecha_pago_pedimento }}">
                            </div>
                        </div>

                        <div class="row" id="fechas-consolidado" style="display: {{ $expediente->tipo_expediente == 'Consolidado' ? 'flex' : 'none' }};">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_apertura" class="form-label">Fecha de Apertura</label>
                                <input type="date" name="fecha_apertura" id="fecha_apertura" class="form-control" value="{{ $expediente->fecha_apertura }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_cierre" class="form-label">Fecha de Cierre</label>
                                <input type="date" name="fecha_cierre" id="fecha_cierre" class="form-control" value="{{ $expediente->fecha_cierre }}">
                            </div>
                        </div>

                        {{-- Observaciones --}}
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" rows="3" class="form-control">{{ $expediente->observaciones }}</textarea>
                        </div>

                        {{-- Estado --}}
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="En proceso" {{ $expediente->estado == 'En proceso' ? 'selected' : '' }}>En proceso</option>
                                <option value="Cerrado" {{ $expediente->estado == 'Cerrado' ? 'selected' : '' }}>Cerrado</option>
                            </select>
                        </div>

                        {{-- Documentos --}}
                        <div class="mb-3">
                            <label for="documentos" class="form-label">Agregar Documentos Nuevos</label>
                            <input type="file" name="documentos[]" multiple class="form-control">
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('expedientes.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-warning">Actualizar Expediente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('tipo_expediente').addEventListener('change', function() {
        document.getElementById('fechas-unico').style.display = (this.value === 'Unico') ? 'flex' : 'none';
        document.getElementById('fechas-consolidado').style.display = (this.value === 'Consolidado') ? 'flex' : 'none';
    });
</script>
@endsection
