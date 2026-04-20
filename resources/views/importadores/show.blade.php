@extends('layouts.app')

@section('title', 'Detalles del Importador')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8"> {{-- Puedes ajustar este ancho (8 de 12 columnas) --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Detalles del Importador</h5>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> {{ $importador->nombre }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tax ID:</strong> {{ $importador->tax_id }}</p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <p><strong>Dirección:</strong></p>
                            <p class="text-muted">{{ $importador->direccion ?? 'No especificada' }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <p><strong>Fecha creación:</strong> {{ $importador->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Última actualización:</strong> {{ $importador->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-white d-flex justify-content-between">
                    <a href="{{ route('importadores.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al listado
                    </a>
                    <div>
                        <a href="{{ route('importadores.edit', $importador) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <form action="{{ route('importadores.destroy', $importador) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger ms-2" onclick="return confirm('¿Confirmas eliminar este importador?')">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection