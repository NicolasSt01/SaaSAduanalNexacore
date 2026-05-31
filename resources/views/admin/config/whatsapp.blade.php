@extends('layouts.app')

@section('title', 'Configuración WhatsApp')

@php
    $plantillas = [
        'breve' => [
            'nombre' => 'Breve',
            'icono' => '⚡',
            'descripcion' => 'Mensaje corto y directo, solo lo esencial.',
            'preview' => "⚡ *Actualización de Trámite*\n\nFactura: 685 | Referencia: 2605190\nEstado: DESADUANAMIENTO LIBRE ✅\n\nCrosspoint - Agencia Aduanal",
        ],
        'detallado' => [
            'nombre' => 'Detallado',
            'icono' => '📋',
            'descripcion' => 'Formato completo con todos los detalles operativos.',
            'preview' => "📦 *Actualización de Trámite - Crosspoint*\n\nSu trámite ha sido completado exitosamente ✅\n\n📄 Factura: 685\n🔑 Referencia: 2605190\n📦 Producto: AGUACATE\n🚛 No. Económico: CTR74\n📅 Fecha: 23/may/2026 15:42\n\n━━━━━━━━━━━━━━━━━\nCrosspoint - Agencia Aduanal",
        ],
        'corporativo' => [
            'nombre' => 'Corporativo',
            'icono' => '🏢',
            'descripcion' => 'Estilo profesional con encabezado y pie de empresa.',
            'preview' => "┌─────────────────────────┐\n│  AVISO OPERATIVO        │\n│  CROSSPOINT             │\n└─────────────────────────┘\n\nEstimado/a Cliente,\n\nSu trámite aduanal presenta:\n  ✅ DESADUANAMIENTO LIBRE\n\nFactura: 685\nProducto: AGUACATE\nReferencia: 2605190\nTermo: CTR74\n\nCordialmente,\nCrosspoint - Agencia Aduanal",
        ],
    ];
    
    $whatsappPlantilla = $evolutionConfig['whatsapp_plantilla'] ?? 'breve';
    $whatsappPlantillaCustom = $evolutionConfig['whatsapp_plantilla_custom'] ?? null;
    $tieneCustom = !empty($whatsappPlantillaCustom);
    $selectedTemplate = $tieneCustom ? 'custom' : $whatsappPlantilla;
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.config') }}"
            class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-800 tracking-tight">Configuración <span class="text-green-600">WhatsApp</span></h1>
            <p class="text-sm text-gray-500 mt-1 font-medium">Conecta tu WhatsApp para enviar notificaciones de modulación a tus clientes.</p>
        </div>
    </div>

    @include('partials.alerts')

    <!-- Sección: Formato de Mensaje (siempre visible) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-1"><i class="fas fa-pen-fancy mr-2 text-green-600"></i> Formato de Mensaje</h2>
        <p class="text-sm text-gray-500 mb-6">Selecciona cómo se enviarán las notificaciones de modulación por WhatsApp a tus clientes.</p>

        @if($tieneCustom)
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 mb-4">
            <div class="flex items-center gap-2">
                <i class="fas fa-crown text-purple-600"></i>
                <span class="text-sm font-bold text-purple-700">Plantilla Personalizada Activa</span>
                <span class="text-xs text-purple-500">(Configurada por NexaCore)</span>
            </div>
            <pre class="mt-2 text-xs text-purple-600 bg-purple-100 p-3 rounded-lg max-h-32 overflow-y-auto whitespace-pre-wrap font-mono">{{ $whatsappPlantillaCustom }}</pre>
        </div>
        @endif

        <form id="whatsappPlantillaForm" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($plantillas as $key => $plantilla)
                <label class="relative cursor-pointer group">
                    <input type="radio" name="whatsapp_plantilla" value="{{ $key }}"
                        class="peer sr-only"
                        {{ $selectedTemplate === $key ? 'checked' : '' }}
                        {{ $tieneCustom ? 'disabled' : '' }}
                        onchange="guardarPlantilla('{{ $key }}')">
                    <div class="border-2 rounded-xl p-4 h-full transition-all
                        peer-checked:border-green-500 peer-checked:bg-green-50/50 peer-checked:ring-2 peer-checked:ring-green-500/20
                        {{ $tieneCustom ? 'opacity-50 cursor-not-allowed' : 'hover:border-gray-300 hover:shadow-sm' }}
                        {{ $selectedTemplate === $key ? 'border-green-500 bg-green-50/50 ring-2 ring-green-500/20' : 'border-gray-200' }}">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xl">{{ $plantilla['icono'] }}</span>
                            <span class="text-sm font-bold text-gray-800">{{ $plantilla['nombre'] }}</span>
                            @if($selectedTemplate === $key)
                                <i class="fas fa-check-circle text-green-500 ml-auto text-sm"></i>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mb-2">{{ $plantilla['descripcion'] }}</p>
                        <pre class="text-[10px] text-gray-600 bg-gray-100 p-2 rounded-lg whitespace-pre-wrap leading-snug font-mono max-h-28 overflow-y-auto">{{ $plantilla['preview'] }}</pre>
                    </div>
                </label>
                @endforeach
            </div>
            <p id="plantillaGuardado" class="text-xs text-green-600 font-bold hidden mt-2"><i class="fas fa-check mr-1"></i> Plantilla guardada</p>
        </form>

        @if(!$tieneCustom)
        <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700 flex items-start gap-2">
            <i class="fas fa-lightbulb mt-0.5"></i>
            <span>¿Ninguna plantilla se adapta a tu agencia? <strong>Solicita una plantilla personalizada</strong> a NexaCore. Tu superadmin puede configurarla para ti con el formato exacto que necesitas.</span>
        </div>
        @endif
    </div>

    <!-- Estado: No configurado -->
    @if($estado === 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
        <div class="mx-auto w-20 h-20 rounded-full bg-green-50 flex items-center justify-center mb-6">
            <i class="fab fa-whatsapp text-4xl text-green-500"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-3">Conecta tu WhatsApp</h2>
        <p class="text-gray-500 max-w-md mx-auto mb-8 leading-relaxed">
            Al conectar tu número de WhatsApp, el sistema podrá notificar automáticamente a tus clientes cuando sus operaciones cambien de estatus (Desaduanamiento Libre, Reconocimiento Aduanero, etc.).
        </p>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 max-w-md mx-auto mb-6 text-left">
            <p class="text-sm text-amber-800 font-bold mb-2"><i class="fas fa-info-circle mr-1"></i> Requisitos:</p>
            <ul class="text-sm text-amber-700 space-y-1 list-disc list-inside">
                <li>Tener un número de WhatsApp activo en tu celular</li>
                <li>Escanea el código QR que aparecerá a continuación</li>
                <li>La sesión se mantiene activa mientras no cierres sesión</li>
            </ul>
        </div>
        <button type="button" onclick="conectarWhatsApp()" id="btnConectar"
            class="inline-flex items-center justify-center rounded-xl bg-green-600 px-8 py-3.5 text-base font-bold text-white shadow-sm hover:bg-green-700 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
            <i class="fab fa-whatsapp mr-2 text-lg"></i> Conectar WhatsApp
        </button>
    </div>
    @endif

    <!-- Estado: Esperando QR -->
    @if($estado === 1)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
        <div class="mx-auto w-20 h-20 rounded-full bg-amber-50 flex items-center justify-center mb-6 animate-pulse">
            <i class="fab fa-whatsapp text-4xl text-amber-500"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-3">Esperando conexión</h2>
        <p class="text-gray-500 max-w-md mx-auto mb-8">
            Ya existe una instancia creada pero WhatsApp no se ha conectado. Genera un nuevo código QR para continuar.
        </p>
        <button type="button" onclick="conectarWhatsApp()" id="btnReconectar"
            class="inline-flex items-center justify-center rounded-xl bg-green-600 px-8 py-3.5 text-base font-bold text-white shadow-sm hover:bg-green-700 transition-colors">
            <i class="fas fa-qrcode mr-2"></i> Generar nuevo QR
        </button>
    </div>
    @endif

    <!-- Estado: Conectado -->
    @if($estado === 2)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10">
        <div class="flex flex-col md:flex-row items-center gap-6 mb-8">
            <div class="mx-auto md:mx-0 w-20 h-20 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                <i class="fab fa-whatsapp text-4xl text-green-600"></i>
            </div>
            <div class="text-center md:text-left">
                <h2 class="text-xl font-bold text-gray-800 mb-1">WhatsApp Conectado</h2>
                <p class="text-gray-500">
                    <i class="fas fa-check-circle text-green-500 mr-1"></i>
                    Tu número está vinculado y activo.
                </p>
                @if(!empty($evolutionConfig['connected_at']))
                <p class="text-xs text-gray-400 mt-1">
                    Conectado desde {{ \Carbon\Carbon::parse($evolutionConfig['connected_at'])->format('d/m/Y H:i') }}
                </p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-2xl mx-auto mb-8">
            <button type="button" onclick="sincronizarContactos()" id="btnContactos"
                class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-5 py-3 text-sm font-bold text-blue-700 hover:bg-blue-100 transition-colors">
                <i class="fas fa-user-friends mr-2"></i> Sincronizar Contactos
            </button>
            <button type="button" onclick="sincronizarGrupos()" id="btnGrupos"
                class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-5 py-3 text-sm font-bold text-indigo-700 hover:bg-indigo-100 transition-colors">
                <i class="fas fa-users mr-2"></i> Sincronizar Grupos
            </button>
            <button type="button" onclick="desconectarWhatsApp()" id="btnDesconectar"
                class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-bold text-red-700 hover:bg-red-100 transition-colors">
                <i class="fas fa-power-off mr-2"></i> Desvincular
            </button>
        </div>

        <!-- Lista de contactos -->
        <div id="contactosContainer" class="mt-6 hidden">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-user-friends mr-2 text-blue-500"></i> Contactos (<span id="contactosCount">0</span>)
            </h3>
            <div class="bg-gray-50 rounded-xl border border-gray-100 max-h-80 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="text-left px-4 py-2 font-semibold text-gray-600">Nombre</th>
                            <th class="text-left px-4 py-2 font-semibold text-gray-600">Teléfono</th>
                            <th class="text-left px-4 py-2 font-semibold text-gray-600">Chat ID</th>
                            <th class="text-center px-4 py-2 font-semibold text-gray-600">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="contactosLista" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>

        <!-- Lista de grupos -->
        <div id="gruposContainer" class="mt-6 hidden">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-users mr-2 text-indigo-500"></i> Grupos (<span id="gruposCount">0</span>)
            </h3>
            <div id="gruposLista" class="space-y-3"></div>
        </div>

        <!-- Notificaciones Pendientes -->
        <div class="mt-8 border-t border-gray-100 pt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-clock mr-2 text-amber-500"></i> Notificaciones Pendientes
                    <span id="pendientesBadge" class="hidden ml-2 bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full font-bold"></span>
                </h3>
                <button type="button" onclick="cargarPendientes()" id="btnPendientes"
                    class="text-sm bg-amber-50 hover:bg-amber-100 text-amber-700 px-3 py-1.5 rounded-lg font-bold transition-colors border border-amber-200">
                    <i class="fas fa-sync-alt mr-1"></i> Actualizar
                </button>
            </div>

            <div id="pendientesContainer" class="space-y-4">
                <p class="text-sm text-gray-400 text-center py-4">Haz clic en "Actualizar" para ver notificaciones pendientes.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal QR -->
    <div id="qrModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="cerrarQrModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 text-center">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Escanea el Código QR</h3>
                <p class="text-sm text-gray-500 mb-4">Abre WhatsApp en tu celular, ve a <strong>Ajustes &gt; Dispositivos vinculados</strong> y escanea este código.</p>
                <div id="qrContainer" class="bg-gray-100 rounded-xl p-4 mb-4 flex items-center justify-center min-h-[250px]">
                    <i class="fas fa-spinner fa-spin text-3xl text-gray-400" id="qrSpinner"></i>
                    <img id="qrImage" src="" alt="QR Code" class="hidden max-w-full rounded-lg">
                </div>
                <p id="qrMensaje" class="text-sm text-amber-600 font-bold mb-4 hidden">
                    <i class="fas fa-check-circle mr-1"></i> ¡WhatsApp conectado exitosamente!
                </p>
                <button type="button" onclick="cerrarQrModal()"
                    class="w-full rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-200 transition-colors">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Límite WhatsApp Excedido -->
    <div id="limiteWhatsappModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="cerrarLimiteModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fab fa-whatsapp text-3xl text-red-500"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Límite de WhatsApp Alcanzado</h3>
                <p class="text-sm text-gray-600 mb-4" id="limiteWhatsappMensaje">
                    Has alcanzado tu límite de mensajes de WhatsApp este mes.
                </p>
                <div class="bg-gray-50 rounded-xl p-4 mb-4">
                    <p class="text-xs text-gray-500 mb-2">Uso actual</p>
                    <p class="text-2xl font-bold text-red-600"><span id="limiteUso">0</span>/<span id="limiteMax">0</span></p>
                </div>
                <p class="text-sm text-gray-600 mb-6">
                    Para ampliar tu límite de mensajes, contacta a:<br>
                    <a href="mailto:contacto@nexacore.com.mx" class="text-indigo-600 font-bold hover:underline">contacto@nexacore.com.mx</a>
                </p>
                <button type="button" onclick="cerrarLimiteModal()"
                    class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition-colors">
                    Entendido
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function conectarWhatsApp() {
    const btn = document.getElementById('btnConectar') || document.getElementById('btnReconectar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Conectando...';

    abrirQrModal();

    fetch('{{ route("admin.whatsapp.conectar") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.paired) {
                document.getElementById('qrSpinner').classList.add('hidden');
                document.getElementById('qrMensaje').classList.remove('hidden');
                setTimeout(() => location.reload(), 2000);
            } else if (data.qr) {
                document.getElementById('qrSpinner').classList.add('hidden');
                const img = document.getElementById('qrImage');
                img.src = data.qr.startsWith('data:') ? data.qr : 'data:image/png;base64,' + data.qr;
                img.classList.remove('hidden');
                iniciarVerificacionEstado();
            } else {
                document.getElementById('qrSpinner').classList.add('hidden');
                document.getElementById('qrMensaje').textContent = data.message || 'No se pudo generar el QR. Intenta de nuevo.';
                document.getElementById('qrMensaje').classList.remove('hidden');
                console.log('QR debug:', data.debug, data);
            }
        } else {
            alert('Error: ' + (data.message || 'No se pudo conectar.'));
            cerrarQrModal();
        }
    })
    .catch(err => {
        alert('Error de conexión: ' + err.message);
        cerrarQrModal();
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = btn === document.getElementById('btnConectar')
            ? '<i class="fab fa-whatsapp mr-2 text-lg"></i> Conectar WhatsApp'
            : '<i class="fas fa-qrcode mr-2"></i> Generar nuevo QR';
    });
}

