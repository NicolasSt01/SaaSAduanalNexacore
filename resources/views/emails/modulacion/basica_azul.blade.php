<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Actualización de Operación</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f7f6; padding-bottom: 40px; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-spacing: 0; color: #333333; border: 1px solid #e1e8ed; border-top: 5px solid #2196F3; }
        .header { padding: 30px; border-bottom: 1px solid #eeeeee; }
        .header-text { font-size: 16px; color: #666666; font-weight: bold; }
        .title { font-size: 24px; font-weight: bold; margin-top: 10px; color: #1e88e5; }
        .content { padding: 30px; }
        .status-box { padding: 15px; text-align: center; font-weight: bold; font-size: 18px; margin-bottom: 25px; border-radius: 4px; }
        .status-ok { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .status-error { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .greeting { font-size: 16px; margin-bottom: 20px; line-height: 1.5; color: #424242; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th { background-color: #f5f5f5; border: 1px solid #dddddd; padding: 12px; text-align: left; font-size: 14px; width: 35%; color: #666666;}
        .table td { border: 1px solid #dddddd; padding: 12px; font-size: 14px; font-weight: bold; color: #222222; }
        .farewell { margin-top: 30px; font-size: 14px; line-height: 1.5; color: #666666; }
        .footer { background-color: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eeeeee; font-size: 12px; color: #999999; }
    </style>
</head>
<body>
    <div class="wrapper">
        <br>
        <table class="main" width="100%">
            <tr>
                <td class="header">
                    <div class="header-text">NexaCore Aduanal <span style="color: #cccccc;">|</span> {{ $tenant_empresa }}</div>
                    <div class="title">Actualización de Operación</div>
                </td>
            </tr>
            <tr>
                <td class="content">
                    @php
                        $isLibre = stripos($estatus, 'LIBRE') !== false;
                        $statusClass = $isLibre ? 'status-ok' : 'status-error';
                    @endphp
                    
                    <div class="status-box {{ $statusClass }}">
                        ESTATUS: {{ mb_strtoupper($estatus) }}
                    </div>

                    <div class="greeting">
                        Estimado(a) <strong>{{ $contacto_nombre }}</strong> de la empresa <strong>{{ $contacto_cliente }}</strong>,<br><br>
                        Le informamos que su trámite ha sido procesado exitosamente por el Sistema Automatizado. A continuación, los detalles de su operación:
                    </div>

                    <table class="table">
                        <tr>
                            <th>Folio Factura</th>
                            <td>{{ $operacion->factura ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Producto</th>
                            <td>{{ $operacion->nombre_producto ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Pedimento Asignado</th>
                            <td>{{ $operacion->expediente->numero_pedimento ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Aduana de Cruce</th>
                            <td>{{ $operacion->aduana->nombre ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Fecha de Modulación</th>
                            <td>{{ $operacion->fecha_pago ?? date('Y-m-d H:i') }}</td>
                        </tr>
                    </table>

                    <div class="farewell">
                        Agradecemos su confianza en nuestros servicios.<br><br>
                        Atentamente,<br>
                        <strong>{{ $tenant_empresa }}</strong>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    &copy; {{ date('Y') }} {{ $tenant_empresa }}. Todos los derechos reservados.<br>
                    Impulsado por <strong>NexaCore Aduanal</strong> | <a href="https://nexacore.com.mx" style="color: #2196F3; text-decoration: none;">nexacore.com.mx</a>
                </td>
            </tr>
        </table>
        <br>
    </div>
</body>
</html>
