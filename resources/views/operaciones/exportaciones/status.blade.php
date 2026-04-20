@component('mail::message')
# Estatus actualizado

Estimado cliente **{{ $operacion->cliente->nombre_empresa }}**,

La operación con factura **{{ $operacion->num_factura }}** y producto **{{ $operacion->nombre_producto }}** cambió su estatus de modulación a:

@switch($operacion->modulacion)
    @case('DESADUANAMIENTO LIBRE')
        ✅ **Verde: Desaduanamiento Libre**
        @break
    @case('RECONOCIMIENTO ADUANERO')
        🚨 **Rojo: Reconocimiento Aduanero**
        @break
    @case('RECONOCIMIENTO ADUANERO CONCLUIDO')
        ✔️ **Reconocimiento Aduanero Concluido**
        @break
    @default
        ⏳ **Pendiente: DODA no presentado**
@endswitch

Gracias,<br>
{{ config('app.name') }}
@endcomponent
