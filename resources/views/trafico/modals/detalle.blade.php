<div class="row">
    {{-- Información General --}}
    <div class="col-lg-6 border-end">
        <h6 class="text-primary fw-bold">
            <i class="fas fa-info-circle me-2"></i>Información General
        </h6>
        <hr class="mt-2 mb-3">
        <ul class="list-unstyled mb-4">
            <li><strong>Código Alpha:</strong> {{ $first->codigo_alpha }}</li>
            <li><strong>DODA:</strong> {{ $first->num_doda ?? 'SIN DODA' }}</li>
            <li><strong>Pedimento:</strong> {{ $first->expediente->numero_pedimento ?? 'SIN PEDIMENTO' }}</li>
            <li><strong>Aduana:</strong> {{ $first->aduana->nombre_aduana ?? 'SIN ADUANA' }}</li>
            <li><strong>Bodega:</strong> {{ $first->bodega->nombre_bodega ?? 'N/A' }}</li>
            <li>
                <strong>Modulación:</strong>
                <span class="badge bg-{{ $color === 'green' ? 'success' : ($color === 'red' ? 'danger' : 'secondary') }}">
                    {{ $estado }}
                </span>
            </li>
        </ul>

        {{-- Conceptos Adicionales --}}
        <div class="mt-4">
            <h6 class="text-primary fw-bold">
                <i class="fas fa-dollar-sign me-2"></i>Conceptos Adicionales del Camión
            </h6>
            <hr class="mt-2 mb-3">
            <div class="mb-3">
                @forelse($conceptosCamion as $concepto)
                    <div class="d-flex justify-content-between align-items-center bg-light rounded-pill px-3 py-2 mb-2 shadow-sm">
                        <span class="small">
                            <i class="fas fa-tag text-success me-2"></i>
                            <strong>{{ ucfirst(str_replace('_', ' ', $concepto->tipo_concepto)) }}</strong>
                            <br>
                            <small class="text-muted">Factura: {{ $concepto->operacion->num_factura ?? 'N/A' }}</small>
                        </span>
                        <form action="{{ route('conceptos.destroy', $concepto->id) }}" method="POST" 
                              onsubmit="return confirm('¿Eliminar este concepto?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1" 
                                    style="width: 24px; height: 24px; line-height: 1;">
                                <i class="fas fa-times" style="font-size: 0.7rem;"></i>
                            </button>
                        </form>
                    </div>
                    @if($concepto->descripcion)
                        <small class="text-muted ms-4 d-block mb-2">
                            <i class="fas fa-comment-dots me-1"></i>{{ $concepto->descripcion }}
                        </small>
                    @endif
                @empty
                    <p class="text-muted small mb-0">
                        <i class="fas fa-info-circle me-1"></i>No hay conceptos adicionales.
                    </p>
                @endforelse
            </div>
            <button class="btn btn-sm btn-success rounded-pill px-3" 
                    onclick="loadConceptosModal('{{ $thermo }}', '{{ $alpha }}', '{{ $fecha }}')">
                <i class="fas fa-plus me-1"></i>Agregar Concepto
            </button>
        </div>

        <h6 class="mt-5 text-primary fw-bold">
            <i class="fas fa-file-invoice me-2"></i>Facturas Asociadas
        </h6>
        <hr class="mt-2 mb-3">
        <ul class="list-group list-group-flush">
            @forelse($registros as $exp)
                <li class="list-group-item d-flex justify-content-between align-items-center bg-light rounded-pill mb-2 p-3 shadow-sm">
                    <span>{{ $exp->cliente->nombre_empresa }} - Factura #{{ $exp->num_factura }}</span>
                    <span class="badge bg-light text-dark fw-normal">{{ $exp->estado }}</span>
                </li>
            @empty
                <p class="text-muted small">No hay operaciones registradas.</p>
            @endforelse
        </ul>
    </div>

    {{-- Documentos --}}
    <div class="col-lg-6">
        <h6 class="text-primary fw-bold">
            <i class="fas fa-folder-open me-2"></i>Documentos
        </h6>
        <hr class="mt-2 mb-3">
        <div class="overflow-auto rounded p-3" style="max-height: 400px; background-color: #ffffff;">
            @forelse($registros as $exp)
                <div class="card shadow-sm mb-3 rounded-3 border-0">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0 fw-bold">Operación REF#{{ $exp->referencia }}</h6>
                        <a href="{{ route('trafico.operaciones.show', $exp->id) }}" 
                           class="btn btn-sm btn-outline-primary me-2 rounded-pill">
                            <i class="fas fa-eye"></i> Ver detalles
                        </a>
                    </div>
                    <div class="card-body py-2">
                        @if($exp->documentos->isNotEmpty())
                            @foreach($exp->documentos->groupBy('tipo_documento') as $tipo => $docs)
                                <div class="mt-2">
                                    <small class="text-muted">{{ ucfirst($tipo) }}</small>
                                    @foreach($docs as $doc)
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 pt-2">
                                            <span class="small text-truncate me-2">{{ $doc->nombre_documento }}</span>
                                            <a href="{{ route('documentos.download', $doc) }}" 
                                               class="btn btn-sm btn-outline-primary rounded-pill">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted small mb-0">Sin documentos.</p>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted small">No hay operaciones registradas.</p>
            @endforelse
        </div>
    </div>
</div>