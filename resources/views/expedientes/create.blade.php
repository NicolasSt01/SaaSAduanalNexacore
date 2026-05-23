@extends('layouts.app')

@section('title', 'Crear Expediente')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-800">Crear <span class="text-indigo-600">Expediente</span></h1>
        <p class="text-sm text-gray-500 mt-1 font-medium">Registra un nuevo expediente en el sistema.</p>
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

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <form action="{{ route('expedientes.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Cliente --}}
            <div class="mb-4">
                <label for="cliente_id" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Cliente <span class="text-rose-500">*</span></label>
                <select name="cliente_id" id="cliente_id" required
                    class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm @error('cliente_id') border-rose-500 @enderror">
                    <option value="">Seleccione un cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>{{ $cliente->nombre }}</option>
                    @endforeach
                </select>
                @error('cliente_id') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
            </div>

            {{-- Patente --}}
            <div class="mb-4">
                <label for="patente_id" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Patente <span class="text-rose-500">*</span></label>
                <select name="patente_id" id="patente_id" required
                    class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm @error('patente_id') border-rose-500 @enderror">
                    <option value="">Seleccione una patente</option>
                    @foreach($patentes as $patente)
                        <option value="{{ $patente->id }}" {{ old('patente_id') == $patente->id ? 'selected' : '' }}>{{ $patente->numero }}</option>
                    @endforeach
                </select>
                @error('patente_id') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
            </div>

            {{-- Aduana --}}
            <div class="mb-4">
                <label for="aduana_id" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Aduana <span class="text-rose-500">*</span></label>
                <select name="aduana_id" id="aduana_id" required
                    class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm @error('aduana_id') border-rose-500 @enderror">
                    <option value="">Seleccione una aduana</option>
                    @foreach($aduanas as $aduana)
                        <option value="{{ $aduana->id }}" {{ old('aduana_id') == $aduana->id ? 'selected' : '' }}>{{ $aduana->nombre }}</option>
                    @endforeach
                </select>
                @error('aduana_id') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
            </div>

            {{-- Categoría y Clave Pedimento --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="categoria" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Categoría <span class="text-rose-500">*</span></label>
                    <select id="categoria" name="categoria" required
                        class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                        <option value="">Seleccionar Categoría</option>
                        <option value="Importacion" {{ old('categoria') == 'Importacion' ? 'selected' : '' }}>Importación</option>
                        <option value="Exportacion" {{ old('categoria') == 'Exportacion' ? 'selected' : '' }}>Exportación</option>
                        <option value="Rectificaciones" {{ old('categoria') == 'Rectificaciones' ? 'selected' : '' }}>Rectificaciones</option>
                    </select>
                </div>
                <div>
                    <label for="clave_pedimento" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Clave Pedimento <span class="text-rose-500">*</span></label>
                    <select id="clave_pedimento" name="clave_pedimento" required
                        class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                        <option value="">Seleccionar Clave</option>
                        <option value="H1" {{ old('clave_pedimento') == 'H1' ? 'selected' : '' }}>H1</option>
                        <option value="A1" {{ old('clave_pedimento') == 'A1' ? 'selected' : '' }}>A1</option>
                        <option value="RT" {{ old('clave_pedimento') == 'RT' ? 'selected' : '' }}>RT</option>
                    </select>
                </div>
            </div>

            {{-- Tipo de Expediente --}}
            <div class="mb-4">
                <label for="tipo_expediente" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Tipo de Expediente <span class="text-rose-500">*</span></label>
                <select name="tipo_expediente" id="tipo_expediente" required
                    class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                    <option value="">Seleccione tipo</option>
                    <option value="Unico" {{ old('tipo_expediente') == 'Unico' ? 'selected' : '' }}>Único</option>
                    <option value="Consolidado" {{ old('tipo_expediente') == 'Consolidado' ? 'selected' : '' }}>Consolidado</option>
                </select>
            </div>

            {{-- Número de Pedimento --}}
            <div class="mb-4">
                <label for="numero_pedimento" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Número de Pedimento <span class="text-rose-500">*</span></label>
                <input type="text" id="numero_pedimento" name="numero_pedimento" value="{{ old('numero_pedimento') }}" required
                    class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm @error('numero_pedimento') border-rose-500 @enderror">
                @error('numero_pedimento') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
            </div>

            {{-- Fechas según tipo --}}
            <div id="fechas-unico" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="fecha_pago_pedimento" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Fecha de Pago</label>
                    <input type="date" name="fecha_pago_pedimento" id="fecha_pago_pedimento"
                        class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                </div>
            </div>

            <div id="fechas-consolidado" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="fecha_apertura" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Fecha de Apertura</label>
                    <input type="date" name="fecha_apertura" id="fecha_apertura"
                        class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                </div>
                <div>
                    <label for="fecha_cierre" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Fecha de Cierre</label>
                    <input type="date" name="fecha_cierre" id="fecha_cierre"
                        class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                </div>
            </div>

            {{-- Observaciones --}}
            <div class="mb-6">
                <label for="observaciones" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Observaciones</label>
                <textarea name="observaciones" id="observaciones" rows="3"
                    class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">{{ old('observaciones') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('expedientes.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all shadow-sm">
                    Crear Expediente
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('tipo_expediente').addEventListener('change', function() {
        var unico = document.getElementById('fechas-unico');
        var consolidado = document.getElementById('fechas-consolidado');
        if (this.value === 'Unico') {
            unico.classList.remove('hidden');
            unico.classList.add('grid');
            consolidado.classList.add('hidden');
            consolidado.classList.remove('grid');
        } else if (this.value === 'Consolidado') {
            unico.classList.add('hidden');
            unico.classList.remove('grid');
            consolidado.classList.remove('hidden');
            consolidado.classList.add('grid');
        } else {
            unico.classList.add('hidden');
            unico.classList.remove('grid');
            consolidado.classList.add('hidden');
            consolidado.classList.remove('grid');
        }
    });

    // Show on load if already selected
    (function() {
        var tipo = document.getElementById('tipo_expediente').value;
        if (tipo === 'Unico') {
            document.getElementById('fechas-unico').classList.remove('hidden');
            document.getElementById('fechas-unico').classList.add('grid');
        } else if (tipo === 'Consolidado') {
            document.getElementById('fechas-consolidado').classList.remove('hidden');
            document.getElementById('fechas-consolidado').classList.add('grid');
        }
    })();
</script>
@endsection
