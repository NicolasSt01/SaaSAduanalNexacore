@extends('layouts.admin')

@section('header_title', 'Editar Agencia (Tenant)')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h3 class="text-2xl font-bold text-gray-800 border-b-2 border-indigo-500 pb-2 inline-block">Editar Agencia</h3>
        <a href="{{ route('admin.tenants.index') }}"
            class="text-gray-500 hover:text-indigo-600 transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md p-8 border border-gray-100">
        <form action="{{ route('admin.tenants.update', $tenant->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Nombre de la Empresa / Agencia -->
                <div class="col-span-1 md:col-span-2">
                    <label for="nombre_empresa" class="block text-sm font-semibold text-gray-700 mb-2">Nombre de la
                        Agencia / Empresa *</label>
                    <input type="text" name="nombre_empresa" id="nombre_empresa"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('nombre_empresa') border-red-500 @enderror"
                        value="{{ old('nombre_empresa', $tenant->nombre_empresa) }}" required>
                    @error('nombre_empresa')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug / Subdominio -->
                <div>
                    <label for="slug" class="block text-sm font-semibold text-gray-700 mb-2">Subdominio Único (sin
                        espacios) *</label>
                    <div class="flex rounded-md shadow-sm">
                        <input type="text" name="slug" id="slug"
                            class="flex-1 min-w-0 block w-full px-4 py-2 rounded-none rounded-l-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('slug') border-red-500 @enderror"
                            value="{{ old('slug', $tenant->slug) }}" required>
                        <span
                            class="inline-flex items-center px-4 rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                            .nexacore.com.mx
                        </span>
                    </div>
                    @error('slug')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Correo Admin -->
                <div>
                    <label for="correo_admin" class="block text-sm font-semibold text-gray-700 mb-2">Correo de Contacto
                        *</label>
                    <input type="email" name="correo_admin" id="correo_admin"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('correo_admin') border-red-500 @enderror"
                        value="{{ old('correo_admin', $tenant->correo_admin) }}" required>
                    @error('correo_admin')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- RFC -->
                <div>
                    <label for="rfc" class="block text-sm font-semibold text-gray-700 mb-2">RFC (Opcional)</label>
                    <input type="text" name="rfc" id="rfc"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                        value="{{ old('rfc', $tenant->rfc) }}">
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-sm font-semibold text-gray-700 mb-2">Teléfono
                        (Opcional)</label>
                    <input type="text" name="telefono" id="telefono"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                        value="{{ old('telefono', $tenant->telefono) }}">
                </div>

                <!-- Plan -->
                <div>
                    <label for="plan" class="block text-sm font-semibold text-gray-700 mb-2">Plan SaaS *</label>
                    <select name="plan" id="plan"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('plan') border-red-500 @enderror"
                        required>
                        <option value="basico" {{ old('plan', $tenant->plan) == 'basico' ? 'selected' : '' }}>Básico
                        </option>
                        <option value="profesional" {{ old('plan', $tenant->plan) == 'profesional' ? 'selected' : '' }}>Profesional</option>
                        <option value="enterprise" {{ old('plan', $tenant->plan) == 'enterprise' ? 'selected' : ''
                            }}>Enterprise</option>
                    </select>
                    @error('plan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado -->
                <div>
                    <label for="estado" class="block text-sm font-semibold text-gray-700 mb-2">Estado *</label>
                    <select name="estado" id="estado"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('estado') border-red-500 @enderror"
                        required>
                        <option value="activo" {{ old('estado', $tenant->estado) == 'activo' ? 'selected' : '' }}>Activo
                        </option>
                        <option value="inactivo" {{ old('estado', $tenant->estado) == 'inactivo' ? 'selected' : ''
                            }}>Inactivo</option>
                    </select>
                    @error('estado')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end gap-4">
                <a href="{{ route('admin.tenants.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition">
                    Cancelar
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-md transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Actualizar Agencia
                </button>
            </div>
        </form>
    </div>
</div>
@endsection