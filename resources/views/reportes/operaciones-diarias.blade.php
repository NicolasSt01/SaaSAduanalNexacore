@extends('layouts.app')

@section('title', 'Control de Operaciones Diarias')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
    }
</script>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300 pb-12">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm mb-8 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-satellite-dish"></i>
                        </div>
                        Monitoreo <span class="text-indigo-600 dark:text-indigo-400">Operativo</span>
                    </h1>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-[10px] font-black uppercase tracking-widest border border-indigo-100 dark:border-indigo-800">
                            {{ $fechaCarbon->locale('es')->isoFormat('dddd, D MMMM YYYY') }}
                        </span>
                        <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-400 dark:text-gray-500 ml-2">
                             <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Sistema en tiempo real
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-4">
                    <form method="GET" action="{{ route('reportes.operaciones-diarias') }}" class="flex items-center gap-2 bg-gray-100 dark:bg-gray-700 p-1.5 rounded-xl border border-gray-200 dark:border-gray-600">
                        <input type="date" name="fecha" value="{{ $fecha }}" class="bg-transparent border-none text-xs font-black text-gray-700 dark:text-gray-200 focus:ring-0 w-32">
                        <button type="submit" class="bg-indigo-600 text-white p-2 rounded-lg hover:bg-indigo-700 transition shadow-sm">
                            <i class="fas fa-search text-xs"></i>
                        </button>
                    </form>
                    <div class="flex items-center gap-2">
                        <button onclick="window.print()" class="p-2.5 rounded-xl bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition" title="Exportar PDF">
                            <i class="fas fa-print"></i>
                        </button>
                        <button onclick="document.documentElement.classList.toggle('dark')" class="p-2.5 rounded-xl bg-white dark:bg-gray-800 text-gray-600 dark:text-yellow-400 border border-gray-200 dark:border-gray-600 shadow-sm hover:scale-105 transition">
                            <i class="fas fa-moon dark:hidden"></i>
                            <i class="fas fa-sun hidden dark:inline"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Dashboard Summary Bar -->
        <div class="relative overflow-hidden mb-8 rounded-3xl bg-indigo-600 shadow-2xl shadow-indigo-200 dark:shadow-none p-8 text-white">
            <div class="absolute top-0 right-0 p-8 opacity-10 scale-150 rotate-12">
                <i class="fas fa-tachometer-alt text-9xl"></i>
            </div>
            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-3 gap-8 items-center">
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-4 mb-4">
                        <h2 class="text-xl font-black uppercase tracking-widest whitespace-nowrap">Control de Programa</h2>
                        <span class="px-3 py-1 rounded-full bg-white/20 text-[10px] font-black tracking-widest">{{ strtoupper($fechaCarbon->format('d-M-Y')) }}</span>
                    </div>
                    
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span class="text-[10px] font-black py-1 px-2 uppercase rounded-full bg-indigo-500 text-white">Eficiencia del Día</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black inline-block text-white" data-progreso-display>{{ $progresoDelDia }}%</span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-4 mb-4 text-xs flex rounded-full bg-indigo-900/30 border border-white/10 shadow-inner">
                            <div style="width: {{ $progresoDelDia }}%" data-progreso-bar class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-white transition-all duration-1000 ease-out"></div>
                        </div>
                    </div>
                    <p class="text-xs font-bold text-white/70 italic flex items-center gap-2" data-progreso-texto>
                        <i class="fas fa-check-circle"></i> {{ $completadas }} de {{ $totalRemesas }} remesas finalizadas exitosamente
                    </p>
                </div>
                <div class="flex flex-col items-center lg:items-end justify-center">
                    <div class="text-6xl font-black mb-1 drop-shadow-lg" data-progreso-display>{{ $progresoDelDia }}%</div>
                    <div class="text-[10px] font-black uppercase tracking-[0.3em] text-white/60">Compliance Index</div>
                </div>
            </div>
        </div>

        <!-- Metrics Layer -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative group overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Entidades</span>
                </div>
                <div class="text-2xl font-black text-gray-900 dark:text-white" data-total-clientes>{{ $totalClientes }}</div>
                <div class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter">Clientes Activos</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative group overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Carga</span>
                </div>
                <div class="text-2xl font-black text-gray-900 dark:text-white" data-total-remesas>{{ $totalRemesas }}</div>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="text-[9px] font-black text-emerald-500" data-completadas>{{ $completadas }} OK</span>
                    <span class="text-[9px] font-black text-amber-500" data-pendientes>{{ $pendientes }} PEND</span>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative group overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm">
                        <i class="fas fa-traffic-light"></i>
                    </div>
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Modulación</span>
                </div>
                <div class="flex items-end gap-1">
                    <div class="text-2xl font-black text-emerald-600" data-verdes>{{ $verdes }}</div>
                    <div class="text-sm font-black text-gray-300 mb-1">/</div>
                    <div class="text-lg font-black text-rose-500 mb-0.5" data-rojos>{{ $rojos }}</div>
                </div>
                <div class="text-[9px] font-black text-emerald-600/70" data-porcentaje-verdes>{{ $porcentajeVerdes }}% Tasa V</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative group overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Bot Status</span>
                </div>
                <div class="text-2xl font-black text-gray-900 dark:text-white" data-finalizadas>{{ $finalizadas }}<span class="text-sm text-gray-300"> / {{ $totalDia }}</span></div>
                @if($detenidas > 0)
                    <div class="text-[9px] font-black text-rose-500 animate-pulse uppercase tracking-tighter" data-detenidas-wrapper>
                        <i class="fas fa-exclamation-circle"></i> <span data-detenidas>{{ $detenidas }}</span> DETENIDAS
                    </div>
                @else
                    <div class="text-[9px] font-black text-emerald-500 uppercase tracking-tighter" data-detenidas-wrapper style="display:none">
                         OPTIMIZADO
                    </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm relative group overflow-hidden hidden lg:block">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm">
                        <i class="fas fa-truck-moving"></i>
                    </div>
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Unidades</span>
                </div>
                <div class="text-2xl font-black text-gray-900 dark:text-white" data-total-camiones>{{ $totalCamiones }}</div>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="text-[9px] font-black text-emerald-500" data-camiones-verdes>{{ $camionesVerdes }} V</span>
                    <span class="text-[9px] font-black text-rose-500" data-camiones-rojos>{{ $camionesRojos }} R</span>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
            <!-- Client Table Column -->
            <div class="lg:col-span-8">
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden h-full flex flex-col">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/80">
                        <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                            <i class="fas fa-building text-indigo-500"></i> Programa de Despacho
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Actualización Automática</span>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto flex-grow custom-scrollbar">
                        <table class="w-full text-left border-separate border-spacing-0">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    <th class="px-6 py-4">Denominación / Ticker</th>
                                    <th class="px-6 py-4 text-center">Volumen</th>
                                    <th class="px-6 py-4 text-center">R / V</th>
                                    <th class="px-6 py-4 text-center">Estado</th>
                                    <th class="px-6 py-4 text-right">Fulfillment</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700" data-exportadores-tbody>
                                @forelse($exportadoresData as $exportador)
                                    @php
                                        $progreso = $exportador['cantidad'] > 0 ? round(($exportador['completadas'] / $exportador['cantidad']) * 100) : 0;
                                    @endphp
                                    <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col gap-1.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-black text-gray-800 dark:text-gray-200 uppercase truncate max-w-[200px]">{{ $exportador['nombre'] }}</span>
                                                    @if($progreso == 100)
                                                        <i class="fas fa-check-circle text-emerald-500 animate-bounce"></i>
                                                    @endif
                                                </div>
                                                
                                                @if($progreso < 100 && isset($exportador['pendientes']) && count($exportador['pendientes']) > 0)
                                                    <div class="ticker-container rounded bg-gray-900 overflow-hidden py-1 px-3 border border-gray-800">
                                                        <div class="ticker-content flex gap-6 whitespace-nowrap text-[9px] font-black text-amber-400">
                                                            @foreach($exportador['pendientes'] as $p)
                                                                <span class="flex items-center gap-1.5 uppercase tracking-tighter">
                                                                    <i class="fas fa-sync-alt animate-spin-slow"></i> {{ $p['referencia'] }}
                                                                    <span class="text-[7px] bg-amber-400/20 px-1 rounded text-amber-500 border border-amber-400/30">{{ $p['estado'] }}</span>
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-[10px] font-black text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800">
                                                {{ $exportador['cantidad'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span class="text-[10px] font-black text-rose-500">{{ $exportador['rojos'] }}R</span>
                                                <span class="text-[10px] text-gray-300 font-bold">/</span>
                                                <span class="text-[10px] font-black text-emerald-500">{{ $exportador['verdes'] }}V</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center space-x-1">
                                             <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-1 rounded-md border border-emerald-100 dark:border-emerald-800 shadow-sm">{{ $exportador['completadas'] }} OK</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3 justify-end">
                                                <div class="w-16 bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                                                    <div class="h-full {{ $progreso == 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $progreso }}%"></div>
                                                </div>
                                                <span class="text-[10px] font-black text-gray-700 dark:text-gray-300 w-8 text-right">{{ $progreso }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-20 text-center">
                                            <div class="flex flex-col items-center">
                                                <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-300 dark:text-gray-600 mb-4">
                                                    <i class="fas fa-box-open text-3xl"></i>
                                                </div>
                                                <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest">Sin operaciones registradas</h4>
                                                <p class="text-[10px] text-gray-400 mt-1 italic font-medium uppercase tracking-widest">Status: Waiting for data injection</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Charts Column -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Dist Aduana -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                        <i class="fas fa-map-marked-alt text-indigo-500"></i> Mix por Aduana
                    </h3>
                    <div class="h-44">
                        <canvas id="ciudadesChart"></canvas>
                    </div>
                    <div class="mt-4 space-y-2" data-ciudades-detalle>
                        @foreach($ciudadesData as $ciudad)
                            <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-tight">
                                <span class="text-gray-500">{{ $ciudad['ciudad'] }}</span>
                                <span class="text-indigo-600 dark:text-indigo-400">{{ $ciudad['porcentaje'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Trend Line -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-line text-emerald-500"></i> Despeño Semanal
                    </h3>
                    <div class="h-44">
                        <canvas id="tramitesSemanaChart"></canvas>
                    </div>
                </div>
                
                 <!-- Mini Donut Row -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center">
                        <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-3">Modulación</span>
                        <div class="h-20 w-full">
                            <canvas id="modulacionChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center">
                        <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-3">Dodas Status</span>
                        <div class="h-20 w-full">
                            <canvas id="dodasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Next Day Preview -->
        @if($pedimentosProximos->count() > 0)
            <div class="bg-indigo-900 rounded-3xl p-8 text-white relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 opacity-5 scale-150">
                    <i class="fas fa-calendar-plus text-9xl"></i>
                </div>
                <div class="flex items-center gap-4 mb-8">
                     <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white border border-white/20">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black uppercase tracking-widest">Pre-Programa Operativo</h3>
                        <p class="text-[10px] text-white/50 font-bold uppercase tracking-[0.2em]">Pronóstico para el {{ $fechaCarbon->copy()->addDay()->format('d-M-Y') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6 relative z-10">
                    @foreach($pedimentosProximos as $pedimento)
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex flex-col items-center group hover:bg-white/10 transition-all cursor-default">
                            <span class="text-3xl font-black mb-1 text-indigo-300 transition-colors group-hover:text-white">{{ $pedimento['cantidad'] }}</span>
                            <span class="text-[9px] font-black uppercase tracking-widest text-white/40 group-hover:text-white/70">{{ $pedimento['aduana'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.2); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.4); }

    .ticker-content {
        animation: ticker 25s linear infinite;
    }
    @keyframes ticker {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .animate-spin-slow {
        animation: spin 3s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    @media print {
        .bg-indigo-600 { background: #4f46e5 !important; -webkit-print-color-adjust: exact; }
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    let charts = {};

    function initCharts() {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

        const chartConfig = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        };

        // Ciudades
        charts.ciudades = new Chart(document.getElementById('ciudadesChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($ciudadesData->pluck('ciudad')) !!},
                datasets: [{
                    data: {!! json_encode($ciudadesData->pluck('cantidad')) !!},
                    backgroundColor: ['#6366f1', '#f59e0b', '#ec4899', '#10b981'],
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: chartConfig
        });

        // Modulación
        charts.modulacion = new Chart(document.getElementById('modulacionChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Verdes', 'Rojos'],
                datasets: [{
                    data: [{{ $verdes }}, {{ $rojos }}],
                    backgroundColor: ['#10b981', '#f43f5e'],
                    borderWidth: 0,
                    cutout: '80%'
                }]
            },
            options: chartConfig
        });

        // DODAS
        charts.dodas = new Chart(document.getElementById('dodasChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Finalizadas', 'Pendientes'],
                datasets: [{
                    data: [{{ $finalizadas }}, {{ $totalDia - $finalizadas }}],
                    backgroundColor: ['#10b981', '#6366f1'],
                    borderWidth: 0,
                    cutout: '80%'
                }]
            },
            options: chartConfig
        });

        // Weekly Trend
        charts.semana = new Chart(document.getElementById('tramitesSemanaChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: @json(collect($tramitesSemanaActual)->pluck('dia')),
                datasets: [
                    {
                        label: 'Actual',
                        data: @json(collect($tramitesSemanaActual)->pluck('cantidad')),
                        borderColor: '#6366f1',
                        borderWidth: 3,
                        pointRadius: 4,
                        tension: 0.4,
                        fill: true,
                        backgroundColor: 'rgba(99, 102, 241, 0.05)'
                    },
                    {
                        label: 'Meta',
                        data: Array(7).fill(34),
                        borderColor: 'rgba(16, 185, 129, 0.4)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0
                    }
                ]
            },
            options: {
                ...chartConfig,
                scales: {
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, font: { weight: 'bold' } } },
                    x: { grid: { display : false }, ticks: { color: textColor, font: { weight: 'bold' } } }
                }
            }
        });
    }

    async function actualizarDatos() {
        try {
            const resp = await fetch(`{{ route('api.reportes.operaciones-diarias') }}?fecha={{ $fecha }}`);
            if (!resp.ok) return;
            const d = await resp.json();

            // Update Text Nodes
            document.querySelectorAll('[data-total-clientes]').forEach(n => n.textContent = d.totalClientes);
            document.querySelectorAll('[data-total-remesas]').forEach(n => n.textContent = d.totalRemesas);
            document.querySelectorAll('[data-total-camiones]').forEach(n => n.textContent = d.totalCamiones);
            document.querySelectorAll('[data-completadas]').forEach(n => n.textContent = d.completadas);
            document.querySelectorAll('[data-pendientes]').forEach(n => n.textContent = d.pendientes);
            document.querySelectorAll('[data-verdes]').forEach(n => n.textContent = d.verdes);
            document.querySelectorAll('[data-rojos]').forEach(n => n.textContent = d.rojos);
            document.querySelectorAll('[data-finalizadas]').forEach(n => n.textContent = d.finalizadas + ' / ' + d.totalDia);
            document.querySelectorAll('[data-porcentaje-verdes]').forEach(n => n.textContent = d.porcentajeVerdes + '% Tasa V');
            document.querySelectorAll('[data-progreso-display]').forEach(n => n.textContent = d.progresoDelDia + '%');
            
            // Update Bar
            document.querySelectorAll('[data-progreso-bar]').forEach(n => n.style.width = d.progresoDelDia + '%');

            // Update Detenidas
            const detnWrapper = document.querySelector('[data-detenidas-wrapper]');
            if (d.detenidas > 0) {
                detnWrapper.style.display = 'block';
                document.querySelector('[data-detenidas]').textContent = d.detenidas;
            } else {
                detnWrapper.style.display = 'none';
            }

            // Update Charts
            charts.modulacion.data.datasets[0].data = [d.verdes, d.rojos]; charts.modulacion.update();
            charts.dodas.data.datasets[0].data = [d.finalizadas, d.totalDia - d.finalizadas]; charts.dodas.update();

        } catch (e) {
            console.error("AutoUpdate failed", e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initCharts();
        setInterval(actualizarDatos, 30000); // 30s auto-refresh
        
        // Setup Infinite Tickers
        document.querySelectorAll('.ticker-content').forEach(t => {
            const clone = t.innerHTML;
            t.innerHTML = clone + clone;
        });
    });
</script>
@endpush
@endsection