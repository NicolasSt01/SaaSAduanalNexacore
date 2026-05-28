<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Pedimentos</title>
    <style>
        @page { margin: 15mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #1a1a2e; }

        .header-border { border-bottom: 2px solid #2563eb; padding-bottom: 8px; margin-bottom: 12px; }
        .company-name { font-size: 12pt; font-weight: bold; color: #2563eb; }
        .report-type { font-size: 9pt; color: #555; }

        h2 { font-size: 10pt; font-weight: bold; color: #2563eb; border-bottom: 1px solid #d0d7e2; padding-bottom: 3px; margin-top: 16px; margin-bottom: 8px; }

        table.stats { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.stats td { width: 25%; padding: 6px; border: 1px solid #d0d7e2; background: #f8f9fb; text-align: center; vertical-align: middle; }
        .stat-num { font-size: 16pt; font-weight: bold; }
        .stat-lbl { font-size: 6.5pt; color: #555; text-transform: uppercase; }
        .blue { color: #2563eb; } .green { color: #16a34a; } .amber { color: #d97706; } .red { color: #dc2626; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 7.5pt; }
        table.data th { background: #2563eb; color: #fff; padding: 4px 5px; text-align: left; font-size: 7pt; }
        table.data td { padding: 3px 5px; border-bottom: 1px solid #e8ecf1; }
        table.data tr:nth-child(even) td { background: #f8f9fb; }
        .center { text-align: center; }

        .badge { padding: 1px 5px; border-radius: 4px; font-size: 6.5pt; font-weight: bold; }
        .bg-green { background: #dcfce7; color: #16a34a; }
        .bg-amber { background: #fef3c7; color: #d97706; }
        .bg-red { background: #fee2e2; color: #dc2626; }
        .bg-sky { background: #e0f2fe; color: #0284c7; }

        .footer { margin-top: 16px; border-top: 1px solid #d0d7e2; padding-top: 6px; text-align: center; font-size: 6.5pt; color: #999; }

        .filtros { font-size: 7pt; color: #777; margin-bottom: 8px; }
    </style>
</head>
<body>

<div class="header-border">
    <table style="width:100%;border:none;"><tr>
        <td style="width:60px;border:none;vertical-align:middle;">
            <div style="width:50px;height:50px;border:2px solid #2563eb;text-align:center;font-size:22pt;font-weight:bold;color:#2563eb;line-height:50px;">N</div>
        </td>
        <td style="border:none;vertical-align:middle;">
            <div class="company-name">NexaCore Aduanal</div>
            <div class="report-type">Reporte de Pedimentos</div>
            <div class="filtros">
                @if($datos['filtros']['desde'] || $datos['filtros']['hasta'])
                    <b>Periodo:</b> {{ $datos['filtros']['desde'] ? \Carbon\Carbon::parse($datos['filtros']['desde'])->format('d/m/Y') : 'Inicio' }} &mdash; {{ $datos['filtros']['hasta'] ? \Carbon\Carbon::parse($datos['filtros']['hasta'])->format('d/m/Y') : 'Actual' }}
                @endif
                @if($datos['filtros']['estado']) | <b>Estado:</b> {{ $datos['filtros']['estado'] }} @endif
                @if($datos['filtros']['categoria']) | <b>Categor&iacute;a:</b> {{ $datos['filtros']['categoria'] }} @endif
                @if($datos['filtros']['numero_pedimento']) | <b>Pedimento:</b> {{ $datos['filtros']['numero_pedimento'] }} @endif
                <br><b>Generado:</b> {{ now()->format('d/m/Y H:i') }}
            </div>
        </td>
    </tr></table>
</div>

<h2>Resumen</h2>
<table class="stats">
    <tr>
        <td><span class="stat-num blue">{{ $datos['kpis']['total'] }}</span><br><span class="stat-lbl">Total Pedimentos</span></td>
        <td><span class="stat-num green">{{ $datos['kpis']['cumplidos'] }}</span><br><span class="stat-lbl">Cumplidos</span></td>
        <td><span class="stat-num amber">{{ $datos['kpis']['pendientes'] }}</span><br><span class="stat-lbl">Pendientes</span></td>
        <td><span class="stat-num red">{{ $datos['kpis']['docs_faltantes'] }}</span><br><span class="stat-lbl">Docs Faltantes</span></td>
    </tr>
</table>

<h2>Detalle de Pedimentos</h2>
<table class="data">
    <thead>
        <tr>
            <th style="width:6%;">#</th>
            <th style="width:14%;">Pedimento</th>
            <th style="width:18%;">Cliente</th>
            <th style="width:10%;">Categor&iacute;a</th>
            <th style="width:10%;">Estado</th>
            <th style="width:12%;">Fecha Apertura</th>
            <th style="width:30%;">Docs Faltantes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($datos['pedimentos'] as $p)
        @php
            $catLabel = $p['categoria'] === 'Importacion' ? 'Importaci&oacute;n' : ($p['categoria'] === 'Exportacion' ? 'Exportaci&oacute;n' : $p['categoria']);
            $estadoBadge = match($p['estado']) {
                'Cerrado' => '<span class="badge bg-green">Cerrado</span>',
                'Cancelado' => '<span class="badge bg-red">Cancelado</span>',
                'Abierto' => '<span class="badge bg-sky">Abierto</span>',
                default => '<span class="badge bg-amber">En proceso</span>',
            };
            $docsText = $p['cumplimiento_completo'] ? '<span class="badge bg-green">Completo</span>' : implode(', ', array_slice($p['documentos_pendientes'], 0, 3)) . (count($p['documentos_pendientes']) > 3 ? '...' : '');
        @endphp
        <tr>
            <td class="center" style="font-weight:bold;color:#2563eb;">{{ $loop->iteration }}</td>
            <td style="font-weight:bold;">{{ $p['numero_pedimento'] }}</td>
            <td>{{ $p['cliente'] }}</td>
            <td>{{ $catLabel }}</td>
            <td class="center">{!! $estadoBadge !!}</td>
            <td>{{ $p['fecha_apertura'] }}</td>
            <td>{!! $docsText !!}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">NexaCore Aduanal &mdash; Reporte generado el {{ now()->format('d/m/Y H:i') }} &mdash; Confidencial</div>

</body>
</html>
