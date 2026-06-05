@extends('layouts.admin')

@section('header_title', 'Add-ons Contratados')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('admin.suscripciones.dashboard') }}" class="text-indigo-600 font-medium"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="mb-4 flex gap-3 flex-wrap">
    <a href="{{ route('admin.suscripciones.addons.contratados') }}" class="px-3 py-1 rounded-full text-xs font-bold {{ !request('estado') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">Todos</a>
    <a href="{{ route('admin.suscripciones.addons.contratados', ['estado' => 'pendiente_pago']) }}" class="px-3 py-1 rounded-full text-xs font-bold {{ request('estado') === 'pendiente_pago' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600' }}">Pendiente Pago</a>
    <a href="{{ route('admin.suscripciones.addons.contratados', ['estado' => 'activo']) }}" class="px-3 py-1 rounded-full text-xs font-bold {{ request('estado') === 'activo' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }}">Activos</a>
    <a href="{{ route('admin.suscripciones.addons.contratados', ['estado' => 'vencido']) }}" class="px-3 py-1 rounded-full text-xs font-bold {{ request('estado') === 'vencido' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600' }}">Vencidos</a>
</div>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr>
            <th class="px-4 py-3">Tenant</th><th class="px-4 py-3">Add-on</th><th class="px-4 py-3">Tipo</th><th class="px-4 py-3">Monto</th><th class="px-4 py-3">Referencia</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Vigencia</th><th class="px-4 py-3">Acciones</th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($contratados as $ac)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-bold">{{ $ac->tenant->nombre_empresa }}</td>
                <td class="px-4 py-3">{{ $ac->addon->nombre }}</td>
                <td class="px-4 py-3"><span class="text-xs bg-{{ $ac->addon->tipo_color }}-50 text-{{ $ac->addon->tipo_color }}-700 px-2 py-0.5 rounded-full font-bold">{{ $ac->addon->tipo_label }}</span></td>
                <td class="px-4 py-3 text-green-600 font-bold">${{ number_format($ac->monto_total, 2) }}</td>
                <td class="px-4 py-3 font-mono text-xs">{{ $ac->referencia_pago }}</td>
                <td class="px-4 py-3">
                    @php
                        $estadoColors = [
                            'pendiente_pago' => 'bg-amber-100 text-amber-700',
                            'activo' => 'bg-emerald-100 text-emerald-700',
                            'vencido' => 'bg-red-100 text-red-700',
                            'rechazado' => 'bg-gray-100 text-gray-700',
                        ];
                    @endphp
                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ $estadoColors[$ac->estado] ?? 'bg-gray-100' }}">{{ ucfirst($ac->estado) }}</span>
                </td>
                <td class="px-4 py-3 text-xs">
                    @if($ac->fecha_fin)
                        {{ $ac->fecha_fin->format('d/m/Y') }}
                        @if($ac->estaActivo())
                            <span class="text-gray-400">({{ $ac->diasRestantes() }}d)</span>
                        @endif
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($ac->estado === 'pendiente_pago')
                        <form method="POST" action="{{ route('admin.suscripciones.addons.aprobar', $ac->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-emerald-600 hover:text-emerald-800 font-bold text-xs mr-2" onclick="return confirm('¿Aprobar este pago?')"><i class="fas fa-check"></i> Aprobar</button>
                        </form>
                        <form method="POST" action="{{ route('admin.suscripciones.addons.rechazar', $ac->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-xs" onclick="return confirm('¿Rechazar este pago?')"><i class="fas fa-times"></i> Rechazar</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No hay add-ons contratados</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $contratados->links() }}</div>
@endsection
