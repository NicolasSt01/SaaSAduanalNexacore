@extends('layouts.admin')
@section('header_title', 'Facturas')
@section('content')

<div class="mb-6">
    <a href="{{ route('admin.finanzas.dashboard') }}" class="text-indigo-600 font-medium"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr>
            <th class="px-4 py-3">Folio</th><th class="px-4 py-3">Tenant</th><th class="px-4 py-3">Periodo</th><th class="px-4 py-3">Monto</th><th class="px-4 py-3">Fecha</th><th class="px-4 py-3"></th>
        </tr></thead>
        <tbody class="divide-y">
            @forelse($facturas as $f)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-mono text-xs">{{ $f->folio }}</td>
                <td class="px-4 py-3 font-bold">{{ $f->tenant?->nombre_empresa }}</td>
                <td class="px-4 py-3">{{ $f->periodo }}</td>
                <td class="px-4 py-3 text-green-600 font-bold">${{ number_format($f->monto, 2) }}</td>
                <td class="px-4 py-3">{{ $f->created_at->format('d/m/Y') }}</td>
                <td class="px-4 py-3"><a href="{{ route('admin.finanzas.facturas.descargar', $f->id) }}" class="text-indigo-600 hover:underline font-bold text-xs"><i class="fas fa-download mr-1"></i> PDF</a></td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No hay facturas generadas</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $facturas->links() }}</div>
@endsection