let verificacionInterval = null;

function iniciarVerificacionEstado() {
    if (verificacionInterval) clearInterval(verificacionInterval);
    verificacionInterval = setInterval(() => {
        fetch('{{ route("admin.whatsapp.estado") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.connected) {
                clearInterval(verificacionInterval);
                document.getElementById('qrImage').classList.add('hidden');
                document.getElementById('qrSpinner').classList.add('hidden');
                document.getElementById('qrMensaje').classList.remove('hidden');
                setTimeout(() => location.reload(), 2000);
            }
        });
    }, 3000);
}

function desconectarWhatsApp() {
    if (!confirm('¿Seguro que deseas desconectar WhatsApp? Dejarás de recibir notificaciones por este canal.')) return;

    const btn = document.getElementById('btnDesconectar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Desconectando...';

    fetch('{{ route("admin.whatsapp.desconectar") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'No se pudo desconectar.'));
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-power-off mr-2"></i> Desconectar';
        }
    });
}

function sincronizarContactos() {
    const btn = document.getElementById('btnContactos');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sincronizando...';

    fetch('{{ route("admin.whatsapp.contactos") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('contactosContainer');
        const lista = document.getElementById('contactosLista');
        const count = document.getElementById('contactosCount');

        if (data.success && data.contactos.length > 0) {
            container.classList.remove('hidden');
            count.textContent = data.total;
            lista.innerHTML = data.contactos.map(c => `
                <tr class="hover:bg-white transition-colors">
                    <td class="px-4 py-2.5 font-medium text-gray-800">${c.nombre}</td>
                    <td class="px-4 py-2.5 text-gray-600">${c.telefono}</td>
                    <td class="px-4 py-2.5">
                        <code class="text-xs bg-gray-200 px-2 py-0.5 rounded text-gray-600 select-all">${c.id}</code>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <button onclick="registrarEnDirectorio('${c.id}', '${c.nombre}', '${c.telefono}')"
                            class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200 transition-colors font-medium">
                            <i class="fas fa-plus mr-1"></i> Registrar
                        </button>
                    </td>
                </tr>
            `).join('');
        } else if (data.success && data.contactos.length === 0) {
            container.classList.remove('hidden');
            count.textContent = '0';
            lista.innerHTML = '<tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No se encontraron contactos en este WhatsApp.</td></tr>';
        } else {
            alert('Error al sincronizar contactos: ' + (data.message || 'Intenta de nuevo.'));
        }
    })
    .catch(err => alert('Error: ' + err.message))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-user-friends mr-2"></i> Sincronizar Contactos';
    });
}

