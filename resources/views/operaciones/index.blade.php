@extends('layouts.app')

@section('title', 'Gestión de Operaciones | NexaCore')

@section('customcss')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    .dark .glass-card {
        background: rgba(31, 41, 55, 0.8);
        border-color: rgba(75, 85, 99, 0.3);
    }
    .priority-urgente { border-left: 4px solid #f43f5e; }
    .priority-alta { border-left: 4px solid #f59e0b; }
    .priority-media { border-left: 4px solid #3b82f6; }
    .priority-regular { border-left: 4px solid #10b981; }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300 pb-12">
    
    <!-- 1. Header & Filters -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        Control <span class="text-indigo-600 dark:text-indigo-400">Operativo</span>
                    </h1>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-1 font-medium">Gestión integral de operaciones por Tenant</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <form action="{{ route('operaciones.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                        <div class="relative group">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
                            <input type="text" name="busqueda" value="{{ request('busqueda') }}" placeholder="Referencia, Factura, Cliente..." 
                                class="pl-11 pr-4 py-2.5 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm font-bold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all w-full md:w-64">
                        </div>
                        <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="py-2.5 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm font-bold focus:ring-indigo-500">
                        <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="py-2.5 rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm font-bold focus:ring-indigo-500">
                        
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-black text-xs shadow-lg hover:bg-indigo-700 transition-all transform hover:scale-105">
                            FILTRAR
                        </button>
                        
                        @if(request()->anyFilled(['busqueda', 'fecha_inicio', 'fecha_fin']))
                            <a href="{{ route('operaciones.index') }}" class="p-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-500 hover:text-rose-500 transition-colors">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- 2. KPIs & Top Users Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- KPIs Generales -->
            <div class="lg:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Hoy</div>
                    <div class="text-3xl font-black text-gray-800 dark:text-white">{{ $totalHoy }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm border-l-4 border-l-amber-500">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pendientes</div>
                    <div class="text-3xl font-black text-amber-500">{{ $pendientesHoy }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm border-l-4 border-l-blue-500">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">En Proceso</div>
                    <div class="text-3xl font-black text-blue-500">{{ $procesoHoy }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm border-l-4 border-l-emerald-500">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Terminados</div>
                    <div class="text-3xl font-black text-emerald-500">{{ $completadosHoy }}</div>
                </div>
            </div>

            <!-- Top Contribuidores -->
            <div class="bg-indigo-600 rounded-2xl p-5 shadow-xl shadow-indigo-200 dark:shadow-none relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 text-white/10">
                    <i class="fas fa-trophy text-6xl rotate-12"></i>
                </div>
                <h3 class="text-xs font-black text-white/80 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-medal"></i> Líderes Operativos (Hoy)
                </h3>
                <div class="space-y-3 relative z-10">
                    @foreach($topRegistradores->take(2) as $reg)
                        <div class="flex items-center justify-between text-white">
                            <span class="text-xs font-bold">{{ $reg->usuarioRegistro->name }}</span>
                            <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded-full font-black">REG: {{ $reg->total }}</span>
                        </div>
                    @endforeach
                    @foreach($topCerradores->take(1) as $cerr)
                        <div class="flex items-center justify-between text-white">
                            <span class="text-xs font-bold">{{ $cerr->usuarioCierre->name }}</span>
                            <span class="text-[10px] bg-emerald-400/30 px-2 py-0.5 rounded-full font-black">CIE: {{ $cerr->total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 3. Main Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden min-h-[500px]">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-700/30 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <th class="px-6 py-4">Operación</th>
                            <th class="px-6 py-4">Cliente / Producto</th>
                            <th class="px-6 py-4">Fecha Cruce</th>
                            <th class="px-6 py-4">Prioridad</th>
                            <th class="px-6 py-4 text-center">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($operaciones as $op)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors group priority-{{ $op->prioridad }}">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-gray-800 dark:text-white tracking-tight">{{ $op->referencia }}</span>
                                        <span class="text-[11px] font-bold text-gray-400 uppercase">Fact: {{ $op->num_factura }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase">{{ $op->cliente->nombre ?? 'N/A' }}</span>
                                        <span class="text-xs font-medium text-gray-500 truncate max-w-[200px]">{{ $op->nombre_producto }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <i class="far fa-calendar text-gray-300"></i>
                                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">{{ $op->fecha_cruce_estimada ? $op->fecha_cruce_estimada->format('d/m/Y') : 'Pendiente' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <select onchange="updatePriority({{ $op->id }}, this.value)" 
                                        class="text-[10px] font-black uppercase rounded-lg border-transparent focus:border-indigo-500 focus:ring-0 bg-transparent cursor-pointer 
                                        {{ $op->prioridad == 'urgente' ? 'text-rose-600' : ($op->prioridad == 'alta' ? 'text-amber-600' : ($op->prioridad == 'media' ? 'text-blue-600' : 'text-emerald-600')) }}">
                                        <option value="regular" {{ $op->prioridad == 'regular' ? 'selected' : '' }}>Regular</option>
                                        <option value="media" {{ $op->prioridad == 'media' ? 'selected' : '' }}>Media</option>
                                        <option value="alta" {{ $op->prioridad == 'alta' ? 'selected' : '' }}>Alta</option>
                                        <option value="urgente" {{ $op->prioridad == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statusClass = [
                                            'pendiente' => 'bg-amber-100 text-amber-700 border-amber-200',
                                            'proceso' => 'bg-blue-100 text-blue-700 border-blue-200',
                                            'terminado' => 'bg-emerald-100 text-emerald-700 border-emerald-200'
                                        ][$op->estado] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase border {{ $statusClass }}">
                                        {{ $op->estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('operaciones.edit', $op->id) }}" class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors shadow-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteOperacion({{ $op->id }})" class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-500 hover:text-rose-600 transition-colors shadow-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center text-gray-300 mb-4">
                                            <i class="fas fa-box-open text-2xl"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-400">No se encontraron operaciones con los filtros aplicados.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $operaciones->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function updatePriority(id, priority) {
        fetch(`/operaciones/${id}/update-priority`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ prioridad: priority })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Prioridad actualizada'
                });
                // Actualizar estilo visual dinámicamente si es necesario
                window.location.reload(); // Recarga simple para actualizar bordes de prioridad
            }
        });
    }

    function deleteOperacion(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "La operación se moverá a la papelera (Soft Delete)",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'rounded-3xl dark:bg-gray-800 dark:text-white',
                confirmButton: 'rounded-xl font-black px-6 py-2.5',
                cancelButton: 'rounded-xl font-black px-6 py-2.5'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/operaciones/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: data.message,
                            customClass: { popup: 'rounded-3xl' }
                        }).then(() => window.location.reload());
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection
