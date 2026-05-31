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

            <!-- #4: Patente Aduanal Distribution -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-purple-500 rounded-full"></span> Distribución por Patente Aduanal
                    </h3>
                    <div class="h-64 mb-4">
                        <canvas id="patentesChart"></canvas>
                    </div>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($porPatente as $p)
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-gray-700/50">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-black text-gray-400">#{{ $loop->iteration }}</span>
                                <div>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">{{ $p->nombre }}</span>
                                    <span class="text-[10px] text-gray-400">Pat. {{ $p->numero }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded">{{ $verdesPorPatente[$p->nombre] ?? 0 }}V</span>
                                <span class="text-[10px] font-bold text-rose-600 bg-rose-50 dark:bg-rose-900/30 px-1.5 py-0.5 rounded">{{ $rojosPorPatente[$p->nombre] ?? 0 }}R</span>
                                <span class="text-xs font-black text-purple-600 dark:text-purple-400 bg-white dark:bg-gray-800 px-2 py-1 rounded-lg border border-gray-100 dark:border-gray-600 shadow-sm">{{ $p->total }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- #5: Top Importadores -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-cyan-500 rounded-full"></span> Top Importadores
                    </h3>
                    <div class="h-64 mb-4">
                        <canvas id="importadoresChart"></canvas>
                    </div>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($porImportador as $imp)
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-gray-700/50">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-black text-gray-400">#{{ $loop->iteration }}</span>
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $imp->nombre }}</span>
                            </div>
                            <span class="text-xs font-black text-cyan-600 dark:text-cyan-400 bg-white dark:bg-gray-800 px-2 py-1 rounded-lg border border-gray-100 dark:border-gray-600 shadow-sm">{{ $imp->total }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- #6: Bodega + #7: Completitud Documental -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-orange-500 rounded-full"></span> Distribución por Bodega
                    </h3>
                    <div class="h-48 mb-4">
                        <canvas id="bodegasChart"></canvas>
                    </div>
                    <div class="space-y-2">
                        @foreach($porBodega as $b)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700/50">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $b->nombre }}</span>
                            <span class="text-xs font-black text-orange-600 dark:text-orange-400">{{ $b->total }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-teal-500 rounded-full"></span> Completitud Documental (Art. 36-A)
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-4 text-center">
                            <div class="text-3xl font-black text-gray-800 dark:text-white">{{ $completitudDocs['total_operaciones'] }}</div>
                            <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wide mt-1">Total Ops</div>
                        </div>
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl p-4 text-center">
                            <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $completitudDocs['completas'] }}</div>
                            <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wide mt-1">Completas</div>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl p-4 text-center">
                            <div class="text-3xl font-black text-amber-600 dark:text-amber-400">{{ $completitudDocs['incompletas'] }}</div>
                            <div class="text-[10px] font-bold text-amber-600 uppercase tracking-wide mt-1">Incompletas</div>
                        </div>
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl p-4 text-center">
                            <div class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $completitudDocs['promedio_docs'] }}</div>
                            <div class="text-[10px] font-bold text-indigo-600 uppercase tracking-wide mt-1">Prom. Docs/Op</div>
                        </div>
                    </div>
                    <div class="mb-2 flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-400">Tasa de Completitud</span>
                        <span class="text-sm font-black text-teal-600 dark:text-teal-400">{{ $completitudDocs['porcentaje_completas'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                        <div class="bg-gradient-to-r from-teal-500 to-emerald-500 h-4 rounded-full transition-all duration-500" style="width: {{ $completitudDocs['porcentaje_completas'] }}%"></div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-2">Documentos requeridos: Factura, Encargo, Transporte, Lista de Empaque</p>
                </div>
            </div>

            <!-- #9: Tendencia de Modulación + Heatmap -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-emerald-500 rounded-full"></span> Tendencia de Modulación (Anual)
                    </h3>
                    <div class="h-64">
                        <canvas id="tendenciaModulacionChart"></canvas>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-rose-500 rounded-full"></span> Heatmap por Día de Semana
                    </h3>
                    <div class="grid grid-cols-7 gap-2">
                        @foreach($heatmap as $h)
                        <div class="text-center p-3 rounded-2xl {{ $h['porcentaje_verde'] >= 70 ? 'bg-emerald-50 dark:bg-emerald-900/30' : ($h['porcentaje_verde'] >= 40 ? 'bg-amber-50 dark:bg-amber-900/30' : 'bg-rose-50 dark:bg-rose-900/30') }}">
                            <div class="text-xs font-black text-gray-600 dark:text-gray-300 mb-1">{{ $h['dia'] }}</div>
                            <div class="text-lg font-black {{ $h['porcentaje_verde'] >= 70 ? 'text-emerald-600' : ($h['porcentaje_verde'] >= 40 ? 'text-amber-600' : 'text-rose-600') }}">{{ $h['porcentaje_verde'] }}%</div>
                            <div class="text-[10px] font-bold text-gray-400 mt-1">{{ $h['total'] }} ops</div>
                            <div class="flex justify-center gap-1 mt-1">
                                <span class="text-[9px] font-bold text-emerald-600">{{ $h['verdes'] }}V</span>
                                <span class="text-[9px] font-bold text-rose-600">{{ $h['rojos'] }}R</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-center gap-4 mt-4 text-[10px] font-bold text-gray-500">
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-100 dark:bg-emerald-900/50"></span> ≥70% Verde</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-100 dark:bg-amber-900/50"></span> 40-69%</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-rose-100 dark:bg-rose-900/50"></span> &lt;40%</span>
                    </div>
                </div>
            </div>

            <!-- #10: Predicción de Volumen -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-violet-500 rounded-full"></span> Predicción de Volumen (Próximo Mes)
                    </h3>
                    <div class="bg-violet-50 dark:bg-violet-900/30 px-4 py-2 rounded-xl">
                        <span class="text-[10px] font-bold text-violet-600 dark:text-violet-400 uppercase tracking-wide">Estimado</span>
                        <span class="text-2xl font-black text-violet-600 dark:text-violet-400 ml-2">{{ $prediccionProximoMes }}</span>
                        <span class="text-xs text-violet-500 ml-1">operaciones</span>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="prediccionChart"></canvas>
                </div>
                <p class="text-[10px] text-gray-400 mt-3 text-center">Predicción basada en promedio móvil de últimos 3 meses + tendencia lineal</p>
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

                    // CHART 4: Patentes (Stacked Bar)
                    new Chart(document.getElementById('patentesChart'), {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($porPatente->pluck('nombre')) !!},
                            datasets: [
                                {
                                    label: 'Verdes',
                                    data: {!! json_encode($porPatente->map(fn($p) => $verdesPorPatente[$p->nombre] ?? 0)) !!},
                                    backgroundColor: '#10b981',
                                    borderRadius: 4
                                },
                                {
                                    label: 'Rojos',
                                    data: {!! json_encode($porPatente->map(fn($p) => $rojosPorPatente[$p->nombre] ?? 0)) !!},
                                    backgroundColor: '#f43f5e',
                                    borderRadius: 4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: true, position: 'top', labels: { color: textColor, font: { size: 10, weight: 'bold' } } } },
                            scales: {
                                x: { stacked: true, grid: { display: false }, ticks: { color: textColor, font: { size: 9, weight: 'bold' } } },
                                y: { stacked: true, beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, font: { weight: 'bold' } } }
                            }
                        }
                    });

                    // CHART 5: Importadores (Horizontal Bar)
                    new Chart(document.getElementById('importadoresChart'), {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($porImportador->pluck('nombre')) !!},
                            datasets: [{
                                label: 'Operaciones',
                                data: {!! json_encode($porImportador->pluck('total')) !!},
                                backgroundColor: ['#06b6d4', '#0891b2', '#0e7490', '#155e75', '#164e63'],
                                borderRadius: 6
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, font: { weight: 'bold' } } },
                                y: { grid: { display: false }, ticks: { color: textColor, font: { size: 10, weight: 'bold' } } }
                            }
                        }
                    });

                    // CHART 6: Bodegas (Doughnut)
                    new Chart(document.getElementById('bodegasChart'), {
                        type: 'doughnut',
                        data: {
                            labels: {!! json_encode($porBodega->pluck('nombre')) !!},
                            datasets: [{
                                data: {!! json_encode($porBodega->pluck('total')) !!},
                                backgroundColor: ['#f97316', '#ea580c', '#c2410c', '#9a3412', '#7c2d12'],
                                borderWidth: 0,
                                cutout: '65%'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } }
                        }
                    });

                    // CHART 9: Tendencia Modulación (Dual Line)
                    new Chart(document.getElementById('tendenciaModulacionChart'), {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($tendenciaMeses) !!},
                            datasets: [
                                {
                                    label: 'Verdes',
                                    data: {!! json_encode($tendenciaVerdes) !!},
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#10b981',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2
                                },
                                {
                                    label: 'Rojos',
                                    data: {!! json_encode($tendenciaRojos) !!},
                                    borderColor: '#f43f5e',
                                    backgroundColor: 'rgba(244, 63, 94, 0.1)',
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#f43f5e',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: true, position: 'top', labels: { color: textColor, font: { size: 10, weight: 'bold' } } } },
                            scales: {
                                y: { beginAtZero: true, grid: { borderDash: [5, 5], color: gridColor }, ticks: { color: textColor, font: { weight: 'bold' } } },
                                x: { grid: { display: false }, ticks: { color: textColor, font: { weight: 'bold' } } }
                            }
                        }
                    });

                    // CHART 10: Predicción (Line + Dotted)
                    const prediccionData = {!! json_encode($prediccionData) !!};
                    const prediccionLabels = {!! json_encode($prediccionLabels) !!};
                    const historicos = prediccionData.slice(0, -1);
                    const prediccion = [null, ...Array(historicos.length - 1).fill(null), prediccionData[prediccionData.length - 1]];

                    new Chart(document.getElementById('prediccionChart'), {
                        type: 'line',
                        data: {
                            labels: prediccionLabels,
                            datasets: [
                                {
                                    label: 'Histórico',
                                    data: historicos,
                                    borderColor: '#8b5cf6',
                                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 5,
                                    pointBackgroundColor: '#8b5cf6',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2
                                },
                                {
                                    label: 'Predicción',
                                    data: prediccion,
                                    borderColor: '#a78bfa',
                                    borderDash: [8, 4],
                                    backgroundColor: 'rgba(167, 139, 250, 0.05)',
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 8,
                                    pointBackgroundColor: '#a78bfa',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 3,
                                    pointStyle: 'star'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: true, position: 'top', labels: { color: textColor, font: { size: 10, weight: 'bold' } } } },
                            scales: {
                                y: { beginAtZero: true, grid: { borderDash: [5, 5], color: gridColor }, ticks: { color: textColor, font: { weight: 'bold' } } },
                                x: { grid: { display: false }, ticks: { color: textColor, font: { size: 9, weight: 'bold' } } }
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