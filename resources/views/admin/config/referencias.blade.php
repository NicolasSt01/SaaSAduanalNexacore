@extends('layouts.app')

@section('title', 'Configuración de Referencias')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.config') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">
                            <i class="fas fa-cog mr-2"></i> Configuración
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                            <span class="text-sm font-medium text-gray-700">Referencias</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Personalización de <span class="text-violet-600">Referencias</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Define el formato de folio para tus nuevas operaciones.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-emerald-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-emerald-700 font-bold">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <div class="max-w-3xl">
            <div class="flex items-center gap-4 mb-8">
                <div class="bg-violet-100 text-violet-600 w-16 h-16 rounded-2xl flex justify-center items-center text-2xl shadow-inner">
                    <i class="fas fa-hashtag"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Folio y Correlativo</h3>
                    <p class="text-sm text-gray-500">Configura el prefijo de tu empresa y el sistema de numeración automática.</p>
                </div>
            </div>

            <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6 mb-8">
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-3">Vista previa del folio generado</p>
                <div class="flex items-baseline gap-2">
                    <span id="previewPrefix" class="text-4xl font-black text-violet-600 tracking-tighter">{{ strtoupper($tenant->referencia_prefijo ?? 'REF') }}</span>
                    <span class="text-4xl font-black text-gray-300">-</span>
                    <span class="text-4xl font-black text-gray-800 tracking-tighter">{{ date('y') }}00001</span>
                </div>
                <div class="mt-6 flex flex-col gap-3">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-info-circle text-violet-500 mt-1"></i>
                        <p class="text-sm text-gray-600">
                            <strong>Año Corriente:</strong> Los dos primeros dígitos después del guión representan el año (<strong>{{ date('y') }}</strong> para el 20{{ date('y') }}).
                        </p>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-sync-alt text-violet-500 mt-1"></i>
                        <p class="text-sm text-gray-600">
                            <strong>Reinicio Anual:</strong> Al iniciar el año 20{{ date('y')+1 }}, el sistema cambiará automáticamente a {{ date('y')+1 }}00001.
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.guardar-referencia') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="referencia_prefijo" class="block text-sm font-bold text-gray-700 mb-2">Prefijo de la Agencia (Abreviación)</label>
                    <div class="flex gap-4">
                        <input type="text" name="referencia_prefijo" id="referencia_prefijo"
                            value="{{ old('referencia_prefijo', $tenant->referencia_prefijo ?? '') }}"
                            maxlength="10"
                            class="w-48 rounded-2xl border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-xl p-4 border shadow-sm bg-gray-50/50 uppercase tracking-widest font-black text-center"
                            placeholder="Ej. ABC"
                            oninput="document.getElementById('previewPrefix').textContent = this.value.toUpperCase() || 'REF'">
                        
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl border border-transparent bg-violet-600 px-8 py-4 text-lg font-bold text-white shadow-lg hover:bg-violet-700 transition-all transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2">
                            <i class="fas fa-save mr-2"></i> Guardar Cambios
                        </button>
                    </div>
                    @error('referencia_prefijo')
                        <p class="text-sm text-red-500 mt-2 font-bold">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-3">Usa caracteres alfanuméricos. El prefijo ayuda a identificar tus operaciones de las de otros agentes.</p>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tailwind script for immediate preview in dev if needed -->
<script src="https://cdn.tailwindcss.com"></script>
@endsection
