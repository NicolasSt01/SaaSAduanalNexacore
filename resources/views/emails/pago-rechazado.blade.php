<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;">
    <tr><td style="background:linear-gradient(135deg,#dc2626,#ef4444);padding:40px 30px;text-align:center;">
        <img src="https://nexacore.com.mx/LogoNexaCore.png" alt="NexaCore" style="height:40px;margin-bottom:16px;filter:brightness(0) invert(1);">
        <h1 style="color:#fff;margin:0;font-size:22px;font-weight:800;">Pago No Aprobado</h1>
        <p style="color:rgba(255,255,255,0.8);margin:8px 0 0;font-size:14px;">Requiere atención</p>
    </td></tr>
    <tr><td style="padding:30px;">
        <p style="color:#374151;font-size:15px;line-height:1.6;">Hola <strong>{{ $suscripcion->tenant->nombre_empresa }}</strong>,</p>
        <p style="color:#374151;font-size:15px;line-height:1.6;">Lamentablemente no pudimos confirmar el pago de tu suscripción al plan <strong>{{ $suscripcion->plan->nombre }}</strong>.</p>

        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:16px;margin:24px 0;">
            <p style="margin:0;color:#991b1b;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:1px;">Motivo</p>
            <p style="margin:8px 0 0;color:#991b1b;font-size:14px;">{{ $motivo }}</p>
        </div>

        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:16px;margin:24px 0;">
            <p style="margin:0;color:#1e40af;font-size:13px;font-weight:700;">¿Qué puedes hacer?</p>
            <ul style="margin:8px 0 0;padding-left:20px;color:#1e40af;font-size:13px;line-height:1.8;">
                <li>Verifica que la transferencia se haya realizado correctamente</li>
                <li>Confirma que la referencia <strong>{{ $suscripcion->referencia_pago }}</strong> fue incluida en el concepto</li>
                <li>Contacta a soporte si necesitas asistencia</li>
            </ul>
        </div>

        <p style="color:#374151;font-size:14px;">Si tienes alguna duda, contáctanos en <strong>{{ $config->email_notificaciones ?? 'contacto@nexacore.com.mx' }}</strong>.</p>
    </td></tr>
    <tr><td style="background:#f9fafb;padding:20px 30px;text-align:center;border-top:1px solid #e5e7eb;">
        <p style="margin:0;color:#9ca3af;font-size:11px;">NexaCore Aduanal — {{ $config->email_notificaciones ?? 'contacto@nexacore.com.mx' }}</p>
    </td></tr>
</table>
</body></html>
