@extends('layouts.app')

@section('title', 'Catálogo de Patentes')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.adminconfig') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                            <i class="fas fa-cog mr-2"></i> Configuración
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                            <span class="text-sm font-medium text-gray-700">Patentes</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Catálogo de <span class="text-blue-600">Patentes</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Administra las patentes de tus Agentes Aduanales operativos.</p>
        </div>
        <div class="flex items-center">
            <button type="button" onclick="openPatenteModal()" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fas fa-plus mr-2"></i> Nueva Patente
            </button>
        </div>
    </div>

    <!-- @include('partials.alerts') -->

    <!-- Content Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex-1">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">N° Patente</th>
                        <th class="px-6 py-4">Agente Aduanal</th>
                        <th class="px-6 py-4">RFC</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    @forelse ($patentes as $patente)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4 font-black text-blue-600 text-lg">
                            {{ $patente->numero_patente ?? $patente->numero ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-700">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg mr-3 shadow-inner">
                                    <i class="fas fa-user-tie text-sm"></i>
                                </div>
                                {{ $patente->nombre ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-medium text-xs">
                            @if($patente->rfc)
                                <div class="mb-1"><i class="fas fa-id-card text-gray-400 mr-1 w-4"></i> {{ $patente->rfc }}</div>
                            @else
                                <span class="text-gray-400 italic">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('patentes.show', $patente) }}" class="text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white border border-blue-200 p-2 rounded-lg shadow-sm transition transform hover:scale-105" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('patentes.edit', $patente) }}" class="text-amber-500 bg-amber-50 hover:bg-amber-500 hover:text-white border border-amber-200 p-2 rounded-lg shadow-sm transition transform hover:scale-105" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('patentes.destroy', $patente) }}" method="POST" class="inline" onsubmit="return confirm('¿Confirma que desea eliminar esta patente?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 bg-red-50 hover:bg-red-600 hover:text-white border border-red-200 p-2 rounded-lg shadow-sm transition transform hover:scale-105" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-stamp text-5xl mb-3 block opacity-50"></i>
                            <p class="font-medium text-lg text-gray-500">No hay patentes registradas.</p>
                            <p class="text-sm mt-1">Comienza agregando los Agentes Aduanales con los que trabajas.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($patentes instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $patentes->links() }}
        </div>
        @endif
    </div>
    </div>

    <!-- Modal Nueva Patente -->
    <div id="patenteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Fondo oscuro -->
            <div class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" aria-hidden="true" onclick="closePatenteModal()"></div>

            <!-- Truco para centrar verticalmente -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Panel del modal -->
            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-6 py-5 bg-blue-600">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-black text-white" id="modal-title">
                            <i class="fas fa-stamp mr-2"></i> Nueva Patente
                        </h3>
                        <button type="button" onclick="closePatenteModal()" class="text-white hover:text-blue-200 focus:outline-none transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <form action="{{ route('patentes.store') }}" method="POST">
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

                        <!-- Número -->
                        <div>
                            <label for="numero" class="block text-sm font-bold text-gray-700 mb-1">N° de Patente <span class="text-red-500">*</span></label>
                            <input type="text" name="numero" id="numero" required value="{{ old('numero') }}" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50 uppercase" placeholder="Ej. 1234">
                        </div>

                        <!-- Agente Aduanal -->
                        <div>
                            <label for="nombre" class="block text-sm font-bold text-gray-700 mb-1">Nombre del Agente Aduanal <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="nombre" required value="{{ old('nombre') }}" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="Ej. Juan Pérez">
                        </div>

                        <!-- RFC -->
                        <div>
                            <label for="rfc" class="block text-sm font-bold text-gray-700 mb-1">RFC</label>
                            <input type="text" name="rfc" id="rfc" maxlength="13" value="{{ old('rfc') }}" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50 uppercase" placeholder="Ej. ABCD123456789">
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 text-right sm:px-6">
                        <button type="button" onclick="closePatenteModal()" class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            Guardar Patente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openPatenteModal() {
        document.getElementById('patenteModal').classList.remove('hidden');
    }

    function closePatenteModal() {
        document.getElementById('patenteModal').classList.add('hidden');
    }

    @if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        openPatenteModal();
    });
    @endif
</script>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection