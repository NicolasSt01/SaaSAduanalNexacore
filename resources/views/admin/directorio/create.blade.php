@extends('layouts.app')

@section('title', 'Nuevo Contacto - Directorio')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('directorio.index') }}"
            class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-800 tracking-tight">Nuevo Contacto <span class="text-indigo-600">de Directorio</span></h1>
            <p class="text-sm text-gray-500 mt-1 font-medium">Agrega un contacto importado desde WhatsApp.</p>
        </div>
    </div>

    @include('partials.alerts')

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <form action="{{ route('directorio.store') }}" method="POST">
            @csrf

            <!-- WhatsApp ID (hidden) -->
            @if(!empty($prefill['whatsapp_id']))
                <input type="hidden" name="whatsapp_chat_id" value="{{ $prefill['whatsapp_id'] }}">
            @endif

            <!-- Cliente ID -->
            <div class="mb-5">
                <label for="cliente_id" class="block text-sm font-bold text-gray-700 mb-1">Cliente <span class="text-red-500">*</span></label>
                <select name="cliente_id" id="cliente_id" required class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50">
                    <option value="">Seleccione un cliente...</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->nombre ?? $cliente->nombre_empresa }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-bold text-gray-700 mb-1">Nombre Completo <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" required value="{{ $prefill['nombre'] }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="Juan Pérez">
                </div>
                <!-- Puesto -->
                <div>
                    <label for="puesto" class="block text-sm font-bold text-gray-700 mb-1">Puesto</label>
                    <input type="text" name="puesto" id="puesto" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="Ej. Gerente de Tráfico">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                <!-- Correo -->
                <div>
                    <label for="correo" class="block text-sm font-bold text-gray-700 mb-1">Correo Electrónico <span class="text-red-500">*</span></label>
                    <input type="email" name="correo" id="correo" required class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="juan@cliente.com">
                </div>
                <!-- Teléfono Oficina -->
                <div>
                    <label for="telefono" class="block text-sm font-bold text-gray-700 mb-1">Teléfono Oficina</label>
                    <input type="text" name="telefono" id="telefono" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="(555) 123-4567">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                <!-- WhatsApp -->
                <div>
                    <label for="whatsapp" class="block text-sm font-bold text-gray-700 mb-1">Número de WhatsApp</label>
                    <input type="text" name="whatsapp" id="whatsapp" value="{{ $prefill['telefono'] }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="5218991234567">
                    <p class="text-xs text-gray-500 mt-1">Número importado desde WhatsApp: <code class="text-xs bg-gray-200 px-1 rounded">{{ $prefill['whatsapp_id'] ?: 'N/A' }}</code></p>
                </div>
                <!-- Canal Preferido -->
                <div>
                    <label for="canal_preferido" class="block text-sm font-bold text-gray-700 mb-1">Canal de Notificación Preferido</label>
                    <select name="canal_preferido" id="canal_preferido" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50">
                        <option value="ambos">Ambos (Correo y WhatsApp)</option>
                        <option value="email">Solo Correo</option>
                        <option value="whatsapp">Solo WhatsApp</option>
                    </select>
                </div>
            </div>

            <!-- Toggle Notificaciones Activas -->
            <div class="flex items-center justify-between p-4 bg-indigo-50 border border-indigo-100 rounded-xl mb-6">
                <div>
                    <h4 class="text-sm font-bold text-indigo-900">Activar Notificaciones Automáticas</h4>
                    <p class="text-xs text-indigo-700">El SOIA-Bot enviará notificaciones y despachos a este usuario.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="recibe_notificaciones" id="recibe_notificaciones" value="1" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none ring-4 ring-white rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('directorio.index') }}" class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-save mr-2"></i> Guardar Contacto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
