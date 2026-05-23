@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
    <!-- Header -->
    <div class="mb-8">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.config') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">
                        <i class="fas fa-cog mr-2"></i> Configuración
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                        <a href="{{ route('usuarios.index') }}" class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">Usuarios</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                        <span class="text-sm font-medium text-gray-700">Editar</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl font-black text-gray-800 tracking-tight">Editar <span class="text-indigo-600">Usuario</span></h1>
        <p class="text-sm text-gray-500 mt-2 font-medium">Actualiza los datos y permisos de {{ $usuario->name }}.</p>
    </div>

    @include('partials.alerts')

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg leading-6 font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user-edit text-indigo-500"></i> Actualizar Perfil
            </h3>
            @if ($usuario->active)
                 <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-1 rounded-lg border border-emerald-200">Activo</span>
            @else
                 <span class="bg-gray-100 text-gray-800 text-xs font-bold px-2.5 py-1 rounded-lg border border-gray-200">Inactivo</span>
            @endif
        </div>

        <div class="p-6 sm:p-8">
            <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @php $userRole = strtolower(old('role', $usuario->role)); @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-bold text-gray-700 mb-1 lg:max-w-md">Nombre Completo <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" required value="{{ old('name', $usuario->name) }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50" placeholder="Ej. Juan Pérez">
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-1 lg:max-w-md">Correo Electrónico <span class="text-rose-500">*</span></label>
                        <input type="email" id="email" name="email" required value="{{ old('email', $usuario->email) }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50" placeholder="Ej. juan@agencia.com">
                        @error('email')
                            <p class="mt-1 text-xs text-rose-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-bold text-gray-700 mb-1">Rol Operativo <span class="text-rose-500">*</span></label>
                        <select id="role" name="role" required class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50">
                            <option value="">-- Selecciona un rol --</option>
                            <option value="admin" {{ $userRole === 'admin' || $userRole === 'administrador' ? 'selected' : '' }}>Administrador (Control total)</option>
                            <option value="documentador" {{ $userRole === 'documentador' ? 'selected' : '' }}>Documentador (Captura Operaciones)</option>
                            <option value="trafico" {{ $userRole === 'trafico' ? 'selected' : '' }}>Tráfico / Operativo</option>
                            <option value="cliente" {{ $userRole === 'cliente' || $userRole === 'clienteadmin' ? 'selected' : '' }}>Cliente (Solo vista)</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-xs text-rose-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="cliente-select-container" style="{{ $userRole === 'cliente' || $userRole === 'clienteadmin' ? 'display:block;' : 'display:none;' }}">
                        <label for="cliente_id" class="block text-sm font-bold text-gray-700 mb-1">Empresa del Cliente <span class="text-rose-500">*</span></label>
                        <select id="cliente_id" name="cliente_id" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50">
                            <option value="">-- Selecciona a qué cliente pertenece --</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('cliente_id', $usuario->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="password" class="block text-sm font-bold text-gray-700 mb-1 lg:max-w-md">Contraseña</label>
                        <input type="password" id="password" name="password" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-100 text-gray-500 cursor-not-allowed" readonly placeholder="••••••••">
                        <p class="text-xs text-gray-400 mt-1"><i class="fas fa-info-circle"></i> La contraseña encriptada no puede verse en texto plano por seguridad.</p>
                        @error('password')
                            <p class="mt-1 text-xs text-rose-600 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @php $currentPermisos = is_array($usuario->permisos) ? $usuario->permisos : []; @endphp
                <div id="permisos-container" style="{{ in_array($userRole, ['documentador', 'trafico']) ? 'display:block;' : 'display:none;' }}" class="mt-6 border-t border-gray-100 pt-6">
                    <h4 class="text-sm font-bold text-gray-800 mb-4"><i class="fas fa-shield-alt text-indigo-500 mr-2"></i> Permisos Adicionales</h4>
                    <p class="text-xs text-gray-500 mb-4">Selecciona las secciones de configuración a las que este usuario tendrá acceso:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($allPermisos as $key => $label)
                            @if(in_array($key, $permisosHabilitados))
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="permisos[]" value="{{ $key }}" {{ in_array($key, $currentPermisos) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                            </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex items-center justify-end gap-3 mt-8">
                    <a href="{{ route('usuarios.index') }}" class="inline-flex justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('role');
    const clienteContainer = document.getElementById('cliente-select-container');
    const clienteSelect = document.getElementById('cliente_id');

    const permisosContainer = document.getElementById('permisos-container');

    function toggleFields() {
        const value = roleSelect.value;
        if (value === 'cliente' || value === 'ClienteAdmin' || value === 'Cliente' || value === 'clienteadmin') {
            clienteContainer.style.display = 'block';
            clienteSelect.setAttribute('required', 'required');
        } else {
            clienteContainer.style.display = 'none';
            if(!clienteSelect.value && !clienteSelect.hasAttribute('value')) {
                clienteSelect.value = '';
            }
            clienteSelect.removeAttribute('required');
        }

        if (value === 'documentador' || value === 'trafico') {
            permisosContainer.style.display = 'block';
        } else {
            permisosContainer.style.display = 'none';
            const checkboxes = permisosContainer.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = false);
        }
    }

    roleSelect.addEventListener('change', toggleFields);
    toggleFields();
});
</script>
@endsection
