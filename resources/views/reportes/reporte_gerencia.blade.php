@extends('layouts.app')

@section('title', 'Reporte de Gerencia - Dashboard Operacional')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    darkMode: 'class',
}
</script>

<div id="gerenciaDash" class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

    <!-- DARK MODE TOGGLE + HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-800 dark:text-white tracking-tight">
                <i class="fas fa-tachometer-alt text-indigo-500 mr-2"></i>Dashboard de <span class="text-indigo-600 dark:text-indigo-400">Gerencia</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium">Panel ejecutivo de operaciones aduanales</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <span class="inline-flex items-center gap-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm">
                <i class="fas fa-calendar-alt text-indigo-500"></i>
                {{ $fechaInicio->format('d M, Y') }} — {{ $fechaFin->format('d M, Y') }}
            </span>
            <span class="inline-flex items-center gap-1.5 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 text-amber-700 dark:text-amber-300 text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm">
                <i class="fas fa-history"></i>
                vs {{ $fechaInicioAnterior->format('d M') }} — {{ $fechaFinAnterior->format('d M') }}
            </span>
            <span class="inline-flex items-center gap-1.5 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300 text-xs font-bold px-3 py-1.5 rounded-lg">
                {{ $diasEnPeriodo }} días
            </span>
            <button onclick="document.getElementById('gerenciaDash').classList.toggle('dark')" class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-yellow-300 hover:scale-105 transition" title="Modo oscuro">
                <i class="fas fa-moon dark:hidden"></i><i class="fas fa-sun hidden dark:inline"></i>
            </button>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-800 dark:to-purple-800 rounded-2xl shadow-lg p-6">
        <form id="filterForm" method="GET" action="{{ route('reportes.gerencia') }}">
            <div class="flex flex-wrap gap-2 justify-center">
                @foreach([
                    'semana_actual' => ['Semana Actual', 'fa-calendar-week'],
                    'semana_anterior' => ['Semana Anterior', 'fa-arrow-left'],
                    'mes_actual' => ['Mes Actual', 'fa-calendar'],
                    'mes_anterior' => ['Mes Anterior', 'fa-arrow-left'],
                ] as $val => [$label, $icon])
                <button type="submit" name="tipo_filtro" value="{{ $val }}"
                    class="px-4 py-2 rounded-xl text-sm font-bold transition-all duration-200 shadow-sm
                    {{ $tipoFiltro == $val ? 'bg-amber-400 text-gray-900 scale-105 ring-2 ring-amber-300' : 'bg-white/90 text-indigo-700 hover:bg-white hover:-translate-y-0.5' }}">
                    <i class="fas {{ $icon }} mr-1"></i> {{ $label }}
                </button>
                @endforeach
                <button type="button" onclick="document.getElementById('customDateModal').classList.remove('hidden')"
                    class="px-4 py-2 rounded-xl text-sm font-bold bg-white/90 text-indigo-700 hover:bg-white hover:-translate-y-0.5 transition-all shadow-sm">
                    <i class="fas fa-calendar-alt mr-1"></i> Rango Personalizado
                </button>
            </div>
        </form>
    </div>

    <!-- KPIs PRINCIPALES -->
    <div>
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-6 bg-indigo-500 rounded-full"></span> Indicadores Principales
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @php
                $kpis = [
                    ['Operaciones Totales', $operacionesTotales, $operacionesTotalesAnterior, 'fa-chart-bar', 'indigo', null],
                    ['Desaduanamiento Libre', $operacionesVerdes, $operacionesVerdesAnterior, 'fa-check-circle', 'emerald', $operacionesTotales > 0 ? round(($operacionesVerdes/$operacionesTotales)*100,1).'% del total' : '0%'],
                    ['Reconocimiento Aduanero', $operacionesRojas, $operacionesRojasAnterior, 'fa-times-circle', 'red', $operacionesTotales > 0 ? round(($operacionesRojas/$operacionesTotales)*100,1).'% del total' : '0%'],
                    ['Camiones', $totalCamiones, $totalCamionesAnterior, 'fa-truck', 'amber', '✓ '.$camionesVerdes.' Verdes | ✗ '.$camionesRojos.' Rojos'],
                ];
            @endphp
            @foreach($kpis as [$label, $actual, $anterior, $icon, $color, $extra])
            @php $diff = $actual - $anterior; $pct = $anterior > 0 ? round(($diff/$anterior)*100,1) : 0; @endphp
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ $label }}</p>
                        <p class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($actual) }}</p>
                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-md {{ $diff > 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' : ($diff < 0 ? 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400') }}">
                            <i class="fas fa-{{ $diff > 0 ? 'arrow-up' : ($diff < 0 ? 'arrow-down' : 'minus') }}"></i>
                            {{ $diff > 0 ? '+' : '' }}{{ $diff }} ({{ $pct }}%)
                        </span>
                        @if($extra)<p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $extra }}</p>@endif
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-{{ $color }}-50 dark:bg-{{ $color }}-900/30 flex items-center justify-center text-{{ $color }}-500 text-xl">
                        <i class="fas {{ $icon }}"></i>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- KPIs SECUNDARIOS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @php
            $kpis2 = [
                ['Permisos Sobrepeso', $totalSobrepeso, $totalSobrepesoAnterior, 'fa-exclamation-triangle', 'cyan', $operacionesTotales > 0 ? round(($totalSobrepeso/$operacionesTotales)*100,1).'% del total' : '0%'],
                ['Uso de Básculas', $totalBasculas, $totalBasculasAnterior, 'fa-tachometer-alt', 'teal', 'Promedio: '.($diasEnPeriodo > 0 ? round($totalBasculas/$diasEnPeriodo,1) : 0).'/día'],
                ['Clientes Activos', $clientesSemana->count(), $clientesSemanaAnterior->count(), 'fa-users', 'violet', 'Con 1+ operaciones'],
                ['Pedimentos', $pedimentosPorAduanaPatente->sum('total_pedimentos'), 0, 'fa-file-alt', 'slate', 'Distintos por aduana/patente'],
            ];
        @endphp
        @foreach($kpis2 as [$label, $actual, $anterior, $icon, $color, $extra])
        @php $diff = $actual - $anterior; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex justify-between items-start">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ $label }}</p>
                    <p class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($actual) }}</p>
                    @if($anterior > 0)
                    <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-md {{ $diff > 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' : ($diff < 0 ? 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-gray-100 text-gray-500') }}">
                        {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                    </span>
                    @endif
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $extra }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 text-xl">
                    <i class="fas {{ $icon }}"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- GRÁFICA COMPARATIVA DIARIA -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-wrap justify-between items-center gap-2">
            <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <span class="w-1 h-5 bg-indigo-500 rounded-full"></span> Operaciones Diarias — Comparativa
            </h3>
            <div class="flex gap-2">
                <span class="text-xs font-bold bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 px-2.5 py-1 rounded-md">Período Actual</span>
                <span class="text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-2.5 py-1 rounded-md">Período Anterior</span>
            </div>
        </div>
        <div class="p-6"><div class="h-[350px]"><canvas id="comparativaChart"></canvas></div></div>
    </div>

    <!-- DISTRIBUCIÓN MODULACIÓN -->
    <div>
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-6 bg-indigo-500 rounded-full"></span> Distribución por Modulación
        </h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4"><i class="fas fa-chart-pie mr-2 text-indigo-500"></i>Gráfica de Distribución</h4>
                <div class="h-[280px]"><canvas id="modulacionChart"></canvas></div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 space-y-4">
                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2"><i class="fas fa-list-check mr-2 text-indigo-500"></i>Detalle de Modulación</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 text-center">
                        <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($operacionesVerdes) }}</p>
                        <p class="text-sm font-bold text-gray-700 dark:text-gray-300 mt-1">Desaduanamiento Libre</p>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-2"><div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $operacionesTotales > 0 ? ($operacionesVerdes/$operacionesTotales)*100 : 0 }}%"></div></div>
                        <p class="text-xs text-gray-400 mt-1">{{ $operacionesTotales > 0 ? round(($operacionesVerdes/$operacionesTotales)*100,1) : 0 }}%</p>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4 text-center">
                        <p class="text-3xl font-black text-red-600 dark:text-red-400">{{ number_format($operacionesRojas) }}</p>
                        <p class="text-sm font-bold text-gray-700 dark:text-gray-300 mt-1">Reconocimiento Aduanero</p>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-2"><div class="bg-red-500 h-1.5 rounded-full" style="width: {{ $operacionesTotales > 0 ? ($operacionesRojas/$operacionesTotales)*100 : 0 }}%"></div></div>
                        <p class="text-xs text-gray-400 mt-1">{{ $operacionesTotales > 0 ? round(($operacionesRojas/$operacionesTotales)*100,1) : 0 }}%</p>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 space-y-2">
                    <h5 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Comparativa con Período Anterior</h5>
                    <div class="flex justify-between text-sm"><span class="text-gray-600 dark:text-gray-400">Verdes:</span><span class="font-bold text-gray-800 dark:text-white">{{ number_format($operacionesVerdes) }} <span class="text-gray-400 text-xs">(ant: {{ number_format($operacionesVerdesAnterior) }})</span></span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-600 dark:text-gray-400">Rojas:</span><span class="font-bold text-gray-800 dark:text-white">{{ number_format($operacionesRojas) }} <span class="text-gray-400 text-xs">(ant: {{ number_format($operacionesRojasAnterior) }})</span></span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- DISTRIBUCIÓN POR ADUANA -->
    <div>
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-6 bg-indigo-500 rounded-full"></span> Distribución por Aduana
        </h3>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="h-[350px]"><canvas id="aduanaChart"></canvas></div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3"><i class="fas fa-trophy mr-2 text-amber-500"></i>Ranking de Aduanas</h4>
                @if($operacionesPorAduana->count() > 0)
                <div class="space-y-2">
                    @foreach($operacionesPorAduana->take(10) as $index => $aduana)
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black text-gray-400 w-5">#{{ $index + 1 }}</span>
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300 truncate max-w-[120px]">{{ $aduana['aduana'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-black text-gray-800 dark:text-white">{{ number_format($aduana['total']) }}</span>
                            <span class="text-xs font-bold bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 px-1.5 py-0.5 rounded">{{ $aduana['porcentaje'] }}%</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-400"><i class="fas fa-inbox text-3xl mb-2"></i><p class="text-sm">Sin datos</p></div>
                @endif
            </div>
        </div>
    </div>

    <!-- MATRIZ MODULACIÓN × ADUANA -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <span class="w-1 h-5 bg-indigo-500 rounded-full"></span> Matriz: Modulación por Aduana
            </h3>
        </div>
        <div class="p-6 overflow-x-auto">
            @if($modulacionPorAduana->count() > 0)
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left py-3 px-4 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Aduana</th>
                    <th class="text-center py-3 px-4 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Total</th>
                    <th class="text-center py-3 px-4 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Verde</th>
                    <th class="text-center py-3 px-4 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Roja</th>
                    <th class="text-center py-3 px-4 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">% Verde</th>
                    <th class="text-center py-3 px-4 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">% Roja</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($modulacionPorAduana as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <td class="py-3 px-4 font-bold text-gray-800 dark:text-white">{{ $row['aduana'] }}</td>
                    <td class="text-center py-3 px-4"><span class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs px-2 py-1 rounded-md">{{ number_format($row['total']) }}</span></td>
                    <td class="text-center py-3 px-4"><span class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-bold text-xs px-2 py-1 rounded-md">{{ number_format($row['verde']) }}</span></td>
                    <td class="text-center py-3 px-4"><span class="bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 font-bold text-xs px-2 py-1 rounded-md">{{ number_format($row['roja']) }}</span></td>
                    <td class="py-3 px-4"><div class="flex items-center gap-2"><div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2"><div class="bg-emerald-500 h-2 rounded-full" style="width:{{ $row['porcentaje_verde'] }}%"></div></div><span class="text-xs font-bold text-gray-600 dark:text-gray-400 w-10 text-right">{{ $row['porcentaje_verde'] }}%</span></div></td>
                    <td class="py-3 px-4"><div class="flex items-center gap-2"><div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2"><div class="bg-red-500 h-2 rounded-full" style="width:{{ $row['porcentaje_roja'] }}%"></div></div><span class="text-xs font-bold text-gray-600 dark:text-gray-400 w-10 text-right">{{ $row['porcentaje_roja'] }}%</span></div></td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-8 text-gray-400 dark:text-gray-500"><i class="fas fa-th text-3xl mb-2"></i><p>Sin datos disponibles</p></div>
            @endif
        </div>
    </div>

    <!-- COMPARATIVA DE CLIENTES -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2"><span class="w-1 h-5 bg-indigo-500 rounded-full"></span> Comparativa de Clientes</h3>
            <span class="text-xs font-bold bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 px-2.5 py-1 rounded-md">Top {{ $clientesComparativa->count() }}</span>
        </div>
        <div class="p-6 overflow-x-auto">
            @if($clientesComparativa->count() > 0)
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left py-3 px-3 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">#</th>
                    <th class="text-left py-3 px-3 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Cliente</th>
                    <th class="text-center py-3 px-3 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Actual</th>
                    <th class="text-center py-3 px-3 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Anterior</th>
                    <th class="text-center py-3 px-3 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Dif</th>
                    <th class="text-center py-3 px-3 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">% Cambio</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($clientesComparativa->take(15) as $index => $cliente)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <td class="py-3 px-3 text-gray-400 text-xs font-bold">#{{ $index + 1 }}</td>
                    <td class="py-3 px-3 font-bold text-gray-800 dark:text-white">{{ $cliente['cliente'] }}</td>
                    <td class="text-center py-3 px-3"><span class="bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold text-xs px-2 py-1 rounded-md">{{ number_format($cliente['actual']) }}</span></td>
                    <td class="text-center py-3 px-3"><span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-bold text-xs px-2 py-1 rounded-md">{{ number_format($cliente['anterior']) }}</span></td>
                    <td class="text-center py-3 px-3"><span class="font-bold text-xs px-2 py-1 rounded-md {{ $cliente['diferencia'] > 0 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : ($cliente['diferencia'] < 0 ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : 'bg-gray-100 text-gray-500') }}">{{ $cliente['diferencia'] > 0 ? '+' : '' }}{{ $cliente['diferencia'] }}</span></td>
                    <td class="text-center py-3 px-3"><span class="font-bold text-xs px-2 py-1 rounded-md {{ $cliente['porcentaje_cambio'] > 0 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600' : ($cliente['porcentaje_cambio'] < 0 ? 'bg-red-100 dark:bg-red-900/30 text-red-600' : 'bg-gray-100 text-gray-500') }}">{{ $cliente['porcentaje_cambio'] > 0 ? '+' : '' }}{{ $cliente['porcentaje_cambio'] }}%</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-8 text-gray-400"><i class="fas fa-users text-3xl mb-2"></i><p>No hay clientes activos</p></div>
            @endif
        </div>
    </div>

    <!-- PEDIMENTOS POR ADUANA Y PATENTE -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2"><span class="w-1 h-5 bg-indigo-500 rounded-full"></span> Pedimentos por Aduana y Patente</h3>
        </div>
        <div class="p-6 overflow-x-auto">
            @if($pedimentosPorAduanaPatente->count() > 0)
            @php $totalPedimentos = $pedimentosPorAduanaPatente->sum('total_pedimentos'); @endphp
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left py-3 px-4 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Aduana</th>
                    <th class="text-left py-3 px-4 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Patente</th>
                    <th class="text-right py-3 px-4 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Total</th>
                    <th class="text-right py-3 px-4 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">%</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($pedimentosPorAduanaPatente as $row)
                @php $porcentaje = $totalPedimentos > 0 ? ($row->total_pedimentos / $totalPedimentos) * 100 : 0; @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <td class="py-3 px-4 font-bold text-gray-800 dark:text-white">{{ optional($row->aduana)->nombre_aduana ?? 'N/A' }}</td>
                    <td class="py-3 px-4"><span class="bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold text-xs px-2 py-1 rounded-md">{{ optional($row->patente)->numero_patente ?? 'N/A' }}</span></td>
                    <td class="text-right py-3 px-4 font-black text-gray-800 dark:text-white">{{ number_format($row->total_pedimentos) }}</td>
                    <td class="text-right py-3 px-4"><span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-bold text-xs px-2 py-1 rounded-md">{{ round($porcentaje, 1) }}%</span></td>
                </tr>
                @endforeach
                </tbody>
                <tfoot><tr class="border-t-2 border-gray-300 dark:border-gray-600">
                    <td colspan="2" class="py-3 px-4 font-black text-gray-800 dark:text-white uppercase text-xs">Total General</td>
                    <td class="text-right py-3 px-4 font-black text-gray-800 dark:text-white">{{ number_format($totalPedimentos) }}</td>
                    <td class="text-right py-3 px-4"><span class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 font-bold text-xs px-2 py-1 rounded-md">100%</span></td>
                </tr></tfoot>
            </table>
            @else
            <div class="text-center py-8 text-gray-400"><i class="fas fa-file-alt text-3xl mb-2"></i><p>Sin pedimentos</p></div>
            @endif
        </div>
    </div>

    <!-- VISTA ANUAL -->
    <div>
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-6 bg-indigo-500 rounded-full"></span> Vista Anual — {{ $anioActual }} vs {{ $anioAnterior }}
        </h3>
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="h-[400px]"><canvas id="anualChart"></canvas></div>
                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach([['Meta Ideal (≥1000)', 'bg-emerald-500'], ['Meta Buena (≥800)', 'bg-amber-500'], ['Por Debajo (≤750)', 'bg-red-500'], ['Año Anterior', 'bg-gray-400']] as [$lbl, $clr])
                    <div class="flex items-center gap-2"><div class="w-4 h-4 rounded {{ $clr }}"></div><span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $lbl }}</span></div>
                    @endforeach
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 space-y-5">
                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300"><i class="fas fa-table mr-2 text-indigo-500"></i>Resumen Anual</h4>
                <div class="space-y-2">
                    <p class="text-xs font-bold text-gray-400 uppercase">Año {{ $anioActual }}</p>
                    <div class="flex justify-between text-sm"><span class="text-gray-500 dark:text-gray-400">Total:</span><span class="font-black text-gray-800 dark:text-white">{{ number_format(array_sum($totalesMesesActual)) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500 dark:text-gray-400">Promedio:</span><span class="font-black text-gray-800 dark:text-white">{{ number_format($promedioMensual, 1) }}/mes</span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500 dark:text-gray-400">Mejor:</span><span class="font-black text-gray-800 dark:text-white">{{ number_format(max($totalesMesesActual)) }}</span></div>
                </div>
                <hr class="border-gray-200 dark:border-gray-700">
                @php
                    $totalActual = array_sum($totalesMesesActual); $totalAnterior = array_sum($totalesMesesAnterior);
                    $diferencia = $totalActual - $totalAnterior;
                    $porcentajeCambio = $totalAnterior > 0 ? round(($diferencia / $totalAnterior) * 100, 1) : 0;
                @endphp
                <div class="space-y-2">
                    <p class="text-xs font-bold text-gray-400 uppercase">vs {{ $anioAnterior }}</p>
                    <div class="flex justify-between text-sm"><span class="text-gray-500 dark:text-gray-400">Diferencia:</span><span class="font-black {{ $diferencia >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $diferencia > 0 ? '+' : '' }}{{ number_format($diferencia) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500 dark:text-gray-400">% Cambio:</span><span class="font-bold text-xs px-2 py-0.5 rounded {{ $porcentajeCambio >= 0 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600' : 'bg-red-100 dark:bg-red-900/30 text-red-600' }}">{{ $porcentajeCambio > 0 ? '+' : '' }}{{ $porcentajeCambio }}%</span></div>
                </div>
                <hr class="border-gray-200 dark:border-gray-700">
                @php
                    $mesesMeta = collect($totalesMesesActual)->filter(fn($v) => $v >= $metaIdealMensual)->count();
                    $mesesBuenos = collect($totalesMesesActual)->filter(fn($v) => $v >= $metaBuenaMensual && $v < $metaIdealMensual)->count();
                    $mesesBajos = collect($totalesMesesActual)->filter(fn($v) => $v > 0 && $v < $metaBuenaMensual)->count();
                @endphp
                <div class="space-y-2">
                    <p class="text-xs font-bold text-gray-400 uppercase">Cumplimiento</p>
                    <div class="flex justify-between text-sm"><span class="text-emerald-600"><i class="fas fa-check-circle mr-1"></i>Ideal:</span><span class="font-bold text-gray-800 dark:text-white">{{ $mesesMeta }}/12</span></div>
                    <div class="flex justify-between text-sm"><span class="text-amber-600"><i class="fas fa-minus-circle mr-1"></i>Buena:</span><span class="font-bold text-gray-800 dark:text-white">{{ $mesesBuenos }}/12</span></div>
                    <div class="flex justify-between text-sm"><span class="text-red-600"><i class="fas fa-times-circle mr-1"></i>Debajo:</span><span class="font-bold text-gray-800 dark:text-white">{{ $mesesBajos }}/12</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DETALLADA MENSUAL -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2"><span class="w-1 h-5 bg-indigo-500 rounded-full"></span> Detalle Mensual {{ $anioActual }}</h3>
        </div>
        <div class="p-6 overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left py-3 px-3 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Mes</th>
                    <th class="text-center py-3 px-3 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">{{ $anioActual }}</th>
                    <th class="text-center py-3 px-3 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">{{ $anioAnterior }}</th>
                    <th class="text-center py-3 px-3 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Dif</th>
                    <th class="text-center py-3 px-3 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Meta</th>
                    <th class="text-center py-3 px-3 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">% Cumplimiento</th>
                    <th class="text-center py-3 px-3 font-bold text-gray-500 dark:text-gray-400 uppercase text-xs">Estado</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @php $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']; @endphp
                @foreach($meses as $index => $mes)
                @php $actual = $totalesMesesActual[$index]; $anterior = $totalesMesesAnterior[$index]; $diff = $actual - $anterior; $cumplimiento = $metaIdealMensual > 0 ? round(($actual / $metaIdealMensual) * 100, 1) : 0; @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <td class="py-3 px-3 font-bold text-gray-800 dark:text-white">{{ $mes }}</td>
                    <td class="text-center py-3 px-3"><span class="bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold text-xs px-2 py-1 rounded-md">{{ number_format($actual) }}</span></td>
                    <td class="text-center py-3 px-3"><span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-bold text-xs px-2 py-1 rounded-md">{{ number_format($anterior) }}</span></td>
                    <td class="text-center py-3 px-3"><span class="font-bold text-xs px-2 py-1 rounded-md {{ $diff >= 0 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600' : 'bg-red-100 dark:bg-red-900/30 text-red-600' }}">{{ $diff > 0 ? '+' : '' }}{{ number_format($diff) }}</span></td>
                    <td class="text-center py-3 px-3 text-gray-500 dark:text-gray-400 font-bold text-xs">{{ number_format($metaIdealMensual) }}</td>
                    <td class="py-3 px-3"><div class="flex items-center gap-2"><div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2"><div class="h-2 rounded-full {{ $cumplimiento >= 100 ? 'bg-emerald-500' : ($cumplimiento >= 80 ? 'bg-amber-500' : 'bg-red-500') }}" style="width:{{ min($cumplimiento, 100) }}%"></div></div><span class="text-xs font-bold text-gray-500 w-12 text-right">{{ $cumplimiento }}%</span></div></td>
                    <td class="text-center py-3 px-3">
                        @if($actual >= $metaIdealMensual)<span class="text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 px-2 py-1 rounded-md"><i class="fas fa-check-circle mr-1"></i>Cumplida</span>
                        @elseif($actual >= $metaBuenaMensual)<span class="text-xs font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-600 px-2 py-1 rounded-md"><i class="fas fa-minus-circle mr-1"></i>Aceptable</span>
                        @elseif($actual > 0)<span class="text-xs font-bold bg-red-100 dark:bg-red-900/30 text-red-600 px-2 py-1 rounded-md"><i class="fas fa-times-circle mr-1"></i>Debajo</span>
                        @else<span class="text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-400 px-2 py-1 rounded-md">Sin Datos</span>@endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div><!-- max-w -->
</div><!-- gerenciaDash -->

<!-- MODAL RANGO PERSONALIZADO -->
<div id="customDateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <form method="GET" action="{{ route('reportes.gerencia') }}">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-600 to-purple-600">
                <h3 class="text-lg font-bold text-white"><i class="fas fa-calendar-alt mr-2"></i>Rango Personalizado</h3>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" name="tipo_filtro" value="personalizado">
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Fecha Inicio</label><input type="date" name="fecha_inicio" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5" required></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Fecha Fin</label><input type="date" name="fecha_fin" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5" required></div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('customDateModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 transition">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition"><i class="fas fa-search mr-1"></i>Consultar</button>
            </div>
        </form>
    </div>
</div>

<!-- CHARTS JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fontColor = '#6b7280';

    // CHART 1: Comparativa Diaria
    const labelsActual = [], dataActual = [], dataAnterior = [];
    @php
        $fechaTemp = $fechaInicio->copy();
        for ($i = 0; $i < $diasEnPeriodo; $i++) {
            $fechaStr = $fechaTemp->format('Y-m-d');
            echo "labelsActual.push('" . $fechaTemp->format('d M') . "');";
            echo "dataActual.push(" . ($operacionesPorDia[$fechaStr] ?? 0) . ");";
            $fechaTemp->addDay();
        }
        $fechaTempAnterior = $fechaInicioAnterior->copy();
        for ($i = 0; $i < $diasEnPeriodo; $i++) {
            $fechaStr = $fechaTempAnterior->format('Y-m-d');
            echo "dataAnterior.push(" . ($operacionesPorDiaAnterior[$fechaStr] ?? 0) . ");";
            $fechaTempAnterior->addDay();
        }
    @endphp
    new Chart(document.getElementById('comparativaChart'), {
        type: 'bar', data: { labels: labelsActual, datasets: [
            {label:'Estabilidad ({{ $metaIdealDiaria }})',type:'line',data:Array(labelsActual.length).fill({{ $metaIdealDiaria }}),borderColor:'#6b7280',borderWidth:2,borderDash:[6,4],pointRadius:0,fill:false,order:0},
            {label:'Proyección 1 ({{ $proyeccion1 }})',type:'line',data:Array(labelsActual.length).fill({{ $proyeccion1 }}),borderColor:'#6366f1',borderWidth:1.5,borderDash:[6,4],pointRadius:0,fill:false,order:0},
            {label:'Proyección 2 ({{ $proyeccion2 }})',type:'line',data:Array(labelsActual.length).fill({{ $proyeccion2 }}),borderColor:'#8b5cf6',borderWidth:1.5,borderDash:[6,4],pointRadius:0,fill:false,order:0},
            {label:'Meta Media ({{ $metaMediaDiaria }})',type:'line',data:Array(labelsActual.length).fill({{ $metaMediaDiaria }}),borderColor:'#f59e0b',borderWidth:1.5,borderDash:[6,4],pointRadius:0,fill:false,order:0},
            {label:'Meta Alta ({{ $metaAltaDiaria }})',type:'line',data:Array(labelsActual.length).fill({{ $metaAltaDiaria }}),borderColor:'#10b981',borderWidth:1.5,borderDash:[6,4],pointRadius:0,fill:false,order:0},
            {label:'Período Anterior',data:dataAnterior,backgroundColor:'rgba(156,163,175,0.2)',borderColor:'rgba(156,163,175,0.5)',borderWidth:2,order:2},
            {label:'Período Actual',data:dataActual,backgroundColor:'rgba(99,102,241,0.8)',borderColor:'rgba(99,102,241,1)',borderWidth:2,borderRadius:6,order:1}
        ]}, options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:fontColor,font:{weight:'bold',size:11}}}},scales:{y:{beginAtZero:true,ticks:{color:fontColor},grid:{color:'rgba(0,0,0,0.05)'}},x:{ticks:{color:fontColor},grid:{display:false}}}}
    });

    // CHART 2: Modulación Doughnut
    new Chart(document.getElementById('modulacionChart'), {
        type: 'doughnut', data: { labels: ['Desaduanamiento Libre', 'Reconocimiento Aduanero'], datasets: [{data:[{{ $operacionesVerdes }},{{ $operacionesRojas }}],backgroundColor:['rgba(16,185,129,0.8)','rgba(239,68,68,0.8)'],borderColor:['#10b981','#ef4444'],borderWidth:2,hoverOffset:8}]},
        options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{color:fontColor,font:{weight:'bold'}}}}}
    });

    // CHART 3: Aduanas Horizontal Bar
    const aduanas=[],verdesA=[],rojasA=[];
    @foreach($operacionesPorAduana->take(10) as $aduana)aduanas.push('{{ $aduana["aduana"] }}');verdesA.push({{ $aduana["verdes"] }});rojasA.push({{ $aduana["rojas"] }});@endforeach
    new Chart(document.getElementById('aduanaChart'), {
        type:'bar',data:{labels:aduanas,datasets:[{label:'Verde',data:verdesA,backgroundColor:'rgba(16,185,129,0.8)',borderRadius:4},{label:'Roja',data:rojasA,backgroundColor:'rgba(239,68,68,0.8)',borderRadius:4}]},
        options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{labels:{color:fontColor,font:{weight:'bold'}}}},scales:{x:{stacked:true,ticks:{color:fontColor},grid:{color:'rgba(0,0,0,0.05)'}},y:{stacked:true,ticks:{color:fontColor},grid:{display:false}}}}
    });

    // CHART 4: Anual
    const mL=['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const dA=@json($totalesMesesActual),dAnt=@json($totalesMesesAnterior);
    function gc(v){if(v>=1000)return'rgba(16,185,129,0.8)';if(v>=800)return'rgba(245,158,11,0.8)';if(v>0)return'rgba(239,68,68,0.8)';return'rgba(156,163,175,0.3)';}
    new Chart(document.getElementById('anualChart'),{
        type:'bar',data:{labels:mL,datasets:[
            {label:'{{ $anioAnterior }}',data:dAnt,backgroundColor:'rgba(156,163,175,0.25)',borderColor:'rgba(156,163,175,0.5)',borderWidth:1,borderRadius:4,order:2},
            {label:'{{ $anioActual }}',data:dA,backgroundColor:dA.map(gc),borderWidth:0,borderRadius:6,order:1},
            {label:'Meta Ideal ({{ $metaIdealMensual }})',type:'line',data:Array(12).fill({{ $metaIdealMensual }}),borderColor:'#10b981',borderWidth:2,borderDash:[8,4],pointRadius:0,fill:false,order:0},
            {label:'Meta Buena ({{ $metaBuenaMensual }})',type:'line',data:Array(12).fill({{ $metaBuenaMensual }}),borderColor:'#f59e0b',borderWidth:2,borderDash:[8,4],pointRadius:0,fill:false,order:0},
            {label:'Meta Mínima ({{ $metaMalaMensual }})',type:'line',data:Array(12).fill({{ $metaMalaMensual }}),borderColor:'#ef4444',borderWidth:2,borderDash:[8,4],pointRadius:0,fill:false,order:0}
        ]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{labels:{color:fontColor,font:{weight:'bold',size:11}}}},scales:{y:{beginAtZero:true,ticks:{color:fontColor,callback:v=>v.toLocaleString()},grid:{color:'rgba(0,0,0,0.05)'}},x:{ticks:{color:fontColor},grid:{display:false}}}}
    });
});
</script>
@endsection