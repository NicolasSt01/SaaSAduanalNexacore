<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notificación de Modulación</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0fdf4; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f0fdf4; padding: 50px 0; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-collapse: collapse; }
        .header { background-color: #10b981; color: white; padding: 30px; text-align: center; }
        .header-brand { font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; opacity: 0.9; }
        .title { font-size: 28px; font-weight: 800; margin: 0; }
        .content { padding: 40px; }
        .status-badge { text-align: center; margin-bottom: 30px; }
        .badge { display: inline-block; padding: 10px 20px; border-radius: 999px; font-weight: bold; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-green { background-color: #d1fae5; color: #047857; }
        .badge-red { background-color: #fee2e2; color: #b91c1c; }
        .message { font-size: 16px; color: #374151; line-height: 1.6; margin-bottom: 30px; }
        .details-card { background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #e5e7eb; }
        .detail-row { margin-bottom: 15px; border-bottom: 1px dashed #e5e7eb; padding-bottom: 10px; }
        .detail-row:last-child { margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
        .detail-label { font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: bold; display: block; margin-bottom: 4px; }
        .detail-value { font-size: 16px; color: #111827; font-weight: 600; }
        .footer { background-color: #ffffff; padding: 30px; text-align: center; border-top: 1px solid #f3f4f6; color: #9ca3af; font-size: 13px; }
        .footer-brand { color: #10b981; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main">
            <tr>
                <td class="header">
                    <div class="header-brand">NexaCore Aduanal × {{ $tenant_empresa }}</div>
                    <h1 class="title">Modulación Finalizada</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    @php
                        $isLibre = stripos($estatus, 'LIBRE') !== false;
                        $badgeClass = $isLibre ? 'badge-green' : 'badge-red';
                    @endphp
                    
                    <div class="status-badge">
                        <span class="badge {{ $badgeClass }}">
                            &#x25cf; {{ mb_strtoupper($estatus) }}
                        </span>
                    </div>

                    <div class="message">
                        Hola <strong>{{ $contacto_nombre }}</strong>,<br><br>
                        De parte de {{ $tenant_empresa }}, le informamos a <strong>{{ $contacto_cliente }}</strong> que su trámite ha sido presentado exitosamente ante el mecanismo de selección automatizado. A continuación adjuntamos los datos de referencia:
                    </div>

                    <div class="details-card">
                        <div class="detail-row">
                            <span class="detail-label">Pedimento</span>
                            <span class="detail-value">{{ $operacion->expediente->numero_pedimento ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Producto</span>
                            <span class="detail-value">{{ $operacion->nombre_producto ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Factura / Referencia</span>
                            <span class="detail-value">{{ $operacion->factura ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Aduana</span>
                            <span class="detail-value">{{ $operacion->aduana->nombre ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Fecha del Movimiento</span>
                            <span class="detail-value">{{ $operacion->fecha_pago ?? date('Y-m-d H:i') }}</span>
                        </div>
                    </div>

                    <div class="message" style="margin-top: 30px; margin-bottom: 0;">
                        Agradecemos su confianza en nuestros servicios logísticos y aduanales.<br><br>
                        <strong>{{ $tenant_empresa }}</strong>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    Este es un correo automático generado por el sistema operativo de tu agencia.<br>
                    Impulsado orgullosamente por <a href="https://nexacore.com.mx" class="footer-brand">NexaCore Aduanal</a>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
