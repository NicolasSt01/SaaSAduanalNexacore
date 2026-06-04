@extends('layouts.admin')
@section('header_title', 'Planes de Suscripción')
@section('content')

<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('admin.finanzas.dashboard') }}" class="text-indigo-600 font-medium"><i class="fas fa-arrow-left"></i> Volver a Finanzas</a>
    <button onclick="document.getElementById('formPlan').classList.toggle('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold text-sm"><i class="fas fa-plus mr-1"></i> Nuevo Plan</button>
</div>

<div id="formPlan" class="hidden bg-gray-50 border rounded-xl p-4 mb-6">
    <form method="POST" action="{{ route('admin.finanzas.planes.store') }}">
        @csrf
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <input type="text" name="nombre" placeholder="Nombre del plan" required class="rounded-lg border-gray-300 text-sm px-3 py-2">
            <input type="number" name="precio_mensual" placeholder="Precio mensual" step="0.01" min="0" required class="rounded-lg border-gray-300 text-sm px-3 py-2">
            <input type="number" name="max_usuarios" placeholder="Máx. usuarios" min="1" required class="rounded-lg border-gray-300 text-sm px-3 py-2">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-2 rounded-lg text-sm">Crear Plan</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($planes as $plan)
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h3 class="font-bold text-lg text-gray-800">{{ $plan->nombre }}</h3>
        <p class="text-3xl font-black text-indigo-600 my-2">${{ number_format($plan->precio_mensual, 2) }}<span class="text-sm font-normal text-gray-400">/mes</span></p>
        <ul class="text-sm text-gray-600 space-y-1 mb-4">
            <li><i class="fas fa-users mr-1"></i> {{ $plan->max_usuarios }} usuarios</li>
            <li><i class="fas fa-file-invoice mr-1"></i> {{ $plan->max_operaciones_mes ?? '∞' }} operaciones/mes</li>
            <li><i class="fas fa-cloud-upload-alt mr-1"></i> {{ $plan->max_documentos_mes ?? '∞' }} docs/mes</li>
        </ul>
        <p class="text-xs text-gray-400">{{ $plan->tenants_count }} tenants asignados</p>
        <form method="POST" action="{{ route('admin.finanzas.planes.destroy', $plan->id) }}" class="mt-3">
            @csrf @method('DELETE')
            <button type="submit" onclick="return confirm('¿Eliminar este plan?')" class="text-red-500 hover:underline text-xs">Eliminar</button>
        </form>
    </div>
    @endforeach
</div>
@endsection
