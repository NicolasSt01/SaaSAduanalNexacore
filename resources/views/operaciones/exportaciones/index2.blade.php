@extends('layouts.app')

@section('title', 'Listado de Operaciones')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Registro de Operaciones</h5>
            <a href="{{ route('operaciones.create') }}" class="btn btn-light btn-sm">
                <i class="fas fa-plus"></i> Nueva Exportación
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Factura</th>
                            <th>Bodega</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($operaciones as $operacion)
                        <tr>
                            <td>{{ $operacion->fecha->format('d/m/Y') }}</td>
                            <td>{{ $operacion->cliente->nombre_empresa }}</td>
                            <td>{{ $operacion->nombre_producto }}</td>
                            <td>{{ $operacion->num_factura }}</td>
                            <td>{{ $operacion->bodega->nombre_bodega }}</td>
                            <td>
                                <a href="{{ route('operaciones.show', $operacion) }}" class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('operaciones.edit', $operacion) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('operaciones.destroy', $operacion) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Eliminar esta exportación?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay operaciones registradas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $operaciones->links() }}
            </div>
        </div>
    </div>
</div>
@endsection