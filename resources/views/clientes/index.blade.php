@extends('layouts.app')

@section('title', 'Directorio de Clientes')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.adminconfig') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-emerald-600 transition-colors">
                            <i class="fas fa-cog mr-2"></i> Configuración
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                            <span class="text-sm font-medium text-gray-700">Clientes</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Directorio de <span class="text-emerald-600">Clientes</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Administra las empresas que despachan por tu agencia.</p>
        </div>
            <button type="button" onclick="openClienteModal()" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                <i class="fas fa-plus mr-2"></i> Agregar Cliente
            </button>
    </div>

    <!-- Buscador -->
    <div class="mb-6">
        <form action="{{ route('clientes.index') }}" method="GET" class="flex max-w-lg">
            <div class="relative flex-grow focus-within:z-10">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="block w-full rounded-none rounded-l-xl border-gray-300 pl-10 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 border shadow-sm bg-white" placeholder="Buscar por nombre, RFC o Tax ID...">
            </div>
            <button type="submit" class="relative -ml-px inline-flex items-center space-x-2 rounded-r-xl border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 shadow-sm transition-colors">
                Buscar
            </button>
        </form>
    </div>

    @include('partials.alerts')

    <!-- Tarjetas de Clientes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($clientes as $cliente)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-lg hover:border-emerald-300 transition-all duration-300 flex flex-col h-full relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 text-emerald-50 opacity-40 group-hover:scale-110 transition-transform duration-500 pointer-events-none">
                <i class="fas fa-building text-9xl"></i>
            </div>
            
            <div class="flex items-center mb-4 relative z-10">
                <div class="h-12 w-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl font-bold shadow-inner mr-4">
                    {{ strtoupper(substr($cliente->nombre ?? $cliente->nombre_empresa ?? 'C', 0, 1)) }}
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 leading-tight">{{ $cliente->nombre ?? $cliente->nombre_empresa }}</h3>
                    <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full mt-1 inline-block border border-gray-200">
                        {{ $cliente->rfc ?? $cliente->tax_id ?? 'Sin RFC' }}
                    </span>
                </div>
            </div>

            <div class="mb-6 space-y-2 flex-grow relative z-10">
                <div class="flex items-start text-sm">
                    <i class="fas fa-envelope text-gray-400 mt-1 w-5 text-center"></i>
                    <span class="ml-2 text-gray-600 truncate" title="{{ $cliente->correo ?? $cliente->correo_contacto_principal ?? 'N/A' }}">
                        {{ $cliente->correo ?? $cliente->correo_contacto_principal ?? 'No registrado' }}
                    </span>
                </div>
                <div class="flex items-start text-sm">
                    <i class="fas fa-phone text-gray-400 mt-1 w-5 text-center"></i>
                    <span class="ml-2 text-gray-600 truncate">
                        {{ $cliente->telefono ?? $cliente->telefono_contacto ?? 'No registrado' }}
                    </span>
                </div>
                <div class="flex items-start text-sm">
                    <i class="fas fa-user-tie text-gray-400 mt-1 w-5 text-center"></i>
                    <span class="ml-2 text-gray-600 truncate">
                        {{ $cliente->persona_contacto ?? 'N/A' }}
                    </span>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-50 flex justify-between gap-2 mt-auto relative z-10">
                <a href="{{ route('clientes.show', $cliente->id) }}" class="flex-1 text-center text-emerald-600 bg-emerald-50 hover:bg-emerald-600 hover:text-white border border-emerald-200 py-2 rounded-lg shadow-sm font-bold text-xs transition-colors" title="Ver Detalles">
                    <i class="fas fa-eye mr-1"></i> Ver
                </a>
                <a href="{{ route('clientes.edit', $cliente->id) }}" class="flex-1 text-center text-amber-500 bg-amber-50 hover:bg-amber-500 hover:text-white border border-amber-200 py-2 rounded-lg shadow-sm font-bold text-xs transition-colors" title="Editar">
                    <i class="fas fa-edit mr-1"></i> Editar
                </a>
                <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="flex-1" onsubmit="return confirm('¿Seguro que deseas eliminar a este cliente?');">
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
            <i class="fas fa-users-slash text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-700">No se encontraron clientes</h3>
            <p class="text-gray-500 mt-2 max-w-sm">No hay clientes dados de alta o tu búsqueda no arrojó resultados.</p>
        </div>
        @endforelse
    </div>

    <!-- Paginación -->
    @if($clientes instanceof \Illuminate\Pagination\LengthAwarePaginator && $clientes->hasPages())
    <div class="mt-8">
        {{ $clientes->links() }}
    </div>
    @endif

    <!-- Modal Nuevo Cliente -->
    <div id="clienteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Fondo oscuro -->
            <div class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" aria-hidden="true" onclick="closeClienteModal()"></div>

            <!-- Truco para centrar verticalmente -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Panel del modal -->
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="px-6 py-5 bg-emerald-600">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-black text-white" id="modal-title">
                            <i class="fas fa-building mr-2"></i> Nuevo Cliente
                        </h3>
                        <button type="button" onclick="closeClienteModal()" class="text-white hover:text-emerald-200 focus:outline-none transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <form action="{{ route('clientes.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-6 bg-white flex flex-col gap-4">
                        
                        @if($errors->any())
                            <div class="mb-2 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-bold text-red-700">Por favor corrige los siguientes errores:</p>
                                        <ul class="mt-1 text-sm text-red-600 list-disc list-inside">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-sm font-bold text-gray-700 mb-1">Nombre de la Empresa <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="nombre" required value="{{ old('nombre') }}" class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="Ej. Comercializadora del Norte S.A.">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- RFC -->
                            <div>
                                <label for="rfc" class="block text-sm font-bold text-gray-700 mb-1">RFC</label>
                                <input type="text" name="rfc" id="rfc" maxlength="13" value="{{ old('rfc') }}" class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50 uppercase" placeholder="Ej. CNO123456789">
                            </div>
                            <!-- Tax ID -->
                            <div>
                                <label for="tax_id" class="block text-sm font-bold text-gray-700 mb-1">Tax ID</label>
                                <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id') }}" class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50 uppercase" placeholder="Ej. 12-3456789">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Correo -->
                            <div>
                                <label for="correo" class="block text-sm font-bold text-gray-700 mb-1">Correo Electrónico <span class="text-red-500">*</span></label>
                                <input type="email" name="correo" id="correo" value="{{ old('correo') }}" required class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="ejemplo@correo.com">
                            </div>
                            <!-- Teléfono -->
                            <div>
                                <label for="telefono" class="block text-sm font-bold text-gray-700 mb-1">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="(555) 123-4567">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Dirección -->
                            <div class="col-span-1 md:col-span-2">
                                <label for="direccion" class="block text-sm font-bold text-gray-700 mb-1">Dirección Fiscal / Operativa</label>
                                <textarea name="direccion" id="direccion" rows="2" class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50 resize-none" placeholder="Av. Principal #123...">{{ old('direccion') }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 text-right sm:px-6">
                        <button type="button" onclick="closeClienteModal()" class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                            Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openClienteModal() {
        document.getElementById('clienteModal').classList.remove('hidden');
    }

    function closeClienteModal() {
        document.getElementById('clienteModal').classList.add('hidden');
    }

    @if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        openClienteModal();
    });
    @endif
</script>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection