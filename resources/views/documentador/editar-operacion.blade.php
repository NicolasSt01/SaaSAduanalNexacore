@extends('layouts.app')

@section('title', 'Editar Operación')

@php
    $modulaciones = [
        'DESADUANAMIENTO LIBRE' => ['emoji' => '✅', 'color' => 'green'],
        'RECONOCIMIENTO ADUANERO' => ['emoji' => '🔴', 'color' => 'red'],
        'RECONOCIMIENTO ADUANERO CONCLUIDO' => ['emoji' => '✅', 'color' => 'green'],
    ];
    $mod = $modulaciones[$operacion->modulacion] ?? null;
    $docs = $operacion->documentos ?? collect();
    $tiposTransaccionales = ['factura','encargo','transporte','empaque','origen','rrna','gastos','doda','cupo','val'];
    $cargados = $docs->whereIn('tipo_documento', $tiposTransaccionales)->unique('tipo_documento')->count();
    $totalReq = count($tiposTransaccionales);
    $pct = $totalReq > 0 ? round(($cargados / $totalReq) * 100) : 0;
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('documentador.dashboard') }}" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-800">Operación <span class="text-indigo-600">{{ $operacion->referencia }}</span></h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ $operacion->fecha_registro?->format('d/m/Y') }}</p>
            </div>
        </div>
        @if($mod)
        <div class="flex items-center gap-2 px-4 py-2 bg-{{ $mod['color'] }}-50 border border-{{ $mod['color'] }}-200 rounded-xl">
            <span class="text-lg">{{ $mod['emoji'] }}</span>
            <span class="text-sm font-bold text-{{ $mod['color'] }}-700">{{ $operacion->modulacion }}</span>
        </div>
        @endif
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        <!-- Columna izquierda: Campos editables -->
        <div class="flex-1 space-y-6">

            {{-- Pedimento (expediente) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-black text-gray-800 border-b pb-3 mb-4">Pedimento</h3>
                <div class="group cursor-pointer" onclick="openEditModal('expediente_id','{{ $operacion->expediente_id }}','pedimento','Pedimento')">
                    <span class="block text-xs font-bold text-gray-400 uppercase">Pedimento Asignado</span>
                    <span class="block text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition">
                        {{ $operacion->expediente->numero_pedimento ?? 'Sin asignar' }}
                    </span>
                </div>
            </div>

            {{-- Datos Generales --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-black text-gray-800 border-b pb-3 mb-4">Datos Generales</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <div class="group cursor-pointer" onclick="openEditModal('cliente_id','{{ $operacion->cliente_id }}','select','Cliente')"><span class="block text-xs font-bold text-gray-400 uppercase">Cliente</span><span class="block text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition">{{ $operacion->cliente->nombre ?? 'Sin asignar' }}</span></div>
                    <div class="group cursor-pointer" onclick="openEditModal('importador_id','{{ $operacion->importador_id }}','select','Importador')"><span class="block text-xs font-bold text-gray-400 uppercase">Importador</span><span class="block text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition">{{ $operacion->importador->nombre ?? 'Sin asignar' }}</span></div>
                    <div class="group cursor-pointer" onclick="openEditModal('nombre_producto','{{ $operacion->nombre_producto }}','text','Producto')"><span class="block text-xs font-bold text-gray-400 uppercase">Producto</span><span class="block text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition">{{ $operacion->nombre_producto ?: 'Sin asignar' }}</span></div>
                    <div class="group cursor-pointer" onclick="openEditModal('num_factura','{{ $operacion->num_factura }}','text','Factura')"><span class="block text-xs font-bold text-gray-400 uppercase">Factura</span><span class="block text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition">{{ $operacion->num_factura ?: 'Sin asignar' }}</span></div>
                </div>
            </div>

            {{-- Referencia y Códigos --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-black text-gray-800 border-b pb-3 mb-4">Referencia y Códigos</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <div class="group cursor-pointer" onclick="openEditModal('referencia','{{ $operacion->referencia }}','text','Referencia')"><span class="block text-xs font-bold text-gray-400 uppercase">Referencia</span><span class="block text-sm font-bold text-gray-800 font-mono group-hover:text-indigo-600 transition">{{ $operacion->referencia }}</span></div>
                    <div class="group cursor-pointer" onclick="openEditModal('num_thermo','{{ $operacion->num_thermo }}','text','No. Económico')"><span class="block text-xs font-bold text-gray-400 uppercase">No. Económico</span><span class="block text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition">{{ $operacion->num_thermo ?: 'Sin asignar' }}</span></div>
                    <div class="group cursor-pointer" onclick="openEditModal('codigo_alpha','{{ $operacion->codigo_alpha }}','text','Código Alpha')"><span class="block text-xs font-bold text-gray-400 uppercase">Código Alpha</span><span class="block text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition">{{ $operacion->codigo_alpha ?: 'Sin asignar' }}</span></div>
                    <div class="group cursor-pointer" onclick="openEditModal('num_doda','{{ $operacion->num_doda }}','text','DODA')"><span class="block text-xs font-bold text-gray-400 uppercase">DODA</span><span class="block text-sm font-bold text-gray-800 font-mono group-hover:text-indigo-600 transition">{{ $operacion->num_doda ?: 'Sin asignar' }}</span></div>
                </div>
            </div>

            {{-- Aduana y Logística --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-black text-gray-800 border-b pb-3 mb-4">Aduana y Logística</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <div><span class="block text-xs font-bold text-gray-400 uppercase">Aduana</span><span class="block text-sm font-bold text-gray-600">{{ $operacion->aduana->nombre ?? 'Sin asignar' }}</span><span class="text-[10px] text-gray-400">(definido por el pedimento)</span></div>
                    <div><span class="block text-xs font-bold text-gray-400 uppercase">Patente</span><span class="block text-sm font-bold text-gray-600">{{ $operacion->patente->numero ?? 'Sin asignar' }}</span><span class="text-[10px] text-gray-400">(definido por el pedimento)</span></div>
                    <div class="group cursor-pointer" onclick="openEditModal('bodega_id','{{ $operacion->bodega_id }}','select','Bodega')"><span class="block text-xs font-bold text-gray-400 uppercase">Bodega</span><span class="block text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition">{{ $operacion->bodega->nombre ?? 'Sin asignar' }}</span></div>
                    <div class="group cursor-pointer" onclick="openEditModal('fecha_cruce_estimada','{{ $operacion->fecha_cruce_estimada?->format('Y-m-d') }}','date','Fecha Cruce')"><span class="block text-xs font-bold text-gray-400 uppercase">Fecha Cruce</span><span class="block text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition">{{ $operacion->fecha_cruce_estimada?->format('d/m/Y') ?: 'Sin asignar' }}</span></div>
                </div>
            </div>

            {{-- Estado y Modulación --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-black text-gray-800 border-b pb-3 mb-4">Estado y Modulación</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <div class="group cursor-pointer" onclick="openEditModal('modulacion','{{ $operacion->modulacion }}','modulacion','Modulación')"><span class="block text-xs font-bold text-gray-400 uppercase">Modulación</span>@if($mod)<span class="text-sm font-bold text-{{ $mod['color'] }}-700">{{ $mod['emoji'] }} {{ $operacion->modulacion }}</span>@else<span class="text-sm text-gray-400">Sin modular</span>@endif</div>
                    <div class="group cursor-pointer" onclick="openEditModal('prioridad','{{ $operacion->prioridad }}','prioridad','Prioridad')"><span class="block text-xs font-bold text-gray-400 uppercase">Prioridad</span><span class="block text-sm font-bold text-gray-800 capitalize group-hover:text-indigo-600 transition">{{ $operacion->prioridad ?: 'normal' }}</span></div>
                    <div class="group cursor-pointer" onclick="openEditModal('estado','{{ $operacion->estado }}','estado','Estado')"><span class="block text-xs font-bold text-gray-400 uppercase">Estado</span><span class="text-xs px-2 py-0.5 rounded-full font-bold {{ $operacion->estado === 'completada' ? 'bg-green-100 text-green-700' : ($operacion->estado === 'cancelada' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ ucfirst(str_replace('_',' ',$operacion->estado)) }}</span></div>
                </div>
            </div>

            {{-- Observaciones --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-black text-gray-800 border-b pb-3 mb-4">Observaciones</h3>
                <div class="group cursor-pointer" onclick="openEditModal('observaciones','{{ $operacion->observaciones }}','textarea','Observaciones')">
                    <div class="text-sm text-gray-600 group-hover:text-indigo-600 transition">{{ $operacion->observaciones ?: 'Sin observaciones — clic para agregar' }}</div>
                </div>
            </div>

            {{-- Documentos --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-black text-gray-800">Documentos ({{ $docs->count() }})</h3>
                    <button onclick="document.getElementById('uploadFileInput').click()" class="text-sm bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg font-bold transition border border-indigo-200">
                        <i class="fas fa-upload mr-1"></i> Subir
                    </button>
                    <form id="uploadForm" class="hidden" enctype="multipart/form-data">
                        <input type="file" id="uploadFileInput" multiple onchange="uploadFilesOp()" class="hidden">
                        <select id="uploadTipoDoc" class="hidden">
                            @foreach($tiposTransaccionales as $t)<option value="{{$t}}">{{strtoupper($t)}}</option>@endforeach
                            <option value="pedimento_pagado">Pedimento Pagado</option>
                            <option value="otros">Otros</option>
                        </select>
                    </form>
                </div>
                @if($docs->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($docs as $doc)
                    <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3 border border-gray-100 group hover:border-indigo-200 transition">
                        <div class="truncate mr-2">
                            <span class="text-xs font-bold text-gray-700 block truncate">{{ $doc->nombre_original ?? 'Documento' }}</span>
                            <span class="text-[10px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded font-bold">{{ strtoupper($doc->tipo_documento) }}</span>
                        </div>
                        <div class="flex gap-1 shrink-0">
                            <a href="{{ route('documentos.preview', $doc->id) }}" target="_blank" class="text-xs text-indigo-500 hover:text-indigo-700" title="Ver"><i class="fas fa-eye"></i></a>
                            <form action="{{ route('documentos.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('¿Eliminar documento?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400 text-center py-6">Sin documentos cargados.</p>
                @endif
            </div>
        </div>

        <!-- Columna derecha: KPI Cumplimiento -->
        <div class="lg:w-80 shrink-0 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-4">
                <h3 class="text-base font-black text-gray-800 mb-4">Cumplimiento Art. 36-A</h3>

                {{-- Círculo de progreso --}}
                <div class="flex justify-center mb-4">
                    <div class="relative w-32 h-32">
                        <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="52" fill="none" stroke="#e5e7eb" stroke-width="10"/>
                            <circle cx="60" cy="60" r="52" fill="none"
                                stroke="{{ $pct >= 100 ? '#22c55e' : ($pct >= 50 ? '#f59e0b' : '#ef4444') }}"
                                stroke-width="10" stroke-linecap="round"
                                stroke-dasharray="{{ 2 * pi() * 52 }}"
                                stroke-dashoffset="{{ 2 * pi() * 52 * (1 - $pct / 100) }}"
                                class="transition-all duration-1000"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-2xl font-black text-gray-800">{{ $pct }}%</span>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-gray-500 text-center mb-4">{{ $cargados }} de {{ $totalReq }} documentos requeridos</p>

                <div class="space-y-1.5">
                    @foreach($tiposTransaccionales as $t)
                    @php $cargado = $docs->where('tipo_documento', $t)->count() > 0; @endphp
                    <div class="flex items-center justify-between text-xs py-1.5 px-2 rounded-lg {{ $cargado ? 'bg-green-50' : 'bg-gray-50' }}">
                        <span class="font-bold {{ $cargado ? 'text-green-700' : 'text-gray-400' }}">{{ ucfirst($t) }}</span>
                        <span class="{{ $cargado ? 'text-green-500' : 'text-gray-300' }}">{{ $cargado ? '✓' : '—' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Edición Inline --}}
<div id="editModal" class="fixed inset-0 z-[80] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEditModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <h3 class="text-lg font-black text-gray-800 mb-4" id="editModalTitle">Editar</h3>
            <form id="editForm" onsubmit="guardarCampo(event)">
                @csrf
                <input type="hidden" id="editField" name="campo">
                <div id="editFieldContainer"></div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm font-bold text-gray-700 transition">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-xl text-sm font-bold text-white transition"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const opId = {{ $operacion->id }};

// Opciones pre-cargadas de selects por tenant
const selectOptions = {
    cliente_id: {!! json_encode($clientes->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre ?? $c->nombre_empresa])) !!},
    importador_id: {!! json_encode($importadores->map(fn($i) => ['id' => $i->id, 'nombre' => $i->nombre])) !!},
    bodega_id: {!! json_encode($bodegas->map(fn($b) => ['id' => $b->id, 'nombre' => $b->nombre])) !!},
};

// Mapa de pedimentos: id -> { numero, aduana_id, patente_id }
const pedimentosMap = {!! json_encode($pedimentos->mapWithKeys(fn($p) => [
    $p->id => ['numero' => $p->numero_pedimento, 'aduana_id' => $p->aduana_id, 'patente_id' => $p->patente_id]
])) !!};

function openEditModal(field, value, type, label) {
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModalTitle').textContent = 'Editar ' + label;
    document.getElementById('editField').value = field;

    let html = '';
    switch(type) {
        case 'text':
            html = '<input type="text" name="valor" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 text-sm" value="' + (value || '') + '">';
            break;
        case 'textarea':
            html = '<textarea name="valor" rows="4" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 text-sm">' + (value || '') + '</textarea>';
            break;
        case 'date':
            html = '<input type="date" name="valor" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 text-sm" value="' + (value || '') + '">';
            break;
        case 'modulacion':
            html = '<select name="valor" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 text-sm">' +
                '<option value="">Sin modular</option>' +
                '<option value="DESADUANAMIENTO LIBRE">✅ Desaduanamiento Libre</option>' +
                '<option value="RECONOCIMIENTO ADUANERO">🔴 Reconocimiento Aduanero</option>' +
                '<option value="RECONOCIMIENTO ADUANERO CONCLUIDO">✅ Reconocimiento Aduanero Concluido</option>' +
                '</select>';
            html = html.replace('value="' + value + '"', 'value="' + value + '" selected');
            break;
        case 'prioridad':
            html = '<select name="valor" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 text-sm">' +
                ['baja','normal','alta','urgente'].map(p => '<option value="'+p+'">'+p.charAt(0).toUpperCase()+p.slice(1)+'</option>').join('') +
                '</select>';
            html = html.replace('value="' + value + '"', 'value="' + value + '" selected');
            break;
        case 'estado':
            html = '<select name="valor" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 text-sm">' +
                [{v:'pendiente',l:'Pendiente'},{v:'en_proceso',l:'En Proceso'},{v:'completada',l:'Completada'},{v:'cancelada',l:'Cancelada'}].map(e => '<option value="'+e.v+'">'+e.l+'</option>').join('') +
                '</select>';
            html = html.replace('value="' + value + '"', 'value="' + value + '" selected');
            break;
        case 'select':
            const opts = selectOptions[field] || [];
            html = '<select name="valor" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 text-sm"><option value="">Sin asignar</option>' +
                opts.map(o => '<option value="'+o.id+'">'+o.nombre+'</option>').join('') +
                '</select>';
            html = html.replace('value="' + value + '"', 'value="' + value + '" selected');
            break;
        case 'pedimento':
            const pedOpts = Object.entries(pedimentosMap);
            html = '<select name="valor" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 text-sm"><option value="">Sin asignar</option>' +
                pedOpts.map(([id, p]) => '<option value="'+id+'">'+p.numero+'</option>').join('') +
                '</select>';
            if (value) html = html.replace('value="' + value + '"', 'value="' + value + '" selected');
            html += '<p class="text-xs text-gray-400 mt-1">Al cambiar el pedimento se actualizarán automáticamente Aduana y Patente.</p>';
            break;
    }
    document.getElementById('editFieldContainer').innerHTML = html;
}

function guardarCampo(e) {
    e.preventDefault();
    const field = document.getElementById('editField').value;
    const valor = document.querySelector('[name="valor"]').value;
    let body = { campo: field, valor: valor };

    // Si cambia pedimento, incluir aduana y patente del expediente
    if (field === 'expediente_id' && valor && pedimentosMap[valor]) {
        body.aduana_id = pedimentosMap[valor].aduana_id;
        body.patente_id = pedimentosMap[valor].patente_id;
    }

    fetch('/documentador/operacion/' + opId + '/campo', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify(body)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { closeEditModal(); location.reload(); }
        else alert('Error: ' + (data.message || 'No se pudo guardar'));
    });
}

function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); }

function uploadFilesOp() {
    const files = document.getElementById('uploadFileInput').files;
    const tipo = document.getElementById('uploadTipoDoc').value || 'factura';
    if (!files.length) return;
    const formData = new FormData();
    for (let f of files) formData.append('archivos[]', f);
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('tipo_documento', tipo);
    var expedienteId = {{ $operacion->expediente_id ?? 0 }};
    var url = expedienteId ? '/expedientes/' + expedienteId + '/documentos' : '/documentador/operacion/' + opId + '/subir-documento';
    fetch(url, { method: 'POST', body: formData })
    .then(() => location.reload())
    .catch(err => alert('Error: ' + err.message));
}
</script>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection
