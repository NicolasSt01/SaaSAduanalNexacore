@component('mail::message')
# Hola {{ $tenant->nombre_empresa }},

Te recordamos que tu pago mensual de **${{ number_format($tenant->renta_mensual ?? 0, 2) }} MXN** está próximo a vencer.

@component('mail::panel')
**Días restantes: {{ $dias }}**  
**Saldo pendiente:** ${{ number_format($tenant->saldo_pendiente, 2) }} MXN  
**Fecha límite:** {{ $tenant->fecha_corte?->format('d/m/Y') }}
@endcomponent

Realiza tu pago a la brevedad para evitar la suspensión del servicio.

@component('mail::button', ['url' => config('app.url')])
Ir a NexaCore
@endcomponent

Saludos,  
Equipo NexaCore Aduanal
@endcomponent
