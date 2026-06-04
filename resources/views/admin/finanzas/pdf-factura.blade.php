<!DOCTYPE html>
<html><head><meta charset="UTF-8"><style>body{font-family:DejaVu Sans,sans-serif;color:#333}.header{text-align:center;border-bottom:2px solid #4F46E5;padding-bottom:15px;margin-bottom:20px}.header h1{color:#4F46E5;margin:0}.table{width:100%;border-collapse:collapse;margin-top:15px}.table th{background:#F3F4F6;text-align:left;padding:8px;font-size:12px}.table td{padding:8px;border-bottom:1px solid #E5E7EB;font-size:12px}.total{text-align:right;font-size:18px;font-weight:bold;margin-top:20px}.footer{text-align:center;font-size:10px;color:#9CA3AF;margin-top:40px;border-top:1px solid #E5E7EB;padding-top:15px}</style></head><body>
<div class="header">
    <h1>NexaCore Aduanal</h1>
    <p>Factura: <strong>{{ $factura->folio }}</strong> | {{ $factura->created_at->format('d/m/Y') }}</p>
</div>

<table class="table"><tr><th>Cliente</th><td>{{ $factura->tenant->nombre_empresa }}</td></tr>
<tr><th>RFC</th><td>{{ $factura->tenant->rfc ?? 'N/A' }}</td></tr>
<tr><th>Periodo</th><td>{{ $factura->periodo }}</td></tr>
<tr><th>Estado</th><td>{{ $factura->estado }}</td></tr></table>

<div class="total">Total: ${{ number_format($factura->monto, 2) }} MXN</div>

<div class="footer"><p>NexaCore Aduanal — contacto@nexacore.com.mx</p><p>Este documento es una representación digital de la factura.</p></div>
</body></html>
