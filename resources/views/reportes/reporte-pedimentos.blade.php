@extends('layouts.app')

@section('title', 'Reporte de Pedimentos')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('reportes.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                            <i class="fas fa-chart-bar mr-2"></i> Reportes
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
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Reporte de <span class="text-blue-600">Pedimentos</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Directorio completo de pedimentos y su estado de cumplimiento.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors" id="toggleFilters">
                <i class="fas fa-filter mr-2"></i> Mostrar Filtros
            </button>
            <a href="{{ route('reportes.pedimentos.pdf', request()->query()) }}" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-bold text-red-600 shadow-sm hover:bg-red-100 transition-colors">
                <i class="fas fa-file-pdf mr-2"></i> Exportar PDF
            </a>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-bold text-blue-500 mb-1 uppercase tracking-wider">Total Pedimentos</p>
                <h3 class="text-3xl font-black text-gray-900">{{ $totalPedimentos }}</h3>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 text-2xl">
                <i class="fas fa-file-invoice"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-green-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-bold text-green-500 mb-1 uppercase tracking-wider">Cumplidos</p>
                <h3 class="text-3xl font-black text-gray-900">{{ $cumplidos }}</h3>
            </div>
            <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center text-green-500 text-2xl">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-amber-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-bold text-amber-500 mb-1 uppercase tracking-wider">Pendientes por Cerrar</p>
                <h3 class="text-3xl font-black text-gray-900">{{ $pendientes }}</h3>
            </div>
            <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-500 text-2xl">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-bold text-red-500 mb-1 uppercase tracking-wider">Docs Faltantes</p>
                <h3 class="text-3xl font-black text-gray-900">{{ $docsFaltantes }}</h3>
            </div>
            <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center text-red-500 text-2xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 mb-6 transition-all duration-300 origin-top overflow-hidden" id="filterCard" style="display:none; max-height: 0px; opacity: 0;">
        <div class="p-6 bg-blue-50/30">
            <h4 class="text-blue-800 font-bold mb-4 flex items-center grid-span-full"><i class="fas fa-filter mr-2"></i> Filtros de Búsqueda</h4>
            <form method="GET" action="{{ route('reportes.pedimentos') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label for="numero_pedimento" class="block text-xs font-bold text-gray-700 uppercase mb-1">Pedimento</label>
                        <input type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white border" id="numero_pedimento" name="numero_pedimento" value="{{ $numeroPedimento ?? '' }}" placeholder="Ej. 1234567">
                    </div>

                    <div>
                        <label for="estado" class="block text-xs font-bold text-gray-700 uppercase mb-1">Estado</label>
                        <select class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white border" id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="En proceso" @if(($estado ?? '') == 'En proceso') selected @endif>En proceso</option>
                            <option value="Abierto" @if(($estado ?? '') == 'Abierto') selected @endif>Abierto</option>
                            <option value="Cerrado" @if(($estado ?? '') == 'Cerrado') selected @endif>Cerrado</option>
                            <option value="Cancelado" @if(($estado ?? '') == 'Cancelado') selected @endif>Cancelado</option>
                        </select>
                    </div>

                    <div>
                        <label for="categoria" class="block text-xs font-bold text-gray-700 uppercase mb-1">Categoría</label>
                        <select class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white border" id="categoria" name="categoria">
                            <option value="">Todas</option>
                            <option value="Importacion" @if(($categoria ?? '') == 'Importacion') selected @endif>Importación</option>
                            <option value="Exportacion" @if(($categoria ?? '') == 'Exportacion') selected @endif>Exportación</option>
                            <option value="Rectificaciones" @if(($categoria ?? '') == 'Rectificaciones') selected @endif>Rectificaciones</option>
                        </select>
                    </div>

                    <div>
                        <label for="cliente_id" class="block text-xs font-bold text-gray-700 uppercase mb-1">Cliente</label>
                        <select class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white border" id="cliente_id" name="cliente_id">
                            <option value="">Todos</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" @if(($clienteId ?? '') == $cliente->id) selected @endif>
                                    {{ $cliente->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="fecha_desde" class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha Desde</label>
                        <input type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white border text-gray-600 cursor-pointer" id="fecha_desde" name="desde" value="{{ $desde ?? '' }}">
                    </div>

                    <div>
                        <label for="fecha_hasta" class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha Hasta</label>
                        <input type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white border text-gray-600 cursor-pointer" id="fecha_hasta" name="hasta" value="{{ $hasta ?? '' }}">
                    </div>

                    <div class="col-span-1 md:col-span-2 flex items-end justify-end gap-2 mt-2 lg:mt-0">
                        <a href="{{ route('reportes.pedimentos') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none transition-colors">
                            Limpiar
                        </a>
                        <button type="submit" class="inline-flex justify-center rounded-lg border border-transparent bg-blue-600 px-6 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-search mr-2"></i> Aplicar Filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('partials.alerts')

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex-1">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Pedimento</th>
                        <th class="px-6 py-4">Cliente</th>
                        <th class="px-6 py-4">Categoría</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-center">Docs Faltantes</th>
                        <th class="px-6 py-4">Fecha Apertura</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                @forelse($pedimentos as $pedimento)
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-black text-blue-600">#{{ $pedimento->numero_pedimento }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $pedimento->cliente?->nombre ?? 'N/D' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200">
                                {{ $pedimento->categoria === 'Importacion' ? 'Importación' : ($pedimento->categoria === 'Exportacion' ? 'Exportación' : $pedimento->categoria) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($pedimento->estado == 'Cerrado')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></div> Cerrado
                                </span>
                            @elseif($pedimento->estado == 'Cancelado')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></div> Cancelado
                                </span>
                            @elseif($pedimento->estado == 'Abierto')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-sky-500 mr-1.5"></div> Abierto
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></div> En proceso
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($pedimento->cumplimiento_completo)
                                <div class="flex flex-col items-center gap-1 text-emerald-600 font-bold">
                                    <i class="fas fa-check-circle text-lg"></i>
                                    <span class="text-[9px] uppercase tracking-tighter">Completo</span>
                                </div>
                            @else
                                @php $pendientesDocs = $pedimento->documentos_pendientes; @endphp
                                <div class="flex flex-col items-center gap-1 text-amber-500 font-bold group relative cursor-help">
                                    <i class="fas fa-exclamation-circle text-lg animate-pulse"></i>
                                    <span class="text-[9px] uppercase tracking-tighter">{{ count($pendientesDocs) }} pendientes</span>

                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 hidden group-hover:block w-56 p-3 bg-gray-900/95 backdrop-blur-sm text-white text-[10px] rounded-2xl shadow-2xl z-50 border border-gray-700">
                                        <div class="flex items-center gap-2 font-black border-b border-gray-700 pb-2 mb-2 text-amber-400 uppercase tracking-widest text-[9px]">
                                            <i class="fas fa-clipboard-list"></i> Documentos faltantes
                                        </div>
                                        <ul class="space-y-1.5 font-medium">
                                            @foreach(array_slice($pendientesDocs, 0, 8) as $doc)
                                                <li class="flex items-center gap-2">
                                                    <div class="w-1 h-1 rounded-full bg-amber-500"></div>
                                                    <span class="truncate">{{ $doc }}</span>
                                                </li>
                                            @endforeach
                                            @if(count($pendientesDocs) > 8)
                                                <li class="pl-3 text-gray-400 italic">Y {{ count($pendientesDocs) - 8 }} más...</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $pedimento->fecha_apertura?->format('d/m/Y') ?? 'N/D' }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <button onclick="openPedimentoModal({{ $pedimento->id }}, '{{ addslashes($pedimento->numero_pedimento) }}', '{{ addslashes($pedimento->cliente?->nombre ?? 'N/D') }}', '{{ addslashes($pedimento->patente?->numero ?? 'N/D') }}', '{{ addslashes($pedimento->aduana?->nombre ?? 'N/D') }}', '{{ $pedimento->categoria }}', '{{ $pedimento->estado }}', '{{ $pedimento->fecha_apertura?->format('d/m/Y') ?? 'N/D' }}', '{{ $pedimento->fecha_cierre?->format('d/m/Y') ?? 'N/D' }}', {{ $pedimento->cumplimiento_completo ? 'true' : 'false' }})" class="text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white border border-blue-200 h-8 w-8 flex items-center justify-center rounded-lg shadow-sm transition transform hover:scale-105" title="Ver detalle">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                                <a href="{{ route('expedientes.show', $pedimento) }}" class="text-purple-600 bg-purple-50 hover:bg-purple-600 hover:text-white border border-purple-200 h-8 w-8 flex items-center justify-center rounded-lg shadow-sm transition transform hover:scale-105" title="Ir a expediente">
                                    <i class="fas fa-external-link-alt text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-file-invoice text-5xl mb-3 block opacity-50 text-blue-300"></i>
                            <h3 class="font-black text-xl text-gray-700 mb-1">Sin Pedimentos</h3>
                            <p class="font-medium text-gray-500">No hay pedimentos registrados o tu búsqueda no arrojó resultados.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($pedimentos->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $pedimentos->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal de Detalle --}}
<div id="pedimentoModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="closePedimentoModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
            <div class="flex justify-between items-center mb-5 border-b pb-4">
                <h3 class="text-xl font-bold leading-6 text-gray-900" id="modal-title">
                    <i class="fas fa-file-invoice text-blue-600 mr-2"></i> Detalle del Pedimento
                </h3>
                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" onclick="closePedimentoModal()">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Pedimento</p>
                        <p class="text-lg font-black text-blue-600" id="modal-numero"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Cliente</p>
                        <p class="text-sm font-bold text-gray-900" id="modal-cliente"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Patente</p>
                        <p class="text-sm font-bold text-gray-900" id="modal-patente"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Aduana</p>
                        <p class="text-sm font-bold text-gray-900" id="modal-aduana"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Categoría</p>
                        <p class="text-sm font-bold text-gray-900" id="modal-categoria"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Estado</p>
                        <p class="text-sm font-bold" id="modal-estado"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Fecha Apertura</p>
                        <p class="text-sm font-bold text-gray-900" id="modal-apertura"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Fecha Cierre</p>
                        <p class="text-sm font-bold text-gray-900" id="modal-cierre"></p>
                    </div>
                </div>

                <div id="modal-docs-section" class="border-t pt-4">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-2">Documentos Faltantes</p>
                    <ul id="modal-docs-list" class="space-y-1"></ul>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button onclick="closePedimentoModal()" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                        Cerrar
                    </button>
                    <a id="modal-expediente-link" href="#" class="inline-flex justify-center rounded-lg border border-transparent bg-purple-600 px-6 py-2 text-sm font-bold text-white shadow-sm hover:bg-purple-700 transition-colors">
                        <i class="fas fa-external-link-alt mr-2"></i> Ir a Expediente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.tailwindcss.com"></script>
<script>
    // Toggle filters
    document.addEventListener('DOMContentLoaded', function () {
        const toggleFiltersBtn = document.getElementById('toggleFilters');
        const filterCard = document.getElementById('filterCard');

        toggleFiltersBtn.addEventListener('click', function () {
            if (filterCard.style.display === 'none') {
                filterCard.style.display = 'block';
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
                }, 300);
                toggleFiltersBtn.innerHTML = '<i class="fas fa-filter mr-2"></i> Mostrar Filtros';
                toggleFiltersBtn.classList.remove('bg-gray-100');
            }
        });
    });

    // Modal functions
    function openPedimentoModal(id, numero, cliente, patente, aduana, categoria, estado, apertura, cierre, completo) {
        document.getElementById('modal-numero').textContent = '#' + numero;
        document.getElementById('modal-cliente').textContent = cliente;
        document.getElementById('modal-patente').textContent = patente;
        document.getElementById('modal-aduana').textContent = aduana;

        const catLabels = {'Importacion': 'Importación', 'Exportacion': 'Exportación'};
        document.getElementById('modal-categoria').textContent = catLabels[categoria] || categoria;

        const estadoEl = document.getElementById('modal-estado');
        const estadoColors = {
            'Cerrado': 'text-green-700',
            'Cancelado': 'text-red-700',
            'Abierto': 'text-sky-700',
            'En proceso': 'text-amber-700'
        };
        estadoEl.textContent = estado;
        estadoEl.className = 'text-sm font-bold ' + (estadoColors[estado] || 'text-gray-700');

        document.getElementById('modal-apertura').textContent = apertura;
        document.getElementById('modal-cierre').textContent = cierre !== 'N/D' ? cierre : 'Sin cerrar';

        const docsSection = document.getElementById('modal-docs-section');
        const docsList = document.getElementById('modal-docs-list');
        docsList.innerHTML = '';

        if (completo) {
            docsSection.style.display = 'none';
        } else {
            docsSection.style.display = 'block';
            // Fetch docs via AJAX
            fetch('/expedientes/' + id + '/documentos-pendientes')
                .then(r => r.json())
                .then(docs => {
                    docs.forEach(doc => {
                        const li = document.createElement('li');
                        li.className = 'flex items-center gap-2 text-sm text-amber-700';
                        li.innerHTML = '<i class="fas fa-exclamation-circle text-amber-500"></i> ' + doc;
                        docsList.appendChild(li);
                    });
                })
                .catch(() => {
                    docsList.innerHTML = '<li class="text-sm text-gray-500">No se pudieron cargar los documentos pendientes</li>';
                });
        }

        document.getElementById('modal-expediente-link').href = '/expedientes/' + id;
        document.getElementById('pedimentoModal').classList.remove('hidden');
    }

    function closePedimentoModal() {
        document.getElementById('pedimentoModal').classList.add('hidden');
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePedimentoModal();
    });
</script>
@endsection
