@extends('layouts.app')

@section('title', 'Listado de Importadores')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Listado de Importadores</h5>
            <a href="{{ route('importadores.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo Importador
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tax ID</th>
                            <th>Dirección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($importadores as $importador)
                            <tr>
                                <td>{{ $importador->nombre }}</td>
                                <td>{{ $importador->tax_id }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($importador->direccion, 50) }}</td>
                                <td>
                                    <a href="{{ route('importadores.show', $importador) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route( 'importadores.edit',$importador) }}"
                                        class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('importadores.destroy', $importador) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Eliminar este importador?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No hay importadores registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $importadores->links() }}
            </div>
        </div>
    </div>
@endsection