function registrarEnDirectorio(chatId, nombre, telefono) {
    // Abrir modal o redirigir al directorio con los datos prellenados
    const url = '{{ route("directorio.create") }}' + '?whatsapp_id=' + encodeURIComponent(chatId)
        + '&nombre=' + encodeURIComponent(nombre)
        + '&telefono=' + encodeURIComponent(telefono);
    window.open(url, '_blank');
}

function sincronizarGrupos() {
    const btn = document.getElementById('btnGrupos');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sincronizando...';

    fetch('{{ route("admin.whatsapp.grupos") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('gruposContainer');
        const lista = document.getElementById('gruposLista');
        const count = document.getElementById('gruposCount');

        if (data.success && data.grupos.length > 0) {
            container.classList.remove('hidden');
            count.textContent = data.grupos.length;
            lista.innerHTML = data.grupos.map(g => `
                <div class="flex items-center justify-between bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <div>
                        <p class="font-bold text-gray-800">${g.nombre}</p>
                        <p class="text-xs text-gray-500 mt-0.5">${g.participant_count} participantes</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="text-xs bg-gray-200 px-2 py-1 rounded text-gray-600 select-all">${g.id}</code>
                        <button onclick="registrarEnDirectorio('${g.id}', '${g.nombre}', 'grupo')"
                            class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200 transition-colors font-medium">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        } else if (data.success && data.grupos.length === 0) {
            container.classList.remove('hidden');
            count.textContent = '0';
            lista.innerHTML = '<p class="text-sm text-gray-500">No se encontraron grupos en este WhatsApp.</p>';
        } else {
            alert('Error al sincronizar grupos.');
        }
    })
    .catch(err => alert('Error: ' + err.message))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-users mr-2"></i> Sincronizar Grupos';
    });
}

function abrirQrModal() {
    document.getElementById('qrModal').classList.remove('hidden');
    document.getElementById('qrSpinner').classList.remove('hidden');
    document.getElementById('qrImage').classList.add('hidden');
    document.getElementById('qrMensaje').classList.add('hidden');
    if (verificacionInterval) clearInterval(verificacionInterval);
}

function cerrarQrModal() {
    document.getElementById('qrModal').classList.add('hidden');
    if (verificacionInterval) clearInterval(verificacionInterval);
}

function guardarPlantilla(templateKey) {
    fetch('{{ route("admin.whatsapp.guardarPlantilla") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ whatsapp_plantilla: templateKey })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const msg = document.getElementById('plantillaGuardado');
            msg.classList.remove('hidden');
            setTimeout(() => msg.classList.add('hidden'), 2000);
        }
    })
    .catch(err => console.error('Error al guardar plantilla:', err));
}

function cargarPendientes() {
    const btn = document.getElementById('btnPendientes');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cargando...';

    fetch('{{ route("admin.whatsapp.pendientes") }}')
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('pendientesContainer');
            const badge = document.getElementById('pendientesBadge');
            const total = data.total_correos + data.total_whatsapp;

            if (total > 0) {
                badge.textContent = total;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            let html = '';

            // Correos pendientes
            if (data.pendientes_correos.length > 0) {
                html += '<div class="bg-blue-50 border border-blue-200 rounded-xl p-4">';
                html += '<div class="flex items-center justify-between mb-3">';
                html += '<h4 class="text-sm font-bold text-blue-800"><i class="fas fa-envelope mr-1"></i> Correos Pendientes (' + data.pendientes_correos.length + ')</h4>';
                html += '<span class="text-xs text-blue-600">Uso: ' + data.info_correos.uso + '/' + (data.info_correos.limite || '∞') + ' hoy</span>';
                html += '</div>';

                data.pendientes_correos.forEach(p => {
                    html += '<div class="flex items-center justify-between bg-white rounded-lg p-3 mb-2 border border-blue-100">';
                    html += '<div class="text-sm"><span class="font-bold text-gray-700">' + p.cliente + '</span>';
                    html += '<span class="text-gray-500 ml-2">' + p.modulacion + '</span>';
                    html += '<span class="text-xs text-gray-400 ml-2">' + p.destinatarios + ' dest.</span></div>';
                    html += '<div class="flex gap-2">';
                    html += '<button onclick="reenviarPendiente(\'' + p.id + '\')" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200 font-bold"><i class="fas fa-paper-plane mr-1"></i> Reenviar</button>';
                    html += '<button onclick="descartarPendiente(\'' + p.id + '\')" class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded hover:bg-gray-200 font-bold"><i class="fas fa-times mr-1"></i></button>';
                    html += '</div></div>';
                });

                if (data.pendientes_correos.length > 1) {
                    html += '<button onclick="descartarTodas(\'correo\')" class="text-xs text-red-500 hover:text-red-700 mt-1">Descartar todos los correos</button>';
                }
                html += '</div>';
            }

            // WhatsApp pendientes
            if (data.pendientes_whatsapp.length > 0) {
                html += '<div class="bg-green-50 border border-green-200 rounded-xl p-4 mt-4">';
                html += '<div class="flex items-center justify-between mb-3">';
                html += '<h4 class="text-sm font-bold text-green-800"><i class="fab fa-whatsapp mr-1"></i> WhatsApp Pendientes (' + data.pendientes_whatsapp.length + ')</h4>';
                html += '<span class="text-xs text-green-600">Uso: ' + data.info_whatsapp.uso + '/' + (data.info_whatsapp.limite || '∞') + ' mes</span>';
                html += '</div>';

                data.pendientes_whatsapp.forEach(p => {
                    html += '<div class="flex items-center justify-between bg-white rounded-lg p-3 mb-2 border border-green-100">';
                    html += '<div class="text-sm"><span class="font-bold text-gray-700">' + p.cliente + '</span>';
                    html += '<span class="text-gray-500 ml-2">' + p.modulacion + '</span>';
                    html += '<span class="text-xs text-gray-400 ml-2">' + p.destinatarios + ' dest.</span></div>';
                    html += '<div class="flex gap-2">';
                    html += '<button onclick="reenviarPendiente(\'' + p.id + '\')" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200 font-bold"><i class="fas fa-paper-plane mr-1"></i> Reenviar</button>';
                    html += '<button onclick="descartarPendiente(\'' + p.id + '\')" class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded hover:bg-gray-200 font-bold"><i class="fas fa-times mr-1"></i></button>';
                    html += '</div></div>';
                });

                if (data.pendientes_whatsapp.length > 1) {
                    html += '<button onclick="descartarTodas(\'whatsapp\')" class="text-xs text-red-500 hover:text-red-700 mt-1">Descartar todos los WhatsApp</button>';
                }
                html += '</div>';
            }

            if (!html) {
                html = '<div class="text-center py-6"><i class="fas fa-check-circle text-green-400 text-3xl mb-2"></i><p class="text-sm text-gray-500">No hay notificaciones pendientes.</p></div>';
            }

            container.innerHTML = html;
        })
        .catch(err => {
            document.getElementById('pendientesContainer').innerHTML = '<p class="text-sm text-red-500">Error al cargar pendientes.</p>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Actualizar';
        });
}

function reenviarPendiente(id) {
    if (!confirm('¿Reenviar esta notificación?')) return;

    fetch('{{ route("admin.whatsapp.reenviarPendiente") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.limit_exceeded) {
            mostrarLimiteModal(data.uso, data.limite);
            return;
        }
        alert(data.message);
        cargarPendientes();
    })
    .catch(err => {
        alert('Error al reenviar la notificación.');
    });
}

function mostrarLimiteModal(uso, limite) {
    document.getElementById('limiteUso').textContent = uso;
    document.getElementById('limiteMax').textContent = limite;
    document.getElementById('limiteWhatsappModal').classList.remove('hidden');
}

function cerrarLimiteModal() {
    document.getElementById('limiteWhatsappModal').classList.add('hidden');
}

function descartarPendiente(id) {
    if (!confirm('¿Descartar esta notificación? No se enviará.')) return;

    fetch('{{ route("admin.whatsapp.descartarPendiente") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id })
    })
    .then(r => r.json())
    .then(data => {
        cargarPendientes();
    });
}

function descartarTodas(type) {
    if (!confirm('¿Descartar TODAS las notificaciones pendientes de ' + type + '? Esta acción no se puede deshacer.')) return;

    fetch('{{ route("admin.whatsapp.descartarTodasPendientes") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ type: type })
    })
    .then(r => r.json())
    .then(data => {
        cargarPendientes();
    });
}
</script>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection
