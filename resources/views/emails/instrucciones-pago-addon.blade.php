<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;">
    <tr><td style="background:linear-gradient(135deg,#7c3aed,#a855f7);padding:40px 30px;text-align:center;">
        <img src="https://nexacore.com.mx/LogoNexaCore.png" alt="NexaCore" style="height:40px;margin-bottom:16px;filter:brightness(0) invert(1);">
        <h1 style="color:#fff;margin:0;font-size:22px;font-weight:800;">Pago de Add-on</h1>
        <p style="color:rgba(255,255,255,0.8);margin:8px 0 0;font-size:14px;">{{ $contratado->addon->nombre }}</p>
    </td></tr>
    <tr><td style="padding:30px;">
        <p style="color:#374151;font-size:15px;line-height:1.6;">Hola <strong>{{ $contratado->tenant->nombre_empresa }}</strong>,</p>
        <p style="color:#374151;font-size:15px;line-height:1.6;">Has contratado un complemento adicional para tu cuenta. Estos son los detalles para realizar el pago:</p>

        <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
            <tr><td colspan="2" style="background:#f9fafb;padding:12px 16px;font-weight:800;color:#7c3aed;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Detalle del Add-on</td></tr>
            <tr><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:13px;width:40%;">Add-on</td><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#111827;font-weight:700;font-size:14px;">{{ $contratado->addon->nombre }}</td></tr>
            <tr><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:13px;">Tipo</td><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#111827;font-weight:700;font-size:14px;">{{ $contratado->addon->tipo_label }}</td></tr>
            <tr><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:13px;">Subtotal</td><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#111827;font-weight:700;font-size:14px;">${{ number_format($contratado->monto_base, 2) }} MXN</td></tr>
            <tr><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:13px;">IVA ({{ $config->iva_porcentaje }}%)</td><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#111827;font-weight:700;font-size:14px;">${{ number_format($contratado->monto_iva, 2) }} MXN</td></tr>
            <tr style="background:#f5f3ff;"><td style="padding:12px 16px;border-top:2px solid #7c3aed;color:#7c3aed;font-weight:800;font-size:14px;">TOTAL</td><td style="padding:12px 16px;border-top:2px solid #7c3aed;color:#7c3aed;font-weight:800;font-size:18px;">${{ number_format($contratado->monto_total, 2) }} MXN</td></tr>
        </table>

        <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
            <tr><td colspan="2" style="background:#f9fafb;padding:12px 16px;font-weight:800;color:#059669;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Datos Bancarios</td></tr>
            <tr><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:13px;width:40%;">Banco</td><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#111827;font-weight:700;font-size:14px;">{{ $config->banco_nombre ?? 'No configurado' }}</td></tr>
            <tr><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:13px;">CLABE</td><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#111827;font-weight:700;font-size:14px;font-family:monospace;">{{ $config->banco_clabe ?? 'No configurado' }}</td></tr>
            <tr><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:13px;">Cuenta</td><td style="padding:10px 16px;border-top:1px solid #e5e7eb;color:#111827;font-weight:700;font-size:14px;font-family:monospace;">{{ $config->banco_cuenta ?? 'No configurado' }}</td></tr>
        </table>

        <div style="background:#fef3c7;border:1px solid #fbbf24;border-radius:12px;padding:16px;margin:24px 0;text-align:center;">
            <p style="margin:0;color:#92400e;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:1px;">Referencia de Pago (OBLIGATORIA)</p>
            <p style="margin:8px 0 0;color:#92400e;font-size:24px;font-weight:900;font-family:monospace;letter-spacing:2px;">{{ $contratado->referencia_pago }}</p>
            <p style="margin:8px 0 0;color:#a16207;font-size:12px;">Coloca esta referencia en el concepto de tu transferencia</p>
        </div>
    </td></tr>
    <tr><td style="background:#f9fafb;padding:20px 30px;text-align:center;border-top:1px solid #e5e7eb;">
        <p style="margin:0;color:#9ca3af;font-size:11px;">NexaCore Aduanal — {{ $config->email_notificaciones ?? 'contacto@nexacore.com.mx' }}</p>
    </td></tr>
</table>
</body></html>
