@extends('layouts.admin')

@section('header_title', 'Catálogo de Add-ons')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('admin.suscripciones.dashboard') }}" class="text-indigo-600 font-medium"><i class="fas fa-arrow-left"></i> Volver</a>
    <button onclick="document.getElementById('formAddon').classList.toggle('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold text-sm"><i class="fas fa-plus mr-1"></i> Nuevo Add-on</button>
</div>

<div id="formAddon" class="hidden bg-gray-50 border rounded-xl p-6 mb-6">
    <form method="POST" action="{{ route('admin.suscripciones.addons.store') }}">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Nombre</label>
                <input type="text" name="nombre" required class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" placeholder="Ej: Reporte de Remesas">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Tipo</label>
                <select name="tipo" id="addonTipo" required onchange="actualizarIdentificadores()" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2">
                    <option value="reporte">Reporte</option>
                    <option value="plantilla_email">Plantilla Email</option>
                    <option value="plantilla_whatsapp">Plantilla WhatsApp</option>
                    <option value="feature">Funcionalidad</option>
                    <option value="recurso_extra">Recurso Extra</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Identificador Técnico</label>
                <select name="identificador" id="addonIdentificador" required class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 font-mono">
                </select>
                <p class="text-xs text-gray-400 mt-1" id="identificadorHint">Selecciona un tipo para ver las opciones</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Precio Mensual (sin IVA)</label>
                <input type="number" name="precio_mensual" step="0.01" min="0" required class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" placeholder="500.00">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Descripción</label>
                <input type="text" name="descripcion" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" placeholder="Descripción breve del add-on">
            </div>
        </div>
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-2 rounded-lg text-sm"><i class="fas fa-save mr-1"></i> Crear Add-on</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($addons as $addon)
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-{{ $addon->tipo_color }}-100 flex items-center justify-center text-{{ $addon->tipo_color }}-600 text-lg">
                <i class="fas {{ $addon->tipo_icon }}"></i>
            </div>
            <span class="text-xs bg-{{ $addon->tipo_color }}-50 text-{{ $addon->tipo_color }}-700 px-2 py-1 rounded-full font-bold">{{ $addon->tipo_label }}</span>
        </div>
        <h3 class="font-bold text-gray-800 mb-1">{{ $addon->nombre }}</h3>
        <p class="text-xs text-gray-500 mb-2 font-mono">{{ $addon->identificador }}</p>
        @if($addon->descripcion)
            <p class="text-sm text-gray-500 mb-3">{{ $addon->descripcion }}</p>
        @endif
        <div class="flex items-center justify-between mt-3 pt-3 border-t">
            <span class="text-xl font-black text-indigo-600">${{ number_format($addon->precio_mensual, 2) }}<span class="text-xs font-normal text-gray-400">/mes</span></span>
            <span class="text-xs text-gray-400">{{ $addon->activos_count }} activos</span>
        </div>
        <form method="POST" action="{{ route('admin.suscripciones.addons.destroy', $addon->id) }}" class="mt-3">
            @csrf @method('DELETE')
            <button type="submit" onclick="return confirm('¿Eliminar este add-on?')" class="text-red-500 hover:underline text-xs"><i class="fas fa-trash mr-1"></i>Eliminar</button>
        </form>
    </div>
    @endforeach
</div>

<script>
const identificadoresPorTipo = {
    reporte: [
        { value: 'clientes', label: 'Reporte de Clientes' },
        { value: 'operacion_semanal', label: 'Operación Semanal' },
        { value: 'remesas', label: 'Reporte de Remesas' },
        { value: 'clientes_pdf', label: 'Envío PDF a Clientes' },
        { value: 'aduanas', label: 'Reporte de Aduanas' },
        { value: 'patron_clientes', label: 'Patrones de Cliente' },
        { value: 'financiero', label: 'Reporte Financiero' },
        { value: 'logistica', label: 'Logística y Tiempo' },
        { value: 'pedimentos', label: 'Reporte de Pedimentos' },
    ],
    plantilla_email: [
        { value: 'email_embarque', label: 'Notificación de Embarque' },
        { value: 'email_recordatorio_custom', label: 'Recordatorio Personalizado' },
        { value: 'email_estado_cuenta', label: 'Estado de Cuenta' },
        { value: 'email_cierre_expediente', label: 'Cierre de Expediente' },
        { value: 'email_bienvenida_cliente', label: 'Bienvenida a Cliente' },
        { value: 'email_documentos_faltantes', label: 'Documentos Faltantes' },
    ],
    plantilla_whatsapp: [
        { value: 'whatsapp_contenedor', label: 'Seguimiento de Contenedor' },
        { value: 'whatsapp_modulacion_premium', label: 'Alerta de Modulación Premium' },
        { value: 'whatsapp_recordatorio', label: 'Recordatorio General' },
        { value: 'whatsapp_estado_cuenta', label: 'Estado de Cuenta' },
        { value: 'whatsapp_desaduanamiento', label: 'Desaduanamiento Libre' },
        { value: 'whatsapp_documentos', label: 'Solicitud de Documentos' },
    ],
    feature: [
        { value: 'email_notifications', label: 'Notificaciones por Email' },
        { value: 'whatsapp_notifications', label: 'Notificaciones por WhatsApp' },
        { value: 'api_access', label: 'Acceso a API' },
        { value: 'soporte_prioritario', label: 'Soporte Prioritario 24/7' },
        { value: 'personalizacion', label: 'Personalización de Marca' },
        { value: 'bot_doda_auto', label: 'Bot DODA Automático' },
        { value: 'integracion_contable', label: 'Integración Sistema Contable' },
        { value: 'exportacion_masiva', label: 'Exportación Masiva de Datos' },
    ],
    recurso_extra: [
        { value: 'recurso_50_ops', label: '+50 Operaciones Mensuales' },
        { value: 'recurso_100_ops', label: '+100 Operaciones Mensuales' },
        { value: 'recurso_5_users', label: '+5 Usuarios Adicionales' },
        { value: 'recurso_10_users', label: '+10 Usuarios Adicionales' },
        { value: 'recurso_500_docs', label: '+500 Documentos Mensuales' },
        { value: 'recurso_1000_docs', label: '+1000 Documentos Mensuales' },
        { value: 'recurso_50_modulaciones', label: '+50 Modulaciones Mensuales' },
        { value: 'recurso_100_modulaciones', label: '+100 Modulaciones Mensuales' },
    ],
};

const hintsPorTipo = {
    reporte: 'Reportes disponibles en el sistema que se pueden vender como add-on',
    plantilla_email: 'Plantillas de correo adicionales para notificaciones personalizadas',
    plantilla_whatsapp: 'Plantillas de WhatsApp adicionales para mensajería automatizada',
    feature: 'Funcionalidades extra que se pueden activar por tenant',
    recurso_extra: 'Ampliación de límites del plan base (operaciones, usuarios, documentos)',
};

function actualizarIdentificadores() {
    const tipo = document.getElementById('addonTipo').value;
    const select = document.getElementById('addonIdentificador');
    const hint = document.getElementById('identificadorHint');
    
    select.innerHTML = '';
    
    const opciones = identificadoresPorTipo[tipo] || [];
    opciones.forEach(op => {
        const option = document.createElement('option');
        option.value = op.value;
        option.textContent = `${op.label} (${op.value})`;
        select.appendChild(option);
    });
    
    hint.textContent = hintsPorTipo[tipo] || '';
}

document.addEventListener('DOMContentLoaded', actualizarIdentificadores);
</script>
@endsection
