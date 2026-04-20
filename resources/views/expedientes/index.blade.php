@extends('layouts.app')

@section('title', 'Directorio de Pedimentos')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.adminconfig') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-purple-600 transition-colors">
                            <i class="fas fa-cog mr-2"></i> Configuración
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                            <span class="text-sm font-medium text-gray-700">Pedimentos</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Directorio de <span class="text-purple-600">Pedimentos</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Controla el estado y vencimiento de todos los pedimentos u operaciones.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2" id="toggleFilters">
                <i class="fas fa-filter mr-2"></i> Mostrar Filtros
            </button>
            <button onclick="toggleNuevoPedimentoModal()" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-purple-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-purple-700 transition-colors focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                <i class="fas fa-plus mr-2"></i> Nuevo Pedimento
            </button>
        </div>
    </div>

    {{-- ==============================
         CONTADORES SUPERIORES
       ============================== --}}
    @php
        $totalUrgentes = collect($expedientes->items())->where('alerta', 'urgente')->count();
        $totalAdvertencia = collect($expedientes->items())->where('alerta', 'advertencia')->count();
        $totalInfo = collect($expedientes->items())->where('alerta', 'info')->count();
        $totalSinFecha = collect($expedientes->items())->where('alerta', 'sin_fecha')->count();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-bold text-red-500 mb-1 uppercase tracking-wider">Urgentes</p>
                <h3 class="text-3xl font-black text-gray-900">{{ collect($expedientes->items())->where('alerta', 'urgente')->count() }}</h3>
            </div>
            <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center text-red-500 text-2xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-amber-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-bold text-amber-500 mb-1 uppercase tracking-wider">Por Cerrar</p>
                <h3 class="text-3xl font-black text-gray-900">{{ collect($expedientes->items())->where('alerta', 'advertencia')->count() }}</h3>
            </div>
            <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-500 text-2xl">
                <i class="fas fa-exclamation-circle"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-sky-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-bold text-sky-500 mb-1 uppercase tracking-wider">En Tiempo</p>
                <h3 class="text-3xl font-black text-gray-900">{{ collect($expedientes->items())->where('alerta', 'info')->count() }}</h3>
            </div>
            <div class="h-12 w-12 rounded-full bg-sky-100 flex items-center justify-center text-sky-500 text-2xl">
                <i class="fas fa-info-circle"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-bold text-gray-500 mb-1 uppercase tracking-wider">Sin Fecha</p>
                <h3 class="text-3xl font-black text-gray-900">{{ collect($expedientes->items())->where('alerta', 'sin_fecha')->count() }}</h3>
            </div>
            <div class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-2xl">
                <i class="fas fa-question-circle"></i>
            </div>
        </div>
    </div>

    {{-- ==============================
         FILTROS
       ============================== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 mb-6 transition-all duration-300 origin-top overflow-hidden" id="filterCard" style="display:none; max-height: 0px; opacity: 0;">
        <div class="p-6 bg-purple-50/30">
            <h4 class="text-purple-800 font-bold mb-4 flex items-center grid-span-full"><i class="fas fa-filter mr-2"></i> Filtros de Búsqueda</h4>
            <form method="GET" action="{{ route('expedientes.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="numero_pedimento" class="block text-xs font-bold text-gray-700 uppercase mb-1">Pedimento</label>
                        <input type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border" id="numero_pedimento" name="numero_pedimento" value="{{ request('numero_pedimento') }}" placeholder="Ej. 1234567">
                    </div>

                    <div>
                        <label for="estado" class="block text-xs font-bold text-gray-700 uppercase mb-1">Estado</label>
                        <select class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border" id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="En proceso" @if(request('estado') == 'En proceso') selected @endif>En proceso</option>
                            <option value="Abierto" @if(request('estado') == 'Abierto') selected @endif>Abierto</option>
                            <option value="Cerrado" @if(request('estado') == 'Cerrado') selected @endif>Cerrado</option>
                            <option value="Cancelado" @if(request('estado') == 'Cancelado') selected @endif>Cancelado</option>
                        </select>
                    </div>

                    <div>
                        <label for="categoria" class="block text-xs font-bold text-gray-700 uppercase mb-1">Categoría</label>
                        <input type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border" id="categoria" name="categoria" value="{{ request('categoria') }}" placeholder="Ej. A1">
                    </div>

                    <div>
                        <label for="cliente_id" class="block text-xs font-bold text-gray-700 uppercase mb-1">Cliente</label>
                        <select class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border" id="cliente_id" name="cliente_id">
                            <option value="">Todos</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" @if(request('cliente_id') == $cliente->id) selected @endif>
                                    {{ $cliente->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="fecha_desde" class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha Desde</label>
                        <input type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border text-gray-600 cursor-pointer" id="fecha_desde" name="fecha_desde" value="{{ request('fecha_desde') }}">
                    </div>

                    <div>
                        <label for="fecha_hasta" class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha Hasta</label>
                        <input type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border text-gray-600 cursor-pointer" id="fecha_hasta" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                    </div>

                    <div>
                        <label for="cumplimiento" class="block text-xs font-bold text-gray-700 uppercase mb-1">Cumplimiento Digital</label>
                        <select name="cumplimiento" id="cumplimiento" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border cursor-pointer font-medium">
                            <option value="">Todos los registros</option>
                            <option value="incompleto" {{ request('cumplimiento') === 'incompleto' ? 'selected' : '' }}>⚠️ Expediente Incompleto</option>
                        </select>
                    </div>

                    <div class="col-span-1 md:col-span-2 flex items-end justify-end gap-2 mt-2 lg:mt-0">
                        <a href="{{ route('expedientes.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none transition-colors">
                            Limpiar
                        </a>
                        <button type="submit" class="inline-flex justify-center rounded-lg border border-transparent bg-purple-600 px-6 py-2 text-sm font-bold text-white shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors">
                            <i class="fas fa-search mr-2"></i> Aplicar Filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('partials.alerts')

    {{-- ==============================
         TABLA OPERATIVA
       ============================== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex-1">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4"># Pedimento</th>
                        <th class="px-6 py-4">Cliente / Patente</th>
                        <th class="px-6 py-4">Categoría / Aduana</th>
                        <th class="px-6 py-4 text-center">Cumplimiento</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-center">Restantes</th>
                        <th class="px-6 py-4 text-center">Alerta</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">

                @php
                    $alertaConfigBase = [
                        'urgente' => [
                            'text_color' => 'text-red-700',
                            'bg_class' => 'bg-red-50/50',
                            'badge_bg' => 'bg-red-100',
                            'badge_text' => 'text-red-800',
                            'border' => 'border-red-200',
                            'icon' => 'fa-exclamation-triangle',
                            'text' => '¡URGENTE! Cierra pronto'
                        ],
                        'advertencia' => [
                            'text_color' => 'text-amber-700',
                            'bg_class' => 'bg-amber-50/30',
                            'badge_bg' => 'bg-amber-100',
                            'badge_text' => 'text-amber-800',
                            'border' => 'border-amber-200',
                            'icon' => 'fa-exclamation-circle',
                            'text' => 'Cierra en %d días'
                        ],
                        'info' => [
                            'text_color' => 'text-sky-700',
                            'bg_class' => 'bg-sky-50/10',
                            'badge_bg' => 'bg-sky-100',
                            'badge_text' => 'text-sky-800',
                            'border' => 'border-sky-200',
                            'icon' => 'fa-info-circle',
                            'text' => 'Cierra en %d días'
                        ],
                        'sin_fecha' => [
                            'text_color' => 'text-gray-700',
                            'bg_class' => '',
                            'badge_bg' => 'bg-gray-100',
                            'badge_text' => 'text-gray-800',
                            'border' => 'border-gray-200',
                            'icon' => 'fa-question-circle',
                            'text' => 'Falta fecha'
                        ]
                    ];
                @endphp

                @forelse($expedientes as $expediente)
                    @php
                        $alerta = collect($expedientes->items())->where('id', $expediente->id)->first()->alerta ?? 'info';
                        $alertaConfig = $alertaConfigBase[$alerta] ?? $alertaConfigBase['info'];

                        if (str_contains($alertaConfig['text'], '%d')) {
                            $alertaConfig['text'] = sprintf(
                                $alertaConfig['text'],
                                ceil((float)($expediente->dias_restantes ?? 0))
                            );
                        }
                    @endphp

                    <tr class="hover:bg-purple-50/20 transition-colors {{ $alertaConfig['bg_class'] }}">
                        <td class="px-6 py-4">
                            <a href="{{ route('expedientes.show', $expediente) }}" class="font-black text-purple-600 hover:text-purple-800 transition-colors">
                                #{{ $expediente->numero_pedimento }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $expediente->cliente?->nombre ?? 'N/D' }}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-stamp text-gray-400"></i> Patente: <span class="font-bold">{{ $expediente->patente?->numero ?? $expediente->patente?->numero_patente ?? 'N/D' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200 mb-1">
                                {{ $expediente->categoria }}
                            </span>
                            <div class="text-xs text-gray-500 truncate max-wxs">
                                <i class="fas fa-building text-gray-400"></i> {{ $expediente->aduana?->nombre ?? $expediente->aduana?->nombre_aduana ?? 'N/D' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($expediente->cumplimiento_completo)
                                <div class="flex flex-col items-center gap-1 text-emerald-600 font-bold group cursor-default">
                                    <i class="fas fa-check-circle text-lg"></i>
                                    <span class="text-[9px] uppercase tracking-tighter">Completo</span>
                                </div>
                            @else
                                @php $pendientes = $expediente->documentos_pendientes; @endphp
                                <div class="flex flex-col items-center gap-1 text-amber-500 font-bold group relative cursor-help">
                                    <i class="fas fa-exclamation-circle text-lg animate-pulse"></i>
                                    <span class="text-[9px] uppercase tracking-tighter">Incompleto</span>
                                    
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 hidden group-hover:block w-56 p-3 bg-gray-900/95 backdrop-blur-sm text-white text-[10px] rounded-2xl shadow-2xl z-50 border border-gray-700 animate-fade-in-up">
                                        <div class="flex items-center gap-2 font-black border-b border-gray-700 pb-2 mb-2 text-amber-400 uppercase tracking-widest text-[9px]">
                                            <i class="fas fa-clipboard-list"></i> Documentos faltantes
                                        </div>
                                        <ul class="space-y-1.5 font-medium">
                                            @foreach(array_slice($pendientes, 0, 8) as $doc)
                                                <li class="flex items-center gap-2">
                                                    <div class="w-1 h-1 rounded-full bg-amber-500"></div>
                                                    <span class="truncate">{{ $doc }}</span>
                                                </li>
                                            @endforeach
                                            @if(count($pendientes) > 8)
                                                <li class="pl-3 text-gray-400 italic">Y {{ count($pendientes) - 8 }} más...</li>
                                            @endif
                                        </ul>
                                        <div class="mt-2 pt-2 border-t border-gray-700 text-[8px] text-gray-400 uppercase text-center font-black">
                                            Haga clic en ver para completar
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if($expediente->estado == 'Abierto')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></div> Abierto
                                </span>
                            @elseif($expediente->estado == 'Cancelado')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></div> Cancelado
                                </span>
                            @elseif($expediente->estado == 'Cerrado')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></div> Cerrado
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></div> {{ $expediente->estado }}
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-center font-bold">
                            @if(!is_null($expediente->dias_restantes))
                                <span class="{{ $alertaConfig['text_color'] }}">{{ ceil((float)$expediente->dias_restantes) }} días</span>
                            @else
                                <span class="text-gray-400 font-medium">N/D</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $alertaConfig['badge_bg'] }} {{ $alertaConfig['badge_text'] }} border {{ $alertaConfig['border'] }}">
                                <i class="fas {{ $alertaConfig['icon'] }} mr-1"></i> {{ $alertaConfig['text'] }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('expedientes.show', $expediente) }}" class="text-purple-600 bg-purple-50 hover:bg-purple-600 hover:text-white border border-purple-200 h-8 w-8 flex items-center justify-center rounded-lg shadow-sm transition transform hover:scale-105" title="Ver Expediente">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>

                                <a href="{{ route('expedientes.edit', $expediente) }}" class="text-amber-500 bg-amber-50 hover:bg-amber-500 hover:text-white border border-amber-200 h-8 w-8 flex items-center justify-center rounded-lg shadow-sm transition transform hover:scale-105" title="Editar Expediente">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-folder-open text-5xl mb-3 block opacity-50 text-purple-300"></i>
                            <h3 class="font-black text-xl text-gray-700 mb-1">Sin Expedientes</h3>
                            <p class="font-medium text-gray-500">No hay pedimentos registrados o tu búsqueda no arrojó resultados.</p>
                        </td>
                    </tr>
                @endforelse

                </tbody>
            </table>
        </div>
        
        @if(method_exists($expedientes, 'links'))
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $expedientes->links() }}
        </div>
        @endif
    </div>

</div>

{{-- Modal de Nuevo Pedimento --}}
<div id="nuevoPedimentoModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Fondo oscuro -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="toggleNuevoPedimentoModal()"></div>

        <!-- Truco para centrar verticalmente -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Panel del modal -->
        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full sm:p-6">
            <div class="flex justify-between items-center mb-5 border-b pb-4">
                <h3 class="text-xl font-bold leading-6 text-gray-900" id="modal-title">
                    <i class="fas fa-plus-circle text-purple-600 mr-2"></i> Nuevo Pedimento
                </h3>
                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" onclick="toggleNuevoPedimentoModal()">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form action="{{ route('expedientes.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Cliente --}}
                    <div class="col-span-1 md:col-span-2">
                        <label for="modal_cliente_id" class="block text-sm font-bold text-gray-700 mb-1">Cliente *</label>
                        <select name="cliente_id" id="modal_cliente_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border" required>
                            <option value="">Seleccione un cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Patente --}}
                    <div>
                        <label for="modal_patente_id" class="block text-sm font-bold text-gray-700 mb-1">Patente *</label>
                        <select name="patente_id" id="modal_patente_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border" required>
                            <option value="">Seleccione una patente</option>
                            @foreach($patentes as $patente)
                                <option value="{{ $patente->id }}">{{ $patente->numero }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Aduana --}}
                    <div>
                        <label for="modal_aduana_id" class="block text-sm font-bold text-gray-700 mb-1">Aduana *</label>
                        <select name="aduana_id" id="modal_aduana_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border" required>
                            <option value="">Seleccione una aduana</option>
                            @foreach($aduanas as $aduana)
                                <option value="{{ $aduana->id }}">{{ $aduana->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Categoría y Clave Pedimento --}}
                    <div>
                        <label for="modal_categoria" class="block text-sm font-bold text-gray-700 mb-1">Categoría *</label>
                        <select id="modal_categoria" name="categoria" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border" required>
                            <option value="">Seleccionar Categoría</option>
                            <option value="Importacion">Importación</option>
                            <option value="Exportacion">Exportación</option>
                            <option value="Rectificaciones">Rectificaciones</option>
                        </select>
                    </div>

                    <div>
                        <label for="modal_clave_pedimento" class="block text-sm font-bold text-gray-700 mb-1">Clave Pedimento *</label>
                        <select id="modal_clave_pedimento" name="clave_pedimento" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border" required>
                            <option value="">Seleccionar Clave</option>
                            <option value="H1">H1</option>
                            <option value="A1">A1</option>
                            <option value="RT">RT</option>
                        </select>
                    </div>

                    {{-- Tipo de Expediente y Número de Pedimento --}}
                    <div>
                        <label for="modal_tipo_expediente" class="block text-sm font-bold text-gray-700 mb-1">Tipo de Expediente *</label>
                        <select name="tipo_expediente" id="modal_tipo_expediente" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border" required>
                            <option value="">Seleccione tipo</option>
                            <option value="Unico">Único</option>
                            <option value="Consolidado">Consolidado</option>
                        </select>
                    </div>

                    <div>
                        <label for="modal_numero_pedimento" class="block text-sm font-bold text-gray-700 mb-1">Número de Pedimento *</label>
                        <input type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border" id="modal_numero_pedimento" name="numero_pedimento" required>
                    </div>

                    {{-- Fecha Apertura y Fecha Cierre --}}
                    <div>
                        <label for="modal_fecha_apertura" class="block text-sm font-bold text-gray-700 mb-1">Fecha de Apertura *</label>
                        <input type="date" name="fecha_apertura" id="modal_fecha_apertura" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border text-gray-600" required>
                    </div>

                    <div>
                        <label for="modal_fecha_cierre" class="block text-sm font-bold text-gray-700 mb-1">Fecha de Cierre (Opcional)</label>
                        <input type="date" name="fecha_cierre" id="modal_fecha_cierre" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border text-gray-600">
                    </div>

                    {{-- Observaciones --}}
                    <div class="col-span-1 md:col-span-2">
                        <label for="modal_observaciones" class="block text-sm font-bold text-gray-700 mb-1">Observaciones</label>
                        <textarea name="observaciones" id="modal_observaciones" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm p-2 bg-white border"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 p-4 bg-gray-50 -mx-6 -mb-6 rounded-b-2xl border-t border-gray-100">
                    <button type="button" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none transition-colors" onclick="toggleNuevoPedimentoModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="inline-flex justify-center rounded-lg border border-transparent bg-purple-600 px-6 py-2 text-sm font-bold text-white shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors">
                        Guardar Pedimento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tailwind CSS & FontAwesome -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
    function toggleNuevoPedimentoModal() {
        const modal = document.getElementById('nuevoPedimentoModal');
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const toggleFiltersBtn = document.getElementById('toggleFilters');
        const filterCard = document.getElementById('filterCard');

        toggleFiltersBtn.addEventListener('click', function () {
            if (filterCard.style.display === 'none') {
                filterCard.style.display = 'block';
                // Trigger reflow
                void filterCard.offsetWidth;
                filterCard.style.maxHeight = '500px';
                filterCard.style.opacity = '1';
                toggleFiltersBtn.innerHTML = '<i class="fas fa-times mr-2"></i> Ocultar Filtros';
                toggleFiltersBtn.classList.add('bg-gray-100');
            } else {
                filterCard.style.maxHeight = '0px';
                filterCard.style.opacity = '0';
                setTimeout(() => {
                    filterCard.style.display = 'none';
                }, 300); // match transition duration
                toggleFiltersBtn.innerHTML = '<i class="fas fa-filter mr-2"></i> Mostrar Filtros';
                toggleFiltersBtn.classList.remove('bg-gray-100');
            }
        });
    });
</script>

<style>
    @keyframes fade-in-up {
        0% { opacity: 0; transform: translate(-50%, 10px); }
        100% { opacity: 1; transform: translate(-50%, 0); }
    }
    .animate-fade-in-up {
        animation: fade-in-up 0.3s ease-out forwards;
    }
    
    /* Personalización adicional para que los tooltips se vean premium */
    [title]:hover::after {
        content: attr(title);
        background: #1f2937;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        white-space: nowrap;
        font-size: 10px;
        z-index: 10;
    }
</style>
@endsection
