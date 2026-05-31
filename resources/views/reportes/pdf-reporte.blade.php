<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte - {{ $datos['cliente']['nombre'] }}</title>
    <style>
        @page { margin: 15mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #1a1a2e; }

        .header-border { border-bottom: 2px solid #1e3a5f; padding-bottom: 8px; margin-bottom: 12px; }
        .company-name { font-size: 12pt; font-weight: bold; color: #1e3a5f; }
        .report-type { font-size: 9pt; color: #555; }
        .client-name { font-size: 14pt; font-weight: bold; }
        .meta-row { font-size: 7.5pt; color: #777; }

        h2 { font-size: 10pt; font-weight: bold; color: #1e3a5f; border-bottom: 1px solid #d0d7e2; padding-bottom: 3px; margin-top: 12px; margin-bottom: 6px; }
        h2.pb { page-break-before: always; }

        table.stats { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.stats td { width: 20%; padding: 6px; border: 1px solid #d0d7e2; background: #f8f9fb; text-align: center; vertical-align: middle; }
        .stat-num { font-size: 16pt; font-weight: bold; }
        .stat-lbl { font-size: 6.5pt; color: #555; text-transform: uppercase; }
        .blue { color: #1e3a5f; } .green { color: #1a7a3a; } .red { color: #b91c1c; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 7.5pt; }
        table.data th { background: #1e3a5f; color: #fff; padding: 4px 5px; text-align: left; font-size: 7pt; }
        table.data td { padding: 3px 5px; border-bottom: 1px solid #e8ecf1; }
        table.data tr:nth-child(even) td { background: #f8f9fb; }
        .right { text-align: right; } .center { text-align: center; }

        table.cal { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.cal th { background: #1e3a5f; color: #fff; padding: 3px; font-size: 6.5pt; text-align: center; }
        table.cal td { text-align: center; padding: 4px 2px; border: 1px solid #d0d7e2; font-size: 7pt; height: 28px; vertical-align: middle; }
        table.cal .out { background: #f5f5f5; color: #bbb; }
        table.cal .has { background: #e8f0fe; font-weight: bold; }
        table.cal .many { background: #1e3a5f; color: #fff; font-weight: bold; }

        .chart-wrap { text-align: center; margin: 6px 0; page-break-inside: avoid; }
        .chart-wrap svg { max-width: 100%; }

        .bar-outer { width: 100%; background: #e8ecf1; height: 8px; margin: 4px 0; }
        .bar-inner { height: 8px; background: #1a7a3a; }

        table.heat { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.heat td { width: 14.28%; padding: 5px 3px; text-align: center; font-size: 7pt; border: 1px solid #d0d7e2; vertical-align: middle; }
        .hg { background: #d4edda; } .ha { background: #fff3cd; } .hr { background: #f8d7da; }

        .footer { margin-top: 16px; border-top: 1px solid #d0d7e2; padding-top: 6px; text-align: center; font-size: 6.5pt; color: #999; }

        .badge { padding: 1px 5px; border-radius: 4px; font-size: 6.5pt; font-weight: bold; }
        .bg { background: #d4edda; color: #1a7a3a; } .br { background: #f8d7da; color: #b91c1c; }

        .section { page-break-inside: avoid; }
    </style>
</head>
<body>

<div class="header-border">
    <table style="width:100%;border:none;"><tr>
        <td style="width:60px;border:none;vertical-align:middle;">
            <div style="width:50px;height:50px;border:2px solid #1e3a5f;text-align:center;font-size:22pt;font-weight:bold;color:#1e3a5f;line-height:50px;">N</div>
        </td>
        <td style="border:none;vertical-align:middle;">
            <div class="company-name">NexaCore Aduanal</div>
            <div class="report-type">Reporte de An&aacute;lisis Operativo</div>
            <div class="client-name">{{ $datos['cliente']['nombre'] }}</div>
            <div class="meta-row">
                <b>Periodo:</b> {{ \Carbon\Carbon::parse($datos['periodo']['desde'])->format('d/m/Y') }} &mdash; {{ \Carbon\Carbon::parse($datos['periodo']['hasta'])->format('d/m/Y') }}
                &nbsp;|&nbsp; <b>Generado:</b> {{ now()->format('d/m/Y H:i') }} CST
            </div>
        </td>
    </tr></table>
</div>

@php
    $tasaExito = $datos['estadisticas']['total'] > 0 ? round(($datos['estadisticas']['greens'] / $datos['estadisticas']['total']) * 100, 1) : 0;
    $tasaRojo = $datos['estadisticas']['total'] > 0 ? round(($datos['estadisticas']['reds'] / $datos['estadisticas']['total']) * 100, 1) : 0;
@endphp

<div class="section">
    <h2>Resumen General de Operaciones</h2>
    <table class="stats">
        <tr>
            <td><span class="stat-num blue">{{ $datos['estadisticas']['total'] }}</span><br><span class="stat-lbl">Total Operaciones</span></td>
            <td><span class="stat-num green">{{ $datos['estadisticas']['greens'] }}</span><br><span class="stat-lbl">Desaduanamiento Libre</span></td>
            <td><span class="stat-num red">{{ $datos['estadisticas']['reds'] }}</span><br><span class="stat-lbl">Reconocimiento Aduanero</span></td>
            <td><span class="stat-num blue">{{ $tasaExito }}%</span><br><span class="stat-lbl">Tasa de &Eacute;xito</span></td>
            <td><span class="stat-num red">{{ $tasaRojo }}%</span><br><span class="stat-lbl">Tasa Reconocimiento</span></td>
        </tr>
    </table>
    @if(!empty($charts['greensReds']))
    <div class="chart-wrap"><img src="{{ $charts['greensReds'] }}" width="300"></div>
    @endif
</div>

@if(!empty($datos['porAduana']))
<div class="section">
    <h2>Distribuci&oacute;n por Aduana</h2>
    @if(!empty($charts['aduanas']))
    <div class="chart-wrap"><img src="{{ $charts['aduanas'] }}" width="340"></div>
    @endif
    <table class="data">
        <thead><tr><th style="width:8%;">#</th><th>Aduana</th><th class="right" style="width:12%;">Total</th><th class="right" style="width:12%;">Verdes</th><th class="right" style="width:12%;">Rojos</th><th class="right" style="width:14%;">% &Eacute;xito</th></tr></thead>
        <tbody>
            @foreach($datos['porAduana'] as $a)
            @php $v = (int)(collect($datos['verdesPorAduana'] ?? [])->firstWhere('aduana', $a['nombre'])['total'] ?? 0); $r = (int)(collect($datos['rojosPorAduana'] ?? [])->firstWhere('aduana', $a['nombre'])['total'] ?? 0); $pct = $a['total'] > 0 ? round(($v / $a['total']) * 100, 1) : 0; @endphp
            <tr>
                <td class="center" style="font-weight:bold;color:#1e3a5f;">{{ $loop->iteration }}</td>
                <td>{{ $a['nombre'] }}</td>
                <td class="right" style="font-weight:bold;">{{ $a['total'] }}</td>
                <td class="right" style="color:#1a7a3a;">{{ $v }}</td>
                <td class="right" style="color:#b91c1c;">{{ $r }}</td>
                <td class="right" style="font-weight:bold;">{{ $pct }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(!empty($datos['historialMeses']))
<div class="section">
    <h2 class="pb">Comportamiento Hist&oacute;rico {{ date('Y') }}</h2>
    @if(!empty($charts['historico']))
    <div class="chart-wrap"><img src="{{ $charts['historico'] }}" width="520"></div>
    @endif
    <table class="data">
        <thead><tr>@foreach(['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'] as $m)<th class="center">{{ $m }}</th>@endforeach</tr></thead>
        <tbody><tr>@for($i = 1; $i <= 12; $i++)<td class="center" style="font-weight:bold;{{ ($datos['historialMeses'][$i] ?? 0) > 0 ? 'color:#1e3a5f;' : 'color:#ccc;' }}">{{ $datos['historialMeses'][$i] ?? 0 }}</td>@endfor</tr></tbody>
    </table>
</div>
@endif

@if(!empty($datos['calendario']))
<div class="section">
    <h2 class="pb">Calendario de Actividad</h2>
    @php $primerDiaActual = collect($datos['calendario'])->flatten(1)->firstWhere('actual', true); $nombreMes = $primerDiaActual ? ucfirst(\Carbon\Carbon::parse($primerDiaActual['fecha'])->locale('es')->isoFormat('MMMM YYYY')) : 'Calendario'; @endphp
    <p style="text-align:center;font-weight:bold;color:#1e3a5f;margin-bottom:6px;">{{ $nombreMes }}</p>
    <table class="cal">
        <thead><tr><th>Lun</th><th>Mar</th><th>Mi&eacute;</th><th>Jue</th><th>Vie</th><th>S&aacute;b</th><th>Dom</th></tr></thead>
        <tbody>
            @foreach($datos['calendario'] as $semana)<tr>
            @foreach($semana as $dia)@php $t = $dia['total'] ?? 0; $cls = !$dia['actual'] ? 'out' : ($t >= 5 ? 'many' : ($t > 0 ? 'has' : '')); @endphp
            <td class="{{ $cls }}">{{ $dia['dia'] }}@if($dia['actual'] && $t > 0)<br><strong>{{ $t }}</strong>@endif</td>
            @endforeach</tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(!empty($datos['porPatente']))
<div class="section">
    <h2>Distribuci&oacute;n por Patente Aduanal</h2>
    @if(!empty($charts['patentes']))
    <div class="chart-wrap"><img src="{{ $charts['patentes'] }}" width="460"></div>
    @endif
    <table class="data">
        <thead><tr><th style="width:6%;">#</th><th>Patente / Agente Aduanal</th><th class="right" style="width:12%;">Total</th><th class="right" style="width:12%;">Verdes</th><th class="right" style="width:12%;">Rojos</th><th class="right" style="width:14%;">% &Eacute;xito</th></tr></thead>
        <tbody>
            @foreach($datos['porPatente'] as $p)
            @php $pv = (int)($datos['verdesPorPatente'][$p['nombre']] ?? 0); $pr = (int)($datos['rojosPorPatente'][$p['nombre']] ?? 0); $ppct = $p['total'] > 0 ? round(($pv / $p['total']) * 100, 1) : 0; @endphp
            <tr>
                <td class="center" style="font-weight:bold;color:#1e3a5f;">{{ $loop->iteration }}</td>
                <td>{{ $p['nombre'] }}<br><span style="font-size:6.5pt;color:#999;">Pat. {{ $p['numero'] }}</span></td>
                <td class="right" style="font-weight:bold;">{{ $p['total'] }}</td>
                <td class="right"><span class="badge bg">{{ $pv }}</span></td>
                <td class="right"><span class="badge br">{{ $pr }}</span></td>
                <td class="right" style="font-weight:bold;">{{ $ppct }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(!empty($datos['topImportadores']))
<div class="section">
    <h2>Top Importadores</h2>
    @if(!empty($charts['importadores']))
    <div class="chart-wrap"><img src="{{ $charts['importadores'] }}" width="420"></div>
    @endif
    <table class="data">
        <thead><tr><th style="width:6%;">#</th><th>Importador</th><th class="right" style="width:14%;">Operaciones</th><th class="right" style="width:14%;">Participaci&oacute;n</th></tr></thead>
        <tbody>
            @foreach($datos['topImportadores'] as $imp)
            @php $pct = $datos['estadisticas']['total'] > 0 ? round(($imp['total'] / $datos['estadisticas']['total']) * 100, 1) : 0; @endphp
            <tr>
                <td class="center" style="font-weight:bold;color:#1e3a5f;">{{ $loop->iteration }}</td>
                <td>{{ $imp['importador'] }}</td>
                <td class="right" style="font-weight:bold;">{{ $imp['total'] }}</td>
                <td class="right">{{ $pct }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(!empty($datos['porBodega']))
<div class="section">
    <h2>Distribuci&oacute;n por Bodega</h2>
    @if(!empty($charts['bodegas']))
    <div class="chart-wrap"><img src="{{ $charts['bodegas'] }}" width="320"></div>
    @endif
    <table class="data">
        <thead><tr><th style="width:6%;">#</th><th>Bodega</th><th class="right" style="width:14%;">Operaciones</th><th class="right" style="width:14%;">%</th></tr></thead>
        <tbody>
            @foreach($datos['porBodega'] as $b)
            @php $bpct = $datos['estadisticas']['total'] > 0 ? round(($b['total'] / $datos['estadisticas']['total']) * 100, 1) : 0; @endphp
            <tr><td class="center" style="font-weight:bold;color:#1e3a5f;">{{ $loop->iteration }}</td><td>{{ $b['nombre'] }}</td><td class="right" style="font-weight:bold;">{{ $b['total'] }}</td><td class="right">{{ $bpct }}%</td></tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(!empty($datos['completitudDocs']))
<div class="section">
    <h2>Completitud Documental (Art. 36-A)</h2>
    <table class="stats">
        <tr>
            <td><span class="stat-num blue">{{ $datos['completitudDocs']['total_operaciones'] }}</span><br><span class="stat-lbl">Total Evaluadas</span></td>
            <td><span class="stat-num green">{{ $datos['completitudDocs']['completas'] }}</span><br><span class="stat-lbl">Completas</span></td>
            <td><span class="stat-num red">{{ $datos['completitudDocs']['incompletas'] }}</span><br><span class="stat-lbl">Incompletas</span></td>
            <td><span class="stat-num blue">{{ $datos['completitudDocs']['promedio_docs'] }}</span><br><span class="stat-lbl">Prom. Docs/Op.</span></td>
            <td><span class="stat-num green">{{ $datos['completitudDocs']['porcentaje_completas'] }}%</span><br><span class="stat-lbl">Tasa Completitud</span></td>
        </tr>
    </table>
    <div class="bar-outer"><div class="bar-inner" style="width:{{ $datos['completitudDocs']['porcentaje_completas'] }}%;"></div></div>
    <p style="font-size:7pt;color:#999;text-align:center;margin-top:3px;">Documentos requeridos: Factura Comercial, Encargo Conferido, Documentos de Transporte, Lista de Empaque</p>
</div>
@endif

<div class="section">
    <h2 class="pb">Tendencia de Modulaci&oacute;n {{ date('Y') }}</h2>
    @if(!empty($charts['tendencia']))
    <div class="chart-wrap"><img src="{{ $charts['tendencia'] }}" width="520"></div>
    @endif
    <table class="data">
        <thead><tr><th>Mes</th><th class="right">Verdes</th><th class="right">Rojos</th><th class="right">Total</th><th class="right">% &Eacute;xito</th></tr></thead>
        <tbody>
            @foreach(['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'] as $i => $m)
            @php $tv = $datos['tendenciaVerdes'][$i] ?? 0; $tr = $datos['tendenciaRojos'][$i] ?? 0; $tt = $tv + $tr; $tp = $tt > 0 ? round(($tv / $tt) * 100, 1) : 0; @endphp
            <tr><td style="font-weight:bold;">{{ $m }}</td><td class="right" style="color:#1a7a3a;">{{ $tv }}</td><td class="right" style="color:#b91c1c;">{{ $tr }}</td><td class="right" style="font-weight:bold;">{{ $tt }}</td><td class="right">{{ $tp }}%</td></tr>
            @endforeach
        </tbody>
    </table>
</div>

@if(!empty($datos['heatmap']))
<div class="section">
    <h2>Efectividad por D&iacute;a de la Semana</h2>
    <table class="heat">
        <tr>
            @foreach($datos['heatmap'] as $h)
            <td class="{{ $h['porcentaje_verde'] >= 70 ? 'hg' : ($h['porcentaje_verde'] >= 40 ? 'ha' : 'hr') }}">
                <strong style="font-size:9pt;">{{ $h['dia'] }}</strong><br>
                <span style="font-size:10pt;font-weight:bold;">{{ $h['porcentaje_verde'] }}%</span><br>
                <span style="font-size:6pt;">{{ $h['total'] }} ops</span><br>
                <span style="font-size:6pt;">{{ $h['verdes'] }}V / {{ $h['rojos'] }}R</span>
            </td>
            @endforeach
        </tr>
    </table>
</div>
@endif

<div class="section">
    <h2>Predicci&oacute;n de Volumen (Pr&oacute;ximo Mes)</h2>
    <table class="stats">
        <tr>
            <td style="width:100%;"><span class="stat-num blue">{{ $datos['prediccionProximoMes'] }}</span><br><span class="stat-lbl">Operaciones Estimadas para el Pr&oacute;ximo Mes</span></td>
        </tr>
    </table>
    <p style="font-size:7pt;color:#999;text-align:center;margin-top:3px;">Metodolog&iacute;a: Promedio m&oacute;vil de &uacute;ltimos 3 meses + tendencia lineal del per&iacute;odo.</p>
</div>

<div class="footer">NexaCore Aduanal &mdash; Reporte generado el {{ now()->format('d/m/Y H:i') }} CST &mdash; Confidencial</div>

</body>
</html>