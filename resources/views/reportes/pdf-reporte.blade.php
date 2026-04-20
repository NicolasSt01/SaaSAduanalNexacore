<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Operaciones - {{ $datos['cliente']['nombre'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #333;
            margin: 15mm 12mm;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #000000ff !important;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .header h1 {
            font-size: 18pt;
            margin-bottom: 3px;
            font-weight: bold;
            color: #000000ff !important;
        }

        .header p {
            font-size: 10pt;
            opacity: 0.95;
            color: #000000ff !important;
        }

        .info-box {
            background: #f8f9fa;
            border: 2px solid #667eea;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            display: table-cell;
            width: 30%;
            font-weight: bold;
            color: #667eea;
            font-size: 10pt;
        }

        .info-value {
            display: table-cell;
            width: 70%;
            font-size: 10pt;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .stat-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 12px 8px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 6px;
        }

        .stat-item:not(:last-child) {
            border-right: none;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .stat-item:not(:first-child) {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .stat-item.primary { border-color: #667eea; }
        .stat-item.success { border-color: #28a745; }
        .stat-item.danger { border-color: #dc3545; }
        .stat-item.warning { border-color: #ffc107; }

        .stat-number {
            font-size: 20pt;
            font-weight: bold;
            display: block;
            margin-bottom: 4px;
        }

        .stat-number.text-primary { color: #667eea; }
        .stat-number.text-success { color: #28a745; }
        .stat-number.text-danger { color: #dc3545; }
        .stat-number.text-warning { color: #ffc107; }

        .stat-label {
            font-size: 8pt;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: bold;
        }

        .stat-percent {
            font-size: 6pt;
            font-weight: bold;
            margin-top: 1px;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #667eea;
        }

        /* CALENDARIO */
        .calendario-container {
            margin-bottom: 20px;
        }

        .mes-titulo {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 12px;
        }

        .calendario-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px;
        }

        .dia-nombre-header {
            text-align: center;
            font-weight: bold;
            color: #495057;
            padding: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            font-size: 9pt;
        }

        .dia-celda {
            text-align: center;
            padding: 8px 4px;
            border-radius: 4px;
            border: 2px solid #e9ecef;
            background-color: #fff;
            height: 50px;
            vertical-align: middle;
        }

        .dia-numero {
            font-size: 10pt;
            font-weight: 600;
            color: #495057;
            display: block;
            margin-bottom: 2px;
        }

        .dia-operaciones {
            font-size: 9pt;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 3px;
            display: inline-block;
        }

        /* Estados del calendario */
        .dia-sin-ops {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .dia-sin-ops .dia-operaciones {
            color: #adb5bd;
            background-color: transparent;
        }

        .dia-bajo {
            background-color: #d1e7dd;
            border-color: #a3cfbb;
        }

        .dia-bajo .dia-operaciones {
            background-color: #28a745;
            color: white;
        }

        .dia-medio {
            background-color: #b3e5b3;
            border-color: #7ec97e;
        }

        .dia-medio .dia-operaciones {
            background-color: #218838;
            color: white;
        }

        .dia-alto {
            background-color: #90d890;
            border-color: #5cb85c;
        }

        .dia-alto .dia-operaciones {
            background-color: #1e7e34;
            color: white;
        }

        .dia-otro-mes {
            opacity: 0.3;
        }

        /* RESUMEN POR ADUANA */
        .aduana-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .aduana-item {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .aduana-nombre {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 8px;
            color: #333;
        }

        .aduana-badges {
            margin-bottom: 5px;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9pt;
            font-weight: bold;
            margin-right: 8px;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .aduana-total {
            font-size: 8pt;
            color: #6c757d;
            margin-top: 5px;
        }

        /* GRÁFICAS */
        .chart-container {
            margin-bottom: 8px;
            text-align: center;
        }

        .chart-title {
            font-size: 9pt;
            font-weight: bold;
            color: #495057;
            margin-bottom: 4px;
            text-align: left;
        }

        .chart-image {
            max-width: 100%;
            width: 100%;
            height: auto;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 8px;
            background: white;
            display: block;
            margin: 0 auto;
        }

        .chart-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            table-layout: fixed;
        }

        .chart-col {
            display: table-cell;
            width: 50%;
            padding: 0 5px;
            vertical-align: top;
        }

        .chart-col:first-child {
            padding-left: 0;
        }

        .chart-col:last-child {
            padding-right: 0;
        }
        .chart-full {
            margin-bottom: 8px;
        }

        .chart-full .chart-image {
           width: 100%;
           height: 250px;
           object-fit: justify;
          max-width: 100%;
        }

        /* TABLA */
        .table-container {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        thead {
            background-color: #667eea;
            color: white;
        }

        th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #5568d3;
        }

        td {
            padding: 6px;
            border: 1px solid #dee2e6;
        }

        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tbody tr:hover {
            background-color: #e9ecef;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .progress-bar-container {
            width: 100%;
            height: 18px;
            background-color: #e9ecef;
            border-radius: 9px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background-color: #667eea;
            border-radius: 9px;
        }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #6c757d;
            padding: 10px 0;
            border-top: 1px solid #dee2e6;
        }

        .page-break {
            page-break-after: always;
        }

        /* LEYENDA DEL CALENDARIO */
        .calendario-leyenda {
            margin-top: 8px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 6px;
            font-size: 8pt;
        }

        .leyenda-item {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 2px;
        }

        .leyenda-color {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            vertical-align: middle;
            margin-right: 5px;
            border: 2px solid #dee2e6;
        }

        .color-alto { background-color: #90d890; }
        .color-medio { background-color: #b3e5b3; }
        .color-bajo { background-color: #d1e7dd; }
        .color-sin { background-color: #f8f9fa; }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <div class="header">
       
        <h1>Reporte de Operaciones</h1>
        <p>Análisis detallado del periodo {{ $datos['periodo']['desde'] }} al {{ $datos['periodo']['hasta'] }}</p>
    </div>

    <!-- INFORMACIÓN DEL CLIENTE -->
    <div class="info-box">
        <div class="info-row">
            <div class="info-label">Cliente:</div>
            <div class="info-value">{{ $datos['cliente']['nombre'] }}</div>
        </div>
        {{--<div class="info-row">
            <div class="info-label">Email:</div>
            <div class="info-value">{{ $datos['cliente']['email'] }}</div>
        </div>--}}
        <div class="info-row">
            <div class="info-label">Periodo:</div>
            <div class="info-value">{{ $datos['periodo']['desde'] }} - {{ $datos['periodo']['hasta'] }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha de generación:</div>
            <div class="info-value">{{ date('d/m/Y H:i:s') }}</div>
        </div>
    </div>

    <!-- ESTADÍSTICAS PRINCIPALES -->
    <div class="stats-grid">
        <div class="stat-item primary">
            <span class="stat-number text-primary">{{ $datos['estadisticas']['total'] }}</span>
            <span class="stat-label">Total Trámites</span>
        </div>
        <div class="stat-item success">
            <span class="stat-number text-success">{{ $datos['estadisticas']['greens'] }}</span>
            <span class="stat-label">Greens</span>
            <div class="stat-percent text-success">
                {{ number_format(($datos['estadisticas']['greens'] / max($datos['estadisticas']['total'], 1)) * 100, 1) }}%
            </div>
        </div>
        <div class="stat-item danger">
            <span class="stat-number text-danger">{{ $datos['estadisticas']['reds'] }}</span>
            <span class="stat-label">Reds</span>
            <div class="stat-percent text-danger">
                {{ number_format(($datos['estadisticas']['reds'] / max($datos['estadisticas']['total'], 1)) * 100, 1) }}%
            </div>
        </div>
        <div class="stat-item warning">
            <span class="stat-number text-warning">{{ $datos['estadisticas']['sobrepesos'] }}</span>
            <span class="stat-label">Sobrepesos</span>
        </div>
    </div>

    <!-- CALENDARIO DE OPERACIONES -->
    @if(isset($datos['calendario']) && count($datos['calendario']) > 0)
    <div class="calendario-container">
        <h2 class="section-title">📅 Calendario de Operaciones</h2>
        
        @php
            $primerDiaActual = collect($datos['calendario'])->flatten(1)->firstWhere('actual', true);
            if ($primerDiaActual) {
                $fecha = \Carbon\Carbon::parse($primerDiaActual['fecha']);
                $nombreMes = ucfirst($fecha->locale('es')->isoFormat('MMMM YYYY'));
            } else {
                $nombreMes = 'Calendario';
            }
        @endphp
        
        <div class="mes-titulo">{{ $nombreMes }}</div>
        
        <table class="calendario-grid">
            <thead>
                <tr>
                    <th class="dia-nombre-header">Lun</th>
                    <th class="dia-nombre-header">Mar</th>
                    <th class="dia-nombre-header">Mié</th>
                    <th class="dia-nombre-header">Jue</th>
                    <th class="dia-nombre-header">Vie</th>
                    <th class="dia-nombre-header">Sáb</th>
                    <th class="dia-nombre-header">Dom</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datos['calendario'] as $semana)
                <tr>
                    @foreach($semana as $dia)
                        @php
                            $claseActual = $dia['actual'] ? '' : 'dia-otro-mes';
                            $total = $dia['total'] ?? 0;
                            
                            $claseEstado = '';
                            $contenido = 'N/A';
                            
                            if ($dia['actual'] && $total > 0) {
                                if ($total >= 10) {
                                    $claseEstado = 'dia-alto';
                                } elseif ($total >= 5) {
                                    $claseEstado = 'dia-medio';
                                } else {
                                    $claseEstado = 'dia-bajo';
                                }
                                $contenido = $total;
                            } elseif ($dia['actual']) {
                                $claseEstado = 'dia-sin-ops';
                            }
                        @endphp
                        <td class="dia-celda {{ $claseActual }} {{ $claseEstado }}">
                            <span class="dia-numero">{{ $dia['dia'] }}</span>
                            <span class="dia-operaciones">{{ $contenido }}</span>
                        </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Leyenda del calendario -->
        <div class="calendario-leyenda">
            <strong>Leyenda:</strong>
            <div class="leyenda-item">
                <span class="leyenda-color color-alto"></span> Alto (10+ ops)
            </div>
            <div class="leyenda-item">
                <span class="leyenda-color color-medio"></span> Medio (5-9 ops)
            </div>
            <div class="leyenda-item">
                <span class="leyenda-color color-bajo"></span> Bajo (1-4 ops)
            </div>
            <div class="leyenda-item">
                <span class="leyenda-color color-sin"></span> Sin operaciones
            </div>
        </div>
    </div>
    @endif

    <div class="page-break"></div>

    <!-- RESUMEN POR ADUANA -->
    @if(isset($datos['porAduana']) && count($datos['porAduana']) > 0)
    <h2 class="section-title">🏢 Resumen por Aduana</h2>
    
    @php
        $aduanasChunks = array_chunk($datos['porAduana'], 3);
    @endphp
    
    @foreach($aduanasChunks as $chunk)
    <div class="aduana-grid" style="margin-bottom: 15px;">
        @foreach($chunk as $aduana)
            @php
                $verdes = collect($datos['verdesPorAduana'] ?? [])->firstWhere('aduana', $aduana['nombre']);
                $rojos = collect($datos['rojosPorAduana'] ?? [])->firstWhere('aduana', $aduana['nombre']);
            @endphp
            <div class="aduana-item" style="margin-right: 10px;">
                <div class="aduana-nombre">{{ $aduana['nombre'] }}</div>
                <div class="aduana-badges">
                    <span class="badge badge-success">✓ Greens: {{ $verdes['total'] ?? 0 }}</span>
                    <span class="badge badge-danger">✗ Reds: {{ $rojos['total'] ?? 0 }}</span>
                </div>
                <div class="aduana-total">Total: {{ $aduana['total'] }} trámites</div>
            </div>
        @endforeach
    </div>
    @endforeach
    @endif

    <!-- GRÁFICAS -->
    <h2 class="section-title">📊 Análisis Gráfico</h2>

    <!-- Fila 1: Modulación y Por Aduana -->
    <div class="chart-row">
        <div class="chart-col">
            <div class="chart-container">
                <div class="chart-title">Modulación (Greens vs Reds)</div>
                @if(isset($charts['greensReds']))
                    <img src="{{ $charts['greensReds'] }}" alt="Gráfica Greens vs Reds" class="chart-image">
                @else
                    <p style="color: #6c757d; font-style: italic;">Gráfica no disponible</p>
                @endif
            </div>
        </div>
        <div class="chart-col">
            <div class="chart-container">
                <div class="chart-title">Distribución por Aduana</div>
                @if(isset($charts['aduanas']))
                    <img src="{{ $charts['aduanas'] }}" alt="Gráfica por Aduana" class="chart-image">
                @else
                    <p style="color: #6c757d; font-style: italic;">Gráfica no disponible</p>
                @endif
            </div>
        </div>
    </div>

    

    <!-- Desglose por Aduana -->

    @if(isset($charts['desglose']))
    <div class="chart-container chart-full">
        <div class="chart-title">Desglose: Greens vs Reds por Aduana</div>
        <img src="{{ $charts['desglose'] }}" alt="Desglose por Aduana" class="chart-image">
    </div>
    @endif
    
    <div class="page-break"></div>


    <!-- Histórico Anual -->
    @if(isset($charts['historico']))
    <div class="chart-container chart-full">
        <div class="chart-title">Histórico Anual {{ date('Y') }}</div>
        <img src="{{ $charts['historico'] }}" alt="Histórico Anual" class="chart-image">
    </div>
    @endif

    

    <!-- Operaciones Diarias -->
    @if(isset($charts['diarios']))
    <div class="chart-container chart-full">
        <div class="chart-title">Operaciones por Día (Periodo Seleccionado)</div>
        <img src="{{ $charts['diarios'] }}" alt="Operaciones Diarias" class="chart-image">
    </div>
    @endif
    
    <div class="page-break"></div>

    <!-- Top Importadores -->
    @if(isset($charts['importadores']))
    <div class="chart-container chart-full">
        <div class="chart-title">Top 10 Importadores</div>
        <img src="{{ $charts['importadores'] }}" alt="Top Importadores" class="chart-image">
    </div>
    @endif

    <!-- TABLA DE IMPORTADORES -->
    @if(isset($datos['topImportadores']) && count($datos['topImportadores']) > 0)
    <div class="table-container">
        <h2 class="section-title">📋 Detalle de Importadores</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 40%;">Importador</th>
                    <th class="text-center" style="width: 17%;">Trámites</th>
                    <th class="text-center" style="width: 15%;">Porcentaje</th>
                    <th style="width: 20%;">Participación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datos['topImportadores'] as $index => $importador)
                    @php
                        $porcentaje = ($importador['total'] / max($datos['estadisticas']['total'], 1)) * 100;
                    @endphp
                    <tr>
                        <td class="text-center" style="font-weight: bold; color: #667eea;">{{ $index + 1 }}</td>
                        <td>{{ $importador['importador'] }}</td>
                        <td class="text-center">
                            <span class="badge" style="background-color: #667eea; color: white; padding: 5px 12px; border-radius: 12px;">
                                {{ $importador['total'] }}
                            </span>
                        </td>
                        <td class="text-center" style="font-weight: bold;">{{ number_format($porcentaje, 1) }}%</td>
                        <td>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: {{ min($porcentaje, 100) }}%;"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        <p>Reporte generado automáticamente el {{ date('d/m/Y H:i:s') }} | {{ $datos['cliente']['nombre'] }}</p>
    </div>

</body>
</html>