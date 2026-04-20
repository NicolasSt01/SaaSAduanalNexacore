@extends('layouts.app')

@section('title', 'Detalles de la Bodega')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Detalles de la Bodega</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> {{ $bodega->nombre_bodega }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tax ID:</strong> {{ $bodega->tax_id }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <p><strong>Domicilio:</strong></p>
                            <p class="text-muted">{{ $bodega->domicilio }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Contacto:</strong> {{ $bodega->contacto }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Registrada el:</strong> {{ $bodega->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('bodegas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <div>
                            <a href="{{ route('bodegas.edit', $bodega) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <form action="{{ route('bodegas.destroy', $bodega) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger ms-2" onclick="return confirm('¿Eliminar esta bodega?')">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection