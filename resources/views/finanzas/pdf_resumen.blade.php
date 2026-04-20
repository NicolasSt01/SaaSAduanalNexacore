<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resumen Semana {{ $semana }} - {{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #007bff;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .stats {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .stat-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin: 0 5px;
        }
        .stat-box h2 {
            margin: 0;
            font-size: 32px;
            color: #007bff;
        }
        .stat-box p {
            margin: 5px 0 0 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th {
            background-color: #007bff;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>📊 Resumen de Operaciones - Finanzas</h1>
        <p><strong>Semana {{ $semana }} del {{ $year }}</strong></p>
        <p>{{ $fechaInicio->format('d/m/Y') }} - {{ $fechaFin->format('d/m/Y') }}</p>
    </div>

    <!-- Estadísticas -->
    <div class="stats">
        <div class="stat-box">
            <h2>{{ $resumen->count() }}</h2>
            <p>Clientes-Patentes</p>
        </div>
        <div class="stat-box" style="margin: 0 10px;">
            <h2>{{ $resumen->sum('total_tramites') }}</h2>
            <p>Total Trámites</p>
        </div>
        <div class="stat-box">
            <h2>{{ $resumen->sum('rojos') }}</h2>
            <p>Trámites Rojos</p>
        </div>
    </div>

    <!-- Tabla de Datos -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Patente</th>
                <th style="text-align: center;">Total Trámites</th>
                <th style="text-align: center;">Rojos</th>
                <th style="text-align: center;">% Rojos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resumen as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $item['cliente_nombre'] }}</strong></td>
                    <td>{{ $item['patente_numero'] }}</td>
                    <td style="text-align: center;">{{ $item['total_tramites'] }}</td>
                    <td style="text-align: center;">
                        <span class="badge-danger">{{ $item['rojos'] }}</span>
                    </td>
                    <td style="text-align: center;">
                        {{ $item['total_tramites'] > 0 ? round(($item['rojos'] / $item['total_tramites']) * 100, 1) : 0 }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #e9ecef; font-weight: bold;">
                <td colspan="3" style="text-align: right; padding: 10px;">TOTALES:</td>
                <td style="text-align: center;">{{ $resumen->sum('total_tramites') }}</td>
                <td style="text-align: center;">
                    <span class="badge-danger">{{ $resumen->sum('rojos') }}</span>
                </td>
                <td style="text-align: center;">
                    {{ $resumen->sum('total_tramites') > 0 ? round(($resumen->sum('rojos') / $resumen->sum('total_tramites')) * 100, 1) : 0 }}%
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