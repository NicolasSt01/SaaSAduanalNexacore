@extends('layouts.app')

@section('title', 'Configuración de Analíticas y Metas')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.config') }}" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-800 tracking-tight">Analíticas y <span class="text-rose-600">Metas</span></h1>
            <p class="text-sm text-gray-500 mt-1 font-medium">Configura los objetivos operacionales de tu agencia para el dashboard de gerencia.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-emerald-500 mr-3"></i>
            <p class="text-sm text-emerald-700 font-bold">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
            <p class="text-sm text-red-700 font-bold">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.config.guardar-analiticas') }}">
        @csrf

        <!-- Metas Diarias -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 text-lg">
                    <i class="fas fa-sun"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Metas Diarias</h2>
                    <p class="text-xs text-gray-400">Objetivos de operaciones por día. Se usan en la gráfica de comparativa diaria.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-600 mb-1.5">
                        <i class="fas fa-check-circle text-emerald-500 mr-1"></i> Meta Ideal
                    </label>
                    <input type="number" name="meta_ideal_diaria" value="{{ $config['meta_ideal_diaria'] ?? 33 }}"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 text-lg font-bold text-gray-800" min="1" required>
                    <p class="text-xs text-gray-400 mt-1">Nivel óptimo de operaciones/día</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-600 mb-1.5">
                        <i class="fas fa-minus-circle text-amber-500 mr-1"></i> Meta Buena
                    </label>
                    <input type="number" name="meta_buena_diaria" value="{{ $config['meta_buena_diaria'] ?? 27 }}"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 text-lg font-bold text-gray-800" min="1" required>
                    <p class="text-xs text-gray-400 mt-1">Nivel aceptable de operaciones/día</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-600 mb-1.5">
                        <i class="fas fa-times-circle text-red-500 mr-1"></i> Meta Mínima
                    </label>
                    <input type="number" name="meta_mala_diaria" value="{{ $config['meta_mala_diaria'] ?? 25 }}"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 text-lg font-bold text-gray-800" min="1" required>
                    <p class="text-xs text-gray-400 mt-1">Por debajo de este nivel es bajo rendimiento</p>
                </div>
            </div>
        </div>

        <!-- Metas Mensuales -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 text-lg">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Metas Mensuales</h2>
                    <p class="text-xs text-gray-400">Objetivos de operaciones por mes. Se usan en la vista anual y tabla de cumplimiento.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-600 mb-1.5">
                        <i class="fas fa-check-circle text-emerald-500 mr-1"></i> Meta Ideal
                    </label>
                    <input type="number" name="meta_ideal_mensual" value="{{ $config['meta_ideal_mensual'] ?? 1000 }}"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 text-lg font-bold text-gray-800" min="1" required>
                    <p class="text-xs text-gray-400 mt-1">Nivel óptimo de operaciones/mes</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-600 mb-1.5">
                        <i class="fas fa-minus-circle text-amber-500 mr-1"></i> Meta Buena
                    </label>
                    <input type="number" name="meta_buena_mensual" value="{{ $config['meta_buena_mensual'] ?? 800 }}"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 text-lg font-bold text-gray-800" min="1" required>
                    <p class="text-xs text-gray-400 mt-1">Nivel aceptable de operaciones/mes</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-600 mb-1.5">
                        <i class="fas fa-times-circle text-red-500 mr-1"></i> Meta Mínima
                    </label>
                    <input type="number" name="meta_mala_mensual" value="{{ $config['meta_mala_mensual'] ?? 750 }}"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 text-lg font-bold text-gray-800" min="1" required>
                    <p class="text-xs text-gray-400 mt-1">Por debajo de este nivel es bajo rendimiento</p>
                </div>
            </div>
        </div>

        <!-- Proyecciones (líneas de referencia en gráfica diaria) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center text-violet-600 text-lg">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Líneas de Proyección</h2>
                    <p class="text-xs text-gray-400">Líneas de referencia adicionales que aparecen en la gráfica comparativa diaria.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-600 mb-1.5">
                        <i class="fas fa-arrow-trend-up text-indigo-500 mr-1"></i> Proyección 1
                    </label>
                    <input type="number" name="proyeccion_1" value="{{ $config['proyeccion_1'] ?? 40 }}"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 text-lg font-bold text-gray-800" min="1" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-600 mb-1.5">
                        <i class="fas fa-arrow-trend-up text-purple-500 mr-1"></i> Proyección 2
                    </label>
                    <input type="number" name="proyeccion_2" value="{{ $config['proyeccion_2'] ?? 50 }}"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 text-lg font-bold text-gray-800" min="1" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-600 mb-1.5">
                        <i class="fas fa-bullseye text-amber-500 mr-1"></i> Meta Media Diaria (línea)
                    </label>
                    <input type="number" name="meta_media_diaria" value="{{ $config['meta_media_diaria'] ?? 80 }}"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 text-lg font-bold text-gray-800" min="1" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-600 mb-1.5">
                        <i class="fas fa-bullseye text-emerald-500 mr-1"></i> Meta Alta Diaria (línea)
                    </label>
                    <input type="number" name="meta_alta_diaria" value="{{ $config['meta_alta_diaria'] ?? 100 }}"
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 text-lg font-bold text-gray-800" min="1" required>
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-indigo-500 mt-0.5"></i>
                <p class="text-sm text-indigo-700">Estos valores se reflejarán automáticamente en el <strong>Dashboard de Gerencia</strong>: las líneas de meta en las gráficas, los colores por cumplimiento, y la tabla de detalle mensual.</p>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.config') }}" class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <i class="fas fa-save mr-1"></i> Guardar Configuración
            </button>
        </div>
    </form>
</div>
@endsection
