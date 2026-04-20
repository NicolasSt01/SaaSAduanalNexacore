<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Operaciones | {{ $cliente->nombre_empresa }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        nunito: ['Nunito', 'sans-serif'],
                    },
                    colors: {
                        primary: '#4f46e5',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .print-shadow {
                box-shadow: none !important;
                border: 1px solid #e5e7eb !important;
            }
        }

        .chart-container {
            position: relative;
            height: 100%;
            width: 100%;
        }

        /* Estilos Calendar Heatmap Premium */
        .calendar-cell {
            aspect-ratio: 1 / 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .calendar-cell.active:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        .calendar-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 20px;
            height: 20px;
            padding: 0 5px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Intensidades */
        .color-0 {
            background-color: #f9fafb;
            color: #d1d5db;
            border: 1px solid #f3f4f6;
        }

        .color-low {
            background-color: #eef2ff;
            color: #6366f1;
            border: 1px solid #e0e7ff;
        }

        .color-medium {
            background-color: #e0e7ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
        }

        .color-high {
            background-color: #4f46e5;
            color: white;
            border: 1px solid #4338ca;
            shadow: 0 4px 6px -1px rgba(79, 70, 225, 0.2);
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900 selection:bg-indigo-100 min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-8">

        <!-- Header Section -->
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-grow">
                <div class="flex items-center gap-3 mb-2">
                    <span
                        class="px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-full">Reporte
                        Digital</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-sm font-bold text-gray-500">{{ now()->format('d M, Y') }}</span>
                </div>
                <h1 class="text-4xl font-black text-gray-800 tracking-tight leading-none mb-2">
                    Operaciones <span class="text-indigo-600">Aduanales</span>
                </h1>
                <p class="text-lg font-bold text-gray-500 uppercase tracking-tight">{{ $cliente->nombre_empresa }}</p>
            </div>

            <div class="flex items-center gap-4 no-print sm:self-start md:self-center">
                <div class="text-right hidden sm:block">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Rango de
                        Consulta</p>
                    <p class="text-sm font-black text-gray-800">{{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} —
                        {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
                    </p>
                </div>
                <button onclick="window.print()"
                    class="bg-indigo-600 hover:bg-slate-800 text-white font-black text-sm px-6 py-4 rounded-2xl shadow-xl shadow-indigo-200 transition-all active:scale-95 flex items-center gap-2 group">
                    <i class="fas fa-file-pdf group-hover:scale-110 transition-transform"></i>
                    DESCARGAR PDF
                </button>
            </div>
        </header>

        <!-- KPI Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <!-- Total Trámites -->
            <div
                class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm transition-all hover:shadow-xl group relative overflow-hidden">
                <div
                    class="absolute -right-4 -top-4 text-indigo-50 opacity-40 group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-boxes-stacked text-8xl"></i>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div
                        class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
                <div class="text-4xl font-black text-gray-900 mb-1 relative z-10">
                    {{ number_format($datos['estadisticas']['total']) }}
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Total Trámites
                </p>
            </div>

            <!-- Greens -->
            <div
                class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm transition-all hover:shadow-xl group relative overflow-hidden">
                <div
                    class="absolute -right-4 -top-4 text-emerald-50 opacity-40 group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-check-circle text-8xl"></i>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div
                        class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                        <i class="fas fa-shield-check"></i>
                    </div>
                    <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">
                        {{ $datos['estadisticas']['total'] > 0 ? number_format(($datos['estadisticas']['greens'] / $datos['estadisticas']['total']) * 100, 0) : 0 }}%
                    </span>
                </div>
                <div class="text-4xl font-black text-emerald-600 mb-1 relative z-10">
                    {{ number_format($datos['estadisticas']['greens']) }}
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Desaduanamiento
                    Libre</p>
            </div>

            <!-- Reds -->
            <div
                class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm transition-all hover:shadow-xl group relative overflow-hidden">
                <div
                    class="absolute -right-4 -top-4 text-rose-50 opacity-40 group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-triangle-exclamation text-8xl"></i>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                        <i class="fas fa-microscope text-lg"></i>
                    </div>
                </div>
                <div class="text-4xl font-black text-rose-600 mb-1 relative z-10">
                    {{ number_format($datos['estadisticas']['reds']) }}
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10">Reconocimiento
                </p>
            </div>

            <!-- Porcentaje Eficiencia -->
            <div class="bg-slate-900 rounded-3xl p-6 shadow-sm relative overflow-hidden group">
                <div
                    class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20">
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div
                        class="w-10 h-10 rounded-xl bg-white/10 text-white flex items-center justify-center text-lg shadow-inner">
                        <i class="fas fa-bolt-lightning"></i>
                    </div>
                </div>
                <div class="text-4xl font-black text-white mb-1 relative z-10">
                    {{ $datos['estadisticas']['total'] > 0 ? number_format(($datos['estadisticas']['greens'] / $datos['estadisticas']['total']) * 100, 1) : '0.0' }}%
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest relative z-10 italic">
                    Efficiency Rate</p>
            </div>
        </div>

        <!-- Main Dashboard Content -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- Left Column: Activity & Geography -->
            <div class="lg:col-span-8 flex flex-col gap-8">

                <!-- Calendar Heatmap Card -->
                <div
                    class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8 group transition-all hover:shadow-2xl">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                        <div>
                            <h3 class="text-xl font-black text-gray-800 tracking-tight flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-indigo-600"></i>
                                Actividad <span class="text-indigo-600">Diaria</span>
                            </h3>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">Intensidad de
                                operaciones por día</p>
                        </div>
                        <div class="flex items-center gap-4 bg-gray-50 px-4 py-2 rounded-2xl border border-gray-100">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-gray-200"></span>
                                <span class="text-[9px] font-black text-gray-400 uppercase">Sin Actividad</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                                <span class="text-[9px] font-black text-gray-400 uppercase">Alta Carga</span>
                            </div>
                        </div>
                    </div>

                    <div id="calendar-heatmap" class="grid grid-cols-7 gap-2 sm:gap-3">
                        <!-- JS injects content here -->
                        <div class="col-span-7 py-20 flex flex-col items-center justify-center opacity-20">
                            <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                            <p class="font-black">PROCESANDO MATRIZ DE DATOS...</p>
                        </div>
                    </div>
                </div>

                <!-- Histórico Chart -->
                <div
                    class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8 transition-all hover:shadow-2xl">
                    <div class="mb-8">
                        <h3 class="text-xl font-black text-gray-800 tracking-tight flex items-center gap-2">
                            <i class="fas fa-chart-line text-indigo-600"></i>
                            Crecimiento <span class="text-indigo-600">Anual</span>
                        </h3>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">Progresión
                            histórica de trámites</p>
                    </div>
                    <div class="h-64 sm:h-80">
                        <canvas id="chartHistoricoMain"></canvas>
                    </div>
                </div>
            </div>

            <!-- Right Column: Distribution & Top Clients -->
            <div class="lg:col-span-4 flex flex-col gap-8">

                <!-- Distribution Card -->
                <div
                    class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8 transition-all hover:shadow-2xl">
                    <h3 class="text-xl font-black text-gray-800 tracking-tight mb-8">
                        Distribución <br>de <span class="text-indigo-600">Modulación</span>
                    </h3>
                    <div class="h-64 mb-8">
                        <canvas id="chartDistributionDonut"></canvas>
                    </div>
                    <!-- Legend Tags -->
                    <div class="space-y-3 pt-6 border-t border-gray-50 font-nunito">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-gray-500 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Desaduanamiento Libre
                            </span>
                            <span
                                class="font-black text-gray-800">{{ number_format($datos['estadisticas']['greens']) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-gray-500 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-500"></span> Reconocimiento Aduanero
                            </span>
                            <span
                                class="font-black text-gray-800">{{ number_format($datos['estadisticas']['reds']) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Regions Card -->
                <div
                    class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8 transition-all hover:shadow-2xl flex-grow">
                    <h3 class="text-xl font-black text-gray-800 tracking-tight mb-6">
                        Operaciones <br>por <span class="text-indigo-600">Aduana</span>
                    </h3>
                    <div class="space-y-4">
                        @foreach($datos['porAduana'] as $index => $aduana)
                            @if($index < 5)
                                <div class="group">
                                    <div class="flex justify-between items-end mb-2">
                                        <span
                                            class="text-sm font-black text-gray-700 group-hover:text-indigo-600 transition-colors uppercase tracking-tight">{{ $aduana['nombre'] }}</span>
                                        <span class="text-sm font-black text-indigo-600">{{ $aduana['total'] }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden shadow-inner">
                                        <div class="bg-indigo-600 h-full rounded-full transition-all duration-1000"
                                            style="width: {{ $datos['estadisticas']['total'] > 0 ? ($aduana['total'] / $datos['estadisticas']['total']) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Charts Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Operaciones por Día -->
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8 transition-all hover:shadow-2xl">
                <h3 class="text-xl font-black text-gray-800 tracking-tight mb-8 flex items-center gap-2">
                    <i class="fas fa-barcode text-indigo-600"></i>
                    Flujo de <span class="text-indigo-600">Operaciones</span>
                </h3>
                <div class="h-64">
                    <canvas id="chartDailyFlow"></canvas>
                </div>
            </div>

            <!-- Top Importadores Table Card -->
            <div
                class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm p-8 transition-all hover:shadow-2xl flex flex-col">
                <h3 class="text-xl font-black text-gray-800 tracking-tight mb-8 flex items-center gap-2">
                    <i class="fas fa-building text-indigo-600"></i>
                    Top <span class="text-indigo-600">Importadores</span>
                </h3>
                <div class="flex-grow overflow-hidden">
                    <table class="w-full text-left font-nunito border-collapse">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th
                                    class="py-3 px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest rounded-l-xl">
                                    Ranking</th>
                                <th class="py-3 px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    Importador</th>
                                <th
                                    class="py-3 px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right rounded-r-xl">
                                    Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 capitalize">
                            @foreach($datos['topImportadores'] as $idx => $importador)
                                @if($idx < 5)
                                    <tr class="hover:bg-indigo-50/20 transition-colors group">
                                        <td
                                            class="py-4 px-4 font-black text-indigo-200 group-hover:text-indigo-400 leading-none text-2xl">
                                            0{{ $idx + 1 }}</td>
                                        <td class="py-4 px-4">
                                            <p class="text-sm font-black text-gray-800 leading-tight">
                                                {{ strtolower($importador['importador']) }}
                                            </p>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-normal">Active
                                                Partner</p>
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <span
                                                class="text-lg font-black text-gray-900">{{ number_format($importador['total']) }}</span>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-8 border-t border-gray-100 pt-12 pb-8 flex flex-col items-center text-center gap-4">
            <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em] leading-none mb-2">Digital
                Integrity & Aduanal Intelligence</p>
            <div class="flex items-center gap-6">
                <span class="text-gray-400 font-bold text-sm">Powered by NexaCore Aduanal</span>
                <span class="w-1 h-1 rounded-full bg-gray-200"></span>
                <span class="text-gray-400 font-bold text-sm">© {{ date('Y') }} NexaCore</span>
            </div>
        </footer>
    </div>

    <!-- Chart Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const rawData = @json($datos);

        document.addEventListener('DOMContentLoaded', function () {
            renderHeatmap(rawData.calendario);
            initCharts(rawData);
        });

        function renderHeatmap(data) {
            const container = document.getElementById('calendar-heatmap');
            if (!data || data.length === 0) return;

            const days = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
            let html = '';

            // Header de días
            days.forEach(d => {
                html += `<div class="text-[10px] font-black text-gray-400 text-center uppercase mb-2">${d}</div>`;
            });

            // Celdas
            data.forEach(semana => {
                semana.forEach(dia => {
                    let colorClass = 'color-0';
                    let hasData = dia.actual && dia.total > 0;

                    if (dia.actual) {
                        if (dia.total > 0) {
                            if (dia.total > 8) colorClass = 'color-high';
                            else if (dia.total > 3) colorClass = 'color-medium';
                            else colorClass = 'color-low active';
                        }
                    } else {
                        colorClass += ' opacity-20';
                    }

                    html += `
                        <div class="calendar-cell ${colorClass} ${dia.actual ? 'active' : ''}">
                            <span class="text-xs font-black">${dia.dia}</span>
                            ${hasData ? `<div class="calendar-badge ${dia.total > 8 ? 'bg-white text-indigo-700' : 'bg-indigo-600 text-white'}">${dia.total}</div>` : ''}
                        </div>
                    `;
                });
            });

            container.innerHTML = html;
        }

        function initCharts(d) {
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false }, ticks: { font: { weight: '800', size: 10 }, color: '#9ca3af' } },
                    x: { grid: { display: false }, ticks: { font: { weight: '800', size: 10 }, color: '#9ca3af' } }
                }
            };

            // Donut Distribution
            new Chart(document.getElementById('chartDistributionDonut'), {
                type: 'doughnut',
                data: {
                    labels: ['Desaduanamiento Libre', 'Reconocimiento Aduanero'],
                    datasets: [{
                        data: [d.estadisticas.greens, d.estadisticas.reds],
                        backgroundColor: ['#10b981', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 20
                    }]
                },
                options: {
                    ...commonOptions,
                    cutout: '80%',
                    plugins: { legend: { display: false } }
                }
            });

            // Anual Growth Line
            const lineCtx = document.getElementById('chartHistoricoMain').getContext('2d');
            const gradientLine = lineCtx.createLinearGradient(0, 0, 0, 400);
            gradientLine.addColorStop(0, 'rgba(79, 70, 229, 0.4)');
            gradientLine.addColorStop(1, 'rgba(79, 70, 229, 0)');

            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    datasets: [{
                        data: Object.values(d.historialMeses),
                        borderColor: '#4f46e5',
                        borderWidth: 4,
                        fill: true,
                        backgroundColor: gradientLine,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHitRadius: 20,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#4f46e5',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 3
                    }]
                },
                options: commonOptions
            });

            // Daily Flow Bar
            new Chart(document.getElementById('chartDailyFlow'), {
                type: 'bar',
                data: {
                    labels: d.tramitesPorDia.map(i => i.dia),
                    datasets: [{
                        data: d.tramitesPorDia.map(i => i.total),
                        backgroundColor: '#4f46e5',
                        borderRadius: 12,
                        barThickness: 12
                    }]
                },
                options: commonOptions
            });
        }
    </script>
</body>

</html>