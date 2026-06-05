@extends('layouts.admin')

@section('header_title', 'Planes de Suscripción')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('admin.suscripciones.dashboard') }}" class="text-indigo-600 font-medium"><i class="fas fa-arrow-left"></i> Volver</a>
    <button onclick="document.getElementById('formPlan').classList.toggle('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold text-sm"><i class="fas fa-plus mr-1"></i> Nuevo Plan</button>
</div>

<div id="formPlan" class="hidden bg-gray-50 border rounded-xl p-6 mb-6">
    <form method="POST" action="{{ route('admin.suscripciones.planes.store') }}">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Nombre del Plan</label>
                <input type="text" name="nombre" required class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" placeholder="Ej: Paquete México">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Precio Base (sin IVA)</label>
                <input type="number" name="precio_base" step="0.01" min="0" required class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" placeholder="5000.00">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Descripción</label>
                <input type="text" name="descripcion" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" placeholder="Descripción breve">
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Máx. Usuarios</label>
                <input type="number" name="max_usuarios" min="1" required class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" value="5">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Máx. Operaciones/Mes</label>
                <input type="number" name="max_operaciones_mes" min="0" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" placeholder="Sin límite">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Máx. Documentos/Mes</label>
                <input type="number" name="max_documentos_mes" min="0" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" placeholder="Sin límite">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Máx. Modulaciones/Mes</label>
                <input type="number" name="max_modulaciones_mes" min="0" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" placeholder="Sin límite">
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-xs font-bold text-gray-600 mb-2">Reportes Habilitados</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @foreach(\App\Models\Tenant::getAllAvailableReports() as $id => $info)
                    @if($info['status'] === 'active')
                    <label class="flex items-center gap-2 text-xs cursor-pointer">
                        <input type="checkbox" name="reportes_habilitados[]" value="{{ $id }}" class="rounded border-gray-300 text-indigo-600">
                        <span>{{ $info['name'] }}</span>
                    </label>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-xs font-bold text-gray-600 mb-2">Features Habilitadas</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                <label class="flex items-center gap-2 text-xs cursor-pointer">
                    <input type="checkbox" name="features_habilitadas[]" value="email_notifications" class="rounded border-gray-300 text-indigo-600" checked>
                    <span>Email Notifications</span>
                </label>
                <label class="flex items-center gap-2 text-xs cursor-pointer">
                    <input type="checkbox" name="features_habilitadas[]" value="whatsapp_notifications" class="rounded border-gray-300 text-indigo-600">
                    <span>WhatsApp Notifications</span>
                </label>
                <label class="flex items-center gap-2 text-xs cursor-pointer">
                    <input type="checkbox" name="features_habilitadas[]" value="api_access" class="rounded border-gray-300 text-indigo-600">
                    <span>API Access</span>
                </label>
            </div>
        </div>
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-2 rounded-lg text-sm"><i class="fas fa-save mr-1"></i> Crear Plan</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($planes as $plan)
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex justify-between items-start mb-3">
            <h3 class="font-bold text-lg text-gray-800">{{ $plan->nombre }}</h3>
            <span class="text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded-full font-bold">{{ $plan->tenants_activos }} activos</span>
        </div>
        <p class="text-3xl font-black text-indigo-600 my-2">${{ number_format($plan->precio_base, 2) }}<span class="text-sm font-normal text-gray-400">/mes + IVA</span></p>
        @if($plan->descripcion)
            <p class="text-sm text-gray-500 mb-3">{{ $plan->descripcion }}</p>
        @endif
        <ul class="text-sm text-gray-600 space-y-1 mb-4">
            <li><i class="fas fa-users mr-1 text-indigo-400"></i> {{ $plan->max_usuarios }} usuarios</li>
            <li><i class="fas fa-file-invoice mr-1 text-indigo-400"></i> {{ $plan->max_operaciones_mes ?? '∞' }} operaciones/mes</li>
            <li><i class="fas fa-cloud-upload-alt mr-1 text-indigo-400"></i> {{ $plan->max_documentos_mes ?? '∞' }} docs/mes</li>
            <li><i class="fas fa-robot mr-1 text-indigo-400"></i> {{ $plan->max_modulaciones_mes ?? '∞' }} modulaciones/mes</li>
            <li><i class="fas fa-chart-bar mr-1 text-indigo-400"></i> {{ count($plan->reportes_habilitados ?? []) }} reportes</li>
        </ul>
        <form method="POST" action="{{ route('admin.suscripciones.planes.destroy', $plan->id) }}" class="mt-3">
            @csrf @method('DELETE')
            <button type="submit" onclick="return confirm('¿Eliminar este plan?')" class="text-red-500 hover:underline text-xs"><i class="fas fa-trash mr-1"></i>Eliminar</button>
        </form>
    </div>
    @endforeach
</div>
@endsection
