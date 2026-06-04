@extends('layouts.admin')

@section('header_title', 'Administración de Agencias (Tenants)')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h3 class="text-2xl text-gray-800 font-bold border-b-2 border-indigo-500 pb-2 inline-block">Agencias Registradas
    </h3>
    <a href="{{ route('admin.tenants.create') }}"
        class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg shadow-md transition flex items-center gap-2 font-medium">
        <i class="fas fa-plus"></i> Nueva Agencia
    </a>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap">
            <thead
                class="bg-gray-50 border-b border-gray-200 text-left text-xs uppercase tracking-wider text-gray-500 font-semibold">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">Agencia / Empresa</th>
                    <th class="px-6 py-4">Subdominio</th>
                    <th class="px-6 py-4">Plan SaaS</th>
                    <th class="px-6 py-4 text-center">Usuarios</th>
                    <th class="px-6 py-4 text-center">Fecha de Corte</th>
                    <th class="px-6 py-4 text-center">Estado</th>
                    <th class="px-6 py-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($tenants as $tenant)
                <tr class="hover:bg-indigo-50 transition duration-150">
                    <td class="px-6 py-4 text-gray-500 font-mono">{{ $tenant->id }}</td>
                    <td class="px-6 py-4 font-medium text-gray-800 flex items-center gap-3">
                        <div
                            class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                            {{ substr($tenant->nombre_empresa, 0, 1) }}
                        </div>
                        {{ $tenant->nombre_empresa }}
                    </td>
                    <td class="px-6 py-4">
                        <span
                            class="bg-gray-100 text-gray-600 px-2 py-1 rounded font-mono text-xs border border-gray-200">
                            {{ $tenant->slug }}.nexacore.com.mx
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $tenant->plan === 'enterprise' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ strtoupper($tenant->plan) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center text-gray-600 font-medium">
                        {{ $tenant->users_count }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($tenant->fecha_vencimiento)
                            @php
                                $diasRestantes = now()->startOfDay()->diffInDays($tenant->fecha_vencimiento, false);
                            @endphp
                            <div class="text-xs">
                                <span class="font-bold text-gray-700 block">{{ $tenant->fecha_vencimiento->format('d/m/Y') }}</span>
                                @if($diasRestantes < 0)
                                    <span class="text-red-600 font-bold">Vencido ({{ abs($diasRestantes) }}d)</span>
                                @elseif($diasRestantes <= 3)
                                    <span class="text-red-600 font-bold">{{ $diasRestantes }}d restantes</span>
                                @elseif($diasRestantes <= 7)
                                    <span class="text-amber-600 font-bold">{{ $diasRestantes }}d restantes</span>
                                @else
                                    <span class="text-green-600">{{ $diasRestantes }}d restantes</span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400 text-xs">Sin fecha</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($tenant->estado === 'activo')
                        <span
                            class="inline-flex items-center gap-1 text-green-600 text-xs font-semibold bg-green-50 px-2.5 py-1 rounded-full border border-green-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo
                        </span>
                        @elseif($tenant->estado === 'suspendido')
                        <span
                            class="inline-flex items-center gap-1 text-amber-600 text-xs font-semibold bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Suspendido
                        </span>
                        @else
                        <span
                            class="inline-flex items-center gap-1 text-red-600 text-xs font-semibold bg-red-50 px-2.5 py-1 rounded-full border border-red-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactivo
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.tenants.show', $tenant->id) }}"
                                class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition"
                                title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.tenants.edit', $tenant->id) }}"
                                class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-lg transition"
                                title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-building text-gray-300 text-4xl mb-3"></i>
                            <p class="text-lg">No hay agencias registradas aún.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tenants->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $tenants->links() }}
    </div>
    @endif
</div>
@endsection