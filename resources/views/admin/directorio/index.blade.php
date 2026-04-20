@extends('layouts.app')

@section('title', 'Directorio de Notificaciones')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.adminconfig') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">
                            <i class="fas fa-cog mr-2"></i> Configuración
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                            <span class="text-sm font-medium text-gray-700">Directorio</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Directorio de <span class="text-indigo-600">Notificaciones</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Administra los contactos de los clientes y sus preferencias de notificación de aduana.</p>
        </div>
        <button type="button" onclick="openDirectorioModal()" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <i class="fas fa-user-plus mr-2"></i> Nuevo Contacto
        </button>
    </div>

    @include('partials.alerts')

    <!-- Tarjetas del Directorio -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($directorios as $contacto)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg hover:border-indigo-300 transition-all duration-300 flex flex-col h-full relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 text-indigo-50 opacity-40 group-hover:scale-110 transition-transform duration-500 pointer-events-none">
                <i class="fas fa-address-book text-9xl"></i>
            </div>
            
            <div class="flex items-center justify-between mb-4 relative z-10 w-full">
                <div class="flex items-center max-w-[80%]">
                    <div class="h-12 w-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl font-bold shadow-inner mr-4 shrink-0">
                        {{ strtoupper(substr($contacto->nombre ?? 'C', 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <h3 class="text-lg font-bold text-gray-900 leading-tight truncate" title="{{ $contacto->nombre }}">{{ $contacto->nombre }}</h3>
                        <span class="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full mt-1 inline-flex items-center border border-gray-200">
                            <i class="fas fa-building mr-1 text-gray-400"></i> <span class="truncate max-w-[120px]" title="{{ $contacto->cliente->nombre ?? 'Desconocido' }}">{{ $contacto->cliente->nombre ?? 'Desconocido' }}</span>
                        </span>
                    </div>
                </div>
                <div class="shrink-0 text-right">
                    @if($contacto->recibe_notificaciones)
                        <span class="flex items-center justify-center h-8 w-8 rounded-full bg-green-100 text-green-600 shadow-sm" title="Notificaciones Pendientes Mapeadas">
                            <i class="fas fa-bell"></i>
                        </span>
                    @else
                        <span class="flex items-center justify-center h-8 w-8 rounded-full bg-gray-100 text-gray-400 shadow-sm" title="Notificaciones Apagadas">
                            <i class="fas fa-bell-slash"></i>
                        </span>
                    @endif
                </div>
            </div>

            <div class="mb-6 space-y-3 flex-grow relative z-10 p-4 bg-gray-50 rounded-xl border border-gray-100">
                @if($contacto->puesto)
                <div class="flex items-start text-sm">
                    <i class="fas fa-briefcase text-indigo-400 mt-0.5 w-5 text-center"></i>
                    <span class="ml-2 text-gray-700 font-medium truncate" title="{{ $contacto->puesto }}">{{ $contacto->puesto }}</span>
                </div>
                @endif
                <div class="flex items-start text-sm">
                    <i class="fas fa-envelope text-indigo-400 mt-0.5 w-5 text-center"></i>
                    <span class="ml-2 text-gray-600 truncate" title="{{ $contacto->correo }}">
                        {{ $contacto->correo ?? 'Sin correo' }}
                    </span>
                </div>
                <div class="flex items-start text-sm">
                    <i class="fas fa-phone text-indigo-400 mt-0.5 w-5 text-center"></i>
                    <span class="ml-2 text-gray-600 truncate">
                        {{ $contacto->telefono ?? 'Sin teléfono' }}
                    </span>
                </div>
                <div class="flex items-start text-sm">
                    <i class="fab fa-whatsapp text-green-500 mt-0.5 w-5 text-center text-lg"></i>
                    <span class="ml-2 text-gray-600 truncate">
                        {{ $contacto->whatsapp ?? 'Sin WhatsApp' }}
                    </span>
                </div>
                <div class="mt-2 pt-2 border-t border-gray-200 flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-500 uppercase">Canal Preferido:</span>
                    @if($contacto->canal_preferido == 'whatsapp')
                        <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded border border-green-200">WhatsApp</span>
                    @elseif($contacto->canal_preferido == 'email')
                        <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">Correo</span>
                    @else
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">Ambos</span>
                    @endif
                </div>
            </div>

            <div class="pt-2 border-t border-gray-50 flex justify-between gap-2 mt-auto relative z-10">
                <button type="button" onclick="editDirectorioModal({{ $contacto->id }}, {{ json_encode($contacto) }})" class="flex-1 text-center text-amber-600 bg-amber-50 hover:bg-amber-500 hover:text-white border border-amber-200 py-2 rounded-lg shadow-sm font-bold text-xs transition-colors" title="Editar">
                    <i class="fas fa-edit mr-1"></i> Editar
                </button>
                <form action="{{ route('directorio.destroy', $contacto->id) }}" method="POST" class="flex-1" onsubmit="return confirm('¿Seguro que deseas eliminar este contacto?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full text-center text-red-600 bg-red-50 hover:bg-red-600 hover:text-white border border-red-200 py-2 rounded-lg shadow-sm font-bold text-xs transition-colors" title="Eliminar">
                        <i class="fas fa-trash mr-1"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center flex flex-col items-center justify-center">
            <i class="fas fa-address-book text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-700">No hay contactos en el directorio</h3>
            <p class="text-gray-500 mt-2 max-w-sm">Aún no has agregado a nadie al directorio de notificaciones.</p>
        </div>
        @endforelse
    </div>

    <!-- Modal Form -->
    <div id="directorioModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" aria-hidden="true" onclick="closeDirectorioModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="px-6 py-5 bg-indigo-600">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-black text-white" id="modal-title">
                            <i class="fas fa-address-book mr-2"></i> Configurar Contacto
                        </h3>
                        <button type="button" onclick="closeDirectorioModal()" class="text-white hover:text-indigo-200 focus:outline-none transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <form id="directorioForm" action="{{ route('directorio.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    
                    <div class="px-6 py-6 bg-white flex flex-col gap-5">
                        
                        <!-- Cliente ID -->
                        <div>
                            <label for="cliente_id" class="block text-sm font-bold text-gray-700 mb-1">Cliente <span class="text-red-500">*</span></label>
                            <select name="cliente_id" id="cliente_id" required class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50">
                                <option value="">Seleccione un cliente...</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->nombre ?? $cliente->nombre_empresa }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nombre -->
                            <div>
                                <label for="nombre" class="block text-sm font-bold text-gray-700 mb-1">Nombre Completo <span class="text-red-500">*</span></label>
                                <input type="text" name="nombre" id="nombre" required class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="Juan Pérez">
                            </div>
                            <!-- Puesto -->
                            <div>
                                <label for="puesto" class="block text-sm font-bold text-gray-700 mb-1">Puesto</label>
                                <input type="text" name="puesto" id="puesto" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="Ej. Gerente de Tráfico">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Correo -->
                            <div>
                                <label for="correo" class="block text-sm font-bold text-gray-700 mb-1">Correo Electrónico <span class="text-red-500">*</span></label>
                                <input type="email" name="correo" id="correo" required class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="juan@cliente.com">
                            </div>
                            <!-- Teléfono Oficina -->
                            <div>
                                <label for="telefono" class="block text-sm font-bold text-gray-700 mb-1">Teléfono Oficina</label>
                                <input type="text" name="telefono" id="telefono" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="(555) 123-4567">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- WhatsApp -->
                            <div>
                                <label for="whatsapp" class="block text-sm font-bold text-gray-700 mb-1">Número de WhatsApp</label>
                                <input type="text" name="whatsapp" id="whatsapp" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="(555) 987-6543">
                                <p class="text-xs text-gray-500 mt-1">Con código de área si es posible.</p>
                            </div>
                            <!-- Canal Preferido -->
                            <div>
                                <label for="canal_preferido" class="block text-sm font-bold text-gray-700 mb-1">Canal de Notificación Preferido</label>
                                <select name="canal_preferido" id="canal_preferido" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50">
                                    <option value="ambos">Ambos (Correo y WhatsApp)</option>
                                    <option value="email">Solo Correo</option>
                                    <option value="whatsapp">Solo WhatsApp</option>
                                </select>
                            </div>
                        </div>

                        <!-- Toggle Notificaciones Activas -->
                        <div class="flex items-center justify-between p-4 bg-indigo-50 border border-indigo-100 rounded-xl mt-2">
                            <div>
                                <h4 class="text-sm font-bold text-indigo-900">Activar Notificaciones Automáticas</h4>
                                <p class="text-xs text-indigo-700">El SOIA-Bot enviará notificaciones y despachos a este usuario.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="recibe_notificaciones" id="recibe_notificaciones" value="1" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none ring-4 ring-white rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 text-right sm:px-6">
                        <button type="button" onclick="closeDirectorioModal()" class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openDirectorioModal() {
        // Reset form for create
        document.getElementById('directorioForm').reset();
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('directorioForm').action = "{{ route('directorio.store') }}";
        document.getElementById('modal-title').innerHTML = '<i class="fas fa-address-book mr-2"></i> Nuevo Contacto';
        
        document.getElementById('directorioModal').classList.remove('hidden');
    }

    function editDirectorioModal(id, data) {
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('directorioForm').action = `/directorio/${id}`;
        document.getElementById('modal-title').innerHTML = '<i class="fas fa-edit mr-2"></i> Editar Contacto';
        
        // Fill data
        document.getElementById('cliente_id').value = data.cliente_id;
        document.getElementById('nombre').value = data.nombre || '';
        document.getElementById('puesto').value = data.puesto || '';
        document.getElementById('correo').value = data.correo || '';
        document.getElementById('telefono').value = data.telefono || '';
        document.getElementById('whatsapp').value = data.whatsapp || '';
        document.getElementById('canal_preferido').value = data.canal_preferido || 'ambos';
        document.getElementById('recibe_notificaciones').checked = (data.recibe_notificaciones == 1);
        
        document.getElementById('directorioModal').classList.remove('hidden');
    }

    function closeDirectorioModal() {
        document.getElementById('directorioModal').classList.add('hidden');
    }

    @if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        openDirectorioModal(); // It might open create randomly if failed edit, but good enough for simple validation
    });
    @endif
</script>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection
