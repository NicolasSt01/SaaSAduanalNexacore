@extends('layouts.app')

@section('title', 'Editar Expediente')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-800">Editar <span class="text-indigo-600">Expediente</span></h1>
        <p class="text-sm text-gray-500 mt-1 font-medium">Modifica la información del expediente {{ $expediente->numero_pedimento }}</p>
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

    {{-- Card principal --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <form action="{{ route('expedientes.update', $expediente) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Información General --}}
            <div class="mb-6">
                <h5 class="text-sm font-black text-gray-800 mb-4 pb-2 border-b border-gray-100">
                    <i class="fas fa-info-circle text-indigo-600 mr-2"></i>Información General
                </h5>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="cliente_id" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Cliente <span class="text-rose-500">*</span></label>
                        <select name="cliente_id" id="cliente_id" required
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ $expediente->cliente_id == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="patente_id" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Patente <span class="text-rose-500">*</span></label>
                        <select name="patente_id" id="patente_id" required
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                            @foreach($patentes as $patente)
                                <option value="{{ $patente->id }}" {{ $expediente->patente_id == $patente->id ? 'selected' : '' }}>
                                    {{ $patente->numero }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="aduana_id" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Aduana <span class="text-rose-500">*</span></label>
                        <select name="aduana_id" id="aduana_id" required
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                            @foreach($aduanas as $aduana)
                                <option value="{{ $aduana->id }}" {{ $expediente->aduana_id == $aduana->id ? 'selected' : '' }}>
                                    {{ $aduana->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="tipo_expediente" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Tipo de Expediente <span class="text-rose-500">*</span></label>
                        <select name="tipo_expediente" id="tipo_expediente" required
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                            <option value="Unico" {{ $expediente->tipo_expediente == 'Unico' ? 'selected' : '' }}>Único</option>
                            <option value="Consolidado" {{ $expediente->tipo_expediente == 'Consolidado' ? 'selected' : '' }}>Consolidado</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Fechas --}}
            <div class="mb-6">
                <h5 class="text-sm font-black text-gray-800 mb-4 pb-2 border-b border-gray-100">
                    <i class="fas fa-calendar-alt text-indigo-600 mr-2"></i>Fechas
                </h5>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="fecha_apertura" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Fecha de Apertura</label>
                        <input type="date" name="fecha_apertura" id="fecha_apertura"
                            value="{{ $expediente->fecha_apertura ? $expediente->fecha_apertura->format('Y-m-d') : '' }}"
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                        <p class="text-xs text-gray-400 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>Fecha en que se abrió el expediente
                        </p>
                    </div>

                    <div>
                        <label for="fecha_cierre" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Fecha de Cierre</label>
                        <input type="date" name="fecha_cierre" id="fecha_cierre"
                            value="{{ $expediente->fecha_cierre ? $expediente->fecha_cierre->format('Y-m-d') : '' }}"
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                        <p class="text-xs text-gray-400 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>Fecha en que se cerró el expediente (opcional)
                        </p>
                    </div>
                </div>
            </div>

            {{-- Estado y Observaciones --}}
            <div class="mb-6">
                <h5 class="text-sm font-black text-gray-800 mb-4 pb-2 border-b border-gray-100">
                    <i class="fas fa-cog text-indigo-600 mr-2"></i>Estado y Observaciones
                </h5>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="estado" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Estado</label>
                        <select name="estado" id="estado"
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                            <option value="En proceso" {{ $expediente->estado == 'En proceso' ? 'selected' : '' }}>En proceso</option>
                            <option value="Abierto" {{ $expediente->estado == 'Abierto' ? 'selected' : '' }}>Abierto</option>
                            <option value="Cerrado" {{ $expediente->estado == 'Cerrado' ? 'selected' : '' }}>Cerrado</option>
                            <option value="Cancelado" {{ $expediente->estado == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <label for="observaciones" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" rows="4"
                            placeholder="Ingresa observaciones adicionales sobre el expediente..."
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">{{ $expediente->observaciones }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>Información adicional relevante para este expediente
                        </p>
                    </div>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-end pt-4 border-t border-gray-100">
                <a href="{{ route('expedientes.show', $expediente) }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all shadow-sm">
                    <i class="fas fa-save"></i> Actualizar Expediente
                </button>
            </div>
        </form>
    </div>

    {{-- Consejo --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-4 mt-4 flex items-center gap-3">
        <i class="fas fa-lightbulb text-amber-500 text-lg"></i>
        <p class="text-sm text-gray-500">
            <strong class="text-gray-700">Consejo:</strong> Asegúrate de que toda la información sea correcta antes de actualizar el expediente. Los cambios se reflejarán inmediatamente en el sistema.
        </p>
    </div>
</div>
@endsection
