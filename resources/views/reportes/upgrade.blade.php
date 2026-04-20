@extends('layouts.app')

@section('title', 'Mejora tu Plan - ' . ($reportInfo['name'] ?? 'Reportes Avanzados'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 pb-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Botón de regreso -->
        <div class="mb-6">
            <a href="{{ route('reportes.index') }}" class="inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold transition">
                <i class="fas fa-arrow-left"></i> Volver al Centro de Reportes
            </a>
        </div>

        <!-- Card Principal -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
            
            <!-- Header con gradiente -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-10 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 opacity-10">
                    @if($reportInfo)
                        <i class="fas {{ $reportInfo['icon'] }} text-[12rem]"></i>
                    @else
                        <i class="fas fa-chart-bar text-[12rem]"></i>
                    @endif
                </div>
                
                <div class="relative z-10">
                    @if($reportInfo)
                        <div class="flex items-center gap-3 mb-4">
                            <div class="bg-white/20 backdrop-blur-sm p-3 rounded-2xl">
                                <i class="fas {{ $reportInfo['icon'] }} text-3xl"></i>
                            </div>
                            <div>
                                <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-white text-[10px] font-black uppercase tracking-widest">
                                    Reporte Premium
                                </span>
                                <h1 class="text-3xl font-black mt-1">{{ $reportInfo['name'] }}</h1>
                            </div>
                        </div>
                    @else
                        <h1 class="text-3xl font-black mb-2">Desbloquea Todo el Potencial de tus Reportes</h1>
                    @endif
                </div>
            </div>

            <!-- Contenido -->
            <div class="px-8 py-10">
                @if($reportInfo)
                    <!-- Mensaje específico del reporte -->
                    <div class="mb-8">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-lock text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-gray-800 dark:text-white mb-2">
                                    Este reporte no está disponible en tu plan actual
                                </h2>
                                <p class="text-gray-600 dark:text-gray-300">
                                    Tu plan actual no incluye acceso al <strong>{{ $reportInfo['name'] }}</strong>, pero puedes mejorar tu plan para desbloquearlo.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción del reporte -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-6 mb-8">
                        <h3 class="text-lg font-black text-gray-800 dark:text-white mb-3">
                            <i class="fas fa-info-circle text-indigo-500 mr-2"></i> ¿Qué ofrece este reporte?
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                            {{ $reportInfo['description'] }}
                        </p>
                    </div>

                    <!-- Beneficios -->
                    <div class="mb-8">
                        <h3 class="text-lg font-black text-gray-800 dark:text-white mb-4">
                            <i class="fas fa-star text-yellow-500 mr-2"></i> Beneficios que obtendrás:
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                                <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Acceso completo al reporte con todas sus funcionalidades</p>
                            </div>
                            <div class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                                <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Exportación de datos en múltiples formatos (PDF, Excel)</p>
                            </div>
                            <div class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                                <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Análisis avanzados y filtros personalizados</p>
                            </div>
                            <div class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                                <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Soporte prioritario para configuración de reportes</p>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Mensaje general -->
                    <div class="text-center mb-8">
                        <div class="inline-block bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 w-20 h-20 rounded-3xl flex items-center justify-center mb-4">
                            <i class="fas fa-rocket text-4xl"></i>
                        </div>
                        <h2 class="text-2xl font-black text-gray-800 dark:text-white mb-3">
                            Mejora tu Plan y Accede a Más Funcionalidades
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                            Actualmente tienes acceso limitado a nuestros reportes. Mejora tu plan para desbloquear todas las herramientas de análisis y reporting.
                        </p>
                    </div>
                @endif

                <!-- Planes disponibles -->
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-2xl p-8 mb-8 border-2 border-indigo-100 dark:border-indigo-800">
                    <h3 class="text-xl font-black text-gray-800 dark:text-white mb-6 text-center">
                        <i class="fas fa-crown text-yellow-500 mr-2"></i> Planes Disponibles
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Plan Básico -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border-2 border-gray-200 dark:border-gray-700">
                            <div class="text-center">
                                <div class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-box text-2xl"></i>
                                </div>
                                <h4 class="text-lg font-black text-gray-800 dark:text-white">Básico</h4>
                                <p class="text-3xl font-black text-blue-600 my-3">$299<span class="text-sm text-gray-500">/mes</span></p>
                                <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-2 text-left mt-4">
                                    <li><i class="fas fa-check text-green-500 mr-2"></i> 4 reportes básicos</li>
                                    <li><i class="fas fa-check text-green-500 mr-2"></i> Exportación PDF</li>
                                    <li><i class="fas fa-check text-green-500 mr-2"></i> Soporte por email</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Plan Profesional -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border-2 border-indigo-500 relative transform scale-105">
                            <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                                <span class="bg-indigo-600 text-white px-4 py-1 rounded-full text-xs font-black">
                                    MÁS POPULAR
                                </span>
                            </div>
                            <div class="text-center">
                                <div class="bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-gem text-2xl"></i>
                                </div>
                                <h4 class="text-lg font-black text-gray-800 dark:text-white">Profesional</h4>
                                <p class="text-3xl font-black text-indigo-600 my-3">$599<span class="text-sm text-gray-500">/mes</span></p>
                                <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-2 text-left mt-4">
                                    <li><i class="fas fa-check text-green-500 mr-2"></i> 6 reportes completos</li>
                                    <li><i class="fas fa-check text-green-500 mr-2"></i> Exportación PDF + Excel</li>
                                    <li><i class="fas fa-check text-green-500 mr-2"></i> Soporte prioritario</li>
                                    <li><i class="fas fa-check text-green-500 mr-2"></i> Reportes personalizados</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Plan Enterprise -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border-2 border-gray-200 dark:border-gray-700">
                            <div class="text-center">
                                <div class="bg-purple-100 dark:bg-purple-900/30 text-purple-600 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-building text-2xl"></i>
                                </div>
                                <h4 class="text-lg font-black text-gray-800 dark:text-white">Enterprise</h4>
                                <p class="text-3xl font-black text-purple-600 my-3">$999<span class="text-sm text-gray-500">/mes</span></p>
                                <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-2 text-left mt-4">
                                    <li><i class="fas fa-check text-green-500 mr-2"></i> Todos los reportes</li>
                                    <li><i class="fas fa-check text-green-500 mr-2"></i> API completa</li>
                                    <li><i class="fas fa-check text-green-500 mr-2"></i> Soporte dedicado 24/7</li>
                                    <li><i class="fas fa-check text-green-500 mr-2"></i> Integraciones custom</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#" class="inline-flex items-center justify-center gap-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-8 py-4 rounded-2xl font-black text-lg shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-0.5">
                        <i class="fas fa-arrow-up"></i>
                        Mejorar Mi Plan Ahora
                    </a>
                    <a href="#" class="inline-flex items-center justify-center gap-3 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-500 text-gray-700 dark:text-gray-200 px-8 py-4 rounded-2xl font-black text-lg shadow-md hover:shadow-lg transition-all">
                        <i class="fas fa-comments"></i>
                        Hablar con Ventas
                    </a>
                </div>

                <!-- Nota adicional -->
                <div class="mt-8 p4 bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 rounded-r-xl p-4">
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>¿Necesitas ayuda?</strong> Contacta a nuestro equipo de ventas para encontrar el plan perfecto para tu agencia. 
                        Todos los planes incluyen soporte técnico y capacitación.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer con reportes disponibles -->
        <div class="mt-8 bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-black text-gray-800 dark:text-white mb-4">
                <i class="fas fa-check-circle text-green-500 mr-2"></i> Reportes que sí tienes disponibles:
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @php
                    $tenant = auth()->user()->tenant;
                    $enabledReports = $tenant ? $tenant->getEnabledReports() : [];
                    $allReports = $tenant ? \App\Models\Tenant::getAllAvailableReports() : [];
                @endphp
                @foreach($enabledReports as $enabledReportId)
                    @if(isset($allReports[$enabledReportId]))
                        <div class="flex items-center gap-2 p-3 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800">
                            <i class="fas {{ $allReports[$enabledReportId]['icon'] }} text-{{ $allReports[$enabledReportId]['color'] }}-500"></i>
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $allReports[$enabledReportId]['name'] }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
