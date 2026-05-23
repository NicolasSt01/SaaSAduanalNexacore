@extends('layouts.app')

@section('title', 'Directorio de Aduanas')

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
                            <span class="text-sm font-medium text-gray-700">Aduanas</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Catálogo de <span class="text-indigo-600">Aduanas</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Administra las aduanas y puertos operativos.</p>
        </div>
        <div class="flex items-center">
            <button type="button" onclick="openAduanaModal()" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <i class="fas fa-plus mr-2"></i> Nueva Aduana
            </button>
        </div>
    </div>

    @include('partials.alerts')

    <!-- Content Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex-1">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Clave</th>
                        <th class="px-6 py-4">Nombre de la Aduana</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    @forelse ($aduanas as $aduana)
                    <tr class="hover:bg-indigo-50/30 transition-colors">
                        <td class="px-6 py-4 font-bold text-gray-900">
                            {{ $aduana->clave ?? $aduana->clave_aduana }}
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-700">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg mr-3 shadow-inner">
                                    <i class="fas fa-building text-sm"></i>
                                </div>
                                {{ $aduana->nombre ?? $aduana->nombre_aduana }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('aduanas.show', $aduana) }}" class="text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white border border-indigo-200 p-2 rounded-lg shadow-sm transition transform hover:scale-105" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('aduanas.edit', $aduana) }}" class="text-amber-500 bg-amber-50 hover:bg-amber-500 hover:text-white border border-amber-200 p-2 rounded-lg shadow-sm transition transform hover:scale-105" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('aduanas.destroy', $aduana) }}" method="POST" class="inline" onsubmit="return confirm('¿Confirma que desea eliminar esta aduana?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white border border-rose-200 p-2 rounded-lg shadow-sm transition transform hover:scale-105" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-building text-5xl mb-3 block opacity-50"></i>
                            <p class="font-medium text-lg text-gray-500">No hay aduanas registradas.</p>
                            <p class="text-sm mt-1">Comienza agregando las aduanas por donde despachas.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($aduanas instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $aduanas->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Nueva Aduana -->
<div id="aduanaModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" aria-hidden="true" onclick="closeAduanaModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="px-6 py-5 bg-indigo-600">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-black text-white" id="modal-title">
                        <i class="fas fa-building mr-2"></i> Nueva Aduana
                    </h3>
                    <button type="button" onclick="closeAduanaModal()" class="text-white hover:text-indigo-200 focus:outline-none transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('aduanas.store') }}" method="POST">
                @csrf
                <div class="px-6 py-6 bg-white flex flex-col gap-4">

                    @if($errors->any())
                        <div class="mb-2 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-rose-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-bold text-rose-700">Revisa los siguientes campos:</p>
                                    <ul class="mt-1 text-sm text-rose-600 list-disc list-inside">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label for="clave" class="block text-sm font-bold text-gray-700 mb-1">Clave de la Aduana <span class="text-rose-500">*</span></label>
                        <input type="text" name="clave" id="clave" maxlength="10" required value="{{ old('clave') }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50 uppercase" placeholder="Ej. 430">
                    </div>

                    <div>
                        <label for="nombre" class="block text-sm font-bold text-gray-700 mb-1">Nombre de la Aduana <span class="text-rose-500">*</span></label>
                        <input type="text" name="nombre" id="nombre" required value="{{ old('nombre') }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50" placeholder="Ej. Aduana de Veracruz">
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 text-right sm:px-6">
                    <button type="button" onclick="closeAduanaModal()" class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                        Guardar Aduana
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAduanaModal() {
        document.getElementById('aduanaModal').classList.remove('hidden');
    }

    function closeAduanaModal() {
        document.getElementById('aduanaModal').classList.add('hidden');
    }

    @if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        openAduanaModal();
    });
    @endif
</script>
@endsection
