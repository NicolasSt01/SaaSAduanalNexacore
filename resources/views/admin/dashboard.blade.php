@extends('layouts.app')

@section('title', 'Dashboard Gerencial | NexaCore')

@section('customcss')
<style>
    /* Estilos específicos para gráficos y transiciones que no se pueden hacer solo con tailwind */
    .chart-container {
        position: relative;
        width: 100%;
        transition: all 0.3s ease;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .dark .glass-card {
        background: rgba(31, 41, 55, 0.8);
    }

    /* Scrollbar personalizado para los filtros si se desbordan en móvil */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300 pb-12">

    <!-- 1. Header & Quick Filters -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <h1
                        class="text-3xl font-black text-gray-800 dark:text-white tracking-tight flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-th-large"></i>
                        </div>
                        Panel <span class="text-indigo-600 dark:text-indigo-400">Gerencial</span>
                    </h1>
                    <div class="flex items-center gap-2 mt-2">
                        <span
                            class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-[10px] font-black uppercase tracking-widest border border-indigo-100 dark:border-indigo-800">
                            {{ $filtroActivo }}
                        </span>
                        <span class="text-xs font-bold text-gray-400 dark:text-gray-500">
                            Agrupación por {{ $tipoAgrupacion }}
                        </span>
                    </div>
                </div>

                {{-- Dashboard Filters - Premium Selector --}}
                <div class="flex flex-wrap items-center gap-2 overflow-x-auto no-scrollbar pb-2 lg:pb-0">
                    @php
                    $periodos = [
                    '7dias' => ['label' => '7 Días', 'icon' => 'calendar-day'],
                    '30dias' => ['label' => '30 Días', 'icon' => 'calendar-week'],
                    'mes_actual' => ['label' => 'Mes Actual', 'icon' => 'calendar'],
                    'anio_actual' => ['label' => 'Anual', 'icon' => 'calendar-check'],
                    ];
                    @endphp

                    @foreach($periodos as $key => $info)
                    <button onclick="aplicarFiltro('{{ $key }}')"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest whitespace-nowrap transition-all
                            {{ $filtroRapido == $key ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 dark:shadow-none' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        <i class="fas fa-{{ $info['icon'] }} mr-2"></i> {{ $info['label'] }}
                    </button>
                    @endforeach

                    <button onclick="togglePersonalizado()"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all shadow-sm">
                        <i class="fas fa-sliders-h mr-2"></i> Rango Custom
                    </button>

                    <button type="button" onclick="document.documentElement.classList.toggle('dark')"
                        class="p-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-yellow-400">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:inline"></i>
                    </button>
                </div>
            </div>

            <!-- Panel Personalizado (Expandible) -->
            <div id="filtro-personalizado"
                class="mt-6 p-6 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-600 shadow-inner hidden animate-fade-in">
                <form method="GET" action="{{ route('admin.admindashboard') }}"
                    class="flex flex-col md:flex-row items-end gap-4">
                    <div class="w-full md:w-auto">
                        <label
                            class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1 ml-1">Fecha
                            Inicio</label>
                        <input type="date" name="fecha_inicio" value="{{ $inicio }}"
                            class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold focus:ring-indigo-500">
                    </div>
                    <div class="w-full md:w-auto">
                        <label
                            class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1 ml-1">Fecha
                            Fin</label>
                        <input type="date" name="fecha_fin" value="{{ $fin }}"
                            class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold focus:ring-indigo-500">
                    </div>
                    <button type="submit"
                        class="w-full md:w-auto bg-indigo-600 text-white px-8 py-2.5 rounded-xl font-black text-xs shadow-lg hover:bg-indigo-700 transition-all">
                        APLICAR RANGO
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Widget de Uso de Recursos del Tenant -->
        @if(!auth()->user()->isSuperAdmin())
        <div class="mb-8">
            <x-tenant-usage-widget :compact="true" />
        </div>
        @endif

        <!-- 2. Tarjetas KPI Principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <!-- Trámites Hoy (Primario) -->
            <div
                class="bg-indigo-600 rounded-3xl p-6 shadow-xl shadow-indigo-200 dark:shadow-none relative overflow-hidden group transform hover:-translate-y-1 transition-all duration-300">
                <div
                    class="absolute -right-4 -bottom-4 text-white/10 group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-clock text-9xl"></i>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div
                        class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center text-lg backdrop-blur-sm">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Trámites Hoy</span>
                </div>
                <div class="text-5xl font-black text-white mb-1 relative z-10 animate-fade-in">{{
                    number_format($tramitesHoy) }}</div>
                <p class="text-xs text-white/60 font-medium relative z-10 italic">{{ now()->translatedFormat('d F, Y')
                    }}</p>
            </div>

            <!-- Total Periodo -->
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span
                        class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Total
                        Periodo</span>
                </div>
                <div class="text-4xl font-black text-gray-900 dark:text-white mb-2">{{ number_format($tramitesTotales)
                    }}</div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500" style="width: 100%"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-2 font-bold">{{ $filtroActivo }}</p>
            </div>

            <!-- Clientes Activos -->
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                        <i class="fas fa-users"></i>
                    </div>
                    <span
                        class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Clientes
                        Activos</span>
                </div>
                <div class="text-4xl font-black text-gray-900 dark:text-white mb-2">{{ number_format($clientesActivos)
                    }}</div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500" style="width: 100%"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-2 font-bold">Empresas Operando</p>
            </div>

            <!-- Modulación Verde % -->
            @php $porcentajeVerde = ($tramitesTotales > 0) ? round(($verdes / $tramitesTotales) * 100, 1) : 0; @endphp
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center text-lg">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <span
                        class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Desaduanamiento
                        Libre</span>
                </div>
                <div class="text-4xl font-black text-emerald-600 mb-2">{{ $porcentajeVerde }}%</div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500" style="width: {{ $porcentajeVerde }}%"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-2 font-bold">{{ $verdes }} de {{ $tramitesTotales }} operaciones
                </p>
            </div>
        </div>

        <!-- 3. Gráficos Principales (Distribución) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

            <!-- Progreso de Trámites (Line Chart) -->
            <div class="lg:col-span-2 space-y-8">
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm h-full flex flex-col">
                    <div class="flex items-center justify-between mb-8">
                        <h3
                            class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span> Progreso Operativo
                        </h3>
                        <div class="flex gap-2">
                            <span
                                class="text-[10px] font-black bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 px-3 py-1 rounded-lg border border-indigo-100 dark:border-indigo-800">
                                {{ ucfirst($tipoAgrupacion) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow min-h-[350px]">
                        <canvas id="tramitesLineChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Modulación (Doughnut) -->
            <div class="space-y-8">
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm h-full flex flex-col">
                    <h3
                        class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight mb-8 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-rose-500 rounded-full"></span> Efectividad de Modulación
                    </h3>
                    <div class="flex-grow flex items-center justify-center min-h-[300px]">
                        <canvas id="modulacionChart"></canvas>
                    </div>
                    <div class="mt-8 grid grid-cols-2 gap-4">
                        <div
                            class="bg-emerald-50 dark:bg-emerald-900/20 p-3 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                            <div class="text-[10px] font-black text-emerald-600 uppercase mb-1">Verdes</div>
                            <div class="text-xl font-black text-emerald-700 dark:text-emerald-400">{{ $verdes }}</div>
                        </div>
                        <div
                            class="bg-rose-50 dark:bg-rose-900/20 p-3 rounded-2xl border border-rose-100 dark:border-rose-800">
                            <div class="text-[10px] font-black text-rose-600 uppercase mb-1">Rojos</div>
                            <div class="text-xl font-black text-rose-700 dark:text-rose-400">{{ $rojos }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Top Clientes & Radar Aduanas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

            <!-- Trámites por Cliente (H-Bar Chart) -->
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm">
                <h3
                    class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight mb-8 flex items-center gap-2">
                    <i class="fas fa-star text-amber-500"></i> Top clientes del periodo
                </h3>
                <div class="min-h-[450px]">
                    <canvas id="clientesChart"></canvas>
                </div>
            </div>

            <!-- Radar de Aduanas -->
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm">
                <h3
                    class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight mb-8 flex items-center gap-2">
                    <i class="fas fa-map-marked-alt text-blue-500"></i> Distribución Aduanal Anual
                </h3>
                <div class="min-h-[450px] flex items-center justify-center">
                    <canvas id="aduanaRadarChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 5. Pareto & Productos -->
        <div class="grid grid-cols-1 gap-8 mb-8">

            <!-- Análisis de Pareto -->
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3
                            class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                            <i class="fas fa-percentage text-purple-500"></i> Análisis de Pareto (Concentración)
                        </h3>
                        <p class="text-xs text-gray-400 mt-1">Determinación del 80/20 en el volumen de negocio por
                            cliente.</p>
                    </div>
                </div>
                <div class="min-h-[400px]">
                    <canvas id="pareto-chart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Productos (Bar Chart) -->
            <div
                class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm">
                <h3
                    class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight mb-8 flex items-center gap-2">
                    <i class="fas fa-box-open text-sky-500"></i> Top 10 Productos
                </h3>
                <div class="min-h-[400px]">
                    <canvas id="productos-chart"></canvas>
                </div>
            </div>

            <!-- Tabla de Operaciones por Mes -->
            <div
                class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">
                <div
                    class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/80">
                    <h3
                        class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                        <i class="fas fa-table text-indigo-500"></i> Desglose Mensual {{ date('Y') }}
                    </h3>
                    <span
                        class="text-[10px] font-black bg-white dark:bg-gray-700 px-3 py-1 rounded-full border border-gray-100 dark:border-gray-600 text-gray-500">Resumen
                        Anual</span>
                </div>
                <div class="overflow-x-auto flex-grow">
                    <table class="w-full text-left">
                        <thead>
                            <tr
                                class="bg-gray-50 dark:bg-gray-700/30 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="px-6 py-4">Mes de Operación</th>
                                <th class="px-6 py-4 text-center">Volumen</th>
                                <th class="px-6 py-4 text-right">Comparativa Anual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @php $totalAnual = array_sum($operacionesRadarData); @endphp
                            @foreach($operacionesRadarLabels as $index => $mes)
                            <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase">{{ $mes }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="px-3 py-1 rounded-lg bg-gray-50 dark:bg-gray-700 text-xs font-black text-gray-800 dark:text-white border border-gray-100 dark:border-gray-600 shadow-sm">
                                        {{ number_format($operacionesRadarData[$index]) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php $porcPareto = ($totalAnual > 0) ? ($operacionesRadarData[$index] / $totalAnual)
                                    * 100 : 0; @endphp
                                    <div class="flex items-center gap-3 justify-end">
                                        <span class="text-[11px] font-black text-indigo-600 dark:text-indigo-400">{{
                                            number_format($porcPareto, 1) }}%</span>
                                        <div
                                            class="w-16 bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                                            <div class="h-full bg-indigo-500" style="width: {{ $porcPareto }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-700/50">
                            <tr class="border-t-2 border-indigo-100 dark:border-indigo-900">
                                <td class="px-6 py-4 text-xs font-black text-gray-500 uppercase">Total Acumulado</td>
                                <td
                                    class="px-6 py-4 text-center font-black text-indigo-600 dark:text-indigo-400 text-lg">
                                    {{ number_format($totalAnual) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-[10px] font-black text-gray-400 uppercase">Operaciones
                                        Totales</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function aplicarFiltro(filtro) {
        const url = new URL(window.location.href);
        url.searchParams.set('filtro_rapido', filtro);
        url.searchParams.delete('fecha_inicio');
        url.searchParams.delete('fecha_fin');
        window.location.href = url.toString();
    }

    function togglePersonalizado() {
        const panel = document.getElementById('filtro-personalizado');
        panel.classList.toggle('hidden');
    }

    document.addEventListener("DOMContentLoaded", function () {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
        const accentColor = '#4f46e5'; // Indigo-600

        // Configuración Común
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5], color: gridColor },
                    ticks: { color: textColor, font: { weight: 'bold', size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: textColor, font: { weight: 'bold', size: 10 } }
                }
            }
        };

        // 1. Line Chart: Trámites
        new Chart(document.getElementById('tramitesLineChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($tramitesDiasLabels ?? []) !!},
                datasets: [{
                    label: 'Operaciones',
                    data: {!! json_encode($tramitesDiasData ?? []) !!},
                    borderColor: accentColor,
                    backgroundColor: isDark ? 'rgba(79, 70, 229, 0.1)' : 'rgba(79, 70, 229, 0.05)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: accentColor,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: commonOptions
        });

        // 2. Doughnut Chart: Modulación
        new Chart(document.getElementById('modulacionChart'), {
            type: 'doughnut',
            data: {
                labels: ['Verdes', 'Rojos'],
                datasets: [{
                    data: [{{ $verdes }}, {{ $rojos }}],
                    backgroundColor: ['#10b981', '#f43f5e'],
                    borderWidth: 0,
                    weight: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // 3. Horizontal Bar Chart: Clientes
        new Chart(document.getElementById('clientesChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($clientesLabels ?? []) !!},
                datasets: [{
                    data: {!! json_encode($clientesData ?? []) !!},
                    backgroundColor: 'rgba(79, 70, 229, 0.8)',
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                ...commonOptions,
                scales: {
                    x: { ...commonOptions.scales.y },
                    y: { ...commonOptions.scales.x }
                }
            }
        });

        // 4. Radar Chart: Aduanas
        const radarDataPHP = {!! json_encode($radarData ?? []) !!};
        const colors = ['#4f46e5', '#10b981', '#f59e0b', '#f43f5e', '#0ea5e9'];
        let idx = 0;
        
        new Chart(document.getElementById('aduanaRadarChart'), {
            type: 'radar',
            data: {
                labels: {!! json_encode($radarLabels ?? []) !!},
                datasets: Object.entries(radarDataPHP).map(([name, data]) => ({
                    label: name,
                    data: data,
                    borderColor: colors[idx++ % colors.length],
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 2
                })).slice(0, 5) // Mostramos solo top 5 para no saturar
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'bottom', labels: { color: textColor, font: { size: 10, weight: 'bold' } } }
                },
                scales: {
                    r: {
                        angleLines: { color: gridColor },
                        grid: { color: gridColor },
                        pointLabels: { color: textColor, font: { weight: 'bold' } },
                        ticks: { display: false }
                    }
                }
            }
        });

        // 5. Pareto Chart
        new Chart(document.getElementById('pareto-chart'), {
            data: {
                labels: {!! json_encode($paretoLabels ?? []) !!},
                datasets: [
                    {
                        type: 'line',
                        label: '% Acumulado',
                        data: {!! json_encode($paretoLinea ?? []) !!},
                        borderColor: '#f43f5e',
                        borderWidth: 3,
                        yAxisID: 'percentage',
                        pointRadius: 4,
                        fill: false
                    },
                    {
                        type: 'bar',
                        label: 'Operaciones',
                        data: {!! json_encode($paretoBarras ?? []) !!},
                        backgroundColor: 'rgba(79, 70, 229, 0.7)',
                        yAxisID: 'counts',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                ...commonOptions,
                scales: {
                    counts: {
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor }
                    },
                    percentage: {
                        position: 'right',
                        max: 100,
                        beginAtZero: true,
                        grid: { display: false },
                        ticks: { 
                            color: '#f43f5e',
                            callback: value => value + '%'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { display: false } // Ocultamos labels largos
                    }
                }
            }
        });

        // 6. Productos Bar Chart
        new Chart(document.getElementById('productos-chart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($productosLabels ?? []) !!},
                datasets: [{
                    data: {!! json_encode($productosData ?? []) !!},
                    backgroundColor: '#0ea5e9',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                ...commonOptions,
                scales: {
                    x: { ...commonOptions.scales.y },
                    y: { ...commonOptions.scales.x }
                }
            }
        });
    });
</script>
@endpush
