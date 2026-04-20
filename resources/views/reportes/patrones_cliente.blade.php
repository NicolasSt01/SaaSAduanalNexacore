@extends('layouts.app')

@section('title', 'Patrones de Cliente - NexaCore Analytics')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    indigo: {
                        50: '#f5f7ff',
                        100: '#ebf0fe',
                        200: '#ced9fd',
                        300: '#adc0fc',
                        400: '#8da7fa',
                        500: '#6d8ef9',
                        600: '#5a76cf',
                        700: '#485ea5',
                        800: '#36477c',
                        900: '#1d2541',
                    }
                }
            }
        }
    }
</script>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300 pb-12 font-['Nunito']">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm mb-8 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-chart-network"></i>
                        </div>
                        Análisis de <span class="text-indigo-600 dark:text-indigo-400">Patrones por Cliente</span>
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 font-medium italic">
                        Visualización vertical por ranking de actividad (Top 15 Clientes)
                    </p>
                </div>
                
                <div class="flex items-center gap-4">
                    <form method="GET" action="{{ route('reportes.patrones-cliente') }}" class="flex items-center gap-2">
                        <select name="mes" class="bg-gray-100 dark:bg-gray-700 border-none rounded-xl text-xs font-black text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all px-4 py-2">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $mesActual == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m, 1)->locale('es')->isoFormat('MMMM') }}
                                </option>
                            @endfor
                        </select>
                        <select name="anio" class="bg-gray-100 dark:bg-gray-700 border-none rounded-xl text-xs font-black text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all px-4 py-2">
                            @for($y = 2024; $y <= now()->year; $y++)
                                <option value="{{ $y }}" {{ $anioActual == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-md">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                    </form>
                    <button onclick="document.documentElement.classList.toggle('dark')" class="p-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-yellow-400 hover:scale-105 transition shadow-sm border border-gray-200 dark:border-gray-600">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:inline"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Legend Panel -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm mb-8 flex flex-wrap items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="flex flex-col">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-swatchbook text-indigo-500"></i> Intensidad Operativa
                    </span>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3.5 h-3.5 rounded bg-rose-500 shadow-sm"></div>
                            <span class="text-[10px] font-black text-gray-500 dark:text-gray-400">BAJO</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3.5 h-3.5 rounded bg-amber-500 shadow-sm"></div>
                            <span class="text-[10px] font-black text-gray-500 dark:text-gray-400">MEDIO</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3.5 h-3.5 rounded bg-emerald-500 shadow-sm"></div>
                            <span class="text-[10px] font-black text-gray-500 dark:text-gray-400">ALTO</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3.5 h-3.5 rounded bg-gray-200 dark:bg-gray-700 shadow-sm"></div>
                            <span class="text-[10px] font-black text-gray-500 dark:text-gray-400">INACTIVO</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <div class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Base Referencial</div>
                    <div class="text-[11px] font-bold text-gray-500 leading-tight italic">Comparativo automático contra el mismo periodo anterior</div>
                </div>
            </div>
        </div>

        <!-- Patterns Table-like Vertical List -->
        <div class="space-y-6">
            @foreach($patronesCliente as $cliente)
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-2xl hover:border-indigo-200 dark:hover:border-indigo-900 transition-all duration-300 group overflow-hidden">
                <div class="flex flex-col lg:flex-row divide-y lg:divide-y-0 lg:divide-x divide-gray-100 dark:divide-gray-700/50">
                    <!-- Column 1: Client Info (fixed width) -->
                    <div class="w-full lg:w-80 p-8 flex flex-col justify-between bg-gray-50/30 dark:bg-gray-800/50">
                        <div>
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg font-black shadow-lg
                                    {{ $cliente['ranking'] == 1 ? 'bg-amber-100 text-amber-600' : 
                                       ($cliente['ranking'] == 2 ? 'bg-gray-200 text-gray-600' : 
                                       ($cliente['ranking'] == 3 ? 'bg-orange-100 text-orange-700' : 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30')) }}">
                                    {{ $cliente['ranking'] }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-black text-gray-900 dark:text-white truncate uppercase tracking-tight group-hover:text-indigo-600 transition-colors" title="{{ $cliente['cliente_nombre'] }}">
                                        {{ $cliente['cliente_nombre'] }}
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">ID: {{ $cliente['cliente_id'] }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            @if($cliente['rangos']['es_nuevo'])
                                <div class="px-4 py-2 rounded-xl bg-sky-50 dark:bg-sky-900/20 text-sky-600 dark:text-sky-400 text-[10px] font-black uppercase tracking-widest border border-sky-100 dark:border-sky-800/40 inline-flex items-center gap-2">
                                    <i class="fas fa-star-shooting"></i> CLIENTE NUEVO
                                </div>
                            @else
                                <div class="grid grid-cols-1 gap-3">
                                    <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-gray-700/40 border border-gray-100 dark:border-gray-700">
                                        <span class="text-[9px] font-black text-gray-400 uppercase">Promedio Diario</span>
                                        <span class="text-xs font-black text-indigo-600 dark:text-indigo-400">{{ $cliente['rangos']['promedio'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-gray-700/40 border border-gray-100 dark:border-gray-700">
                                        <span class="text-[9px] font-black text-gray-400 uppercase">Máximo Histórico</span>
                                        <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">{{ $cliente['rangos']['maximo'] }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Mini Badges for products -->
                        <div class="flex flex-wrap gap-2 mt-8 pt-6 border-t border-gray-100 dark:border-gray-700/50">
                            @foreach($cliente['productos'] as $producto)
                            <div class="w-8 h-8 flex items-center justify-center bg-white dark:bg-gray-700 text-gray-400 dark:text-gray-500 rounded-lg border border-gray-100 dark:border-gray-600 shadow-xs hover:border-indigo-300 hover:text-indigo-500 transition-all cursor-help" title="{{ $producto }}">
                                {{ $controlador->obtenerIconoProducto($producto) }}
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Column 2: Heatmap Days Grid (The main responsive block) -->
                    <div class="flex-1 p-8 overflow-hidden">
                        <div class="flex items-center justify-between mb-6">
                            <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-th text-indigo-400"></i> Distribución Diaria
                            </span>
                            <span class="text-[10px] font-black text-gray-300 dark:text-gray-600 italic">Total de 31 días (Max)</span>
                        </div>

                        <!-- RESPONSIVE GRID: 2-3 columns on mobile, many on desktop -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 xl:grid-cols-10 2xl:grid-cols-15 gap-2.5">
                            @foreach($cliente['dias'] as $dia)
                            @php
                                $colorClasses = [
                                    'rojo' => 'bg-rose-500 text-white shadow-rose-200 dark:shadow-none',
                                    'amarillo' => 'bg-amber-500 text-amber-950 shadow-amber-200 dark:shadow-none',
                                    'verde' => 'bg-emerald-500 text-white shadow-emerald-200 dark:shadow-none',
                                    'gris' => 'bg-gray-100 dark:bg-gray-700/50 text-gray-300 dark:text-gray-500'
                                ][$dia['color']] ?? 'bg-gray-100';
                                
                                $isToday = $dia['es_hoy'];
                            @endphp
                            <div 
                                class="h-14 rounded-xl {{ $colorClasses }} flex flex-col items-center justify-center transition-all duration-300 hover:scale-105 cursor-pointer relative group/day shrink-0 {{ $isToday ? 'ring-4 ring-indigo-500 ring-offset-2 dark:ring-offset-gray-900 border-2 border-white dark:border-gray-800' : '' }}"
                                data-tippy-content="<strong>{{ $dia['dia_semana'] }} {{ $dia['dia_numero'] }}</strong><br>{{ $dia['cantidad'] }} operaciones"
                            >
                                @if($isToday)
                                    <div class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-indigo-600 border-2 border-white dark:border-gray-800 flex items-center justify-center">
                                        <i class="fas fa-clock text-[6px] text-white"></i>
                                    </div>
                                @endif
                                <span class="text-lg font-black leading-none">{{ $dia['cantidad'] }}</span>
                                <div class="flex items-center gap-1 opacity-60">
                                    <span class="text-[7px] font-black uppercase tracking-tighter">{{ substr($dia['dia_semana'], 0, 3) }}</span>
                                    <span class="text-[8px] font-black">{{ $dia['dia_numero'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Column 3: Total Monthly Stats -->
                    <div class="w-full lg:w-48 p-8 flex flex-col items-center justify-center bg-indigo-50/20 dark:bg-indigo-950/20">
                        <div class="text-center group-hover:scale-110 transition-transform duration-500">
                            <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-2">Mensual</span>
                            <div class="text-5xl font-black text-gray-900 dark:text-white mb-2 tracking-tighter">
                                {{ $cliente['total_mes'] }}
                            </div>
                            <div class="px-3 py-1 rounded-full bg-white dark:bg-gray-800 text-[10px] font-black text-indigo-600 dark:text-indigo-400 shadow-sm border border-gray-100 dark:border-gray-700">
                                OPERACIONES
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://unpkg.com/@@popperjs/core@@2"></script>
<script src="https://unpkg.com/tippy.js@@6"></script>
<style>
    .tippy-box[data-theme~='nexacore'] {
        background-color: #1e293b;
        color: #f8fafc;
        border-radius: 12px;
        font-size: 11px;
        padding: 4px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border: 1px solid #334155;
    }
    .dark .tippy-box[data-theme~='nexacore'] { background-color: #0f172a; border-color: #1e293b; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        tippy('[data-tippy-content]', {
            theme: 'nexacore',
            allowHTML: true,
            animation: 'shift-away',
        });
    });
</script>

@endsection