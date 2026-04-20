<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reconocimiento Aduanero –Thermo: {{ $first->num_thermo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            color: #1a202c;
            background: #fff;
        }

        /* ── Header ── */
        .header {
            background-color: #1a365d;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header img  { height: 40px; }
        .header-info { color: #90cdf4; font-size: 11px; text-align: right; }
        .header-info strong { color: #fff; font-size: 13px; display: block; }

        /* ── Status Banner ── */
        .status-banner {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .status-banner.green { background: #10b981; }
        .status-banner.red   { background: #dc2626; }
        .status-banner.muted { background: #6b7280; }

        .status-icon {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: rgba(255,255,255,0.25);
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; color: #fff;
            flex-shrink: 0;
        }
        .status-text h2 { color: #fff; font-size: 18px; font-weight: 700; }
        .status-text p  { color: rgba(255,255,255,0.8); font-size: 12px; margin-top: 3px; }

        /* ── Content ── */
        .content { padding: 20px 24px; }

        .card {
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 14px;
        }
        .card-gray   { background: #f7fafc; border: 1px solid #e2e8f0; }
        .card-green  { background: #f0fff4; border-left: 4px solid #10b981; }
        .card-blue   { background: #ebf8ff; border-left: 4px solid #3b82f6; }

        .card h2 { font-size: 13px; font-weight: 700; margin-bottom: 10px; }

        .row-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .row-item:last-child { border-bottom: none; }
        .row-item .label { color: #64748b; }
        .row-item .value { font-weight: 600; }

        /* ── Footer ── */
        .footer {
            border-top: 1px solid #e2e8f0;
            padding: 12px 24px;
            font-size: 10px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        /* ── Print controls (only on screen) ── */
        .print-bar {
            background: #1e3a8a;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-print {
            background: #fff;
            color: #1e3a8a;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print:hover { background: #eff6ff; }
        .btn-close-tab {
            color: rgba(255,255,255,0.7);
            background: transparent;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }
        .btn-close-tab:hover { color: #fff; border-color: #fff; }

        @media print {
            .print-bar { display: none !important; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

{{-- Barra de acción (solo en pantalla, no en PDF) --}}
<div class="print-bar">
    <button class="btn-close-tab" onclick="window.close()">✕ Cerrar</button>
    <button class="btn-print" onclick="window.print()">
        🖨️ Guardar / Imprimir PDF
    </button>
</div>

{{-- Header --}}
<div class="header">
    <img src="https://salassys.com/wp-content/uploads/2025/11/white-2.png" alt="Logo">
    <div class="header-info">
        <strong>Ficha Técnica de Modulación (Finanzas)</strong>
        Pedimento: {{ $first->expediente->numero_pedimento ?? 'N/A' }} | Generado: {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

{{-- Status Banner --}}
@php
    $statusText = match($first->modulacion) {
        'DESADUANAMIENTO LIBRE' => 'Verde - Desaduanamiento Libre',
        'RECONOCIMIENTO ADUANERO CONCLUIDO' => 'Rojo - Reconocimiento Aduanero Concluido',
        'RECONOCIMIENTO ADUANERO' => 'En Proceso - Reconocimiento Aduanero',
        default => $estado
    };
@endphp
<div class="status-banner {{ $color }}">
    <div class="status-icon">{{ $color == 'green' ? '✓' : ($color == 'red' ? '✕' : '⏳') }}</div>
    <div class="status-text">
        <h2>{{ $statusText }}</h2>
        <p>
            @if($color == 'green')   Operación con desaduanamiento libre.
            @elseif($color == 'red') Operación bajo reconocimiento aduandero.
            @else                    La operación sigue en proceso de modulación.
            @endif
        </p>
    </div>
</div>

{{-- Content --}}
<div class="content">

    {{-- Detalles del Económico --}}
    <div class="card card-gray">
        <h2>📋 Información del Cruce</h2>
        <div class="row-item">
            <span class="label">Económico (Thermo):</span>
            <span class="value">{{ $first->num_thermo }}</span>
        </div>
        <div class="row-item">
            <span class="label">Código Alpha:</span>
            <span class="value">{{ $first->codigo_alpha ?? 'N/A' }}</span>
        </div>
        <div class="row-item">
            <span class="label">Fecha de Operación:</span>
            <span class="value">{{ \Carbon\Carbon::parse($first->fecha)->format('d/m/Y') }}</span>
        </div>
    </div>

    {{-- Datos Aduaneros --}}
    <div class="card card-green">
        <h2>🏛️ Identificación Aduanera</h2>
        <div class="row-item">
            <span class="label">No. Pedimento:</span>
            <span class="value">{{ $first->expediente->numero_pedimento ?? 'N/A' }}</span>
        </div>
        <div class="row-item">
            <span class="label">No. Patente:</span>
            <span class="value">{{ $first->patente->numero_patente ?? 'N/A' }}</span>
        </div>
        <div class="row-item">
            <span class="label">Cliente:</span>
            <span class="value">{{ $first->cliente->nombre_empresa ?? 'N/A' }}</span>
        </div>
    </div>

    {{-- Facturas --}}
    <div class="card card-blue">
        <h2>📦 Operaciones y Facturas Asociadas</h2>
        @foreach($registros as $r)
        <div class="row-item">
            <span class="label">Ref: <strong>{{ $r->referencia }}</strong></span>
            <span class="value">Factura: {{ $r->num_factura ?? 'N/A' }}</span>
        </div>
        @endforeach
    </div>

</div>

{{-- Footer --}}
<div class="footer">
    <span>Plataforma Crosspoint – Sistema de Finanzas</span>
    <span>ID Operación: {{ $first->id }} | {{ $first->num_thermo }}</span>
</div>

<script>
    // Auto-trigger el diálogo de impresión al cargar
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 600);
    });
</script>
</body>
</html>
