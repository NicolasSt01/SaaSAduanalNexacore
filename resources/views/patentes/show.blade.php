@extends('layouts.app')

@section('title', 'Detalles de Patente')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">Detalles de Patente</h3>
                        <a href="{{ route('patentes.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Regresar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Información Básica</h5>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <strong>Número de Patente:</strong> {{ $patente->numero_patente }}
                                </li>
                                <li class="list-group-item">
                                    <strong>Agente Aduanal:</strong> {{ $patente->nombre_agente_aduanal }}
                                </li>
                                <li class="list-group-item">
                                    <strong>RFC:</strong> {{ $patente->rfc_agente ?? 'No especificado' }}
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end gap-2 mb-3">
                                <a href="{{ route('patentes.edit', $patente) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form action="{{ route('patentes.destroy', $patente) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('¿Eliminar esta patente?')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Aduanas Asignadas -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Aduanas Habilitadas</h5>
                        </div>
                        <div class="card-body">
                            @if($patente->aduanas->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Clave</th>
                                            <th>Nombre</th>
                                            <!--<th>Acciones</th>-->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($patente->aduanas as $aduana)
                                        <tr>
                                            <td>{{ $aduana->clave_aduana }}</td>
                                            <td>{{ $aduana->nombre_aduana }}</td>
                                            <!--<td>
                                                <a href="{{ route('aduanas.show', $aduana) }}" 
                                                   class="btn btn-sm btn-info" title="Ver Aduana">
                                                    <i class="fas fa-eye"></i>
                                                </a>-->
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-warning">
                                Esta patente no tiene aduanas asignadas.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection