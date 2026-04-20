<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a NexaCore Aduanal</title>
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
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 30px;
            border-radius: 12px 12px 0 0;
            text-align: center;
            margin: -30px -30px 30px -30px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .section {
            background-color: #f9fafb;
            border-left: 4px solid #4f46e5;
            padding: 20px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .section h3 {
            margin: 0 0 10px 0;
            color: #4f46e5;
            font-size: 18px;
        }
        .password-box {
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
        }
        .password-box .label {
            font-size: 14px;
            color: #92400e;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .password-box .password {
            font-size: 24px;
            font-weight: 700;
            color: #92400e;
            letter-spacing: 2px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .features {
            background-color: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .features h3 {
            margin: 0 0 15px 0;
            color: #065f46;
            font-size: 18px;
        }
        .features ul {
            margin: 0;
            padding-left: 20px;
        }
        .features li {
            margin: 8px 0;
            color: #065f46;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .warning {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 ¡Bienvenido a NexaCore Aduanal!</h1>
            <p>Tu cuenta ha sido creada exitosamente</p>
        </div>

        <p>Hola <strong>{{ $user->name }}</strong>,</p>

        <p>Tu empresa <strong>{{ $tenant->nombre_empresa }}</strong> ha sido registrada en nuestra plataforma. Estamos emocionados de que formes parte de NexaCore Aduanal.</p>

        <div class="section">
            <h3>🔐 Tu Contraseña Temporal</h3>
            <p>Para tu primer acceso, utiliza la siguiente contraseña temporal:</p>
            <div class="password-box">
                <div class="label">CONTRASEÑA TEMPORAL</div>
                <div class="password">{{ $passwordTemporal }}</div>
            </div>
            <p style="font-size: 14px; color: #6b7280; margin-top: 10px;">
                ⚠️ <strong>Importante:</strong> Esta contraseña es temporal y deberás cambiarla en tu primer inicio de sesión.
            </p>
        </div>

        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="btn">
                ✅ Verificar Mi Correo Electrónico
            </a>
        </div>

        <div class="features">
            <h3>🚀 Tu Período de Trial Incluye:</h3>
            <ul>
                <li><strong>7 días</strong> de acceso completo a la plataforma</li>
                <li><strong>20 modulaciones</strong> disponibles para probar el SOIA-Bot</li>
                <li><strong>5 clientes</strong> para gestionar</li>
                <li><strong>1 usuario administrador</strong></li>
                <li><strong>SOIA-Bot en modo manual</strong> (tú controlas cuándo ejecutar)</li>
                <li>Acceso a dashboard básico y notificaciones por email</li>
            </ul>
        </div>

        <div class="warning">
            <strong>⏰ Importante:</strong> Tu período de trial comenzará cuando verifiques tu correo y hagas tu primer inicio de sesión. Tienes 7 días para explorar todas las funcionalidades.
        </div>

        <h3>📋 Pasos para Comenzar:</h3>
        <ol>
            <li>Haz click en el botón <strong>"Verificar Mi Correo Electrónico"</strong> de arriba</li>
            <li>Inicia sesión con tu correo y la contraseña temporal proporcionada</li>
            <li>El sistema te pedirá que cambies tu contraseña por una personalizada</li>
            <li>¡Listo! Comienza a explorar NexaCore Aduanal</li>
        </ol>

        <p style="margin-top: 30px;">Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.</p>

        <p><strong>¡Te deseamos mucho éxito en tu período de prueba!</strong></p>

        <p>Saludos cordiales,<br>
        <strong>El equipo de NexaCore Aduanal</strong></p>

        <div class="footer">
            <p>
                Este es un mensaje automático generado por el sistema.<br>
                Por favor no respondas a este correo.
            </p>
            <p style="margin-top: 10px; font-size: 12px;">
                © {{ date('Y') }} NexaCore Aduanal. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
