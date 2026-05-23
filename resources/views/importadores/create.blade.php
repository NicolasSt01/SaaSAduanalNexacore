@extends('layouts.app')

@section('title', 'Crear Nuevo Importador')

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
                        <a href="{{ route('importadores.index') }}" class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">Importadores</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                        <span class="text-sm font-medium text-gray-700">Nuevo Importador</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl font-black text-gray-800 tracking-tight">Nuevo <span class="text-indigo-600">Importador</span></h1>
        <p class="text-sm text-gray-500 mt-2 font-medium">Registra un nuevo importador en el sistema.</p>
    </div>

    @include('partials.alerts')

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg leading-6 font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-truck text-indigo-500"></i> Formulario de Registro
            </h3>
        </div>

        <div class="p-6 sm:p-8">
            <form action="{{ route('importadores.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nombre" class="block text-sm font-bold text-gray-700 mb-1">Nombre <span class="text-rose-500">*</span></label>
                        <input type="text" id="nombre" name="nombre" required value="{{ old('nombre') }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50" placeholder="Ej: Importadora Global">
                        @error('nombre')
                            <p class="mt-1 text-xs text-rose-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tax_id" class="block text-sm font-bold text-gray-700 mb-1">Tax ID</label>
                        <input type="text" id="tax_id" name="tax_id" value="{{ old('tax_id') }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50" placeholder="Equivalente al RFC en USA">
                        @error('tax_id')
                            <p class="mt-1 text-xs text-rose-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="direccion" class="block text-sm font-bold text-gray-700 mb-1">Dirección</label>
                        <textarea id="direccion" name="direccion" rows="3" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50" placeholder="Dirección completa del importador">{{ old('direccion') }}</textarea>
                        @error('direccion')
                            <p class="mt-1 text-xs text-rose-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                    <a href="{{ route('importadores.index') }}" class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-save mr-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
