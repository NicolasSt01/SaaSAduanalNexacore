@extends('layouts.app')

@section('title', 'Mis Expedientes')

@section('customcss')
    <style>
        .expediente-card {
            transition: all 0.3s ease;
            border-left: 4px solid #3b82f6;
        }

        .expediente-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .documento-item {
            transition: background-color 0.2s;
        }

        .documento-item:hover {
            background-color: #f8f9fa;
        }

        .stat-card {
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">
        {{-- Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">Mis Expedientes</h2>
                        <p class="text-muted mb-0">{{ $cliente->nombre ?? 'Cliente' }}</p>
                    </div>
                    <div class="text-end">
                        <div class="stat-card p-3 shadow-sm">
                            <h4 class="mb-0">{{ $expedientes->total() }}</h4>
                            <small>Total de Expedientes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros rápidos --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="GET" action="{{ route('expedientes.indexcliente') }}" class="row g-3">
                            <div class="col-md-4">
                                <label for="numero_pedimento" class="form-label small">No. Pedimento</label>
                                <input type="text" class="form-control" id="numero_pedimento" name="numero_pedimento"
                                    placeholder="Buscar por pedimento" value="{{ request('numero_pedimento') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="estado" class="form-label small">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos los estados</option>
                                    <option value="En proceso" {{ request('estado') == 'En proceso' ? 'selected' : '' }}>En
                                        proceso</option>
                                    <option value="Abierto" {{ request('estado') == 'Abierto' ? 'selected' : '' }}>Abierto
                                    </option>
                                    <option value="Cerrado" {{ request('estado') == 'Cerrado' ? 'selected' : '' }}>Cerrado
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_desde" class="form-label small">Fecha Desde</label>
                                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde"
                                    value="{{ request('fecha_desde') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de Expedientes --}}
        <div class="row">
            @forelse($expedientes as $expediente)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card expediente-card h-100 shadow-sm">
                        <div class="card-header bg-white border-0 pt-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1">
                                        <i class="fas fa-file-alt text-primary me-2"></i>
                                        {{ $expediente->numero_pedimento ?? 'Sin Pedimento' }}
                                    </h5>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt"></i>
                                        {{ $expediente->created_at->format('d/m/Y') }}
                                    </small>
                                </div>
                                <span
                                    class="badge badge-custom bg-{{ $expediente->estado == 'Cerrado' ? 'success' : ($expediente->estado == 'En proceso' ? 'warning' : 'primary') }}">
                                    {{ $expediente->estado ?? 'Sin estado' }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            {{-- Información del expediente --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Aduana:</span>
                                    <span class="small fw-bold">
                                        {{ $expediente->aduana->nombre ?? 'No especificada' }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Categoría:</span>
                                    <span class="small fw-bold">
                                        {{ $expediente->categoria ?? 'No especificada' }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Documentos:</span>
                                    <span class="small fw-bold">
                                        {{ $expediente->documentos->count() ?? 0 }}
                                    </span>
                                </div>
                            </div>

                            {{-- Documentos recientes --}}
                            {{--@if($expediente->documentos->count() > 0)
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-2">Documentos recientes:</small>
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($expediente->documentos->take(2) as $documento)
                                            <span class="badge bg-light text-dark border small text-start">
                                                <i class="fas fa-file-pdf text-danger me-1"></i>
                                                {{ Str::limit($documento->nombre_documento ?? 'Documento', 20) }}
                                            </span>
                                        @endforeach
                                        @if($expediente->documentos->count() > 2)
                                            <span class="badge bg-light text-dark small">
                                                +{{ $expediente->documentos->count() - 2 }} más
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif--}}
                        </div>
                        <div class="card-footer bg-white border-0 pb-3">
                            <a href="{{ route('expedientes.showclient', $expediente->id) }}" class="btn btn-primary w-100">
                                <i class="fas fa-folder-open"></i> Ver Documentos
                            </a>
                        </div>
                        {{--<div class="card-footer bg-white border-0 pb-3">
                            <button class="btn btn-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#expedienteModal{{ $expediente->id }}">
                                <i class="fas fa-folder-open"></i> Ver Documentos
                            </button>
                        </div>--}}
                    </div>
                </div>

                {{-- Modal de detalles del expediente --}}
                <div class="modal fade" id="expedienteModal{{ $expediente->id }}" tabindex="-1" aria-hidden="true"
                    data-bs-backdrop="static">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content rounded-4 shadow-lg">
                            <div class="modal-header"
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                <h5 class="modal-title">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Expediente: {{ $expediente->numero_pedimento ?? 'Sin Pedimento' }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                {{-- Información general --}}
                                <div class="card mb-4 border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-info-circle"></i> Información General
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-2">
                                                    <strong>Pedimento:</strong>
                                                    {{ $expediente->numero_pedimento ?? 'Sin Pedimento' }}
                                                </p>
                                                <p class="mb-2">
                                                    <strong>Aduana:</strong>
                                                    {{ $expediente->aduana->nombre ?? 'No especificada' }}
                                                </p>
                                                <p class="mb-2">
                                                    <strong>Categoría:</strong>
                                                    {{ $expediente->categoria ?? 'No especificada' }}
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-2">
                                                    <strong>Fecha de Creación:</strong>
                                                    {{ $expediente->created_at->format('d/m/Y H:i') }}
                                                </p>
                                                <p class="mb-2">
                                                    <strong>Estado:</strong>
                                                    <span
                                                        class="badge bg-{{ $expediente->estado == 'Cerrado' ? 'success' : ($expediente->estado == 'En proceso' ? 'warning' : 'primary') }}">
                                                        {{ $expediente->estado ?? 'Sin estado' }}
                                                    </span>
                                                </p>
                                                <p class="mb-2">
                                                    <strong>Total Documentos:</strong>
                                                    {{ $expediente->documentos->count() }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Documentos del expediente --}}
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-file-download"></i> Documentos del Expediente
                                </h6>

                                @if($expediente->documentos->isNotEmpty())
                                    <div class="card shadow-sm">
                                        <div class="card-body">
                                            <div class="list-group">
                                                @foreach($expediente->documentos->groupBy('tipo_documento') as $tipo => $documentos)
                                                    <div class="mb-4">
                                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                                            <i class="fas fa-folder me-2"></i>
                                                            {{ ucfirst($tipo) }} ({{ $documentos->count() }})
                                                        </h6>
                                                        @foreach($documentos as $documento)
                                                            <div
                                                                class="documento-item d-flex justify-content-between align-items-center p-3 border rounded mb-2">
                                                                <div class="d-flex align-items-center flex-grow-1">
                                                                    <i class="fas fa-file-pdf text-danger me-3 fs-5"></i>
                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-bold text-dark">
                                                                            {{ $documento->nombre_documento ?? 'Documento sin nombre' }}
                                                                        </div>
                                                                        <div class="small text-muted">
                                                                            <i class="fas fa-calendar-alt me-1"></i>
                                                                            {{ $documento->created_at->format('d/m/Y H:i') }}
                                                                            @if($documento->descripcion)
                                                                                • {{ $documento->descripcion }}
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex gap-2">
                                                                    @if($documento->archivo_url)
                                                                        <a href="{{ Storage::url($documento->archivo_url) }}"
                                                                            class="btn btn-sm btn-outline-primary rounded-pill" target="_blank"
                                                                            data-bs-toggle="tooltip" title="Ver documento">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                        <a href="{{ Storage::url($documento->archivo_url) }}"
                                                                            class="btn btn-sm btn-primary rounded-pill" download
                                                                            data-bs-toggle="tooltip" title="Descargar documento">
                                                                            <i class="fas fa-download"></i>
                                                                        </a>
                                                                    @else
                                                                        <span class="badge bg-warning text-dark">
                                                                            Sin archivo
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        No hay documentos disponibles para este expediente.
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                                    <i class="fas fa-times"></i> Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay expedientes disponibles</h4>
                            <p class="text-muted">Aún no tienes expedientes registrados en el sistema.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        @if($expedientes->hasPages())
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        Mostrando <strong>{{ $expedientes->firstItem() }}</strong> a
                                        <strong>{{ $expedientes->lastItem() }}</strong> de
                                        <strong>{{ $expedientes->total() }}</strong> expedientes
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    {{ $expedientes->appends(request()->query())->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <script>
        // Inicializar tooltips
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>

    @push('scripts')

    @endpush
@endsection