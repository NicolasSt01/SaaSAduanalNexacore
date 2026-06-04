@extends('layouts.admin')

@section('header_title', 'Dashboard Financiero')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow p-5 text-white">
        <p class="text-green-100 text-xs font-bold uppercase mb-1">Ingresos del Mes</p>
        <span class="text-3xl font-black">${{ number_format($ingresosMes, 2) }}</span>
    </div>
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow p-5 text-white">
        <p class="text-blue-100 text-xs font-bold uppercase mb-1">Tenants Activos</p>
        <span class="text-3xl font-black">{{ $tenantsActivos }}</span>
    </div>
    <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow p-5 text-white">
        <p class="text-amber-100 text-xs font-bold uppercase mb-1">Morosos</p>
        <span class="text-3xl font-black">{{ $tenantsMorosos }}</span>
    </div>
    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow p-5 text-white">
        <p class="text-red-100 text-xs font-bold uppercase mb-1">Suspendidos</p>
        <span class="text-3xl font-black">{{ $tenantsSuspendidos }}</span>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h4 class="font-bold text-gray-800 mb-2">Próximos Vencimientos (7 días)</h4>
        <span class="text-2xl font-black text-amber-600">{{ $proximosVencer }}</span>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h4 class="font-bold text-gray-800 mb-2">Total Facturado (Mes)</h4>
        <span class="text-2xl font-black text-indigo-600">${{ number_format($totalFacturado, 2) }}</span>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
    <h4 class="font-bold text-gray-800 mb-4">Últimos Pagos</h4>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr>
            <th class="px-4 py-2">Tenant</th><th class="px-4 py-2">Monto</th><th class="px-4 py-2">Fecha</th><th class="px-4 py-2">Método</th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($pagosRecientes as $p)
            <tr>
                <td class="px-4 py-2">{{ $p->tenant?->nombre_empresa }}</td>
                <td class="px-4 py-2 font-bold">${{ number_format($p->monto, 2) }}</td>
                <td class="px-4 py-2">{{ $p->fecha_pago->format('d/m/Y') }}</td>
                <td class="px-4 py-2">{{ $p->metodo }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-4 text-center text-gray-400">Sin pagos aún</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex gap-3">
    <a href="{{ route('admin.finanzas.pagos') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold text-sm"><i class="fas fa-money-bill mr-1"></i> Gestionar Pagos</a>
    <a href="{{ route('admin.finanzas.planes') }}" class="bg-white border hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-bold text-sm"><i class="fas fa-box mr-1"></i> Planes</a>
    <a href="{{ route('admin.finanzas.facturas') }}" class="bg-white border hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-bold text-sm"><i class="fas fa-file-invoice mr-1"></i> Facturas</a>
</div>
@endsection
