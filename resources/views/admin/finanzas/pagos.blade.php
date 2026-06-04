@extends('layouts.admin')
@section('header_title', 'Gestión de Pagos')
@section('content')

<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('admin.finanzas.dashboard') }}" class="text-indigo-600 font-medium"><i class="fas fa-arrow-left"></i> Volver</a>
    <button onclick="document.getElementById('formPago').classList.toggle('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold text-sm"><i class="fas fa-plus mr-1"></i> Registrar Pago</button>
</div>

<div id="formPago" class="hidden bg-gray-50 border rounded-xl p-4 mb-6">
    <form method="POST" action="{{ route('admin.finanzas.pagos.store') }}">
        @csrf
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <select name="tenant_id" required class="rounded-lg border-gray-300 text-sm px-3 py-2">
                <option value="">Seleccionar Agencia</option>
                @foreach($tenants as $t)
                <option value="{{ $t->id }}">{{ $t->nombre_empresa }}</option>
                @endforeach
            </select>
            <input type="number" name="monto" placeholder="Monto" step="0.01" min="0.01" required class="rounded-lg border-gray-300 text-sm px-3 py-2">
            <input type="date" name="fecha_pago" value="{{ now()->format('Y-m-d') }}" required class="rounded-lg border-gray-300 text-sm px-3 py-2">
            <select name="metodo" required class="rounded-lg border-gray-300 text-sm px-3 py-2">
                <option value="transferencia">Transferencia</option>
                <option value="efectivo">Efectivo</option>
                <option value="cheque">Cheque</option>
                <option value="otro">Otro</option>
            </select>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3">
            <input type="text" name="periodo_inicio" placeholder="Periodo inicio (ej. 2026-06)" class="rounded-lg border-gray-300 text-sm px-3 py-2">
            <input type="text" name="periodo_fin" placeholder="Periodo fin (ej. 2026-06)" class="rounded-lg border-gray-300 text-sm px-3 py-2">
            <input type="text" name="notas" placeholder="Notas" class="rounded-lg border-gray-300 text-sm px-3 py-2">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-2 rounded-lg text-sm"><i class="fas fa-save mr-1"></i> Registrar Pago</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr>
            <th class="px-4 py-3">Tenant</th><th class="px-4 py-3">Monto</th><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Método</th><th class="px-4 py-3">Notas</th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($pagos as $p)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-bold">{{ $p->tenant?->nombre_empresa }}</td>
                <td class="px-4 py-3 text-green-600 font-bold">${{ number_format($p->monto, 2) }}</td>
                <td class="px-4 py-3">{{ $p->fecha_pago->format('d/m/Y') }}</td>
                <td class="px-4 py-3">{{ $p->metodo }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $p->notas }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No hay pagos registrados</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $pagos->links() }}</div>
@endsection
