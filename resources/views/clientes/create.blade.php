@extends('layouts.app')

@section('title', 'Registrar Nuevo Cliente')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.adminconfig') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-emerald-600 transition-colors">
                    <i class="fas fa-cog mr-2"></i> Configuración
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                    <a href="{{ route('clientes.index') }}" class="text-sm font-medium text-gray-500 hover:text-emerald-600 transition-colors">Clientes</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                    <span class="text-sm font-bold text-gray-700">Nuevo Cliente</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-12 w-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fas fa-building"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-gray-800">Nuevo <span class="text-emerald-600">Cliente</span></h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Registra una nueva empresa en tu directorio de clientes.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-rose-500 mt-0.5"></i>
                    <div class="ml-3">
                        <p class="text-sm font-bold text-rose-700">Por favor corrige los siguientes errores:</p>
                        <ul class="mt-1 text-sm text-rose-600 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('clientes.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="nombre" class="block text-sm font-bold text-gray-700 mb-1">Nombre de la Empresa <span class="text-rose-500">*</span></label>
                    <input type="text" name="nombre" id="nombre" required value="{{ old('nombre') }}"
                        class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50"
                        placeholder="Ej. Comercializadora del Norte S.A.">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="rfc" class="block text-sm font-bold text-gray-700 mb-1">RFC</label>
                        <input type="text" name="rfc" id="rfc" maxlength="13" value="{{ old('rfc') }}"
                            class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50 uppercase"
                            placeholder="Ej. CNO123456789">
                    </div>
                    <div>
                        <label for="tax_id" class="block text-sm font-bold text-gray-700 mb-1">Tax ID</label>
                        <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id') }}"
                            class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50 uppercase"
                            placeholder="Ej. 12-3456789">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="correo" class="block text-sm font-bold text-gray-700 mb-1">Correo Electrónico <span class="text-rose-500">*</span></label>
                        <input type="email" name="correo" id="correo" value="{{ old('correo') }}" required
                            class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50"
                            placeholder="ejemplo@correo.com">
                    </div>
                    <div>
                        <label for="telefono" class="block text-sm font-bold text-gray-700 mb-1">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}"
                            class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50"
                            placeholder="(555) 123-4567">
                    </div>
                </div>

                <div>
                    <label for="direccion" class="block text-sm font-bold text-gray-700 mb-1">Dirección Fiscal / Operativa</label>
                    <textarea name="direccion" id="direccion" rows="2"
                        class="w-full rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 border shadow-sm bg-gray-50/50 resize-none"
                        placeholder="Av. Principal #123, Colonia, Ciudad, Estado, CP">{{ old('direccion') }}</textarea>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('clientes.index') }}" class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                    Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
