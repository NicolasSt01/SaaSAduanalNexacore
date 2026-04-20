@extends('layouts.app')

@section('title', 'Reporte de Operación Semanal')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
    }
</script>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300 pb-12">
    @php
    $diasEnPeriodo = $fechaInicio->startOfDay()->diffInDays($fechaFin->copy()->startOfDay()) + 1;
    $promedioDiarioGlobal = $diasEnPeriodo > 0 ? $operacionesTotales / $diasEnPeriodo : 0;

    $diasSemana = [];
    $fechaActual = Carbon\Carbon::parse($fechaInicio);
    $operacionesPorDiaCompleto = [];

    for ($i = 0; $i < 7; $i++) { $fechaStr=$fechaActual->format('Y-m-d');
        $count = $operacionesPorDia[$fechaStr] ?? 0;
        $operacionesPorDiaCompleto[$fechaStr] = $count;

        $diasSemana[$fechaStr] = [
        'date' => $fechaActual->copy(),
        'count' => $count,
        'day_name' => $fechaActual->isoFormat('ddd'),
        'day_number' => $fechaActual->format('d'),
        'month' => $fechaActual->isoFormat('MMM'),
        ];
        $fechaActual->addDay();
        }

        $maxOperaciones = max($operacionesPorDiaCompleto);
        $minOperaciones = $operacionesTotales > 0 ? min(array_filter($operacionesPorDiaCompleto, function ($val) {
        return $val > 0;
        })) : 0;

        foreach ($diasSemana as $fechaStr => &$dia) {
        $dia['is_high'] = $maxOperaciones > 0 && $dia['count'] == $maxOperaciones;
        $dia['is_low'] = $minOperaciones > 0 && $dia['count'] == $minOperaciones && $dia['count'] > 0;
        }
        unset($dia);
        @endphp

        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm mb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div>
                        <h1
                            class="text-2xl font-black text-gray-800 dark:text-white tracking-tight flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                            Operación <span class="text-indigo-600 dark:text-indigo-400">Semanal</span>
                        </h1>
                        <div class="flex items-center gap-2 mt-2">
                            <span
                                class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-[10px] font-black uppercase tracking-widest border border-indigo-100 dark:border-indigo-800">
                                Semana {{ $fechaInicio->format('W') }}
                            </span>
                            <span class="text-xs font-bold text-gray-400 dark:text-gray-500">
                                {{ $fechaInicio->format('d M') }} — {{ $fechaFin->format('d M, Y') }}
                            </span>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form id="filterForm" method="GET" action="{{ route('reportes.operacion_semanal') }}"
                        class="flex flex-wrap items-end gap-3 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-600 shadow-inner">
                        <div class="w-full sm:w-40">
                            <label
                                class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1 ml-1">Lunes
                                (Inicio)</label>
                            <input type="date" name="fecha_inicio"
                                value="{{ request('fecha_inicio') ?? $fechaInicio->format('Y-m-d') }}"
                                class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-indigo-500 transition-all">
                        </div>
                        <div class="w-full sm:w-40">
                            <label
                                class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1 ml-1">Domingo
                                (Fin)</label>
                            <input type="date" name="fecha_fin"
                                value="{{ request('fecha_fin') ?? $fechaFin->format('Y-m-d') }}"
                                class="w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-indigo-500 transition-all">
                        </div>
                        <div class="flex gap-2">
                            <button type="button" onclick="setWeek('current')"
                                class="p-2.5 rounded-xl bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm"
                                title="Esta Semana">
                                <i class="fas fa-calendar-day"></i>
                            </button>
                            <button type="button" onclick="setWeek('last')"
                                class="p-2.5 rounded-xl bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm"
                                title="Semana Anterior">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-black text-xs shadow-lg shadow-indigo-200 dark:shadow-none transition-all hover:-translate-y-0.5 whitespace-nowrap">
                                CONSULTAR
                            </button>
                            <button type="button" onclick="document.documentElement.classList.toggle('dark')"
                                class="p-2.5 rounded-xl bg-white dark:bg-gray-800 text-gray-600 dark:text-yellow-400 border border-gray-200 dark:border-gray-600 shadow-sm">
                                <i class="fas fa-moon dark:hidden"></i>
                                <i class="fas fa-sun hidden dark:inline"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- KPI Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Operaciones Totales -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm group">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span
                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-indigo-500 transition-colors">Operaciones
                            Totales</span>
                    </div>
                    <div class="text-4xl font-black text-gray-900 dark:text-white mb-2">{{
                        number_format($operacionesTotales) }}</div>
                    <div class="flex items-center gap-2">
                        <span
                            class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800">{{
                            $operacionesVerdes }} V</span>
                        <span
                            class="text-[10px] font-bold px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800">{{
                            $operacionesRojas }} R</span>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase">Promedio
                                Diario</span>
                            <span class="text-xs font-black text-indigo-600 dark:text-indigo-400">{{
                                round($promedioDiarioGlobal, 1) }}</span>
                        </div>
                        @php $eficiencia = ($metaIdealDiaria > 0) ? ($promedioDiarioGlobal / $metaIdealDiaria) * 100 :
                        0; @endphp
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                            <div class="h-full {{ $eficiencia >= 100 ? 'bg-emerald-500' : ($eficiencia >= 80 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                style="width: {{ min($eficiencia, 100) }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Camiones -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 text-amber-500/10 scale-150 rotate-12">
                        <i class="fas fa-truck text-6xl"></i>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                            <i class="fas fa-truck"></i>
                        </div>
                        <span
                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Camiones
                            Únicos</span>
                    </div>
                    <div class="text-4xl font-black text-gray-900 dark:text-white mb-2 relative z-10">{{
                        number_format($totalCamiones) }}</div>
                    <div class="flex items-center gap-2 relative z-10">
                        <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">{{ round(($totalCamiones >
                            0 ? ($camionesVerdes / $totalCamiones) * 100 : 0), 1) }}% de efectividad</span>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 relative z-10">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase">Verdes /
                                Rojos</span>
                            <span class="text-xs font-black text-gray-700 dark:text-gray-300">{{ $camionesVerdes }} / {{
                                $camionesRojos }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden flex">
                            <div class="h-full bg-emerald-500"
                                style="width: {{ ($totalCamiones > 0 ? ($camionesVerdes / $totalCamiones) * 100 : 0) }}%">
                            </div>
                            <div class="h-full bg-rose-500"
                                style="width: {{ ($totalCamiones > 0 ? ($camionesRojos / $totalCamiones) * 100 : 0) }}%">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sobrepesos -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg">
                            <i class="fas fa-weight-hanging"></i>
                        </div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Sobrepesos</span>
                    </div>
                    <div class="text-4xl font-black text-gray-900 dark:text-white mb-2">{{
                        number_format($totalSobrepeso) }}</div>
                    <p class="text-xs text-gray-500 font-medium">Permisos especiales detectados</p>
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-1">
                            <span
                                class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase">Incidencia</span>
                            <span class="text-xs font-black text-rose-600 dark:text-rose-400">{{ $operacionesTotales > 0
                                ? round(($totalSobrepeso / $operacionesTotales) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                            <div class="h-full bg-rose-500"
                                style="width: {{ min(($operacionesTotales > 0 ? ($totalSobrepeso / $operacionesTotales) * 100 : 0), 100) }}%">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Basculas -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Uso de
                            Básculas</span>
                    </div>
                    <div class="text-4xl font-black text-gray-900 dark:text-white mb-2">{{ number_format($totalBasculas)
                        }}</div>
                    <p class="text-xs text-gray-500 font-medium">Pesajes realizados en semana</p>
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase">Promedio
                                p/día</span>
                            <span class="text-xs font-black text-sky-600 dark:text-sky-400">{{ round($totalBasculas / 7,
                                1) }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                            <div class="h-full bg-sky-500"
                                style="width: {{ min(($totalBasculas / (7 * 100) * 100), 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Chart & Weekly Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Chart Column -->
                <div class="lg:col-span-2">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm h-full flex flex-col">
                        <div class="flex items-center justify-between mb-6">
                            <h3
                                class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                                <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span> Operativa con Líneas de Meta
                            </h3>
                            <div class="flex gap-2">
                                <div
                                    class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-[10px] font-black text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800">
                                    <span class="w-2 h-0.5 bg-emerald-500 rounded-full"></span> Meta: {{
                                    $metaIdealDiaria }}
                                </div>
                                <div
                                    class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-[10px] font-black text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-800">
                                    <span class="w-2 h-0.5 bg-amber-500 rounded-full"></span> Buena: {{ $metaBuenaDiaria
                                    }}
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow min-h-[350px]">
                            <canvas id="operacionesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Weekly Grid Column -->
                <div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm h-full flex flex-col">
                        <h3
                            class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight mb-6 flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-purple-500 rounded-full"></span> Cuadrícula de Rendimiento
                        </h3>

                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-2 gap-3 flex-grow">
                            @foreach($diasSemana as $dia)
                            <div class="p-3 rounded-2xl border transition-all duration-300 relative overflow-hidden group
                            {{ $dia['is_high'] ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg -translate-y-1' : (
                               ($dia['count'] >= $metaIdealDiaria && $dia['count'] > 0) ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-100 dark:border-emerald-800' : (
                               ($dia['count'] >= $metaBuenaDiaria && $dia['count'] > 0) ? 'bg-amber-50 dark:bg-amber-900/30 border-amber-100 dark:border-amber-800' : (
                               ($dia['count'] > 0) ? 'bg-white dark:bg-gray-700 border-gray-100 dark:border-gray-600' : 'bg-gray-50 dark:bg-gray-800/50 border-transparent opacity-60'
                            ))) }}">

                                @if($dia['is_high'])
                                <div class="absolute -right-2 -bottom-2 opacity-20">
                                    <i class="fas fa-crown text-4xl"></i>
                                </div>
                                @endif

                                <div class="flex justify-between items-start mb-1">
                                    <span
                                        class="text-[10px] font-black uppercase tracking-widest {{ $dia['is_high'] ? 'text-indigo-100' : 'text-gray-400' }}">{{
                                        $dia['day_name'] }}</span>
                                    <span
                                        class="text-[10px] font-bold {{ $dia['is_high'] ? 'text-white' : 'text-gray-500 dark:text-gray-400' }}">{{
                                        $dia['day_number'] }}</span>
                                </div>
                                <div
                                    class="text-2xl font-black {{ $dia['is_high'] ? 'text-white' : 'text-gray-800 dark:text-white' }}">
                                    {{ $dia['count'] }}</div>
                                <div class="flex items-center gap-1 mt-1">
                                    @if($dia['count'] >= $metaIdealDiaria && $dia['count'] > 0)
                                    <span
                                        class="text-[8px] font-black uppercase {{ $dia['is_high'] ? 'text-indigo-100' : 'text-emerald-600' }}">Óptimo</span>
                                    @elseif($dia['count'] >= $metaBuenaDiaria && $dia['count'] > 0)
                                    <span class="text-[8px] font-black uppercase text-amber-600">Regular</span>
                                    @elseif($dia['count'] > 0)
                                    <span class="text-[8px] font-black uppercase text-rose-500">Bajo</span>
                                    @else
                                    <span class="text-[8px] font-black uppercase text-gray-400 italic">Off</span>
                                    @endif
                                    <div
                                        class="w-1 h-1 rounded-full {{ $dia['count'] >= $metaIdealDiaria ? 'bg-emerald-500' : ($dia['count'] >= $metaBuenaDiaria ? 'bg-amber-500' : ($dia['count'] > 0 ? 'bg-rose-500' : 'bg-gray-300')) }}">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-6 space-y-3 pt-6 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-500 font-medium">Día con mayor carga:</span>
                                <span
                                    class="font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-tighter">
                                    @foreach($diasSemana as $dia) @if($dia['is_high'] && $dia['count'] > 0) {{
                                    $dia['day_name'] }} ({{ $dia['count'] }}) @endif @endforeach
                                </span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-500 font-medium">Día con menor carga:</span>
                                <span class="font-black text-gray-700 dark:text-gray-300 uppercase tracking-tighter">
                                    @foreach($diasSemana as $dia) @if($dia['is_low'] && $dia['count'] > 0) {{
                                    $dia['day_name'] }} ({{ $dia['count'] }}) @endif @endforeach
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Top Clientes -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/80">
                        <h3
                            class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                            <i class="fas fa-users text-indigo-500"></i> Top Clientes Activos
                        </h3>
                        <span
                            class="text-[10px] font-black bg-white dark:bg-gray-700 px-3 py-1 rounded-full border border-gray-100 dark:border-gray-600 text-gray-500 dark:text-gray-400">Semana
                            Actual</span>
                    </div>
                    <div class="overflow-x-auto flex-grow">
                        <table class="w-full text-left">
                            <thead>
                                <tr
                                    class="bg-gray-50 dark:bg-gray-700/30 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    <th class="px-6 py-4"># Pos</th>
                                    <th class="px-6 py-4">Denominación del Cliente</th>
                                    <th class="px-6 py-4 text-center">Operaciones</th>
                                    <th class="px-6 py-4 text-right">Participación</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($clientesSemana->take(6) as $index => $cliente)
                                <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                                    <td class="px-6 py-4">
                                        <div
                                            class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-[10px]
                                        {{ $index == 0 ? 'bg-amber-100 text-amber-700' : ($index == 1 ? 'bg-slate-100 text-slate-700' : ($index == 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500')) }}">
                                            {{ $index + 1 }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase truncate max-w-xs">
                                            {{ $cliente['cliente'] ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-700 text-xs font-black text-gray-800 dark:text-gray-300 border border-gray-100 dark:border-gray-600 shadow-sm">
                                            {{ $cliente['total'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3 justify-end">
                                            <span class="text-[11px] font-black text-indigo-600 dark:text-indigo-400">{{
                                                number_format($cliente['porcentaje'], 1) }}%</span>
                                            <div
                                                class="w-16 bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                                                <div class="h-full bg-indigo-500"
                                                    style="width: {{ $cliente['porcentaje'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pedimentos por Aduana -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/80">
                        <h3
                            class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                            <i class="fas fa-file-invoice text-emerald-500"></i> Pedimentos por Aduana
                        </h3>
                        <span
                            class="text-[10px] font-black bg-white dark:bg-gray-700 px-3 py-1 rounded-full border border-gray-100 dark:border-gray-600 text-gray-500 dark:text-gray-400">Total:
                            {{ $pedimentosPorAduanaPatente->sum('total_pedimentos') }}</span>
                    </div>
                    <div class="overflow-x-auto flex-grow">
                        <table class="w-full text-left">
                            <thead>
                                <tr
                                    class="bg-gray-50 dark:bg-gray-700/30 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    <th class="px-6 py-4">Aduana / Punto de Control</th>
                                    <th class="px-6 py-4 text-center">Patente</th>
                                    <th class="px-6 py-4 text-right">Cant. Pedimentos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @php $totalPed = $pedimentosPorAduanaPatente->sum('total_pedimentos'); @endphp
                                @foreach($pedimentosPorAduanaPatente as $row)
                                <tr class="hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-700 dark:text-gray-200">{{
                                            optional($row->aduana)->nombre ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-[10px] font-bold text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600">
                                            {{ optional($row->patente)->numero ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="text-[10px] font-bold text-gray-400">({{ $totalPed > 0 ?
                                                round(($row->total_pedimentos / $totalPed) * 100, 1) : 0 }}%)</div>
                                            <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{
                                                $row->total_pedimentos }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-xs font-black text-gray-500 uppercase">Total
                                        General Reportado</td>
                                    <td
                                        class="px-6 py-4 text-right text-sm font-black text-indigo-600 dark:text-indigo-400 underline decoration-indigo-200 underline-offset-4">
                                        {{ $totalPed }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function setWeek(type) {
        const today = new Date();
        let start, end;
        if (type === 'current') {
            start = new Date(today);
            start.setDate(today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1));
            end = new Date(start);
            end.setDate(start.getDate() + 6);
        } else if (type === 'last') {
            start = new Date(today);
            start.setDate(today.getDate() - today.getDay() - 6 + (today.getDay() === 0 ? -6 : 1));
            end = new Date(start);
            end.setDate(start.getDate() + 6);
        }
        document.querySelector('[name="fecha_inicio"]').value = start.toISOString().split('T')[0];
        document.querySelector('[name="fecha_fin"]').value = end.toISOString().split('T')[0];
        document.getElementById('filterForm').submit();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

        const metaIdeal = {{ $metaIdealDiaria ?? 33 }};
        const metaBuena = {{ $metaBuenaDiaria ?? 27 }};
        const diasLabels = {!! json_encode(array_values(array_map(fn($d) => $d['day_name'].' '.$d['day_number'], $diasSemana ?? []))) !!};
        const opsData = {!! json_encode(array_values($operacionesPorDiaCompleto ?? [])) !!};

        new Chart(document.getElementById('operacionesChart'), {
            type: 'bar',
            data: {
                labels: diasLabels,
                datasets: [
                    {
                        label: 'Meta Ideal',
                        type: 'line',
                        data: Array(7).fill(metaIdeal),
                        borderColor: '#10b981',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false
                    },
                    {
                        label: 'Meta Buena',
                        type: 'line',
                        data: Array(7).fill(metaBuena),
                        borderColor: '#f59e0b',
                        borderWidth: 1.5,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false
                    },
                    {
                        label: 'Operaciones',
                        data: opsData,
                        backgroundColor: function(context) {
                            const val = context.raw;
                            if (val >= metaIdeal) return 'rgba(16, 185, 129, 0.8)';
                            if (val >= metaBuena) return 'rgba(245, 158, 11, 0.8)';
                            return 'rgba(244, 63, 94, 0.8)';
                        },
                        borderRadius: 8,
                        barThickness: 35
                    }
                ]
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
                        grid: { color: gridColor },
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
@endsection