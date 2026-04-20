@extends('layouts.app')

@section('title', 'Reporte de Remesas - Análisis Operativo')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
    }
</script>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300 pb-12">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-boxes"></i>
                        </div>
                        Reporte de <span class="text-indigo-600 dark:text-indigo-400">Remesas</span>
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Visualización y análisis de flujos operativos por periodo.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" id="btnExportExcel" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs transition-all shadow-lg shadow-emerald-100 dark:shadow-none hover:-translate-y-0.5">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </button>
                    <button type="button" id="btnExportPDF" class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs transition-all shadow-lg shadow-rose-100 dark:shadow-none hover:-translate-y-0.5">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </button>
                    <button onclick="document.documentElement.classList.toggle('dark')" class="p-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-yellow-400 hover:scale-105 transition shadow-sm border border-gray-200 dark:border-gray-600">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:inline"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Filters Card -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-sm font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-filter text-indigo-500"></i> Parámetros de Análisis
                </h3>
                <button type="button" id="btnResetFilters" class="text-[10px] font-black text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors uppercase tracking-widest">
                    Limpiar Filtros <i class="fas fa-redo ml-1"></i>
                </button>
            </div>
            
            <form method="GET" action="{{ route('reportes.remesas') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1 ml-1">Año Fiscal</label>
                    <select name="year" class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all">
                        @foreach(range(date('Y')-5, date('Y')) as $year)
                            <option value="{{ $year }}" {{ request('year', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-1">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1 ml-1">Periodo Mensual</label>
                    <select name="month" class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all">
                        <option value="">Anual / Todos</option>
                        @foreach([
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ] as $num => $name)
                            <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1 ml-1">Entidad Cliente</label>
                    <select name="cliente_id" class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all">
                        <option value="">Todas las entidades</option>
                        @foreach($clientes ?? [] as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>{{ $cliente->nombre_empresa }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-black text-xs shadow-lg shadow-indigo-100 dark:shadow-none transition-all hover:-translate-y-0.5">
                        <i class="fas fa-play mr-2"></i> GENERAR
                    </button>
                </div>
            </form>
        </div>

        <!-- KPI Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 text-indigo-500/10 scale-150 rotate-12 transition-transform group-hover:scale-[1.7]">
                    <i class="fas fa-boxes text-6xl"></i>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Total Remesas</span>
                </div>
                <div class="text-4xl font-black text-gray-900 dark:text-white mb-1 relative z-10">{{ number_format($totalRemesas ?? 0, 0) }}</div>
                <p class="text-xs text-gray-500 font-medium relative z-10">Volumen detectado en periodo</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 text-emerald-500/10 scale-150 rotate-12 transition-transform group-hover:scale-[1.7]">
                    <i class="fas fa-users text-6xl"></i>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Clientes Activos</span>
                </div>
                <div class="text-4xl font-black text-emerald-600 dark:text-emerald-400 mb-1 relative z-10">{{ $clientesActivos ?? 0 }}</div>
                <p class="text-xs text-gray-500 font-medium relative z-10">Entidades con movimientos</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 text-amber-500/10 scale-150 rotate-12 transition-transform group-hover:scale-[1.7]">
                    <i class="fas fa-calendar-check text-6xl"></i>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                        <i class="fas fa-history"></i>
                    </div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Periodos Analizados</span>
                </div>
                <div class="text-4xl font-black text-amber-600 dark:text-amber-400 mb-1 relative z-10">{{ count($reporte ?? []) }}</div>
                <p class="text-xs text-gray-500 font-medium relative z-10">Registros temporales encontrados</p>
            </div>
        </div>

        <!-- Main Chart Section -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 border border-gray-100 dark:border-gray-700 shadow-sm mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <div>
                    <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-indigo-500 rounded-full"></span> Análisis Gráfico de Tendencias
                    </h3>
                    <div class="flex items-center gap-4 mt-2">
                        <div class="flex items-center gap-1.5 text-[10px] font-black text-emerald-600">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> META: ≥1000
                        </div>
                        <div class="flex items-center gap-1.5 text-[10px] font-black text-amber-600">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> ACEPTABLE: ≥800
                        </div>
                        <div class="flex items-center gap-1.5 text-[10px] font-black text-rose-600">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span> CRÍTICO: <800
                        </div>
                    </div>
                </div>
                <div class="relative group">
                    <button class="bg-gray-50 dark:bg-gray-700 px-4 py-2 rounded-xl text-xs font-black text-gray-600 dark:text-gray-300 flex items-center gap-2 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-600 transition-all border border-transparent hover:border-indigo-100">
                        <i class="fas fa-download"></i> EXPORTAR IMAGEN <i class="fas fa-chevron-down ml-1 text-[8px]"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 hidden group-hover:block z-50 overflow-hidden">
                        <a href="#" id="exportChartPNG" class="block px-4 py-3 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 transition-colors">Formato PNG High</a>
                        <a href="#" id="exportChartJPEG" class="block px-4 py-3 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 transition-colors">Formato JPEG Standard</a>
                    </div>
                </div>
            </div>
            
            <div class="h-96 w-full">
                <canvas id="graficaRemesas"></canvas>
            </div>
        </div>

        <!-- Detail Table Section -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-800/80">
                <div>
                    <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                        <i class="fas fa-list text-gray-400"></i> Desglose Detallado de Remesas
                    </h3>
                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-widest mt-0.5">Listado completo filtrado por parámetros</p>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input type="text" id="searchTable" placeholder="Filtrar en tabla..." class="pl-10 pr-4 py-2.5 w-full md:w-64 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500 transition-all shadow-sm">
                </div>
            </div>

            <div class="overflow-x-auto max-h-[500px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-left border-separate border-spacing-0" id="remesasTable">
                    <thead class="sticky top-0 z-20">
                        <tr class="bg-indigo-600 text-[10px] font-black text-white uppercase tracking-widest">
                            <th class="px-6 py-4">Período de Referencia</th>
                            <th class="px-6 py-4">Entidad / Cliente</th>
                            <th class="px-6 py-4 text-center">Magnitud</th>
                            <th class="px-6 py-4 text-center">Tipología</th>
                            <th class="px-6 py-4 text-right">Estatus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($reporteDetalle ?? [] as $item)
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $item->fecha_formateada ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ $item->cliente_nombre ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-block px-3 py-1 rounded-lg bg-gray-50 dark:bg-gray-700 text-xs font-black text-indigo-600 dark:text-indigo-400 border border-gray-100 dark:border-gray-600 shadow-sm">
                                    {{ number_format($item->cantidad, 0) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full {{ $item->tipo == 'mes' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' }}">
                                    {{ $item->tipo == 'mes' ? 'Mensual' : 'Semanal' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded-md border border-emerald-100 dark:border-emerald-800">
                                    <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span> Activo
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-300 dark:text-gray-600 mb-4">
                                        <i class="fas fa-database text-3xl"></i>
                                    </div>
                                    <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest">Sin registros disponibles</h4>
                                    <p class="text-xs text-gray-400 mt-1 italic">Intenta ajustar los parámetros de filtro arriba.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(!empty($reporteDetalle))
                    <tfoot class="sticky bottom-0 z-20 bg-gray-100 dark:bg-gray-700">
                        <tr class="text-xs font-black">
                            <td colspan="2" class="px-6 py-4 text-gray-500 uppercase tracking-tighter">Total Acumulado Detectado</td>
                            <td class="px-6 py-4 text-center text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-800 border-x border-gray-200 dark:border-gray-600">{{ number_format($totalRemesas ?? 0, 0) }}</td>
                            <td colspan="2" class="px-6 py-4 text-right text-gray-400 italic font-medium">unidades reportadas</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.2); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.4); }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    const ctx = document.getElementById('graficaRemesas').getContext('2d');
    let chartInstance = null;
    const GOAL = 1000;
    const BUENO = 800;
    const MALO = 750;

    const labels = @json(collect($reporte ?? [])->pluck('label'));
    const data = @json(collect($reporte ?? [])->pluck('total'));
    const datosOrganizados = @json($datosOrganizados ?? []);
    const totalesPorMes = @json($totalesPorMes ?? []);

    function generarColoresAcumulativos() {
        const coloresBackground = [];
        const coloresBorder = [];
        const datosPorLabel = {};
        datosOrganizados.forEach(dato => { datosPorLabel[dato.label] = dato; });
        
        labels.forEach((label, index) => {
            const dato = datosPorLabel[label];
            if (!dato) {
                coloresBackground.push('rgba(99, 102, 241, 0.85)');
                coloresBorder.push('rgba(99, 102, 241, 1)');
                return;
            }
            
            const valToCheck = dato.tipo === 'mes' ? data[index] : (totalesPorMes[dato.mes_key] || 0);
            
            if (valToCheck >= GOAL) {
                coloresBackground.push('rgba(16, 185, 129, 0.85)');
                coloresBorder.push('rgba(16, 185, 129, 1)');
            } else if (valToCheck >= BUENO) {
                coloresBackground.push('rgba(245, 158, 11, 0.85)');
                coloresBorder.push('rgba(245, 158, 11, 1)');
            } else {
                coloresBackground.push('rgba(244, 63, 94, 0.85)');
                coloresBorder.push('rgba(244, 63, 94, 1)');
            }
        });
        return { coloresBackground, coloresBorder };
    }

    function initializeChart() {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
        const { coloresBackground, coloresBorder } = generarColoresAcumulativos();
        
        if (chartInstance) chartInstance.destroy();
        
        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Efectividad Mensual (1000)',
                        type: 'line',
                        data: Array(data.length).fill(GOAL),
                        borderColor: 'rgba(16, 185, 129, 0.6)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false
                    },
                    {
                        label: 'Nivel Aceptable (800)',
                        type: 'line',
                        data: Array(data.length).fill(BUENO),
                        borderColor: 'rgba(245, 158, 11, 0.6)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false
                    },
                    {
                        label: 'Remesas Registradas',
                        data: data,
                        backgroundColor: coloresBackground,
                        borderColor: coloresBorder,
                        borderWidth: 0,
                        borderRadius: 8,
                        barPercentage: 0.6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor, font: { weight: 'bold' } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { weight: 'bold' }, maxRotation: 45, minRotation: 45 }
                    }
                }
            }
        });
    }

    // Export Excel
    document.getElementById('btnExportExcel').addEventListener('click', function() {
        const table = document.getElementById('remesasTable');
        const wb = XLSX.utils.table_to_book(table, {sheet: "Remesas"});
        XLSX.writeFile(wb, `remesas_export_${new Date().getTime()}.xlsx`);
    });

    // Export PDF
    document.getElementById('btnExportPDF').addEventListener('click', function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.setFontSize(18);
        doc.text('Reporte de Remesas - NexaCore', 14, 20);
        doc.setFontSize(10);
        doc.text(`Fecha de emisión: ${new Date().toLocaleString()}`, 14, 28);
        doc.autoTable({
            html: '#remesasTable',
            startY: 35,
            theme: 'striped',
            headStyles: { fillColor: [79, 70, 229] }
        });
        doc.save('reporte_remesas.pdf');
    });

    // Export Chart
    document.getElementById('exportChartPNG').addEventListener('click', (e) => {
        e.preventDefault();
        const link = document.createElement('a');
        link.download = `chart_${new Date().getTime()}.png`;
        link.href = document.getElementById('graficaRemesas').toDataURL('image/png');
        link.click();
    });

    // Filter search
    document.getElementById('searchTable').addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('#remesasTable tbody tr');
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
        });
    });

    // Reset filters
    document.getElementById('btnResetFilters').addEventListener('click', () => {
        const form = document.getElementById('filterForm');
        form.querySelector('[name="year"]').selectedIndex = 5;
        form.querySelector('[name="month"]').selectedIndex = 0;
        form.querySelector('[name="cliente_id"]').selectedIndex = 0;
        form.submit();
    });

    document.addEventListener('DOMContentLoaded', initializeChart);
</script>
@endpush
@endsection