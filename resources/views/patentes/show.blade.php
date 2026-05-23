@extends('layouts.app')

@section('title', 'Detalles de Patente')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.adminconfig') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">
                        <i class="fas fa-cog mr-2"></i> Configuración
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                        <a href="{{ route('patentes.index') }}" class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">Patentes</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                        <span class="text-sm font-medium text-gray-700">{{ $patente->numero_patente }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl font-black text-gray-800 tracking-tight">Detalles de <span class="text-indigo-600">Patente</span></h1>
    </div>

    @include('partials.alerts')

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg leading-6 font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-file-contract text-indigo-500"></i> {{ $patente->numero_patente }}
            </h3>
            <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-indigo-200 uppercase tracking-widest">Patente</span>
        </div>

        <div class="p-6 sm:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Número de Patente</p>
                    <p class="text-sm font-bold text-gray-800">{{ $patente->numero_patente }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Agente Aduanal</p>
                    <p class="text-sm font-bold text-gray-800">{{ $patente->nombre_agente_aduanal }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">RFC</p>
                    <p class="text-sm font-bold text-gray-800">{{ $patente->rfc_agente ?? 'No especificado' }}</p>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 mt-6 flex items-center justify-between">
                <a href="{{ route('patentes.index') }}" class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Regresar
                </a>
                <div class="flex gap-2">
                    <a href="{{ route('patentes.edit', $patente) }}" class="inline-flex justify-center rounded-xl border border-transparent bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-edit mr-2"></i> Editar
                    </a>
                    <form action="{{ route('patentes.destroy', $patente) }}" method="POST" onsubmit="return confirm('¿Eliminar esta patente?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-rose-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-colors">
                            <i class="fas fa-trash mr-2"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
        <div class="bg-indigo-600 px-6 py-4">
            <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2">
                <i class="fas fa-building"></i> Aduanas Habilitadas
            </h3>
        </div>
        <div class="p-6 sm:p-8">
            @if($patente->aduanas->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Clave</th>
                            <th class="px-6 py-4">Nombre</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm">
                        @foreach($patente->aduanas as $aduana)
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $aduana->clave_aduana }}</td>
                            <td class="px-6 py-4 font-medium text-gray-700">{{ $aduana->nombre_aduana }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                    <div class="ml-3">
                        <p class="text-sm text-amber-700 font-bold">Esta patente no tiene aduanas asignadas.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
