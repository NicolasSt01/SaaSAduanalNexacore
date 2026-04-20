{{-- Header --}}
<div class="p-4 text-center" style="background-color: #1a365d;">
    <img src="https://salassys.com/wp-content/uploads/2025/11/white-2.png" alt="Logo" width="150" class="mb-2">
</div>

{{-- Status Card --}}
<div class="text-center p-4" 
     style="background-color: {{ $color == 'green' ? '#10b981' : ($color == 'red' ? '#dc2626' : '#6b7280') }};">
    <div style="width: 70px; height: 70px; background-color: rgba(255,255,255,0.2); 
                border-radius: 50%; margin: 0 auto 15px; display: flex; 
                justify-content:center; align-items:center; font-size: 32px; color: #fff;">
        {{ $color == 'green' ? '✓' : '!' }}
    </div>
    <h3 class="text-white fw-bold mb-1">{{ $estado }}</h3>
    <p class="text-white-50 mb-0" style="font-size: 14px;">
        @if($color == 'green')
            Desaduanamiento Libre.
        @elseif($color == 'red')
            Reconocimiento Aduanero.
        @else
            La operación sigue en proceso.
        @endif
    </p>
</div>

{{-- Body --}}
<div class="p-4">
    <div class="p-3 mb-3" style="background-color: #f7fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h5 class="fw-bold mb-3">📋 Detalles del Económico</h5>
        <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-secondary fw-semibold">Económico:</span>
            <span class="fw-bold">{{ $first->num_thermo }}</span>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-secondary fw-semibold">Código Alpha:</span>
            <span class="fw-bold">{{ $first->codigo_alpha ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="p-3 mb-3" style="background-color: #f0fff4; border-radius: 8px; border-left: 4px solid #10b981;">
        <h6 class="fw-bold mb-2">🏛️ Datos Aduaneros</h6>
        <div class="d-flex justify-content-between py-1 border-bottom">
            <span class="text-secondary">No. Patente:</span>
            <span class="fw-bold">{{ $first->patente->numero_patente ?? 'N/A' }}</span>
        </div>
        <div class="d-flex justify-content-between py-1">
            <span class="text-secondary">No. Pedimento:</span>
            <span class="fw-bold">{{ $first->expediente->numero_pedimento ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="p-3" style="background-color: #ebf8ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
        <h6 class="fw-bold mb-2">📦 Facturas Asociadas</h6>
        @foreach($registros as $r)
            <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-secondary">Factura:</span>
                <span class="fw-bold">{{ $r->num_factura }}</span>
            </div>
        @endforeach
    </div>
</div>

{{-- Footer --}}
<div class="p-3 d-flex justify-content-between align-items-center border-top">

    @php
        $printUrl = route('finanzas.modal.modulacion.print', ['id' => $first->id]);
    @endphp
    <a href="{{ $printUrl }}"
       target="_blank"
       class="btn btn-sm btn-danger"
       title="Abrir ficha para guardar como PDF">
        <i class="fas fa-file-pdf me-1"></i> Guardar PDF
    </a>

    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
</div>
