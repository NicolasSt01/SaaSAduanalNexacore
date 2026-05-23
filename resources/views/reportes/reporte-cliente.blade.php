@extends('layouts.app')

@section('title', 'Reporte Detallado de Cliente')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
    }
</script>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300 pb-12">
    <!-- Header & Filters -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        Reporte por <span class="text-indigo-600 dark:text-indigo-400">Cliente</span>
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Análisis profundo de operaciones y comportamiento aduanal por cliente.</p>
                </div>
                
                <form method="GET" action="{{ route('reportes.cliente') }}" class="flex flex-wrap items-end gap-4 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-600 shadow-inner">
                    <div class="w-full sm:w-64">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1 ml-1">Seleccionar Cliente</label>
                        <select name="cliente_id" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                            <option value="">-- Todos los Clientes --</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}" {{ $clienteId == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-40">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1 ml-1">Desde</label>
                        <input type="date" name="desde" value="{{ $desde }}" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-indigo-500 transition-all">
                    </div>
                    <div class="w-full sm:w-40">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1 ml-1">Hasta</label>
                        <input type="date" name="hasta" value="{{ $hasta }}" class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-indigo-500 transition-all">
                    </div>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-black text-xs shadow-lg shadow-indigo-200 dark:shadow-none transition-all hover:-translate-y-0.5 whitespace-nowrap">
                        VER REPORTE
                    </button>
                    <button type="button" onclick="document.documentElement.classList.toggle('dark')" class="p-2.5 rounded-xl bg-white dark:bg-gray-800 text-gray-600 dark:text-yellow-400 border border-gray-200 dark:border-gray-600 shadow-sm">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:inline"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($cliente))
            <!-- Client Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 dark:text-white">{{ $cliente->nombre }}</h2>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40 px-2 py-0.5 rounded-md">Analítica de Periodo</span>
                        <span class="text-xs font-medium text-gray-500 tracking-wider uppercase">{{ \Carbon\Carbon::parse($desde)->format('d M, Y') }} — {{ \Carbon\Carbon::parse($hasta)->format('d M, Y') }}</span>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('reportes.cliente.pdf', ['cliente_id' => $clienteId, 'desde' => $desde, 'hasta' => $hasta]) }}"
                        class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 px-5 py-2.5 rounded-2xl font-bold text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                        <i class="fas fa-file-pdf text-rose-500"></i> Exportar PDF
                    </a>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Operaciones</span>
                    </div>
                    <div class="text-4xl font-black text-gray-900 dark:text-white mb-1">{{ $total }}</div>
                    <p class="text-xs text-gray-500 font-medium">Trámites acumulados en el periodo</p>
                </div>

                <!-- Green Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 text-emerald-500/10 group-hover:scale-110 transition-transform duration-500">
                        <i class="fas fa-check-circle text-8xl"></i>
                    </div>
                    <div class="flex items-center justify-between mb-4 relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Desaduanamiento Libre</span>
                    </div>
                    <div class="text-4xl font-black text-emerald-600 dark:text-emerald-400 mb-1 relative z-10">{{ $greens }}</div>
                    <div class="space-y-1 relative z-10">
                        @foreach($verdesPorAduana as $item)
                        <div class="flex justify-between items-center text-[10px] font-bold text-gray-500 dark:text-gray-400 px-1">
                            <span>{{ $item->aduana }}</span>
                            <span class="text-emerald-600 dark:text-emerald-400">{{ $item->total }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Red Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 text-rose-500/10 group-hover:scale-110 transition-transform duration-500">
                        <i class="fas fa-exclamation-triangle text-8xl"></i>
                    </div>
                    <div class="flex items-center justify-between mb-4 relative z-10">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Reconocimiento</span>
                    </div>
                    <div class="text-4xl font-black text-rose-600 dark:text-rose-400 mb-1 relative z-10">{{ $reds }}</div>
                    <div class="space-y-1 relative z-10">
                        @foreach($rojosPorAduana as $item)
                        <div class="flex justify-between items-center text-[10px] font-bold text-gray-500 dark:text-gray-400 px-1">
                            <span>{{ $item->aduana }}</span>
                            <span class="text-rose-600 dark:text-rose-400">{{ $item->total }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Modulación Chart Mini -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h5 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Efectividad de Modulación</h5>
                    <div class="h-24"><canvas id="greensReds"></canvas></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Historico Column -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm h-full flex flex-col">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                                <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span> Comportamiento Histórico
                            </h3>
                            <div class="flex gap-2">
                                <span class="text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 px-2 py-0.5 rounded">Mensual</span>
                            </div>
                        </div>
                        <div class="flex-grow min-h-[300px]">
                            <canvas id="historico"></canvas>
                        </div>
                        <div class="mt-6 overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="text-gray-400 font-black uppercase text-center border-b border-gray-100 dark:border-gray-700">
                                        @foreach(array_keys($historialMeses) as $m)
                                        <th class="pb-2">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('M') }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center font-black text-gray-700 dark:text-gray-200">
                                        @foreach($historialMeses as $v)
                                        <td class="pt-3">
                                            <span class="inline-block px-2 py-1 rounded bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600">{{ $v }}</span>
                                        </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Customs Distribution -->
                <div>
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm h-full">
                        <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight mb-6 flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-amber-500 rounded-full"></span> Distribución Aduanal
                        </h3>
                        <div class="h-64 mb-6">
                            <canvas id="aduanas"></canvas>
                        </div>
                        <div class="space-y-2">
                            @foreach($porAduana as $index => $a)
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-gray-700/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-black text-gray-400">#{{ $index+1 }}</span>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $a->nombre }}</span>
                                </div>
                                <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-800 px-2 py-1 rounded-lg border border-gray-100 dark:border-gray-600 shadow-sm">{{ $a->total }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 border border-gray-100 dark:border-gray-700 shadow-sm mb-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                    <div>
                        <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-rose-500 rounded-full"></span> Calendario de Actividad
                        </h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 font-medium">Volumen diario de trámites en el mes seleccionado.</p>
                    </div>
                    
                    <form method="GET" action="{{ route('reportes.cliente') }}" class="flex items-center gap-3">
                        <input type="hidden" name="cliente_id" value="{{ $clienteId }}">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-calendar-alt text-xs"></i>
                            </div>
                            <input type="month" name="mes_calendario" value="{{ $mesCalendario }}" onchange="this.form.submit()" class="pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700 border-none rounded-xl text-sm font-black text-indigo-600 dark:text-indigo-400 focus:ring-2 focus:ring-indigo-500 transition-all shadow-inner">
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-separate border-spacing-1.5 text-center table-fixed min-w-[600px]">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="pb-2">Lun</th>
                                <th class="pb-2">Mar</th>
                                <th class="pb-2">Mié</th>
                                <th class="pb-2">Jue</th>
                                <th class="pb-2">Vie</th>
                                <th class="pb-2">Sáb</th>
                                <th class="pb-2">Dom</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($calendario as $semana)
                            <tr>
                                @foreach($semana as $dia)
                                @php
                                    $isToday = \Carbon\Carbon::parse($dia['fecha'] ?? now())->isToday();
                                    $hasData = ($dia['total'] ?? 0) > 0;
                                @endphp
                                <td class="group aspect-square p-0 outline-none">
                                    <div class="w-full h-full rounded-2xl flex flex-col justify-between p-2.5 border transition-all duration-300
                                        {{ !($dia['actual'] ?? true) ? 'bg-gray-50/50 dark:bg-gray-800/20 border-transparent opacity-20' : 
                                           ($isToday ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-100 dark:shadow-none translate-y-[-2px]' : 
                                           ($hasData ? 'bg-white dark:bg-gray-700 border-indigo-100 dark:border-indigo-900 group-hover:border-indigo-500 dark:group-hover:border-indigo-400 shadow-sm' : 
                                           'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700 text-gray-300 dark:text-gray-600 opacity-60')) }}">
                                        
                                        <span class="text-xs font-black block text-left {{ $isToday ? 'text-white' : 'text-gray-800 dark:text-gray-200' }}">
                                            {{ $dia['dia'] }}
                                        </span>
                                        
                                        @if($hasData)
                                        <div class="text-center">
                                            <span class="text-sm font-black {{ $isToday ? 'text-white' : 'text-indigo-600 dark:text-indigo-400' }}">
                                                {{ $dia['total'] }}
                                            </span>
                                            <span class="block text-[8px] font-bold uppercase tracking-tighter {{ $isToday ? 'text-white/80' : 'text-gray-400' }}">Oper</span>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Client Script Resources -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const isDark = document.documentElement.classList.contains('dark');
                    const textColor = isDark ? '#9ca3af' : '#6b7280';
                    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

                    // CHART 1: Pie Modulación
                    new Chart(document.getElementById('greensReds'), {
                        type: 'pie',
                        data: {
                            labels: ['Verdes', 'Rojos'],
                            datasets: [{
                                data: [{{ $greens }}, {{ $reds }}],
                                backgroundColor: ['#10b981', '#f43f5e'],
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });

                    // CHART 2: Doughnut Aduanas
                    new Chart(document.getElementById('aduanas'), {
                        type: 'doughnut',
                        data: {
                            labels: {!! json_encode($porAduana->pluck('nombre')) !!},
                            datasets: [{
                                data: {!! json_encode($porAduana->pluck('total')) !!},
                                backgroundColor: ['#6366f1', '#8b5cf6', '#d946ef', '#ec4899', '#f43f5e'],
                                borderWidth: 0,
                                cutout: '70%'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });

                    // CHART 3: Line Historico
                    new Chart(document.getElementById('historico'), {
                        type: 'line',
                        data: {
                            labels: {!! json_encode(array_map(fn($m) => \Carbon\Carbon::create()->month($m)->translatedFormat('F'), array_keys($historialMeses))) !!},
                            datasets: [{
                                label: 'Operaciones',
                                data: {!! json_encode(array_values($historialMeses)) !!},
                                borderColor: '#4f46e5',
                                backgroundColor: 'rgba(79, 70, 229, 0.05)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointBackgroundColor: '#4f46e5',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { borderDash: [5, 5], color: gridColor },
                                    ticks: { color: textColor, font: { weight: 'bold' } }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { color: textColor, font: { weight: 'bold' } }
                                }
                            }
                        }
                    });
                });
            </script>
        @else
            <!-- Empty State -->
            <div class="py-24 text-center">
                <div class="bg-gray-100 dark:bg-gray-800 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-search text-gray-400 text-4xl"></i>
                </div>
                <h3 class="text-xl font-black text-gray-800 dark:text-white">Selecciona un cliente</h3>
                <p class="text-gray-500 max-w-sm mx-auto mt-2">Usa el filtro superior para generar el reporte analítico detallado de sus operaciones.</p>
            </div>
        @endif
    </div>
</div>
@endsection