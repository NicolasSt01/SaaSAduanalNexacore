<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Conexión SMTP</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .success-badge {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
        }

        .info-box {
            background-color: #f3f4f6;
            border-left: 4px solid #f97316;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
        }

        .info-box strong {
            color: #ea580c;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }

        .check-icon {
            width: 60px;
            height: 60px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: white;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🎉 ¡Conexión SMTP Exitosa!</h1>
            <div class="success-badge">Configuración Verificada</div>
        </div>

        <div style="text-align: center; margin-bottom: 25px;">
            <div class="check-icon">✓</div>
        </div>

        <p style="font-size: 16px; color: #374151;">
            Este es un correo de <strong>prueba</strong> para verificar que la configuración SMTP de tu Empresa está
            funcionando correctamente.
        </p>

        <div class="info-box">
            <p style="margin: 0 0 10px 0;"><strong>Detalles de la prueba:</strong></p>
            <p style="margin: 5px 0;"><strong>Empresa:</strong> {{ $tenant ?? 'Agencia Aduanal' }}</p>
            <p style="margin: 5px 0;"><strong>Fecha:</strong> {{ $timestamp ?? date('Y-m-d H:i:s') }}</p>
        </div>

        <p style="font-size: 14px; color: #6b7280;">
            Si recibiste este correo, significa que:
        </p>
        <ul style="font-size: 14px; color: #6b7280; line-height: 2;">
            <li>✅ Las credenciales SMTP son correctas</li>
            <li>✅ El servidor de correo está accesible</li>
            <li>✅ Los correos de notificación se enviarán correctamente a tus clientes</li>
        </ul>

        <div class="footer">
            <p style="margin: 0;">
                Este es un mensaje automático generado por el sistema de notificaciones.<br>
                <strong>No es necesario responder este correo.</strong>
            </p>
        </div>
    </div>
</body>

</html>