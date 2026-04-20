<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detalle {{ $cliente->nombre_empresa }} - Patente {{ $patente->numero_patente }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #007bff;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
            border-radius: 3px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 16px;
        }
        .info-box p {
            margin: 5px 0;
        }
        .stats {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .stat-box h2 {
            margin: 0;
            font-size: 28px;
            color: #007bff;
        }
        .stat-box p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th {
            background-color: #007bff;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        table td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 9px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>📋 Detalle de Operaciones por Expedientes</h1>
        <p><strong>Semana {{ $semana }} del {{ $year }}</strong></p>
        <p>{{ $fechaInicio->format('d/m/Y') }} - {{ $fechaFin->format('d/m/Y') }}</p>
    </div>

    <!-- Información del Cliente y Patente -->
    <div class="info-box">
        <h3>🏢 {{ $cliente->nombre_empresa }}</h3>
        <p><strong>Patente:</strong> {{ $patente->numero_patente }}</p>
        @if($cliente->rfc)
            <p><strong>RFC:</strong> {{ $cliente->rfc }}</p>
        @endif
    </div>

    <!-- Estadísticas Generales -->
    <div class="stats">
        <div class="stat-box">
            <h2>{{ $expedientes->count() }}</h2>
            <p>Expedientes</p>
        </div>
        <div class="stat-box" style="margin: 0 8px;">
            <h2>{{ $expedientes->sum('total_tramites') }}</h2>
            <p>Total Trámites</p>
        </div>
        <div class="stat-box">
            <h2>{{ $expedientes->sum('rojos') }}</h2>
            <p>Trámites Rojos</p>
        </div>
    </div>

    <!-- Tabla de Expedientes -->
    <h3 style="color: #007bff; margin-top: 25px;">Expedientes Detallados</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Expediente</th>
                <th>Fecha Apertura</th>
                <th>Fecha Cierre</th>
                <th style="text-align: center;">Total Trámites</th>
                <th style="text-align: center;">Rojos</th>
                <th style="text-align: center;">% Rojos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expedientes as $index => $exp)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $exp['expediente_numero'] }}</strong></td>
                    <td>
                        @if($exp['fecha_apertura'])
                            <span class="badge-success">
                                {{ \Carbon\Carbon::parse($exp['fecha_apertura'])->format('d/m/Y') }}
                            </span>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($exp['fecha_cierre'])
                            {{ \Carbon\Carbon::parse($exp['fecha_cierre'])->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $exp['total_tramites'] }}</td>
                    <td style="text-align: center;">
                        <span class="badge-danger">{{ $exp['rojos'] }}</span>
                    </td>
                    <td style="text-align: center;">
                        {{ $exp['total_tramites'] > 0 ? round(($exp['rojos'] / $exp['total_tramites']) * 100, 1) : 0 }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #e9ecef; font-weight: bold;">
                <td colspan="4" style="text-align: right; padding: 8px;">TOTALES:</td>
                <td style="text-align: center;">{{ $expedientes->sum('total_tramites') }}</td>
                <td style="text-align: center;">
                    <span class="badge-danger">{{ $expedientes->sum('rojos') }}</span>
                </td>
                <td style="text-align: center;">
                    {{ $expedientes->sum('total_tramites') > 0 ? round(($expedientes->sum('rojos') / $expedientes->sum('total_tramites')) * 100, 1) : 0 }}%
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Sistema de Gestión de Operaciones - Departamento de Finanzas</p>
    </div>
</body>
</html>