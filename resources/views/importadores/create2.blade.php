@extends('layouts.app')

@section('title', 'Crear Nuevo Importador')

@section('content')

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Crear Nuevo Importador</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('importadores.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>

            <div class="mb-3">
                <label for="tax_id" class="form-label">Tax ID</label>
                <input type="text" class="form-control" id="tax_id" name="tax_id" required>
                <small class="text-muted">Equivalente al RFC en USA</small>
            </div>

            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <textarea class="form-control" id="direccion" name="direccion" rows="3"></textarea>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('importadores.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection