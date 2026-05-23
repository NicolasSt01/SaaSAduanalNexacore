@extends('layouts.app')

@section('title', 'Mis Expedientes')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-gray-800">Mis <span class="text-indigo-600">Expedientes</span></h1>
            <p class="text-sm text-gray-500 mt-1 font-medium">{{ $cliente->nombre ?? 'Cliente' }}</p>
        </div>
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl px-6 py-4 shadow-sm text-white">
            <p class="text-3xl font-black">{{ $expedientes->total() }}</p>
            <p class="text-xs font-bold uppercase tracking-wider text-indigo-100">Total de Expedientes</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-6">
        <form method="GET" action="{{ route('expedientes.indexcliente') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="numero_pedimento" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">No. Pedimento</label>
                <input type="text" id="numero_pedimento" name="numero_pedimento"
                    placeholder="Buscar por pedimento" value="{{ request('numero_pedimento') }}"
                    class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
            </div>
            <div>
                <label for="estado" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Estado</label>
                <select id="estado" name="estado"
                    class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                    <option value="">Todos los estados</option>
                    <option value="En proceso" {{ request('estado') == 'En proceso' ? 'selected' : '' }}>En proceso</option>
                    <option value="Abierto" {{ request('estado') == 'Abierto' ? 'selected' : '' }}>Abierto</option>
                    <option value="Cerrado" {{ request('estado') == 'Cerrado' ? 'selected' : '' }}>Cerrado</option>
                </select>
            </div>
            <div>
                <label for="fecha_desde" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Fecha Desde</label>
                <input type="date" id="fecha_desde" name="fecha_desde"
                    value="{{ request('fecha_desde') }}"
                    class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all shadow-sm">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </form>
    </div>

    {{-- Grid de expedientes --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($expedientes as $expediente)
            @php
                $estadoBadgeClass = match($expediente->estado) {
                    'Cerrado' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'En proceso' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'Cancelado' => 'bg-rose-50 text-rose-700 border-rose-200',
                    default => 'bg-blue-50 text-blue-700 border-blue-200',
                };
            @endphp
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:border-indigo-300 transition-all duration-300 transform hover:-translate-y-1 flex flex-col h-full border-l-4 border-l-indigo-500">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h5 class="text-base font-black text-gray-800">
                            <i class="fas fa-file-alt text-indigo-600 mr-2"></i>
                            {{ $expediente->numero_pedimento ?? 'Sin Pedimento' }}
                        </h5>
                        <p class="text-xs text-gray-400 mt-1">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            {{ $expediente->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest border {{ $estadoBadgeClass }}">
                        {{ $expediente->estado ?? 'Sin estado' }}
                    </span>
                </div>

                <div class="space-y-2 flex-grow">
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400 font-medium">Aduana:</span>
                        <span class="text-xs font-bold text-gray-700">
                            {{ $expediente->aduana->nombre ?? 'No especificada' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400 font-medium">Categoría:</span>
                        <span class="text-xs font-bold text-gray-700">
                            {{ $expediente->categoria ?? 'No especificada' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400 font-medium">Documentos:</span>
                        <span class="text-xs font-bold text-gray-700">
                            {{ $expediente->documentos->count() ?? 0 }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <button type="button" onclick="openExpedienteModal('{{ $expediente->id }}')"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all shadow-sm">
                        <i class="fas fa-folder-open"></i> Ver Documentos
                    </button>
                </div>
            </div>

            {{-- Modal de detalles del expediente --}}
            <div id="expedienteModal{{ $expediente->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" aria-hidden="true" onclick="closeExpedienteModal('{{ $expediente->id }}')"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
                        <div class="px-6 py-5 bg-gradient-to-r from-indigo-500 to-purple-600">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xl font-black text-white">
                                    <i class="fas fa-file-alt mr-2"></i>
                                    Expediente: {{ $expediente->numero_pedimento ?? 'Sin Pedimento' }}
                                </h3>
                                <button type="button" onclick="closeExpedienteModal('{{ $expediente->id }}')" class="text-white hover:text-indigo-200 focus:outline-none transition-colors">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            {{-- Información general --}}
                            <div class="bg-gray-50 rounded-2xl border border-gray-100 p-6 mb-6">
                                <h6 class="text-sm font-black text-indigo-600 mb-4">
                                    <i class="fas fa-info-circle mr-2"></i>Información General
                                </h6>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Pedimento</label>
                                        <p class="text-sm font-bold text-gray-800">{{ $expediente->numero_pedimento ?? 'Sin Pedimento' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Aduana</label>
                                        <p class="text-sm font-bold text-gray-800">{{ $expediente->aduana->nombre ?? 'No especificada' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Categoría</label>
                                        <p class="text-sm font-bold text-gray-800">{{ $expediente->categoria ?? 'No especificada' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Fecha de Creación</label>
                                        <p class="text-sm font-bold text-gray-800">{{ $expediente->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Estado</label>
                                        <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest border {{ $estadoBadgeClass }}">
                                            {{ $expediente->estado ?? 'Sin estado' }}
                                        </span>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Total Documentos</label>
                                        <p class="text-sm font-bold text-gray-800">{{ $expediente->documentos->count() }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Documentos --}}
                            <h6 class="text-sm font-black text-indigo-600 mb-4">
                                <i class="fas fa-file-download mr-2"></i>Documentos del Expediente
                            </h6>

                            @if($expediente->documentos->isNotEmpty())
                                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                                    @foreach($expediente->documentos->groupBy('tipo_documento') as $tipo => $documentos)
                                        <div class="mb-4">
                                            <h6 class="text-sm font-black text-indigo-600 mb-3 pb-2 border-b border-gray-100">
                                                <i class="fas fa-folder mr-2"></i>
                                                {{ ucfirst($tipo) }} ({{ $documentos->count() }})
                                            </h6>
                                            @foreach($documentos as $documento)
                                                <div class="flex items-center justify-between p-3 border border-gray-100 rounded-xl mb-2 hover:bg-indigo-50/30 transition-colors">
                                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                                        <i class="fas fa-file-pdf text-rose-500 text-xl"></i>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-bold text-gray-800 truncate">
                                                                {{ $documento->nombre_documento ?? 'Documento sin nombre' }}
                                                            </p>
                                                            <p class="text-xs text-gray-400">
                                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                                {{ $documento->created_at->format('d/m/Y H:i') }}
                                                                @if($documento->descripcion)
                                                                    • {{ $documento->descripcion }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-2 ml-4 flex-shrink-0">
                                                        @if($documento->archivo_url)
                                                            <a href="{{ Storage::url($documento->archivo_url) }}"
                                                                class="inline-flex items-center gap-1 px-3 py-1.5 border border-blue-200 bg-blue-50 text-blue-700 rounded-lg font-bold text-xs hover:bg-blue-100 transition-all"
                                                                target="_blank" title="Ver documento">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="{{ Storage::url($documento->archivo_url) }}"
                                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white rounded-lg font-bold text-xs hover:bg-indigo-700 transition-all shadow-sm"
                                                                download title="Descargar documento">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        @else
                                                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest border bg-amber-50 text-amber-700 border-amber-200">
                                                                Sin archivo
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-xl shadow-sm">
                                    <div class="flex">
                                        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                                        <div class="ml-3">
                                            <p class="text-sm text-blue-700 font-bold">No hay documentos disponibles para este expediente.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
                            <button type="button" onclick="closeExpedienteModal('{{ $expediente->id }}')"
                                class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl shadow-sm border border-gray-100 p-12 text-center flex flex-col items-center justify-center">
                <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-700">No hay expedientes disponibles</h3>
                <p class="text-gray-500 mt-2">Aún no tienes expedientes registrados en el sistema.</p>
            </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    @if($expedientes->hasPages())
        <div class="mt-8">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-gray-500">
                        Mostrando
                        <span class="font-bold text-gray-700">{{ $expedientes->firstItem() }}</span>
                        a
                        <span class="font-bold text-gray-700">{{ $expedientes->lastItem() }}</span>
                        de
                        <span class="font-bold text-gray-700">{{ $expedientes->total() }}</span>
                        expedientes
                    </p>
                    <div>
                        {{ $expedientes->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function openExpedienteModal(id) {
        document.getElementById('expedienteModal' + id).classList.remove('hidden');
    }

    function closeExpedienteModal(id) {
        document.getElementById('expedienteModal' + id).classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.fixed.inset-0.z-50').forEach(function(modal) {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            });
        }
    });
</script>
@endsection
