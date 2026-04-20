@extends('layouts.app')

@section('title', 'Directorio de Bodegas')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.adminconfig') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-amber-600 transition-colors">
                            <i class="fas fa-cog mr-2"></i> Configuración
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                            <span class="text-sm font-medium text-gray-700">Bodegas</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Directorio de <span class="text-amber-600">Bodegas</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Gestiona los centros de distribución y bodegas.</p>
        </div>
        <div class="flex items-center">
            <button type="button" onclick="openBodegaModal()" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-amber-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-amber-700 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                <i class="fas fa-plus mr-2"></i> Nueva Bodega
            </button>
        </div>
    </div>

    @include('partials.alerts')

    <!-- Content Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex-1">
        @if ($bodegas->count())
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Nombre de la Bodega</th>
                        <th class="px-6 py-4">Tax ID / Identificador</th>
                        <th class="px-6 py-4">Contacto Principal</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    @foreach ($bodegas as $bodega)
                    <tr class="hover:bg-amber-50/30 transition-colors">
                        <td class="px-6 py-4 font-bold text-gray-900">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-lg mr-3 shadow-inner">
                                    <i class="fas fa-warehouse text-sm"></i>
                                </div>
                                {{ $bodega->nombre ?? $bodega->nombre_bodega ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-600">
                            <span class="bg-gray-100 px-3 py-1 rounded-full text-xs font-bold border border-gray-200">
                                {{ $bodega->tax_id ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-medium">
                            <i class="fas fa-user-circle text-gray-400 mr-1"></i>
                            {{ $bodega->contacto ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('bodegas.show', $bodega) }}" class="text-amber-600 bg-amber-50 hover:bg-amber-600 hover:text-white border border-amber-200 p-2 rounded-lg shadow-sm transition transform hover:scale-105" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('bodegas.edit', $bodega) }}" class="text-orange-500 bg-orange-50 hover:bg-orange-500 hover:text-white border border-orange-200 p-2 rounded-lg shadow-sm transition transform hover:scale-105" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('bodegas.destroy', $bodega) }}" method="POST" class="inline" onsubmit="return confirm('¿Confirma que desea eliminar esta bodega?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 bg-red-50 hover:bg-red-600 hover:text-white border border-red-200 p-2 rounded-lg shadow-sm transition transform hover:scale-105" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if(method_exists($bodegas, 'links'))
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $bodegas->links() }}
        </div>
        @endif

        @else
        <!-- Empty State -->
        <div class="p-12 text-center text-gray-400">
            <i class="fas fa-warehouse text-7xl mb-4 block opacity-50 text-amber-300"></i>
            <h3 class="font-black text-2xl text-gray-700 mb-2">Sin Bodegas</h3>
            <p class="font-medium text-gray-500 max-w-sm mx-auto">No tienes bodegas registradas actualmente. Configura las bodegas donde opera tu agencia.</p>
            <button type="button" onclick="openBodegaModal()" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-amber-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-amber-700 mt-6 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                <i class="fas fa-plus mr-2"></i> Nueva Bodega
            </button>
        </div>
        @endif
    </div>

    <!-- Modal Nueva Bodega -->
    <div id="bodegaModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Fondo oscuro -->
            <div class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" aria-hidden="true" onclick="closeBodegaModal()"></div>

            <!-- Truco para centrar verticalmente -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Panel del modal -->
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
                <div class="px-6 py-5 bg-amber-600">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-black text-white" id="modal-title">
                            <i class="fas fa-warehouse mr-2"></i> Nueva Bodega
                        </h3>
                        <button type="button" onclick="closeBodegaModal()" class="text-white hover:text-amber-200 focus:outline-none transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <form action="{{ route('bodegas.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-6 bg-white flex flex-col gap-4">
                        
                        @if($errors->any())
                            <div class="mb-2 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-bold text-red-700">Revisa los siguientes campos:</p>
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
                            <label for="nombre" class="block text-sm font-bold text-gray-700 mb-1">Nombre de la Bodega <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="nombre" required value="{{ old('nombre') }}" class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="Ej. Centro de Distribución Norte">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Tax ID -->
                            <div>
                                <label for="tax_id" class="block text-sm font-bold text-gray-700 mb-1">Tax ID / Identificador</label>
                                <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id') }}" class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50 uppercase" placeholder="Ej. 12-3456789">
                            </div>

                            <!-- Contacto -->
                            <div>
                                <label for="contacto" class="block text-sm font-bold text-gray-700 mb-1">Contacto Principal</label>
                                <input type="text" name="contacto" id="contacto" value="{{ old('contacto') }}" class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="Ej. Juan Pérez">
                            </div>
                        </div>

                        <!-- Domicilio -->
                        <div>
                            <label for="domicilio" class="block text-sm font-bold text-gray-700 mb-1">Domicilio</label>
                            <textarea name="domicilio" id="domicilio" rows="2" class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50 resize-none" placeholder="Av. Principal #123...">{{ old('domicilio') }}</textarea>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 text-right sm:px-6">
                        <button type="button" onclick="closeBodegaModal()" class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-amber-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-colors">
                            Guardar Bodega
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
</div>
<script>
    function openBodegaModal() {
        document.getElementById('bodegaModal').classList.remove('hidden');
    }

    function closeBodegaModal() {
        document.getElementById('bodegaModal').classList.add('hidden');
    }

    @if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        openBodegaModal();
    });
    @endif
</script>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection