{{-- resources/views/documentador/partials/tramite-item.blade.php --}}

@php
    // Determinar si es consolidado
    $esConsolidado = ($operacion->consolidado_count ?? 1) > 1;
    $esPrimero = $operacion->consolidado_first ?? true;

    // Determinar si es el último del grupo consolidado
    $esUltimo = false;
    if ($esConsolidado && isset($loop) && !$loop->last) {
        $siguiente = $loop->iteration < count($operaciones ?? []) ? ($operaciones[$loop->iteration] ?? null) : null;
        if ($siguiente) {
            $esUltimo = ($siguiente->num_thermo ?? '') !== $operacion->num_thermo ||
                ($siguiente->codigo_alpha ?? '') !== $operacion->codigo_alpha;
        }
    } elseif ($esConsolidado && isset($loop) && $loop->last) {
        $esUltimo = true;
    }

    // Clases de consolidado
    $consolidadoClasses = '';
    if ($esConsolidado) {
        $consolidadoClasses = 'consolidado';
        if ($esPrimero)
            $consolidadoClasses .= ' consolidado-first';
        if ($esUltimo)
            $consolidadoClasses .= ' consolidado-last';
    }

    // Estado y badges
    $estado = strtolower($operacion->estado ?? 'pendiente');
    $badgeClass = match ($estado) {
        'terminado' => 'badge-success',
        'proceso' => 'badge-warning',
        'pendiente' => 'badge-info',
        default => 'badge-info'
    };

    $estadoTexto = match ($estado) {
        'terminado' => 'Terminado',
        'proceso' => 'En Proceso',
        'pendiente' => 'Pendiente',
        default => 'Pendiente'
    };
@endphp

<div class="tramite-item {{ $consolidadoClasses }}" style="{{ $tipo === 'otro' ? 'opacity: 0.6;' : '' }}">
    {{-- ID Badge --}}
    <div class="id-badge">#{{ $operacion->referencia }}</div>

    {{-- Info Principal --}}
    <div class="tramite-info">
        <div class="tramite-cliente">{{ $operacion->cliente->nombre_empresa ?? 'Sin cliente' }}</div>
        <div class="tramite-ref">
            Factura • {{ $operacion->num_factura ?? 'Sin factura' }}

            @if($esConsolidado)
                <span class="badge-consolidado">
                    <i class="fas fa-truck"></i> {{ $operacion->consolidado_count }} en 1 camión
                </span>
            @endif
        </div>
    </div>

    {{-- Metadata --}}
    <div class="tramite-meta">
        <div class="meta-row">
            <i class="fas fa-box meta-icon"></i>
            <!--<span>{{ substr($operacion->nombre_producto ?? 'Producto', 0, 20) }}{{ strlen($operacion->nombre_producto ?? '') > 20 ? '...' : '' }}
                • {{ number_format($operacion->peso_bruto ?? 0) }} kg</span>-->
            <span>{{ substr($operacion->nombre_producto ?? 'Producto', 0, 20) }}{{ strlen($operacion->nombre_producto ?? '') > 20 ? '...' : '' }}
            </span>
        </div>
        <div class="meta-row">
            @if($esConsolidado && $operacion->num_thermo && $operacion->codigo_alpha)
                <i class="fas fa-barcode meta-icon"></i>
                <span>{{ $operacion->num_thermo }} • {{ $operacion->codigo_alpha }}</span>
            @else
                <i class="fas fa-map-marker-alt meta-icon"></i>
                <span>{{ $operacion->aduana->nombre_aduana ?? 'Sin aduana' }}</span>
            @endif
        </div>
    </div>

    {{-- Estado --}}
    <div>
        <span class="badge {{ $badgeClass }}">{{ $estadoTexto }}</span>
    </div>

    {{-- Acciones --}}
    <div>
        @if($tipo === 'propio')
            {{-- Mis trámites --}}
            @if($operacion->estado !== 'terminado')
                <a href="{{ route('documentador.trabajar', $operacion->id) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-arrow-right"></i> Trabajar
                </a>
            @else
                <a href="{{ route('documentador.trabajar', $operacion->id) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-eye"></i> Ver
                </a>
            @endif
            {{-- Botón Soltar (solo si está en pendiente) --}}
            @if($operacion->estado === 'pendiente')
                <form action="{{ route('documentador.soltar_tramite', $operacion->id) }}" method="POST"
                    onsubmit="return confirm('¿Estás seguro de soltar este trámite?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="fas fa-hand-paper me-1"></i> Soltar
                    </button>
                </form>
            @endif

        @elseif($tipo === 'disponible')
            {{-- Trámites disponibles --}}
            <form action="{{ route('documentador.tomar_tramite') }}" method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="operacion_id" value="{{ $operacion->id }}">
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-hand-paper"></i> Tomar
                </button>
            </form>

        @else
            {{-- Asignados a otros --}}
            <button class="btn btn-sm" style="background: var(--bg-secondary); color: var(--text-muted);" disabled>
                <i class="fas fa-lock"></i> Asignado
            </button>
        @endif
    </div>
</div>