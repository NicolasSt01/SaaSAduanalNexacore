<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estatus Aduanal</title>
    <style>
        body { font-family: 'Georgia', serif; background-color: #111827; margin: 0; padding: 0; color: #e5e7eb; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #111827; padding: 40px 0; }
        .main { background-color: #1f2937; margin: 0 auto; width: 100%; max-width: 600px; border: 1px solid #374151; border-top: 4px solid #818cf8; }
        .header { padding: 40px 30px; text-align: center; border-bottom: 1px solid #374151; }
        .header-subtitle { font-size: 12px; letter-spacing: 2px; text-transform: uppercase; color: #9ca3af; margin-bottom: 10px; font-family: sans-serif; }
        .title { font-size: 26px; font-weight: normal; margin: 0; color: #f3f4f6; }
        .content { padding: 40px 30px; }
        .status-container { background-color: #111827; padding: 20px; text-align: center; border-radius: 4px; margin-bottom: 30px; border: 1px solid #374151; }
        .status-label { font-size: 11px; text-transform: uppercase; color: #9ca3af; letter-spacing: 1px; margin-bottom: 5px; font-family: sans-serif; }
        .status-text { font-size: 18px; font-weight: bold; letter-spacing: 1px; }
        .text-green { color: #34d399; }
        .text-red { color: #f87171; }
        .greeting { font-size: 15px; line-height: 1.8; color: #d1d5db; margin-bottom: 30px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table td { padding: 15px 0; border-bottom: 1px solid #374151; font-family: sans-serif; }
        .data-label { color: #9ca3af; font-size: 13px; text-transform: uppercase; width: 40%; }
        .data-value { color: #f3f4f6; font-size: 14px; font-weight: bold; text-align: right; }
        .separator { height: 30px; }
        .farewell { font-size: 14px; color: #9ca3af; line-height: 1.8; font-style: italic; }
        .footer { padding: 30px; text-align: center; background-color: #0f172a; border-top: 1px solid #1e293b; color: #64748b; font-size: 11px; font-family: sans-serif; }
        .footer a { color: #818cf8; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main">
            <tr>
                <td class="header">
                    <div class="header-subtitle">Aviso Operativo | {{ $tenant_empresa }}</div>
                    <h1 class="title">Resolución Aduanal</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    @php
                        $isLibre = stripos($estatus, 'LIBRE') !== false;
                        $colorClass = $isLibre ? 'text-green' : 'text-red';
                    @endphp

                    <div class="status-container">
                        <div class="status-label">Resultado de Selección Automatizada</div>
                        <div class="status-text {{ $colorClass }}">{{ mb_strtoupper($estatus) }}</div>
                    </div>

                    <div class="greeting">
                        Estimado/a <strong>{{ $contacto_nombre }}</strong>, representante de <strong>{{ $contacto_cliente }}</strong>:<br><br>
                        Nos ponemos en contacto para confirmarle que su operación ha sido procesada de manera exitosa. A continuación, el desglose de los identificadores asociados al trámite.
                    </div>

                    <table class="data-table">
                        <tr>
                            <td class="data-label">Pedimento</td>
                            <td class="data-value">{{ $operacion->expediente->numero_pedimento ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Factura Comercial</td>
                            <td class="data-value">{{ $operacion->factura ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Clase de Producto</td>
                            <td class="data-value">{{ $operacion->nombre_producto ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Puerto / Aduana</td>
                            <td class="data-value">{{ $operacion->aduana->nombre ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Registro Temporal</td>
                            <td class="data-value">{{ $operacion->fecha_pago ?? date('Y-m-d H:i') }}</td>
                        </tr>
                    </table>

                    <div class="separator"></div>

                    <div class="farewell">
                        Agradecemos su confianza en nuestros servicios logísticos y aduanales de primer nivel.<br><br>
                        Cordialmente,<br>
                        <span style="color: #f3f4f6; font-style: normal;">{{ $tenant_empresa }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    &copy; {{ date('Y') }} {{ $tenant_empresa }}. Todos los derechos reservados.<br><br>
                    Plataforma operativa proporcionada por <strong>NexaCore Aduanal</strong><br>
                    Visite <a href="https://nexacore.com.mx">nexacore.com.mx</a>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
