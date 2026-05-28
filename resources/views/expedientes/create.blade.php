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
                        <optgroup label="Importaciones y Exportaciones Definitivas">
                            <option value="A1" {{ old('clave_pedimento') == 'A1' ? 'selected' : '' }}>A1 — Importación y Exportación Definitiva</option>
                            <option value="A3" {{ old('clave_pedimento') == 'A3' ? 'selected' : '' }}>A3 — Importación Definitiva virtual y Regularización</option>
                            <option value="L1" {{ old('clave_pedimento') == 'L1' ? 'selected' : '' }}>L1 — Pequeña Importación y Exportación Definitiva</option>
                            <option value="C1" {{ old('clave_pedimento') == 'C1' ? 'selected' : '' }}>C1 — Importación Definitiva a Región Fronteriza</option>
                            <option value="D1" {{ old('clave_pedimento') == 'D1' ? 'selected' : '' }}>D1 — Sustitución de Importaciones Definitivas</option>
                            <option value="C2" {{ old('clave_pedimento') == 'C2' ? 'selected' : '' }}>C2 — Importación Definitiva de Vehículos a Franja Fronteriza</option>
                            <option value="K1" {{ old('clave_pedimento') == 'K1' ? 'selected' : '' }}>K1 — Retorno de Exportación Definitiva y Desistimiento</option>
                            <option value="P1" {{ old('clave_pedimento') == 'P1' ? 'selected' : '' }}>P1 — Reexpedición Definitiva</option>
                            <option value="T1" {{ old('clave_pedimento') == 'T1' ? 'selected' : '' }}>T1 — Importación y Exportación Definitiva (mensajería)</option>
                            <option value="S1" {{ old('clave_pedimento') == 'S1' ? 'selected' : '' }}>S1 — Importación Definitiva de Insumos (Cuenta Aduanera)</option>
                            <option value="S2" {{ old('clave_pedimento') == 'S2' ? 'selected' : '' }}>S2 — Importación Definitiva de Bienes mismo estado (Cuenta Aduanera)</option>
                            <option value="S9" {{ old('clave_pedimento') == 'S9' ? 'selected' : '' }}>S9 — Reexpedición Temporal (Cuenta Aduanera)</option>
                            <option value="V2" {{ old('clave_pedimento') == 'V2' ? 'selected' : '' }}>V2 — Transferencias (Cuenta Aduanera)</option>
                            <option value="H1" {{ old('clave_pedimento') == 'H1' ? 'selected' : '' }}>H1 — Retorno al Extranjero de mercancía en su mismo estado</option>
                            <option value="H8" {{ old('clave_pedimento') == 'H8' ? 'selected' : '' }}>H8 — Retorno de envases Exportados o Importados Temporalmente</option>
                            <option value="BB" {{ old('clave_pedimento') == 'BB' ? 'selected' : '' }}>BB — Exportación Definitiva virtual</option>
                            <option value="VU" {{ old('clave_pedimento') == 'VU' ? 'selected' : '' }}>VU — Importación Definitiva de Vehículos Usados</option>
                        </optgroup>
                        <optgroup label="Importaciones Temporales (Maquiladoras, PITEX, ECEX)">
                            <option value="A2" {{ old('clave_pedimento') == 'A2' ? 'selected' : '' }}>A2 — Importación Temporal de bienes distintos a activo fijo (PITEX)</option>
                            <option value="A6" {{ old('clave_pedimento') == 'A6' ? 'selected' : '' }}>A6 — Importación Temporal de Activo Fijo (PITEX)</option>
                            <option value="J2" {{ old('clave_pedimento') == 'J2' ? 'selected' : '' }}>J2 — Retorno de Mercancías Elaboradas (PITEX)</option>
                            <option value="H2" {{ old('clave_pedimento') == 'H2' ? 'selected' : '' }}>H2 — Importación Temporal de bienes (Maquiladoras)</option>
                            <option value="H3" {{ old('clave_pedimento') == 'H3' ? 'selected' : '' }}>H3 — Importación Temporal de Activo Fijo (Maquiladoras)</option>
                            <option value="J1" {{ old('clave_pedimento') == 'J1' ? 'selected' : '' }}>J1 — Retorno de Mercancías Elaboradas (Maquiladoras)</option>
                            <option value="V1" {{ old('clave_pedimento') == 'V1' ? 'selected' : '' }}>V1 — Transferencias (Maquiladoras, PITEX o ECEX)</option>
                            <option value="F4" {{ old('clave_pedimento') == 'F4' ? 'selected' : '' }}>F4 — Cambio de Régimen de Temporal a Definitiva (bienes)</option>
                            <option value="F5" {{ old('clave_pedimento') == 'F5' ? 'selected' : '' }}>F5 — Cambio de Régimen de Temporal a Definitiva (Activo Fijo)</option>
                            <option value="V5" {{ old('clave_pedimento') == 'V5' ? 'selected' : '' }}>V5 — Importación Definitiva y Exportación Virtual</option>
                        </optgroup>
                        <optgroup label="Temporales para Retornar en su Mismo Estado">
                            <option value="BA" {{ old('clave_pedimento') == 'BA' ? 'selected' : '' }}>BA — Importación y Exportación Temporal para retornar</option>
                            <option value="AJ" {{ old('clave_pedimento') == 'AJ' ? 'selected' : '' }}>AJ — Importación/Exportación Temporal de Envases</option>
                            <option value="BP" {{ old('clave_pedimento') == 'BP' ? 'selected' : '' }}>BP — Importación Temporal de Muestras y Muestrarios</option>
                            <option value="V4" {{ old('clave_pedimento') == 'V4' ? 'selected' : '' }}>V4 — Exportación Virtual (Industria de Autopartes)</option>
                            <option value="AD" {{ old('clave_pedimento') == 'AD' ? 'selected' : '' }}>AD — Importación Temporal para Convenciones y Congresos</option>
                            <option value="BC" {{ old('clave_pedimento') == 'BC' ? 'selected' : '' }}>BC — Importación Temporal para Eventos Culturales/Deportivos</option>
                            <option value="BF" {{ old('clave_pedimento') == 'BF' ? 'selected' : '' }}>BF — Exportación Temporal para Exposiciones/Convenciones</option>
                            <option value="BM" {{ old('clave_pedimento') == 'BM' ? 'selected' : '' }}>BM — Exportación Temporal para Transformación/Reparación</option>
                            <option value="BO" {{ old('clave_pedimento') == 'BO' ? 'selected' : '' }}>BO — Exportación Temporal y Retorno de Activo Fijo</option>
                            <option value="BH" {{ old('clave_pedimento') == 'BH' ? 'selected' : '' }}>BH — Importación Temporal de contenedores, aviones, etc.</option>
                            <option value="BD" {{ old('clave_pedimento') == 'BD' ? 'selected' : '' }}>BD — Importación Temporal de Equipo para Filmación</option>
                            <option value="BE" {{ old('clave_pedimento') == 'BE' ? 'selected' : '' }}>BE — Importación de Vehículos de Prueba</option>
                            <option value="BR" {{ old('clave_pedimento') == 'BR' ? 'selected' : '' }}>BR — Exportación Temporal de Mercancía fungible</option>
                        </optgroup>
                        <optgroup label="Depósito Fiscal en Almacén General">
                            <option value="A4" {{ old('clave_pedimento') == 'A4' ? 'selected' : '' }}>A4 — Importación/Exportación a Depósito Fiscal (Almacén)</option>
                            <option value="G1" {{ old('clave_pedimento') == 'G1' ? 'selected' : '' }}>G1 — Extracción para Importación o Exportación Definitiva</option>
                            <option value="C3" {{ old('clave_pedimento') == 'C3' ? 'selected' : '' }}>C3 — Extracción de Depósito Fiscal (Franja Fronteriza)</option>
                            <option value="K2" {{ old('clave_pedimento') == 'K2' ? 'selected' : '' }}>K2 — Extracción de Depósito Fiscal para Retorno</option>
                            <option value="H4" {{ old('clave_pedimento') == 'H4' ? 'selected' : '' }}>H4 — Extracción para Importación Temporal Activo Fijo (Maq.)</option>
                            <option value="H5" {{ old('clave_pedimento') == 'H5' ? 'selected' : '' }}>H5 — Extracción para Importación Temporal bienes (Maq.)</option>
                            <option value="A7" {{ old('clave_pedimento') == 'A7' ? 'selected' : '' }}>A7 — Extracción para Importación Temporal Activo Fijo (PITEX)</option>
                            <option value="A8" {{ old('clave_pedimento') == 'A8' ? 'selected' : '' }}>A8 — Extracción para Importación Temporal bienes (PITEX)</option>
                            <option value="S3" {{ old('clave_pedimento') == 'S3' ? 'selected' : '' }}>S3 — Exportación de insumos (Cuenta Aduanera)</option>
                            <option value="S4" {{ old('clave_pedimento') == 'S4' ? 'selected' : '' }}>S4 — Extracción con pago en Cuenta Aduanera</option>
                        </optgroup>
                        <optgroup label="Depósito Fiscal en Local Autorizado">
                            <option value="A5" {{ old('clave_pedimento') == 'A5' ? 'selected' : '' }}>A5 — Importación a Depósito Fiscal (Exposiciones)</option>
                            <option value="G2" {{ old('clave_pedimento') == 'G2' ? 'selected' : '' }}>G2 — Extracción para Importación Definitiva</option>
                            <option value="K3" {{ old('clave_pedimento') == 'K3' ? 'selected' : '' }}>K3 — Extracción para retorno al extranjero</option>
                            <option value="H6" {{ old('clave_pedimento') == 'H6' ? 'selected' : '' }}>H6 — Extracción para Importación Temporal Activo Fijo (Maq.)</option>
                            <option value="H7" {{ old('clave_pedimento') == 'H7' ? 'selected' : '' }}>H7 — Extracción para Importación Temporal bienes (Maq.)</option>
                            <option value="A9" {{ old('clave_pedimento') == 'A9' ? 'selected' : '' }}>A9 — Extracción para Importación Temporal Activo Fijo (PITEX)</option>
                            <option value="AA" {{ old('clave_pedimento') == 'AA' ? 'selected' : '' }}>AA — Extracción para Importación Temporal de Insumos (PITEX)</option>
                            <option value="S5" {{ old('clave_pedimento') == 'S5' ? 'selected' : '' }}>S5 — Exportación de Insumos (Cuenta Aduanera)</option>
                            <option value="S6" {{ old('clave_pedimento') == 'S6' ? 'selected' : '' }}>S6 — Extracción con pago en Cuenta Aduanera</option>
                            <option value="F2" {{ old('clave_pedimento') == 'F2' ? 'selected' : '' }}>F2 — Depósito Fiscal (Industria Automotriz)</option>
                            <option value="V3" {{ old('clave_pedimento') == 'V3' ? 'selected' : '' }}>V3 — Transferencia (Industria Automotriz y PITEX)</option>
                            <option value="F3" {{ old('clave_pedimento') == 'F3' ? 'selected' : '' }}>F3 — Extracción para Importación Definitiva (Ind. Automotriz)</option>
                            <option value="I1" {{ old('clave_pedimento') == 'I1' ? 'selected' : '' }}>I1 — Retorno de Mercancías Elaboradas/Transformadas</option>
                        </optgroup>
                        <optgroup label="Depósito Fiscal para Exposición y Venta">
                            <option value="F8" {{ old('clave_pedimento') == 'F8' ? 'selected' : '' }}>F8 — Depósito Fiscal exposición/venta mercancías nacionales</option>
                            <option value="F9" {{ old('clave_pedimento') == 'F9' ? 'selected' : '' }}>F9 — Depósito Fiscal exposición/venta mercancías extranjeras</option>
                            <option value="G6" {{ old('clave_pedimento') == 'G6' ? 'selected' : '' }}>G6 — Extracción exposición/venta mercancías nacionales</option>
                            <option value="G7" {{ old('clave_pedimento') == 'G7' ? 'selected' : '' }}>G7 — Extracción exposición/venta mercancías extranjeras</option>
                        </optgroup>
                        <optgroup label="Recinto Fiscalizado">
                            <option value="M1" {{ old('clave_pedimento') == 'M1' ? 'selected' : '' }}>M1 — Mercancías destinadas a Recinto Fiscalizado</option>
                            <option value="M2" {{ old('clave_pedimento') == 'M2' ? 'selected' : '' }}>M2 — Maquinaria y Equipo para Recinto Fiscalizado</option>
                            <option value="J3" {{ old('clave_pedimento') == 'J3' ? 'selected' : '' }}>J3 — Retorno al Extranjero de Insumos (Recinto Fiscalizado)</option>
                        </optgroup>
                        <optgroup label="Tránsitos">
                            <option value="T3" {{ old('clave_pedimento') == 'T3' ? 'selected' : '' }}>T3 — Tránsito interno</option>
                            <option value="T6" {{ old('clave_pedimento') == 'T6' ? 'selected' : '' }}>T6 — Tránsito internacional por territorio extranjero</option>
                            <option value="T7" {{ old('clave_pedimento') == 'T7' ? 'selected' : '' }}>T7 — Tránsito internacional por territorio nacional</option>
                            <option value="T8" {{ old('clave_pedimento') == 'T8' ? 'selected' : '' }}>T8 — Tránsito para el transbordo</option>
                            <option value="R3" {{ old('clave_pedimento') == 'R3' ? 'selected' : '' }}>R3 — Rectificación a pedimento de tránsito</option>
                        </optgroup>
                        <optgroup label="Otros">
                            <option value="RT" {{ old('clave_pedimento') == 'RT' ? 'selected' : '' }}>RT — Rectificación</option>
                            <option value="R1" {{ old('clave_pedimento') == 'R1' ? 'selected' : '' }}>R1 — Rectificación de pedimentos</option>
                            <option value="CT" {{ old('clave_pedimento') == 'CT' ? 'selected' : '' }}>CT — Pedimento complementario (Art. 303 TLCAN)</option>
                        </optgroup>
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
