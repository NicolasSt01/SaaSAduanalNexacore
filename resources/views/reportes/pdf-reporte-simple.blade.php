<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte - {{ $datos['cliente']['nombre'] }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
            margin: 20mm;
        }
        h1 { font-size: 18pt; color: #667eea; margin-bottom: 5px; }
        h2 { font-size: 14pt; color: #667eea; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #667eea; padding-bottom: 5px; }
        .header { margin-bottom: 20px; }
        .info-box { background: #f8f9fa; padding: 10px; border-left: 4px solid #667eea; margin-bottom: 15px; }
        .stats-grid { width: 100%; margin-bottom: 20px; }
        .stats-grid td { width: 25%; text-align: center; padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; }
        .stat-number { font-size: 20pt; font-weight: bold; display: block; }
        .stat-label { font-size: 8pt; color: #6c757d; text-transform: uppercase; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-primary { color: #007bff; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #667eea; color: white; padding: 8px; text-align: left; font-size: 9pt; }
        td { padding: 6px 8px; border-bottom: 1px solid #dee2e6; font-size: 9pt; }
        .page-break { page-break-after: always; }
        .small { font-size: 8pt; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Operaciones</h1>
        <p><strong>Cliente:</strong> {{ $datos['cliente']['nombre'] }}</p>
        <p><strong>Período:</strong> {{ $datos['periodo']['desde'] }} al {{ $datos['periodo']['hasta'] }}</p>
    </div>

    <h2>Resumen General</h2>
    <table class="stats-grid">
        <tr>
            <td>
                <span class="stat-number text-primary">{{ $datos['estadisticas']['total'] }}</span>
                <span class="stat-label">Total Operaciones</span>
            </td>
            <td>
                <span class="stat-number text-success">{{ $datos['estadisticas']['greens'] }}</span>
                <span class="stat-label">Desaduanamiento Libre</span>
            </td>
            <td>
                <span class="stat-number text-danger">{{ $datos['estadisticas']['reds'] }}</span>
                <span class="stat-label">Reconocimiento Aduanero</span>
            </td>
            <td>
                <span class="stat-number">{{ $datos['estadisticas']['total'] > 0 ? round(($datos['estadisticas']['greens'] / $datos['estadisticas']['total']) * 100, 1) : 0 }}%</span>
                <span class="stat-label">Tasa de Éxito</span>
            </td>
        </tr>
    </table>

    @if(!empty($datos['porAduana']))
    <h2>Distribución por Aduana</h2>
    <table>
        <thead>
            <tr>
                <th>Aduana</th>
                <th style="text-align: right;">Total</th>
                <th style="text-align: right;">Verdes</th>
                <th style="text-align: right;">Rojos</th>
                <th style="text-align: right;">% Éxito</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos['porAduana'] as $aduana)
                @php
                    $verdes = collect($datos['verdesPorAduana'])->firstWhere('aduana', $aduana['nombre'])['total'] ?? 0;
                    $rojos = collect($datos['rojosPorAduana'])->firstWhere('aduana', $aduana['nombre'])['total'] ?? 0;
                    $porcentaje = $aduana['total'] > 0 ? round(($verdes / $aduana['total']) * 100, 1) : 0;
                @endphp
                <tr>
                    <td>{{ $aduana['nombre'] }}</td>
                    <td style="text-align: right;"><strong>{{ $aduana['total'] }}</strong></td>
                    <td style="text-align: right; color: #28a745;">{{ $verdes }}</td>
                    <td style="text-align: right; color: #dc3545;">{{ $rojos }}</td>
                    <td style="text-align: right;"><strong>{{ $porcentaje }}%</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(!empty($datos['historialMeses']))
    <div class="page-break"></div>
    <h2>Histórico Mensual ({{ date('Y') }})</h2>
    <table>
        <thead>
            <tr>
                @foreach(['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $mes)
                    <th style="text-align: center;">{{ $mes }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @for($i = 1; $i <= 12; $i++)
                    <td style="text-align: center;"><strong>{{ $datos['historialMeses'][$i] ?? 0 }}</strong></td>
                @endfor
            </tr>
        </tbody>
    </table>
    @endif

    @if(!empty($datos['calendario']))
    <h2>Calendario de Operaciones</h2>
    <p class="small">Operaciones por día del mes seleccionado</p>
    <table>
        <thead>
            <tr>
                <th style="text-align: center;">Lun</th>
                <th style="text-align: center;">Mar</th>
                <th style="text-align: center;">Mié</th>
                <th style="text-align: center;">Jue</th>
                <th style="text-align: center;">Vie</th>
                <th style="text-align: center;">Sáb</th>
                <th style="text-align: center;">Dom</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos['calendario'] as $semana)
            <tr>
                @foreach($semana as $dia)
                    <td style="text-align: center; {{ !$dia['actual'] ? 'color: #ccc;' : '' }}">
                        <strong>{{ $dia['dia'] }}</strong>
                        @if($dia['actual'] && $dia['total'] > 0)
                            <br><span style="color: #007bff; font-size: 8pt;">{{ $dia['total'] }}</span>
                        @endif
                    </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div style="margin-top: 30px; text-align: center; font-size: 8pt; color: #6c757d;">
        Generado el {{ now()->format('d/m/Y H:i') }} | NexaCore Aduanal
    </div>
</body>
</html>
