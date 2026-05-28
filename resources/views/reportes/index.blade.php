@extends('layouts.app')

@section('title', 'Centro de Reportes - Analíticas Operativas')

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
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-black text-gray-800 dark:text-white tracking-tight">
                            Centro de <span class="text-indigo-600 dark:text-indigo-400">Reportes</span>
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Analíticas avanzadas y
                            seguimiento operativo de tu agencia.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="document.documentElement.classList.toggle('dark')"
                            class="p-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-yellow-400 hover:scale-105 transition shadow-sm border border-gray-200 dark:border-gray-600">
                            <i class="fas fa-moon dark:hidden"></i>
                            <i class="fas fa-sun hidden dark:inline"></i>
                        </button>
                        <div
                            class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 px-4 py-2 rounded-xl flex items-center shadow-sm">
                            <i class="fas fa-chart-pie text-indigo-500 mr-2"></i>
                            <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300">Inteligencia de
                                Datos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Dashboard Executive Link (Destacado) -->
            <div class="mb-10">
                <a href="{{ route('reportes.gerencia') }}"
                    class="group relative block bg-gradient-to-r from-indigo-600 to-purple-700 rounded-3xl p-8 shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div
                        class="absolute top-0 right-0 -mt-10 -mr-10 text-white/10 group-hover:scale-110 transition-transform duration-700">
                        <i class="fas fa-tachometer-alt text-[15rem]"></i>
                    </div>
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="max-w-xl">
                            <span
                                class="inline-block px-3 py-1 rounded-full bg-white/20 text-white text-[10px] font-black uppercase tracking-widest mb-4">Dashboard
                                Ejecutivo</span>
                            <h2 class="text-3xl font-black text-white mb-3">Dashboard de Gerencia v2.0</h2>
                            <p class="text-indigo-100 font-medium">Visualiza KPIs de alto nivel, comparativas anuales, metas
                                diarias y distribución por aduana en una interfaz de última generación.</p>
                        </div>
                        <div>
                            <span
                                class="inline-flex items-center gap-2 bg-white text-indigo-700 px-6 py-3 rounded-2xl font-black text-sm group-hover:bg-indigo-50 transition-colors shadow-lg">
                                Ir al Dashboard <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Sections Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($allReports as $reportId => $reportInfo)
                    @php
                        $isEnabled = in_array($reportId, $enabledReports);
                        $isComingSoon = $reportInfo['status'] === 'coming_soon';

                        // Mapeo correcto de reportId a nombre de ruta
                        $routeMap = [
                            'clientes' => 'reportes.cliente',
                            'operacion_semanal' => 'reportes.operacion_semanal',
                            'remesas' => 'reportes.remesas',
                            'clientes_pdf' => 'reportes.cliente.mail',
                            'aduanas' => 'reportes.aduanas',
                            'patron_clientes' => 'reportes.patrones-cliente',
                            'pedimentos' => 'reportes.pedimentos',
                            'financiero' => null, // Próximamente
                            'logistica' => null,  // Próximamente
                        ];

                        $routeName = $routeMap[$reportId] ?? null;
                        $route = ($isComingSoon || !$routeName) ? '#' : route($routeName);
                    @endphp
                    <div
                        class="group relative bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-xl transition-all duration-300 flex flex-col h-full transform hover:-translate-y-1 {{ !$isEnabled ? 'opacity-75' : '' }} hover:border-{{ $reportInfo['color'] }}-300 dark:hover:border-{{ $reportInfo['color'] }}-500">
                        <div
                            class="absolute -right-4 -top-4 text-{{ $reportInfo['color'] }}-50 dark:text-{{ $reportInfo['color'] }}-900/10 opacity-50 group-hover:scale-110 transition-transform duration-500">
                            <i class="fas {{ $reportInfo['icon'] }} text-8xl"></i>
                        </div>

                        <div class="flex items-start gap-4 mb-4 relative z-10">
                            <div
                                class="bg-{{ $reportInfo['color'] }}-100 dark:bg-{{ $reportInfo['color'] }}-900/30 text-{{ $reportInfo['color'] }}-600 dark:text-{{ $reportInfo['color'] }}-400 w-12 h-12 rounded-2xl flex justify-center items-center text-xl shadow-inner">
                                <i class="fas {{ $reportInfo['icon'] }}"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3
                                        class="text-lg font-black text-gray-800 dark:text-white group-hover:text-{{ $reportInfo['color'] }}-600 transition-colors">
                                        {{ $reportInfo['name'] }}
                                    </h3>
                                    @if($isEnabled)
                                        <span class="text-green-500" title="Disponible en tu plan">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                    @else
                                        <span class="text-gray-400" title="No disponible en tu plan actual">
                                            <i class="fas fa-minus-circle"></i>
                                        </span>
                                    @endif
                                </div>
                                @if($isComingSoon)
                                    <span
                                        class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 mt-1">
                                        <i class="fas fa-clock mr-1"></i> Próximamente
                                    </span>
                                @elseif($isEnabled)
                                    <span
                                        class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-{{ $reportInfo['color'] }}-50 dark:bg-{{ $reportInfo['color'] }}-900/20 text-{{ $reportInfo['color'] }}-500 dark:text-{{ $reportInfo['color'] }}-400 mt-1">
                                        <i class="fas fa-check mr-1"></i> Incluido
                                    </span>
                                @else
                                    <span
                                        class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-orange-50 dark:bg-orange-900/20 text-orange-500 dark:text-orange-400 mt-1">
                                        <i class="fas fa-lock mr-1"></i> Requiere upgrade
                                    </span>
                                @endif
                            </div>
                        </div>

                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 relative z-10 mb-6 flex-grow leading-relaxed">
                            {{ $reportInfo['description'] }}
                        </p>

                        <div class="relative z-10">
                            @if($isComingSoon)
                                <button disabled
                                    class="inline-flex items-center justify-center w-full bg-gray-100 dark:bg-gray-700 py-3 rounded-2xl text-gray-400 dark:text-gray-500 text-xs font-black cursor-not-allowed">
                                    <i class="fas fa-clock mr-2"></i> PRÓXIMAMENTE
                                </button>
                            @elseif($isEnabled)
                                <a href="{{ $route }}"
                                    class="inline-flex items-center justify-center w-full bg-gray-50 dark:bg-gray-700/50 hover:bg-{{ $reportInfo['color'] }}-600 group-hover:text-white py-3 rounded-2xl text-{{ $reportInfo['color'] }}-600 dark:text-{{ $reportInfo['color'] }}-400 text-xs font-black transition-all duration-300 shadow-sm">
                                    CONSULTAR REPORTE <i
                                        class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                                </a>
                            @else
                                <a href="{{ route('reportes.upgrade', ['reporte' => $reportId]) }}"
                                    class="inline-flex items-center justify-center w-full bg-orange-50 dark:bg-orange-900/20 hover:bg-orange-600 hover:text-white py-3 rounded-2xl text-orange-600 dark:text-orange-400 text-xs font-black transition-all duration-300 shadow-sm">
                                    <i class="fas fa-arrow-up mr-2"></i> MEJORAR MI PLAN
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Footer Info -->
            <div class="mt-12 text-center">
                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium italic">
                    <i class="fas fa-lock mr-1"></i> Algunos reportes pueden requerir permisos adicionales configurados por
                    el administrador de la agencia.
                </p>
            </div>
        </div>
    </div>

    <style>
        /* Soporte para colores dinámicos de Tailwind en modo JIT */
        .hover\:border-blue-300:hover {
            border-color: rgb(147 197 253);
        }

        .hover\:border-emerald-300:hover {
            border-color: rgb(110 231 183);
        }

        .hover\:border-sky-300:hover {
            border-color: rgb(125 211 252);
        }

        .hover\:border-rose-300:hover {
            border-color: rgb(252 165 165);
        }

        .hover\:border-amber-300:hover {
            border-color: rgb(252 211 153);
        }

        .hover\:border-purple-300:hover {
            border-color: rgb(216 180 254);
        }

        .hover\:border-red-300:hover {
            border-color: rgb(252 165 165);
        }

        .hover\:border-slate-300:hover {
            border-color: rgb(203 213 225);
        }
    </style>
@endsection