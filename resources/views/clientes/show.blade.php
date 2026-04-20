@extends('layouts.app')

@section('title', 'Detalle Cliente')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg rounded-3">
                <div class="card-header text-center bg-primary text-white">
                    <h4>Detalle del Cliente</h4>
                </div>
                <div class="card-body">
                    <h5 class="card-title text-center mb-4">{{ $cliente->nombre_empresa }}</h5>

                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item"><strong>RFC:</strong> {{ $cliente->rfc }}</li>
                        <li class="list-group-item"><strong>Tax ID:</strong> {{ $cliente->tax_id }}</li>
                        <li class="list-group-item"><strong>Correo de Contacto:</strong> {{ $cliente->correo_contacto_principal }}</li>
                        <li class="list-group-item"><strong>Teléfono:</strong> {{ $cliente->telefono_contacto }}</li>
                        <li class="list-group-item"><strong>Dirección Fiscal:</strong> {{ $cliente->direccion_fiscal }}</li>
                        <li class="list-group-item"><strong>Persona de Contacto:</strong> {{ $cliente->persona_contacto }}</li>
                    </ul>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-warning w-45">
                            Editar
                        </a>
                        <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?');" class="w-45">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
