@extends('layouts.app')

@section('title', 'Listado de Operaciones')

@section('content')
<div class="container-fluid py-3">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Gestión de Operaciones</h1>
            <a href="{{ route('operaciones.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Nueva Exportación
            </a>
        </div>
    </div>

    <!-- Cards de Operaciones -->
    <div class="row">
        @forelse($operaciones as $operacion)
            <div class="col-xxl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
                <div class="card h-100 shadow-sm border-start border-3
                    @if(($operacion->prioridad ?? 'baja') == 'alta') border-danger
                    @elseif(($operacion->prioridad ?? 'baja') == 'media') border-warning
                    @elseif(($operacion->prioridad ?? 'baja') == 'urgente') border-dark
                    @elseif(($operacion->estado ?? 'pendiente') == 'completado') border-success
                    @else border-primary @endif">

                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0 text-truncate">{{ $operacion->cliente->nombre_empresa }}</h6>
                            <span class="badge 
                                @if(($operacion->estado ?? 'pendiente') == 'pendiente') bg-secondary
                                @elseif(($operacion->estado ?? 'pendiente') == 'asignado') bg-info
                                @elseif(($operacion->estado ?? 'pendiente') == 'completado') bg-success
                                @else bg-warning @endif">
                                {{ ucfirst($operacion->estado ?? 'pendiente') }}
                            </span>
                        </div>

                        <p class="card-text small mb-1 text-truncate">
                            <i class="fas fa-box me-1 text-muted"></i>{{ $operacion->nombre_producto }}
                        </p>

                        <p class="card-text small mb-1">
                            <i class="fas fa-file-invoice me-1 text-muted"></i><strong>Factura:</strong> {{ $operacion->num_factura }}
                        </p>

                        <p class="card-text small mb-2">
                            <i class="fas fa-warehouse me-1 text-muted"></i><strong>Bodega:</strong> {{ $operacion->bodega->nombre_bodega }}
                        </p>

                        <div class="mb-2">
                            <span class="badge 
                                @if(($operacion->prioridad ?? 'baja') == 'alta') bg-danger
                                @elseif(($operacion->prioridad ?? 'baja') == 'media') bg-warning
                                @elseif(($operacion->prioridad ?? 'baja') == 'urgente') bg-dark
                                @else bg-primary @endif">
                                Prioridad: {{ ucfirst($operacion->prioridad ?? 'baja') }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('operaciones.show', $operacion) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('operaciones.edit', $operacion) }}" class="btn btn-sm btn-outline-secondary ms-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-primary asignar-btn" data-bs-toggle="modal"
                                    data-bs-target="#asignarModal"
                                    data-operacion-id="{{ $operacion->id }}"
                                    data-cliente="{{ $operacion->cliente->nombre_empresa }}"
                                    data-producto="{{ $operacion->nombre_producto }}">
                                    <i class="fas fa-user-plus"></i>
                                </button>

                                <form action="{{ route('operaciones.destroy', $operacion) }}" method="POST" class="d-inline ms-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('¿Eliminar esta exportación?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay operaciones registradas</h5>
                <a href="{{ route('operaciones.create') }}" class="btn btn-primary mt-2">
                    <i class="fas fa-plus me-2"></i>Crear Exportación
                </a>
            </div>
        @endforelse
    </div>

    <!-- Modal Asignar -->
    <div class="modal fade" id="asignarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title">Asignar Exportación</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="asignarForm" method="POST" action="{{ route('operaciones.asignar') }}">
                        @csrf
                        <input type="hidden" name="operacion_id" id="operacion_id">

                        <p><strong>Cliente:</strong> <span id="modal_cliente"></span></p>
                        <p><strong>Producto:</strong> <span id="modal_producto"></span></p>

                        <div class="mb-2">
                            <label for="usuario_cierre_id" class="form-label">Documentador:</label>
                            <select name="usuario_cierre_id" id="usuario_cierre_id" class="form-select" required>
                                <option value="">Seleccione</option>
                                @foreach($documentadores as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label for="prioridad" class="form-label">Prioridad:</label>
                            <select name="prioridad" id="prioridad" class="form-select" required>
                                <option value="regular">Regular</option>
                                <option value="media">Media</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>

                        <input type="hidden" name="estado" value="pendiente">
                    </form>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary btn-sm" id="confirmarAsignacion">Asignar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function(){
    // Modal
    const asignarModal = document.getElementById('asignarModal');
    const confirmarBtn = document.getElementById('confirmarAsignacion');

    if(asignarModal){
        asignarModal.addEventListener('show.bs.modal', function(event){
            const button = event.relatedTarget;
            const operacionId = button.getAttribute('data-operacion-id');
            const cliente = button.getAttribute('data-cliente');
            const producto = button.getAttribute('data-producto');

            document.getElementById('operacion_id').value = operacionId;
            document.getElementById('modal_cliente').textContent = cliente;
            document.getElementById('modal_producto').textContent = producto;
        });
    }

    if(confirmarBtn){
        confirmarBtn.addEventListener('click', function(){
            document.getElementById('asignarForm').submit();
        });
    }
});
</script>
@endsection
