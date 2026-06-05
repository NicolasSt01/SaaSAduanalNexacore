@extends('layouts.admin')

@section('header_title', 'Gestión de Suscripciones')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('admin.suscripciones.dashboard') }}" class="text-indigo-600 font-medium"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="mb-4 flex gap-3 flex-wrap">
    <a href="{{ route('admin.suscripciones.index') }}" class="px-3 py-1 rounded-full text-xs font-bold {{ !request('estado') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">Todas</a>
    <a href="{{ route('admin.suscripciones.index', ['estado' => 'pendiente_pago']) }}" class="px-3 py-1 rounded-full text-xs font-bold {{ request('estado') === 'pendiente_pago' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600' }}">Pendiente Pago</a>
    <a href="{{ route('admin.suscripciones.index', ['estado' => 'pendiente_aprobacion']) }}" class="px-3 py-1 rounded-full text-xs font-bold {{ request('estado') === 'pendiente_aprobacion' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600' }}">Pendiente Aprobación</a>
    <a href="{{ route('admin.suscripciones.index', ['estado' => 'activa']) }}" class="px-3 py-1 rounded-full text-xs font-bold {{ request('estado') === 'activa' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }}">Activas</a>
    <a href="{{ route('admin.suscripciones.index', ['estado' => 'vencida']) }}" class="px-3 py-1 rounded-full text-xs font-bold {{ request('estado') === 'vencida' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600' }}">Vencidas</a>
    <a href="{{ route('admin.suscripciones.index', ['estado' => 'rechazada']) }}" class="px-3 py-1 rounded-full text-xs font-bold {{ request('estado') === 'rechazada' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-600' }}">Rechazadas</a>
</div>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr>
            <th class="px-4 py-3">Tenant</th><th class="px-4 py-3">Plan</th><th class="px-4 py-3">Monto</th><th class="px-4 py-3">Referencia</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Vigencia</th><th class="px-4 py-3">Acciones</th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($suscripciones as $s)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-bold">{{ $s->tenant->nombre_empresa }}</td>
                <td class="px-4 py-3">{{ $s->plan->nombre }}</td>
                <td class="px-4 py-3 text-green-600 font-bold">${{ number_format($s->monto_total, 2) }}</td>
                <td class="px-4 py-3 font-mono text-xs">{{ $s->referencia_pago }}</td>
                <td class="px-4 py-3">
                    @php
                        $estadoColors = [
                            'pendiente_pago' => 'bg-amber-100 text-amber-700',
                            'pendiente_aprobacion' => 'bg-blue-100 text-blue-700',
                            'activa' => 'bg-emerald-100 text-emerald-700',
                            'vencida' => 'bg-red-100 text-red-700',
                            'rechazada' => 'bg-gray-100 text-gray-700',
                            'cancelada' => 'bg-gray-100 text-gray-500',
                        ];
                    @endphp
                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ $estadoColors[$s->estado] ?? 'bg-gray-100' }}">{{ ucfirst(str_replace('_', ' ', $s->estado)) }}</span>
                </td>
                <td class="px-4 py-3 text-xs">
                    @if($s->fecha_fin)
                        {{ $s->fecha_fin->format('d/m/Y') }}
                        @if($s->estaActiva())
                            <span class="text-gray-400">({{ $s->diasRestantes() }}d)</span>
                        @endif
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($s->estado === 'pendiente_aprobacion')
                        <form method="POST" action="{{ route('admin.suscripciones.aprobar', $s->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-emerald-600 hover:text-emerald-800 font-bold text-xs mr-2" onclick="return confirm('¿Aprobar este pago?')"><i class="fas fa-check"></i> Aprobar</button>
                        </form>
                        <button type="button" onclick="rechazarPago({{ $s->id }})" class="text-red-500 hover:text-red-700 font-bold text-xs"><i class="fas fa-times"></i> Rechazar</button>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No hay suscripciones</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $suscripciones->links() }}</div>

<script>
function rechazarPago(id) {
    const motivo = prompt('Motivo del rechazo:');
    if (!motivo) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/nexacore-admin/suscripciones/rechazar/${id}`;
    form.innerHTML = `@csrf<input type="hidden" name="motivo" value="${motivo}">`;
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
