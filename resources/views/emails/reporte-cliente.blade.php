<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Operaciones</title>
    <style>
        /* NexaCore Design System - Email Logic */
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            background-color: #f8fafc;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        table {
            border-spacing: 0;
            border-collapse: collapse;
            width: 100%;
        }
        img {
            border: 0;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding-bottom: 40px;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-collapse: collapse;
            border-spacing: 0;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        /* NexaCore Branding */
        .branding-header {
            padding: 40px 40px 20px 40px;
            text-align: left;
        }
        .header-badge {
            background-color: #eef2ff;
            color: #4f46e5;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            display: inline-block;
        }
        .content-area {
            padding: 0 40px 40px 40px;
        }
        h1 {
            color: #1e293b;
            font-size: 28px;
            font-weight: 800;
            margin: 16px 0;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }
        h2 {
            color: #4f46e5;
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 24px 0;
        }
        p {
            color: #64748b;
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 20px 0;
        }
        .kpi-container {
            background-color: #f1f5f9;
            border-radius: 16px;
            padding: 24px;
            margin: 32px 0;
        }
        .kpi-label {
            font-size: 10px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 8px;
        }
        .kpi-value {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
        }
        .button-container {
            margin: 40px 0;
            text-align: center;
        }
        .button {
            background-color: #4f46e5;
            color: #ffffff !important;
            padding: 18px 36px;
            text-decoration: none;
            border-radius: 16px;
            font-weight: 800;
            font-size: 16px;
            display: inline-block;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }
        .security-box {
            border-top: 1px solid #e2e8f0;
            padding-top: 32px;
            margin-top: 32px;
        }
        .security-text {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
        }
        .footer {
            max-width: 600px;
            margin: 0 auto;
            padding: 32px 40px;
            text-align: center;
        }
        .footer-text {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        .footer-brand {
            font-size: 11px;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.2em;
        }
    </style>
</head>
<body>
    <center class="wrapper">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td align="center" style="padding: 40px 10px;">
                    <table class="main" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <!-- Branding Header -->
                        <tr>
                            <td class="branding-header">
                                <div class="header-badge">NexaCore Digital Intelligence</div>
                                <h1>Reporte de <span style="color: #4f46e5;">Operaciones</span></h1>
                            </td>
                        </tr>

                        <!-- Main Content -->
                        <tr>
                            <td class="content-area">
                                <p>Hola <strong>{{ $cliente->nombre_empresa }}</strong>,</p>
                                <p>Tu análisis operativo ha sido generado con éxito. Este reporte contiene la consolidación de tus trámites aduanales procesados recientemente.</p>
                                
                                <!-- Info Box -->
                                <table class="kpi-container" width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td>
                                            <div class="kpi-label">Periodo de Análisis</div>
                                            <div class="kpi-value">
                                                {{ \Carbon\Carbon::parse($desde)->format('d M') }} — 
                                                {{ \Carbon\Carbon::parse($hasta)->format('d M, Y') }}
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                <p>Para consultar el detalle completo, gráficas de eficiencia y el calendario de actividad, por favor utiliza el siguiente botón seguro:</p>

                                <!-- Action Button -->
                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td align="center" class="button-container">
                                            <a href="{{ $urlReporte }}" class="button">ACCEDER AL REPORTE</a>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Security Signature -->
                                <div class="security-box">
                                    <p style="margin-bottom: 8px; color: #1e293b; font-weight: 700; font-size: 14px;">
                                        Saludos cordiales,
                                    </p>
                                    <p style="margin-bottom: 0; color: #4f46e5; font-weight: 800; font-size: 16px;">
                                        {{ $tenant->nombre_empresa ?? 'Tu Agente Aduanal' }}
                                    </p>
                                    <p class="security-text" style="margin-top: 24px;">
                                        <strong>Protección de Datos:</strong> Este enlace es personal y confidencial. Expirará automáticamente en 7 días para proteger la integridad de tu información.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <!-- Footer Info -->
                    <table class="footer" width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td>
                                <div class="footer-text">Powered by NexaCore Aduanal SaaS</div>
                                <div class="footer-brand">Digital Excellence in International Trade</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>