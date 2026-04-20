@extends('layouts.app')

@section('title', 'Editar Operación | NexaCore')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300 pb-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <a href="{{ route('operaciones.index') }}" class="text-indigo-600 dark:text-indigo-400 text-xs font-black uppercase tracking-widest flex items-center gap-2 mb-2 hover:translate-x--1 transition-transform">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white tracking-tight">
                    Editar Operación <span class="text-indigo-600 dark:text-indigo-400">{{ $operacion->referencia }}</span>
                </h1>
            </div>
            <div class="px-4 py-2 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block">Estado Actual</span>
                <span class="text-sm font-black text-indigo-600 dark:text-indigo-400 uppercase">{{ $operacion->estado }}</span>
            </div>
        </div>

        <!-- Validation Errors -->
        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-100 dark:bg-rose-900/20 dark:border-rose-900/30">
                <div class="flex items-center gap-3 text-rose-600 dark:text-rose-400 mb-2">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                    <span class="text-sm font-black uppercase tracking-widest">Atención</span>
                </div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li class="text-xs font-bold text-rose-500 dark:text-rose-400/80">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden">
            <form action="{{ route('operaciones.update', $operacion->id) }}" method="POST" class="p-8 space-y-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Referencia -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Referencia</label>
                        <input type="text" value="{{ $operacion->referencia }}" disabled class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-400 cursor-not-allowed">
                    </div>

                    <!-- Fecha de Cruce -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Fecha de Cruce Estimada</label>
                        <input type="date" name="fecha_cruce_estimada" value="{{ old('fecha_cruce_estimada', $operacion->fecha_cruce_estimada ? $operacion->fecha_cruce_estimada->format('Y-m-d') : '') }}" required
                            class="w-full bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                    </div>

                    <!-- Factura -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Número de Factura</label>
                        <input type="text" name="num_factura" value="{{ old('num_factura', $operacion->num_factura) }}" required
                            class="w-full bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                    </div>

                    <!-- Cliente -->
                    <div class="lg:col-span-1">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Cliente</label>
                        <select name="cliente_id" required class="w-full bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('cliente_id', $operacion->cliente_id) == $cliente->id ? 'selected' : '' }}>{{ $cliente->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Importador -->
                    <div class="lg:col-span-1">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Importador</label>
                        <select name="importador_id" required class="w-full bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                            @foreach($importadores as $importador)
                                <option value="{{ $importador->id }}" {{ old('importador_id', $operacion->importador_id) == $importador->id ? 'selected' : '' }}>{{ $importador->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Bodega -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Bodega</label>
                        <select name="bodega_id" class="w-full bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="">Seleccione Bodega</option>
                            @foreach($bodegas as $bodega)
                                <option value="{{ $bodega->id }}" {{ old('bodega_id', $operacion->bodega_id) == $bodega->id ? 'selected' : '' }}>{{ $bodega->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Aduana -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Aduana</label>
                        <select name="aduana_id" required class="w-full bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                            @foreach($aduanas as $aduana)
                                <option value="{{ $aduana->id }}" {{ old('aduana_id', $operacion->aduana_id) == $aduana->id ? 'selected' : '' }}>{{ $aduana->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Patente -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Patente</label>
                        <select name="patente_id" class="w-full bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="">Seleccione Patente</option>
                            @foreach($patentes as $patente)
                                <option value="{{ $patente->id }}" {{ old('patente_id', $operacion->patente_id) == $patente->id ? 'selected' : '' }}>{{ $patente->numero }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Prioridad -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Prioridad</label>
                        <select name="prioridad" required class="w-full bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="regular" {{ old('prioridad', $operacion->prioridad) == 'regular' ? 'selected' : '' }}>Regular</option>
                            <option value="media" {{ old('prioridad', $operacion->prioridad) == 'media' ? 'selected' : '' }}>Media</option>
                            <option value="alta" {{ old('prioridad', $operacion->prioridad) == 'alta' ? 'selected' : '' }}>Alta</option>
                            <option value="urgente" {{ old('prioridad', $operacion->prioridad) == 'urgente' ? 'selected' : '' }}>Urgente</option>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Estado</label>
                        <select name="estado" required class="w-full bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="pendiente" {{ old('estado', $operacion->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="proceso" {{ old('estado', $operacion->estado) == 'proceso' ? 'selected' : '' }}>En Proceso</option>
                            <option value="terminado" {{ old('estado', $operacion->estado) == 'terminado' ? 'selected' : '' }}>Terminado</option>
                        </select>
                    </div>
                </div>

                <!-- Producto -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Descripción del Producto</label>
                    <textarea name="nombre_producto" rows="2" required
                        class="w-full bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 transition-all">{{ old('nombre_producto', $operacion->nombre_producto) }}</textarea>
                </div>

                <!-- Observaciones -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Observaciones Internas</label>
                    <textarea name="observaciones" rows="3"
                        class="w-full bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="Notas adicionales sobre la operación...">{{ old('observaciones', $operacion->observaciones) }}</textarea>
                </div>

                <!-- Footer Acciones -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('operaciones.index') }}" class="px-6 py-2.5 rounded-xl text-xs font-black text-gray-400 hover:text-gray-600 transition-colors uppercase tracking-widest">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-indigo-600 text-white px-10 py-2.5 rounded-xl font-black text-xs shadow-lg hover:bg-indigo-700 transition-all transform hover:scale-105 active:scale-95 uppercase tracking-widest">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
