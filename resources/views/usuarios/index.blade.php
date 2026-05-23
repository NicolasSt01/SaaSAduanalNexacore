@extends('layouts.app')

@section('title', 'Directorio de Usuarios')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.config') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">
                            <i class="fas fa-cog mr-2"></i> Configuración
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                            <span class="text-sm font-medium text-gray-700">Usuarios</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Directorio de <span class="text-indigo-600">Usuarios</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Administra el talento y los accesos operativos (Límite: {{ auth()->user()->tenant->max_usuarios ?? 'N/A' }} usuarios).</p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('usuarios.create') }}" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <i class="fas fa-user-plus mr-2"></i> Nuevo Usuario
            </a>
        </div>
    </div>

    @include('partials.alerts')

    <!-- Content Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex-1">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Usuario / Email</th>
                        <th class="px-6 py-4">Rol</th>
                        <th class="px-6 py-4">Cliente Asignado</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    @forelse ($usuarios as $usuario)
                    <tr class="hover:bg-indigo-50/30 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg mr-3 shadow-inner">
                                    {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800">{{ $usuario->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $usuario->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($usuario->role === 'admin')
                                <span class="bg-indigo-100 text-indigo-700 font-bold px-3 py-1 rounded-lg text-xs tracking-wider border border-indigo-200">ADMIN</span>
                            @elseif($usuario->role === 'documentador')
                                <span class="bg-blue-100 text-blue-700 font-bold px-3 py-1 rounded-lg text-xs tracking-wider border border-blue-200">DOCUMENTADOR</span>
                            @elseif($usuario->role === 'cliente')
                                <span class="bg-emerald-100 text-emerald-700 font-bold px-3 py-1 rounded-lg text-xs tracking-wider border border-emerald-200">CLIENTE</span>
                            @else
                                <span class="bg-gray-100 text-gray-700 font-bold px-3 py-1 rounded-lg text-xs tracking-wider border border-gray-200">{{ strtoupper($usuario->role) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($usuario->role === 'cliente')
                                <div class="font-medium text-gray-700">{{ $usuario->cliente->nombre ?? 'Sin Asignar' }}</div>
                            @else
                                <span class="text-gray-400 text-xs italic">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($usuario->active)
                                <div class="flex items-center">
                                    <div class="h-2.5 w-2.5 rounded-full bg-emerald-500 mr-2 shadow-[0_0_5px_rgba(16,185,129,0.5)]"></div>
                                    <span class="font-bold text-gray-700">Activo</span>
                                </div>
                            @else
                                <div class="flex items-center">
                                    <div class="h-2.5 w-2.5 rounded-full bg-gray-400 mr-2"></div>
                                    <span class="font-bold text-gray-500">Inactivo</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('usuarios.edit', $usuario->id) }}" class="text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white border border-indigo-200 p-2 rounded-lg shadow-sm transition transform hover:scale-105" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                @if ($usuario->active)
                                    <form action="{{ route('usuarios.desactivar', $usuario->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Deseas dar de baja a este usuario? Ya no podrá acceder al sistema.');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white border border-rose-200 p-2 rounded-lg shadow-sm transition transform hover:scale-105" title="Dar de baja">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-users-slash text-5xl mb-3 block opacity-50"></i>
                            <p class="font-medium text-lg text-gray-500">No hay usuarios registrados.</p>
                            <p class="text-sm mt-1">Comienza agregando talento a tu equipo.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
