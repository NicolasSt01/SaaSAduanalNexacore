@extends('layouts.admin')

@section('header_title', 'Dashboard de Suscripciones')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow p-5 text-white">
        <p class="text-emerald-100 text-xs font-bold uppercase mb-1">Suscripciones Activas</p>
        <span class="text-3xl font-black">{{ $suscripcionesActivas }}</span>
    </div>
    <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow p-5 text-white">
        <p class="text-amber-100 text-xs font-bold uppercase mb-1">Pagos Pendientes</p>
        <span class="text-3xl font-black">{{ $pagosPendientes }}</span>
    </div>
    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow p-5 text-white">
        <p class="text-indigo-100 text-xs font-bold uppercase mb-1">Ingresos del Mes</p>
        <span class="text-3xl font-black">${{ number_format($ingresosMes, 2) }}</span>
    </div>
    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow p-5 text-white">
        <p class="text-red-100 text-xs font-bold uppercase mb-1">Próximos a Vencer (7d)</p>
        <span class="text-3xl font-black">{{ $proximosVencer }}</span>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
    <div class="flex justify-between items-center mb-4">
        <h4 class="font-bold text-gray-800">Pagos Pendientes de Aprobación</h4>
        <a href="{{ route('admin.suscripciones.index', ['estado' => 'pendiente_aprobacion']) }}" class="text-indigo-600 text-sm font-bold hover:underline">Ver todos</a>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr>
            <th class="px-4 py-2">Tenant</th><th class="px-4 py-2">Plan</th><th class="px-4 py-2">Monto</th><th class="px-4 py-2">Referencia</th><th class="px-4 py-2">Acciones</th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($pendientesRecientes as $s)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-bold">{{ $s->tenant->nombre_empresa }}</td>
                <td class="px-4 py-3">{{ $s->plan->nombre }}</td>
                <td class="px-4 py-3 text-green-600 font-bold">${{ number_format($s->monto_total, 2) }}</td>
                <td class="px-4 py-3 font-mono text-xs">{{ $s->referencia_pago }}</td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.suscripciones.aprobar', $s->id) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-emerald-600 hover:text-emerald-800 font-bold text-xs"><i class="fas fa-check mr-1"></i>Aprobar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No hay pagos pendientes</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex gap-3 flex-wrap">
    <a href="{{ route('admin.suscripciones.planes') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold text-sm"><i class="fas fa-box mr-1"></i> Gestionar Planes</a>
    <a href="{{ route('admin.suscripciones.index') }}" class="bg-white border hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-bold text-sm"><i class="fas fa-list mr-1"></i> Suscripciones</a>
    <a href="{{ route('admin.suscripciones.addons') }}" class="bg-white border hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-bold text-sm"><i class="fas fa-puzzle-piece mr-1"></i> Add-ons</a>
    <a href="{{ route('admin.suscripciones.addons.contratados') }}" class="bg-white border hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-bold text-sm"><i class="fas fa-shopping-cart mr-1"></i> Add-ons Contratados</a>
    <a href="{{ route('admin.suscripciones.configuracion') }}" class="bg-white border hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-bold text-sm"><i class="fas fa-cog mr-1"></i> Configuración</a>
</div>
@endsection
