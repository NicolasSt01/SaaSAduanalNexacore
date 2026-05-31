@extends('layouts.admin')

@section('title', 'Capacidades y Límites - ' . $tenant->nombre_empresa)

@section('content')
@php
    $config = $tenant->configuracion ?? [];
    $botConfig = $config['bot'] ?? [];
    $limites = $config['limites']['recursos'] ?? [];
    $funcionalidades = $config['limites']['funcionalidades'] ?? [];
    $enabledFeatures = $config['features_enabled'] ?? [];
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-800 tracking-tight">Capacidades y Límites - <span class="text-indigo-600">{{ $tenant->nombre_empresa }}</span></h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Configura los límites y capacidades de este tenant</p>
            </div>
        </div>
        <div class="flex gap-2">
            <form action="{{ route('admin.tenants.capabilities.apply-defaults', $tenant->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow-sm transition text-sm font-bold">
                    <i class="fas fa-magic mr-1"></i> Aplicar Defaults del Plan
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-emerald-500 mr-3"></i>
            <p class="text-sm text-emerald-700 font-bold">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <!-- Panel de depuración: Mostrar configuración actual JSON -->
    <div class="mb-6 bg-gray-50 border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-gray-700">
                <i class="fas fa-code text-gray-500 mr-1"></i> Configuración Actual (JSON)
            </h3>
            <button onclick="document.getElementById('json_debug').classList.toggle('hidden')" 
                class="text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded font-bold text-gray-600 transition">
                <i class="fas fa-eye mr-1"></i> Mostrar/Ocultar
            </button>
        </div>
        <div id="json_debug" class="hidden">
            <pre class="text-xs bg-gray-900 text-green-400 p-3 rounded-lg overflow-x-auto">{{ json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded font-bold">
                    Bot Mode: {{ $botConfig['mode'] ?? 'No configurado' }}
                </span>
                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded font-bold">
                    Bot Enabled: {{ ($botConfig['mode'] ?? 'manual') !== 'deshabilitado' ? 'Sí' : 'No' }}
                </span>
                <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded font-bold">
                    Bot Automático: {{ ($botConfig['mode'] ?? 'manual') === 'automatico' ? 'Sí' : 'No' }}
                </span>
                <span class="bg-cyan-100 text-cyan-700 px-2 py-1 rounded font-bold">
                    Reportes Habilitados: {{ count($config['reportes']['enabled'] ?? []) }}
                </span>
            </div>
        </div>
    </div>

    @if($nearLimitResources)
    <div class="mb-6 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg shadow-sm">
        <div class="flex items-start gap-3">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
            <div>
                <p class="text-sm font-bold text-amber-800 mb-2">Recursos cerca del límite (>70%):</p>
                <ul class="text-sm text-amber-700 space-y-1">
                    @foreach($nearLimitResources as $resource => $data)
                    <li>• {{ ucfirst($resource) }}: {{ $data['uso'] }}/{{ $data['limite'] }} ({{ number_format($data['porcentaje'], 1) }}%)</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.tenants.capabilities.update', $tenant->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Columna izquierda: Configuración -->
            <div class="lg:col-span-2 space-y-6">

                <!-- SOIA-Bot Configuration -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 text-lg">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Configuración del SOIA-Bot</h2>
                            <p class="text-xs text-gray-400">Controla cómo el bot automatizado interactúa con este tenant</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Bot Mode -->
                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-cog text-purple-500 mr-1"></i> Modo del Bot
                            </label>
                            <select name="bot_mode" class="w-full rounded-xl border-gray-300 focus:ring-purple-500 focus:border-purple-500 px-4 py-2.5 font-bold">
                                <option value="manual" {{ ($botConfig['mode'] ?? 'manual') === 'manual' ? 'selected' : '' }}>
                                    🔧 Manual - El usuario debe activar las consultas
                                </option>
                                <option value="automatico" {{ ($botConfig['mode'] ?? '') === 'automatico' ? 'selected' : '' }}>
                                    🤖 Automático - El bot consulta automáticamente
                                </option>
                                <option value="deshabilitado" {{ ($botConfig['mode'] ?? '') === 'deshabilitado' ? 'selected' : '' }}>
                                    ❌ Deshabilitado - El bot no está disponible
                                </option>
                            </select>
                        </div>

                        <!-- Bot Consultas Limite -->
                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-search text-purple-500 mr-1"></i> Límite de Consultas por Mes
                            </label>
                            <input type="number" name="bot_consultas_limite_mes" value="{{ $botConfig['consultas_limite_mes'] ?? '' }}"
                                placeholder="Dejar vacío para ilimitado"
                                class="w-full rounded-xl border-gray-300 focus:ring-purple-500 focus:border-purple-500 px-4 py-2.5 font-bold">
                            <p class="text-xs text-gray-400 mt-1">Número máximo de consultas al SOIA-Bot permitidas por mes</p>
                        </div>
                    </div>
                </div>

                <!-- Límites de Recursos -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 text-lg">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Límites de Recursos</h2>
                            <p class="text-xs text-gray-400">Cantidad máxima de elementos que el tenant puede crear</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-users text-blue-500 mr-1"></i> Clientes
                            </label>
                            <input type="number" name="limite_clientes" value="{{ $limites['clientes'] ?? '' }}"
                                placeholder="Sin límite"
                                class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 px-4 py-2.5 font-bold">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-globe text-blue-500 mr-1"></i> Importadores
                            </label>
                            <input type="number" name="limite_importadores" value="{{ $limites['importadores'] ?? '' }}"
                                placeholder="Sin límite"
                                class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 px-4 py-2.5 font-bold">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-warehouse text-blue-500 mr-1"></i> Bodegas
                            </label>
                            <input type="number" name="limite_bodegas" value="{{ $limites['bodegas'] ?? '' }}"
                                placeholder="Sin límite"
                                class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 px-4 py-2.5 font-bold">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-building text-blue-500 mr-1"></i> Aduanas
                            </label>
                            <input type="number" name="limite_aduanas" value="{{ $limites['aduanas'] ?? '' }}"
                                placeholder="Sin límite"
                                class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 px-4 py-2.5 font-bold">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-id-badge text-blue-500 mr-1"></i> Patentes
                            </label>
                            <input type="number" name="limite_patentes" value="{{ $limites['patentes'] ?? '' }}"
                                placeholder="Sin límite"
                                class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 px-4 py-2.5 font-bold">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-file-invoice text-blue-500 mr-1"></i> Pedimentos/Mes
                            </label>
                            <input type="number" name="limite_pedimentos_mes" value="{{ $limites['pedimentos_mes'] ?? '' }}"
                                placeholder="Sin límite"
                                class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 px-4 py-2.5 font-bold">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-cloud-upload-alt text-blue-500 mr-1"></i> Documentos/Mes
                            </label>
                            <input type="number" name="limite_documentos_mes" value="{{ $limites['documentos_mes'] ?? '' }}"
                                placeholder="Sin límite"
                                class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 px-4 py-2.5 font-bold">
                        </div>
                    </div>
                </div>

                <!-- Límites de Funcionalidades -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600 text-lg">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Límites de Funcionalidades</h2>
                            <p class="text-xs text-gray-400">Controla el uso de correos, reportes y WhatsApp</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-chart-bar text-orange-500 mr-1"></i> Reportes/Mes
                            </label>
                            <input type="number" name="limite_reportes_mes" value="{{ $funcionalidades['reportes_mes'] ?? '' }}"
                                placeholder="Sin límite"
                                class="w-full rounded-xl border-gray-300 focus:ring-orange-500 focus:border-orange-500 px-4 py-2.5 font-bold">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-envelope text-orange-500 mr-1"></i> Correos/Día
                            </label>
                            <input type="number" name="limite_correos_dia" value="{{ $funcionalidades['correos_dia'] ?? '' }}"
                                placeholder="Sin límite"
                                class="w-full rounded-xl border-gray-300 focus:ring-orange-500 focus:border-orange-500 px-4 py-2.5 font-bold">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fab fa-whatsapp text-orange-500 mr-1"></i> WhatsApp/Mes
                            </label>
                            <input type="number" name="limite_whatsapp_mes" value="{{ $funcionalidades['whatsapp_mes'] ?? '' }}"
                                placeholder="Sin límite"
                                class="w-full rounded-xl border-gray-300 focus:ring-orange-500 focus:border-orange-500 px-4 py-2.5 font-bold">
                        </div>
                    </div>
                </div>

                <!-- Configuración de Reportes -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-cyan-100 flex items-center justify-center text-cyan-600 text-lg">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">📊 Configuración de Reportes</h2>
                            <p class="text-xs text-gray-400">Controla qué reportes puede generar este tenant</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @php
                            $enabledReports = $config['reportes']['enabled'] ?? [];
                            $allReports = \App\Models\Tenant::getAllAvailableReports();
                        @endphp

                        @foreach($allReports as $reportId => $reportInfo)
                            @php
                                $isEnabled = in_array($reportId, $enabledReports);
                                $isComingSoon = $reportInfo['status'] === 'coming_soon';
                            @endphp
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 {{ $isComingSoon ? 'border-gray-200 bg-gray-50 opacity-60' : ($isEnabled ? 'border-cyan-300 bg-cyan-50' : 'border-gray-200 bg-gray-50') }} hover:bg-cyan-50 cursor-pointer transition group">
                                <div class="flex-shrink-0 mt-0.5">
                                    @if($isComingSoon)
                                        <input type="checkbox" disabled 
                                            class="w-5 h-5 rounded text-gray-400 cursor-not-allowed">
                                    @else
                                        <input type="checkbox" name="reportes_enabled[]" value="{{ $reportId }}"
                                            {{ $isEnabled ? 'checked' : '' }}
                                            class="w-5 h-5 text-cyan-600 rounded focus:ring-cyan-500 cursor-pointer">
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <i class="fas {{ $reportInfo['icon'] }} text-{{ $reportInfo['color'] }}-500"></i>
                                        <span class="text-sm font-bold text-gray-800">{{ $reportInfo['name'] }}</span>
                                        @if($isComingSoon)
                                            <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full font-bold">
                                                <i class="fas fa-clock mr-1"></i> Próximamente
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">{{ $reportInfo['description'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Botones de selección rápida -->
                    <div class="mt-4 pt-4 border-t border-gray-200 flex gap-2">
                        <button type="button" onclick="selectAllReports()" 
                            class="text-xs bg-cyan-100 hover:bg-cyan-200 text-cyan-700 px-3 py-1.5 rounded-lg font-bold transition">
                            <i class="fas fa-check-double mr-1"></i> Seleccionar Todos
                        </button>
                        <button type="button" onclick="deselectAllReports()" 
                            class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg font-bold transition">
                            <i class="fas fa-times mr-1"></i> Deseleccionar Todos
                        </button>
                    </div>
                </div>

                <!-- Features Habilitadas -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 text-lg">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Características Habilitadas</h2>
                            <p class="text-xs text-gray-400">Selecciona qué funcionalidades están disponibles</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @php
                            $availableFeatures = [
                                'email_notifications' => '📧 Notificaciones por Email',
                                'whatsapp_notifications' => '💬 Notificaciones por WhatsApp',
                            ];
                        @endphp

                        @foreach($availableFeatures as $key => $label)
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                            <input type="checkbox" name="features_enabled[]" value="{{ $key }}" 
                                {{ in_array($key, $enabledFeatures) ? 'checked' : '' }}
                                class="w-5 h-5 text-emerald-500 rounded focus:ring-emerald-500">
                            <span class="text-sm font-bold text-gray-700">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Plantilla WhatsApp Personalizada (Superadmin) -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 text-lg">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Plantilla WhatsApp Personalizada</h2>
                            <p class="text-xs text-gray-400">Formato de mensaje personalizado para notificaciones de modulación</p>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 mb-3">
                        <i class="fas fa-info-circle text-purple-500 mr-1"></i>
                        Si se configura, esta plantilla <strong>reemplaza</strong> las 3 básicas (Breve, Detallado, Corporativo).
                        Usa los siguientes placeholders:
                    </p>

                    <div class="bg-gray-50 rounded-xl p-3 mb-4 text-xs font-mono text-gray-600 space-y-1">
                        <div><code class="text-purple-600">@{{operacion.modulacion}}</code> — Estatus detectado (ej. "DESADUANAMIENTO LIBRE")</div>
                        <div><code class="text-purple-600">@{{operacion.factura}}</code> — Número de factura</div>
                        <div><code class="text-purple-600">@{{operacion.referencia}}</code> — Referencia del cliente</div>
                        <div><code class="text-purple-600">@{{operacion.producto}}</code> — Nombre del producto</div>
                        <div><code class="text-purple-600">@{{operacion.thermo}}</code> — Número económico</div>
                        <div><code class="text-purple-600">@{{operacion.doda}}</code> — Número de DODA</div>
                        <div><code class="text-purple-600">@{{operacion.fecha}}</code> — Fecha de modulación</div>
                        <div><code class="text-purple-600">@{{tenant.nombre}}</code> — Nombre de la agencia</div>
                        <div><code class="text-purple-600">@{{destinatario.nombre}}</code> — Nombre del contacto</div>
                        <div><code class="text-purple-600">@{{emoji}}</code> — ✅ para libre, ⚠️ para otros estados</div>
                    </div>

                    <label class="block text-sm font-bold text-gray-700 mb-2">Texto de la Plantilla</label>
                    <textarea name="whatsapp_plantilla_custom" rows="8"
                        class="w-full rounded-xl border-gray-300 focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50 font-mono text-sm"
                        placeholder="Ejemplo:&#10;{emoji} *Actualizacion - {tenant.nombre}*&#10;&#10;Factura: {operacion.factura}&#10;Estado: {operacion.modulacion} {emoji}&#10;Producto: {operacion.producto}&#10;&#10;{tenant.nombre}"
                    >{{ $config['evolution_api']['whatsapp_plantilla_custom'] ?? '' }}</textarea>

                    <div class="flex items-center gap-3 mt-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="whatsapp_plantilla_custom_clear" value="1"
                                class="w-4 h-4 text-red-500 rounded focus:ring-red-500">
                            <span class="text-xs text-red-600 font-bold">Quitar plantilla personalizada (volver a plantillas básicas)</span>
                        </label>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                        <i class="fas fa-save mr-1"></i> Guardar Configuración
                    </button>
                </div>
            </div>

            <!-- Columna derecha: Resumen de uso actual -->
            <div class="space-y-6">
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 rounded-2xl p-6 sticky top-4">
                    <h3 class="text-lg font-black text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-indigo-500"></i>
                        Uso Actual
                    </h3>

                    <div class="space-y-4">
                        @foreach($usageSummary as $key => $data)
                            @if($key !== 'bot' && isset($data['limite']))
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-bold text-gray-600 uppercase">{{ ucfirst(str_replace('_', ' ', $data['nombre'])) }}</span>
                                    <span class="text-xs font-black text-gray-800">
                                        @if($data['sin_limite'])
                                            <span class="text-emerald-600">∞</span>
                                        @else
                                            {{ $data['uso'] }}/{{ $data['limite'] }}
                                        @endif
                                    </span>
                                </div>
                                @if(!$data['sin_limite'])
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $data['porcentaje'] >= 90 ? 'bg-red-500' : ($data['porcentaje'] >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}" 
                                         style="width: {{ $data['porcentaje'] }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">{{ number_format($data['porcentaje'], 1) }}% usado</p>
                                @else
                                <p class="text-xs text-emerald-600 font-bold">Sin límite</p>
                                @endif
                            </div>
                            @endif
                        @endforeach

                        @if(isset($usageSummary['bot']))
                        <div class="pt-4 border-t border-indigo-100">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-bold text-gray-600 uppercase">SOIA-Bot Consultas</span>
                                <span class="text-xs font-black text-gray-800">
                                    @if($usageSummary['bot']['limite_consultas_mes'])
                                        {{ $usageSummary['bot']['consultas_usadas'] }}/{{ $usageSummary['bot']['limite_consultas_mes'] }}
                                    @else
                                        <span class="text-emerald-600">∞</span>
                                    @endif
                                </span>
                            </div>
                            @if($usageSummary['bot']['limite_consultas_mes'])
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                @php
                                    $botPorcentaje = ($usageSummary['bot']['limite_consultas_mes'] > 0) 
                                        ? min(100, ($usageSummary['bot']['consultas_usadas'] / $usageSummary['bot']['limite_consultas_mes']) * 100) 
                                        : 0;
                                @endphp
                                <div class="h-2 rounded-full {{ $botPorcentaje >= 90 ? 'bg-red-500' : ($botPorcentaje >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}" 
                                     style="width: {{ $botPorcentaje }}%"></div>
                            </div>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">
                                Modo: <span class="font-bold">{{ ucfirst($usageSummary['bot']['modo']) }}</span>
                            </p>
                        </div>
                        @endif
                    </div>

                    <div class="mt-6 pt-4 border-t border-indigo-100">
                        <a href="{{ route('admin.tenants.usage', $tenant->id) }}" target="_blank" 
                           class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                            <i class="fas fa-external-link-alt"></i> Ver JSON completo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function selectAllReports() {
    const checkboxes = document.querySelectorAll('input[name="reportes_enabled[]"]:not([disabled])');
    checkboxes.forEach(cb => cb.checked = true);
}

function deselectAllReports() {
    const checkboxes = document.querySelectorAll('input[name="reportes_enabled[]"]');
    checkboxes.forEach(cb => cb.checked = false);
}
</script>

@endsection
