@extends('layouts.app')

@section('title', 'Reporte de Aduanas - Análisis Operativo')

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
                    <h1
                        class="text-2xl font-black text-gray-800 dark:text-white tracking-tight flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-passport"></i>
                        </div>
                        Gestión de <span class="text-indigo-600 dark:text-indigo-400">Aduanas</span>
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Análisis detallado de flujos y
                        comparativa entre puntos de control.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="window.print()"
                        class="inline-flex items-center gap-2 bg-gray-800 dark:bg-gray-700 hover:bg-black text-white px-5 py-2.5 rounded-xl font-bold text-xs transition-all shadow-lg hover:-translate-y-0.5 whitespace-nowrap">
                        <i class="fas fa-print"></i> IMPRIMIR REPORTE
                    </button>
                    <button onclick="document.documentElement.classList.toggle('dark')"
                        class="p-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-yellow-400 hover:scale-105 transition shadow-sm border border-gray-200 dark:border-gray-600">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:inline"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Only Header -->
    <div class="hidden print:block mb-8 border-b-2 border-gray-300 pb-4">
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">REPORTE OPERATIVO DE ADUANAS</h1>
                <p class="text-sm text-gray-600">NexaCore Management System</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-bold text-gray-500 uppercase">Fecha de Emisión</p>
                <p class="text-sm font-bold">{{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Filter Panel -->
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm mb-8 no-print">
            <h3
                class="text-sm font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest flex items-center gap-2 mb-6">
                <i class="fas fa-filter text-indigo-500"></i> Parámetros de Selección
            </h3>

            <form method="GET" action="{{ route('reportes.aduanas') }}" id="formReporte">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                    <div class="lg:col-span-1">
                        <label
                            class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1 ml-1">Aduana
                            Principal</label>
                        <select name="aduana_id"
                            class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="">Seleccione aduana</option>
                            @foreach($aduanas as $a)
                            <option value="{{ $a->id }}" {{ $aduanaId==$a->id ? 'selected' : '' }}>{{ $a->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-1">
                        <label
                            class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1 ml-1">Comparar
                            con</label>
                        <select name="comparacion_id"
                            class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="">Sin comparación</option>
                            @foreach($aduanas as $a)
                            <option value="{{ $a->id }}" {{ ($comparacionId ?? '' )==$a->id ? 'selected' : '' }}>{{
                                $a->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1 ml-1">Fecha
                            Inicio</label>
                        <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                            class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all">
                    </div>

                    <div>
                        <label
                            class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1 ml-1">Fecha
                            Fin</label>
                        <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                            class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all">
                    </div>

                    <div class="flex items-end">
                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-black text-xs shadow-lg shadow-indigo-100 dark:shadow-none transition-all hover:-translate-y-0.5">
                            <i class="fas fa-search mr-2"></i> CONSULTAR
                        </button>
                    </div>
                </div>

                @if(isset($clientesDisponibles) && $clientesDisponibles->count() > 0)
                <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest">
                            <i class="fas fa-users-cog text-indigo-500 mr-2"></i> Inclusión selectiva de clientes
                            <span
                                class="text-[9px] font-medium lowercase text-gray-400 ml-2 italic tracking-normal">(marque
                                para excluir del análisis)</span>
                        </label>
                        <div class="flex gap-2">
                            <button type="button" id="btnSelectAll"
                                class="text-[9px] font-black bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 px-3 py-1 rounded-lg border border-indigo-100 dark:border-indigo-800 hover:bg-indigo-600 hover:text-white transition-all uppercase tracking-tighter">Incluir
                                Todos</button>
                            <button type="button" id="btnDeselectAll"
                                class="text-[9px] font-black bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-3 py-1 rounded-lg border border-gray-100 dark:border-gray-600 hover:bg-gray-600 hover:text-white transition-all uppercase tracking-tighter">Excluir
                                Todos</button>
                        </div>
                    </div>
                    <div
                        class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 max-h-48 overflow-y-auto p-4 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-100 dark:border-gray-800 custom-scrollbar shadow-inner">
                        @foreach($clientesDisponibles as $cli)
                        @php $excluido = in_array($cli->id, $clientesExcluidos ?? []); @endphp
                        <label
                            class="flex items-center gap-3 p-2 rounded-xl hover:bg-white dark:hover:bg-gray-800 transition-all cursor-pointer group">
                            <input type="checkbox" name="clientes_excluidos[]" value="{{ $cli->id }}"
                                class="cliente-check w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                                {{ $excluido ? 'checked' : '' }}>
                            <span
                                class="text-xs font-bold text-gray-600 dark:text-gray-300 group-hover:text-indigo-600 transition-colors truncate {{ $excluido ? 'line-through opacity-50' : '' }}">{{
                                $cli->nombre }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif
            </form>
        </div>

        @if(isset($aduana))
        <!-- Report Title Section -->
        <div
            class="mb-8 border-l-4 border-indigo-500 pl-6 py-2 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight uppercase">
                    Aduana <span class="text-indigo-600 dark:text-indigo-400">{{ $aduana->nombre }}</span>
                </h2>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 flex items-center gap-1.5">
                        <i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} —
                        {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                    </span>
                    @if(isset($aduanaComparacion))
                    <span
                        class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-[10px] font-black uppercase tracking-widest border border-amber-100 dark:border-amber-800">
                        VS {{ $aduanaComparacion->nombre }}
                    </span>
                    @endif
                    @if(!empty($clientesExcluidos))
                    <span
                        class="px-2 py-0.5 rounded-md bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 text-[10px] font-black uppercase tracking-widest border border-rose-100 dark:border-rose-800">
                        {{ count($clientesExcluidos) }} EXCLUIDOS
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- KPI Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                <div
                    class="absolute -right-4 -bottom-4 text-indigo-500/10 scale-150 rotate-12 transition-transform group-hover:scale-[1.7]">
                    <i class="fas fa-box text-6xl"></i>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div
                        class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Total
                        Operaciones</span>
                </div>
                <div class="text-4xl font-black text-gray-900 dark:text-white mb-1 relative z-10">{{
                    number_format($totalOperaciones) }}</div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                <div
                    class="absolute -right-4 -bottom-4 text-purple-500/10 scale-150 rotate-12 transition-transform group-hover:scale-[1.7]">
                    <i class="fas fa-file-invoice text-6xl"></i>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div
                        class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Total
                        Pedimentos</span>
                </div>
                <div class="text-4xl font-black text-gray-900 dark:text-white mb-1 relative z-10">{{
                    number_format($totalPedimentos) }}</div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                <div
                    class="absolute -right-4 -bottom-4 text-emerald-500/10 scale-150 rotate-12 transition-transform group-hover:scale-[1.7]">
                    <i class="fas fa-users text-6xl"></i>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div
                        class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Clientes
                        Activos</span>
                </div>
                <div class="text-4xl font-black text-gray-900 dark:text-white mb-1 relative z-10">{{
                    number_format($totalClientes) }}</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                <h3
                    class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2 mb-6">
                    <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span> Operaciones por Mes
                </h3>
                <div class="h-64">
                    <canvas id="chartOperaciones"></canvas>
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                <h3
                    class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2 mb-6">
                    <span class="w-1.5 h-6 bg-purple-500 rounded-full"></span> Pedimentos por Mes
                </h3>
                <div class="h-64">
                    <canvas id="chartPedimentos"></canvas>
                </div>
            </div>
        </div>

        <!-- Detail Table -->
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/80">
                <h3
                    class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                    <i class="fas fa-list-ol text-gray-400"></i> Desglose por Entidad / Cliente
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr
                            class="bg-gray-50 dark:bg-gray-700/50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <th class="px-6 py-4"># Pos</th>
                            <th class="px-6 py-4">Denominación del Cliente</th>
                            <th class="px-6 py-4 text-center">Operaciones</th>
                            <th class="px-6 py-4 text-center">Pedimentos</th>
                            <th class="px-6 py-4 text-right">Cuota de Participación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($desglosePorCliente as $i => $item)
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-[10px] font-black text-gray-400">{{ $i + 1 }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div
                                    class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase truncate max-w-xs">
                                    {{ $item->nombre }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="px-3 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-xs font-black text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800">
                                    {{ $item->operaciones }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="px-3 py-1 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-xs font-black text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-800">
                                    {{ $item->pedimentos }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php $porcentaje = $totalOperaciones > 0 ? round(($item->operaciones /
                                $totalOperaciones) * 100, 1) : 0; @endphp
                                <div class="flex items-center gap-3 justify-end">
                                    <div class="w-24 bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500" style="width:{{ $porcentaje }}%"></div>
                                    </div>
                                    <span class="text-xs font-black text-gray-800 dark:text-gray-200 w-10 text-right">{{
                                        $porcentaje }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-300 dark:text-gray-600 mb-4 font-black">
                                        <i class="fas fa-inbox text-3xl"></i>
                                    </div>
                                    <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest">Sin datos
                                        reportados</h4>
                                    <p class="text-xs text-gray-400 mt-1 italic">Intenta con otros filtros o periodos
                                        alternativos.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($desglosePorCliente->count() > 0)
                    <tfoot class="bg-gray-50 dark:bg-gray-700/50">
                        <tr class="text-xs font-black text-gray-500">
                            <td colspan="2" class="px-6 py-4 uppercase tracking-tighter">Total General de la Aduana</td>
                            <td
                                class="px-6 py-4 text-center text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-800 border-x border-gray-100 dark:border-gray-700">
                                {{ $totalOperaciones }}</td>
                            <td class="px-6 py-4 text-center text-purple-600 dark:text-purple-400">{{ $totalPedimentos
                                }}</td>
                            <td class="px-6 py-4 text-right pr-12">100% de representatividad</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @elseif($aduanaId)
        <div class="text-center py-20">
            <div
                class="inline-flex w-20 h-20 bg-rose-50 dark:bg-rose-900/40 rounded-full items-center justify-center text-rose-500 mb-6 border-4 border-white dark:border-gray-800 shadow-xl">
                <i class="fas fa-exclamation-triangle text-4xl"></i>
            </div>
            <h2 class="text-2xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Recurso no encontrado
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">No se logró localizar la aduana
                especificada en los parámetros.</p>
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-32 opacity-50 dark:opacity-40">
            <div
                class="w-32 h-32 mx-auto mb-8 bg-indigo-50 dark:bg-indigo-900/30 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <i class="fas fa-passport text-6xl"></i>
            </div>
            <h2 class="text-2xl font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Esperando
                Parámetros</h2>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-4 font-medium max-w-sm mx-auto">Selecciona una aduana
                y define el periodo temporal para generar la analítica correspondiente.</p>
        </div>
        @endif
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(99, 102, 241, 0.2);
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(99, 102, 241, 0.5);
    }

    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: white !important;
        }

        .bg-gray-50 {
            background: white !important;
        }

        .bg-white {
            border: 1px solid #eee !important;
            box-shadow: none !important;
        }

        .rounded-3xl {
            border-radius: 0 !important;
        }

        .shadow-sm {
            box-shadow: none !important;
        }
    }
</style>

@push('scripts')
@if(isset($aduana))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

        const labels = {!! json_encode($labelsGrafico) !!};
    const dataOp = {!! json_encode($dataOperaciones) !!};
    const dataPed = {!! json_encode($dataPedimentos) !!};
    const dataCompOp = {!! json_encode($dataCompOperaciones) !!};
    const dataCompPed = {!! json_encode($dataCompPedimentos) !!};
    const tieneComparacion = {{ (isset($aduanaComparacion) && $aduanaComparacion) ? 'true' : 'false' }};
    const nombreComparacion = {!! json_encode(isset($aduanaComparacion) ? $aduanaComparacion->nombre : '') !!};
    const nombrePrincipal = {!! json_encode($aduana->nombre) !!};

    // Chart Operaciones
    const ctxOp = document.getElementById('chartOperaciones').getContext('2d');
    new Chart(ctxOp, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: nombrePrincipal,
                    data: dataOp,
                    backgroundColor: 'rgba(99, 102, 241, 0.85)',
                    borderRadius: 6,
                    barThickness: 25
                },
                ...(tieneComparacion ? [{
                    label: nombreComparacion,
                    data: dataCompOp,
                    backgroundColor: 'rgba(200, 200, 220, 0.4)',
                    borderRadius: 6,
                    barThickness: 25
                }] : [])
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: tieneComparacion, position: 'top', labels: { color: textColor, font: { weight: 'bold', size: 10 } } } },
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, font: { weight: 'bold' } } },
                x: { grid: { display: false }, ticks: { color: textColor, font: { weight: 'bold' }, maxRotation: 45 } }
            }
        }
    });

    // Chart Pedimentos
    const ctxPed = document.getElementById('chartPedimentos').getContext('2d');
    new Chart(ctxPed, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: nombrePrincipal,
                    data: dataPed,
                    backgroundColor: 'rgba(168, 85, 247, 0.85)',
                    borderRadius: 6,
                    barThickness: 25
                },
                ...(tieneComparacion ? [{
                    label: nombreComparacion,
                    data: dataCompPed,
                    backgroundColor: 'rgba(200, 200, 220, 0.4)',
                    borderRadius: 6,
                    barThickness: 25
                }] : [])
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: tieneComparacion, position: 'top', labels: { color: textColor, font: { weight: 'bold', size: 10 } } } },
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, font: { weight: 'bold' } } },
                x: { grid: { display: false }, ticks: { color: textColor, font: { weight: 'bold' }, maxRotation: 45 } }
            }
        }
    });

    // Checkbox Handlers
    document.getElementById('btnSelectAll')?.addEventListener('click', () => {
        document.querySelectorAll('.cliente-check').forEach(cb => {
            cb.checked = false;
            cb.nextElementSibling.classList.remove('line-through', 'opacity-50');
        });
    });
    document.getElementById('btnDeselectAll')?.addEventListener('click', () => {
        document.querySelectorAll('.cliente-check').forEach(cb => {
            cb.checked = true;
            cb.nextElementSibling.classList.add('line-through', 'opacity-50');
        });
    });
    document.querySelectorAll('.cliente-check').forEach(cb => {
        cb.addEventListener('change', function () {
            if (this.checked) this.nextElementSibling.classList.add('line-through', 'opacity-50');
            else this.nextElementSibling.classList.remove('line-through', 'opacity-50');
        });
    });
    });
</script>
@endif
@endpush
@endsection