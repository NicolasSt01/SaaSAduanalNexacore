<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;">
    <tr><td style="background:linear-gradient(135deg,#059669,#10b981);padding:40px 30px;text-align:center;">
        <img src="https://nexacore.com.mx/LogoNexaCore.png" alt="NexaCore" style="height:40px;margin-bottom:16px;filter:brightness(0) invert(1);">
        <h1 style="color:#fff;margin:0;font-size:22px;font-weight:800;">¡Pago Aprobado!</h1>
        <p style="color:rgba(255,255,255,0.8);margin:8px 0 0;font-size:14px;">Tu suscripción ya está activa</p>
    </td></tr>
    <tr><td style="padding:30px;">
        <p style="color:#374151;font-size:15px;line-height:1.6;">Hola <strong>{{ $suscripcion->tenant->nombre_empresa }}</strong>,</p>
        <p style="color:#374151;font-size:15px;line-height:1.6;">Hemos confirmado tu pago y tu suscripción al plan <strong>{{ $suscripcion->plan->nombre }}</strong> ha sido activada exitosamente.</p>

        <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
            <tr><td style="padding:10px 16px;color:#6b7280;font-size:13px;width:40%;border-bottom:1px solid #e5e7eb;">Plan</td><td style="padding:10px 16px;color:#111827;font-weight:700;font-size:14px;border-bottom:1px solid #e5e7eb;">{{ $suscripcion->plan->nombre }}</td></tr>
            <tr><td style="padding:10px 16px;color:#6b7280;font-size:13px;border-bottom:1px solid #e5e7eb;">Monto pagado</td><td style="padding:10px 16px;color:#059669;font-weight:800;font-size:14px;border-bottom:1px solid #e5e7eb;">${{ number_format($suscripcion->monto_total, 2) }} MXN</td></tr>
            <tr><td style="padding:10px 16px;color:#6b7280;font-size:13px;border-bottom:1px solid #e5e7eb;">Fecha de activación</td><td style="padding:10px 16px;color:#111827;font-weight:700;font-size:14px;border-bottom:1px solid #e5e7eb;">{{ $suscripcion->fecha_inicio->format('d/m/Y') }}</td></tr>
            <tr><td style="padding:10px 16px;color:#6b7280;font-size:13px;">Vigencia hasta</td><td style="padding:10px 16px;color:#111827;font-weight:700;font-size:14px;">{{ $suscripcion->fecha_fin->format('d/m/Y') }}</td></tr>
        </table>

        <div style="text-align:center;margin:30px 0;">
            <a href="{{ config('app.url') }}" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:14px 40px;border-radius:12px;font-weight:800;font-size:14px;">Acceder a NexaCore</a>
        </div>
    </td></tr>
    <tr><td style="background:#f9fafb;padding:20px 30px;text-align:center;border-top:1px solid #e5e7eb;">
        <p style="margin:0;color:#9ca3af;font-size:11px;">NexaCore Aduanal — {{ $config->email_notificaciones ?? 'contacto@nexacore.com.mx' }}</p>
    </td></tr>
</table>
</body></html>
