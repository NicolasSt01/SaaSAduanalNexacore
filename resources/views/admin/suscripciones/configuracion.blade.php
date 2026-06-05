@extends('layouts.admin')

@section('header_title', 'Configuración de Facturación')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.suscripciones.dashboard') }}" class="text-indigo-600 font-medium"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<form method="POST" action="{{ route('admin.suscripciones.configuracion.update') }}" class="space-y-6">
    @csrf

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-building text-indigo-500"></i> Datos de la Empresa</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Nombre de la Empresa</label>
                <input type="text" name="empresa_nombre" value="{{ $config->empresa_nombre }}" required class="w-full rounded-lg border-gray-300 text-sm px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">RFC</label>
                <input type="text" name="empresa_rfc" value="{{ $config->empresa_rfc }}" maxlength="20" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 uppercase">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-university text-emerald-500"></i> Datos Bancarios</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Banco</label>
                <input type="text" name="banco_nombre" value="{{ $config->banco_nombre }}" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" placeholder="Ej: BBVA, Banorte, Santander">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">CLABE Interbancaria</label>
                <input type="text" name="banco_clabe" value="{{ $config->banco_clabe }}" maxlength="20" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 font-mono" placeholder="012345678901234567">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Número de Cuenta</label>
                <input type="text" name="banco_cuenta" value="{{ $config->banco_cuenta }}" maxlength="20" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 font-mono" placeholder="0123456789">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Prefijo de Referencia</label>
                <input type="text" name="banco_referencia_prefix" value="{{ $config->banco_referencia_prefix }}" maxlength="10" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 font-mono uppercase" placeholder="NX">
                <p class="text-xs text-gray-400 mt-1">Se usa para generar referencias: PREFIJO-AÑO-RANDOM</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-percentage text-amber-500"></i> Configuración Fiscal</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">IVA (%)</label>
                <select name="iva_porcentaje" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2">
                    <option value="8" {{ $config->iva_porcentaje == 8 ? 'selected' : '' }}>8% (Zona Fronteriza)</option>
                    <option value="16" {{ $config->iva_porcentaje == 16 ? 'selected' : '' }}>16% (General)</option>
                    <option value="0" {{ $config->iva_porcentaje == 0 ? 'selected' : '' }}>0% (Exento)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Email de Notificaciones</label>
                <input type="email" name="email_notificaciones" value="{{ $config->email_notificaciones }}" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" placeholder="facturacion@nexacore.com.mx">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Logo URL</label>
                <input type="url" name="logo_url" value="{{ $config->logo_url }}" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" placeholder="https://nexacore.com.mx/LogoNexaCore.png">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-xs font-bold text-gray-600 mb-1">Notas Legales (pie de emails)</label>
            <textarea name="notas_legales" rows="2" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2" placeholder="Texto legal que aparecerá en el pie de los emails de facturación...">{{ $config->notas_legales }}</textarea>
        </div>
    </div>

    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-3 rounded-lg text-sm"><i class="fas fa-save mr-1"></i> Guardar Configuración</button>
</form>
@endsection
