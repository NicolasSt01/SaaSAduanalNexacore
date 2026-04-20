@props(['tenant' => null, 'compact' => false])

@php
    if (!$tenant && auth()->check() && auth()->user()->tenant) {
        $tenant = auth()->user()->tenant;
    }
    
    if (!$tenant) {
        return;
    }

    $capabilityService = new \App\Services\TenantCapabilityService();
    $usageSummary = $capabilityService->getTenantUsageSummary($tenant);
    $nearLimitResources = $capabilityService->getNearLimitResources($tenant, 80);
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 {{ $compact ? 'p-4' : 'p-6' }}">
    <div class="flex items-center justify-between mb-4">
        <h3 class="{{ $compact ? 'text-base' : 'text-lg' }} font-black text-gray-800 flex items-center gap-2">
            <i class="fas fa-chart-pie text-indigo-500"></i>
            Uso de Recursos
        </h3>
        @if(!$compact)
        <a href="{{ route('admin.tenants.capabilities', $tenant->id) }}" 
           class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
            <i class="fas fa-cog mr-1"></i> Configurar
        </a>
        @endif
    </div>

    @if($nearLimitResources)
    <div class="mb-4 bg-amber-50 border border-amber-200 rounded-lg p-3">
        <div class="flex items-start gap-2">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 text-sm"></i>
            <div>
                <p class="text-xs font-bold text-amber-800">Recursos cerca del límite:</p>
                <ul class="text-xs text-amber-700 mt-1 space-y-0.5">
                    @foreach(array_slice($nearLimitResources, 0, 3) as $resource => $data)
                    <li>• {{ ucfirst(str_replace('_', ' ', $resource)) }}: {{ $data['uso'] }}/{{ $data['limite'] }} ({{ number_format($data['porcentaje'], 0) }}%)</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <div class="space-y-3">
        {{-- SOIA-Bot --}}
        @if(isset($usageSummary['bot']))
        <div class="pb-3 {{ !$compact ? 'border-b border-gray-100' : '' }}">
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs font-bold text-gray-600 uppercase flex items-center gap-1">
                    <i class="fas fa-robot text-purple-500"></i> SOIA-Bot
                </span>
                <div class="flex items-center gap-2">
                    <span class="text-xs px-2 py-0.5 rounded-full font-bold {{ $usageSummary['bot']['enabled'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $usageSummary['bot']['enabled'] ? 'Activo' : 'Inactivo' }}
                    </span>
                    @if($usageSummary['bot']['limite_consultas_mes'])
                    <span class="text-xs font-black text-gray-800">
                        {{ $usageSummary['bot']['consultas_usadas'] }}/{{ $usageSummary['bot']['limite_consultas_mes'] }}
                    </span>
                    @endif
                </div>
            </div>
            @if($usageSummary['bot']['limite_consultas_mes'])
            @php
                $botPorcentaje = ($usageSummary['bot']['limite_consultas_mes'] > 0) 
                    ? min(100, ($usageSummary['bot']['consultas_usadas'] / $usageSummary['bot']['limite_consultas_mes']) * 100) 
                    : 0;
            @endphp
            <div class="w-full bg-gray-200 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $botPorcentaje >= 90 ? 'bg-red-500' : ($botPorcentaje >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}" 
                     style="width: {{ $botPorcentaje }}%"></div>
            </div>
            @endif
            <p class="text-xs text-gray-500 mt-1">
                Modo: <span class="font-bold">{{ ucfirst($usageSummary['bot']['modo']) }}</span>
            </p>
        </div>
        @endif

        {{-- Recursos principales --}}
        @foreach(['clientes', 'importadores', 'bodegas', 'patentes', 'pedimentos_mes', 'documentos_mes'] as $recurso)
            @if(isset($usageSummary[$recurso]) && !$usageSummary[$recurso]['sin_limite'])
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-bold text-gray-600 uppercase">{{ ucfirst(str_replace('_', ' ', $usageSummary[$recurso]['nombre'])) }}</span>
                    <span class="text-xs font-black text-gray-800">{{ $usageSummary[$recurso]['uso'] }}/{{ $usageSummary[$recurso]['limite'] }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full {{ $usageSummary[$recurso]['porcentaje'] >= 90 ? 'bg-red-500' : ($usageSummary[$recurso]['porcentaje'] >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}" 
                         style="width: {{ $usageSummary[$recurso]['porcentaje'] }}%"></div>
                </div>
            </div>
            @endif
        @endforeach
    </div>

    @if(!$compact)
    <div class="mt-4 pt-4 border-t border-gray-100">
        <a href="{{ route('admin.tenants.capabilities', $tenant->id) }}" 
           class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
            Ver detalles completos <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    @endif
</div>
