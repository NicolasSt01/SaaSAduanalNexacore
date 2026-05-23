@extends('layouts.app')

@section('title', 'Detalle Cliente')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.adminconfig') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-emerald-600 transition-colors">
                    <i class="fas fa-cog mr-2"></i> Configuración
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                    <a href="{{ route('clientes.index') }}" class="text-sm font-medium text-gray-500 hover:text-emerald-600 transition-colors">Clientes</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                    <span class="text-sm font-bold text-gray-700">{{ $cliente->nombre }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Detalle del <span class="text-emerald-600">Cliente</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Información registrada del cliente en el sistema.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.edit', $cliente->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 border border-amber-200 text-amber-600 rounded-xl font-bold text-sm hover:bg-amber-100 transition-all shadow-sm">
                <i class="fas fa-edit"></i> Editar
            </a>
            <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl font-bold text-sm hover:bg-rose-100 transition-all shadow-sm">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm">
            <div class="flex">
                <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                <div class="ml-3">
                    <p class="text-sm text-emerald-700 font-bold">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm">
            <div class="flex">
                <i class="fas fa-exclamation-circle text-rose-500 mt-0.5"></i>
                <div class="ml-3">
                    <p class="text-sm text-rose-700 font-bold">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Tarjeta Principal -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 relative overflow-hidden">
                <div class="absolute -right-12 -top-12 text-gray-50 opacity-50 pointer-events-none">
                    <i class="fas fa-building text-9xl"></i>
                </div>

                <div class="flex items-center gap-4 mb-8 relative z-10">
                    <div class="h-16 w-16 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl font-black shadow-inner">
                        {{ strtoupper(substr($cliente->nombre ?? 'C', 0, 2)) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-900">{{ $cliente->nombre }}</h2>
                        <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full border border-gray-200">
                            {{ $cliente->rfc ?? $cliente->tax_id ?? 'Sin identificador fiscal' }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">RFC</label>
                        <p class="text-sm font-bold text-gray-800">{{ $cliente->rfc ?? 'No registrado' }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Tax ID</label>
                        <p class="text-sm font-bold text-gray-800">{{ $cliente->tax_id ?? 'No registrado' }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Correo Electrónico</label>
                        <p class="text-sm font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-envelope text-gray-400 text-xs"></i>
                            <a href="mailto:{{ $cliente->correo }}" class="text-indigo-600 hover:text-indigo-800 transition-colors">{{ $cliente->correo }}</a>
                        </p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Teléfono</label>
                        <p class="text-sm font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-phone text-gray-400 text-xs"></i>
                            {{ $cliente->telefono ?? 'No registrado' }}
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Dirección</label>
                        <p class="text-sm font-bold text-gray-800 flex items-start gap-2">
                            <i class="fas fa-map-marker-alt text-gray-400 text-xs mt-0.5"></i>
                            {{ $cliente->direccion ?? 'No registrada' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- ==================== SECCIÓN: DOCUMENTACIÓN LEGAL ART. 36-A ==================== --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 relative overflow-hidden">
                <div class="absolute -right-12 -top-12 text-indigo-50 opacity-50 pointer-events-none">
                    <i class="fas fa-file-contract text-9xl"></i>
                </div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 relative z-10">
                    <div>
                        <h3 class="text-xl font-black text-gray-800 flex items-center gap-2">
                            <i class="fas fa-shield-alt text-indigo-600"></i> Documentación Legal
                        </h3>
                        <p class="text-xs text-gray-500 mt-1 font-medium">Art. 36-A L.A. — Documentos del Expediente Maestro</p>
                    </div>
                    @php
                        $totalMaestro = count($maestroDocs);
                        $cargados = $cliente->documentosMaestros->count();
                        $porcentaje = $totalMaestro > 0 ? round(($cargados / $totalMaestro) * 100) : 0;
                        $colorBarra = $porcentaje == 100 ? 'bg-emerald-500' : ($porcentaje >= 60 ? 'bg-amber-500' : 'bg-rose-500');
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <span class="text-2xl font-black {{ $porcentaje == 100 ? 'text-emerald-600' : 'text-rose-500' }}">{{ $cargados }}/{{ $totalMaestro }}</span>
                            <span class="text-xs text-gray-400 block">documentos</span>
                        </div>
                        <div class="w-24 bg-gray-100 rounded-full h-2.5 overflow-hidden">
                            <div class="h-full {{ $colorBarra }} rounded-full transition-all duration-500" style="width: {{ $porcentaje }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 relative z-10">
                    @foreach($maestroDocs as $clave => $etiqueta)
                        @php
                            $doc = $cliente->documentosMaestros->firstWhere('tipo_documento', $clave);
                            $esCSF = $clave === 'rfc';
                            $diasRestantes = null;
                            $csfVencida = false;
                            if ($esCSF && $doc && $doc->fecha_vencimiento) {
                                $diasRestantes = (int) now()->startOfDay()->diffInDays($doc->fecha_vencimiento, false);
                                $csfVencida = $diasRestantes < 0;
                            }
                        @endphp
                        <div class="flex items-center gap-4 p-4 rounded-2xl border {{ $doc ? ($csfVencida ? 'border-rose-200 bg-rose-50/50' : 'border-emerald-200 bg-emerald-50/30') : 'border-gray-100 bg-gray-50/50' }} transition-all hover:shadow-sm">
                            {{-- Icono de estado --}}
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $doc ? ($csfVencida ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600') : 'bg-gray-200 text-gray-400' }}">
                                @if($doc && !$csfVencida)
                                    <i class="fas fa-check-circle"></i>
                                @elseif($doc && $csfVencida)
                                    <i class="fas fa-exclamation-triangle"></i>
                                @else
                                    <i class="fas fa-times-circle"></i>
                                @endif
                            </div>

                            {{-- Info del documento --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-800 truncate">{{ $etiqueta }}</p>
                                @if($doc)
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[10px] text-gray-500 font-bold uppercase">
                                            {{ $doc->nombre }}.{{ $doc->extension }}
                                        </span>
                                        <span class="text-[10px] text-gray-400">·</span>
                                        <span class="text-[10px] text-gray-400">{{ $doc->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    @if($esCSF && $doc->fecha_vencimiento)
                                        <div class="mt-1.5">
                                            @if($csfVencida)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 border border-rose-200">
                                                    <i class="fas fa-exclamation-circle"></i> VENCIDA ({{ abs($diasRestantes) }} días)
                                                </span>
                                            @elseif($diasRestantes <= 5)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-700 border border-amber-200">
                                                    <i class="fas fa-clock"></i> Vence en {{ $diasRestantes }} día(s)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                    <i class="fas fa-shield-alt"></i> Vigente {{ $diasRestantes }} día(s) · vence {{ $doc->fecha_vencimiento->format('d/m/Y') }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <p class="text-[10px] text-gray-400 font-bold uppercase mt-0.5">No cargado</p>
                                @endif
                            </div>

                            {{-- Acciones --}}
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($doc)
                                    <a href="{{ route('documentos.preview', $doc->id) }}" target="_blank" title="Vista Previa" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-colors">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('documentos.download', $doc->id) }}" title="Descargar" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors">
                                        <i class="fas fa-download text-xs"></i>
                                    </a>
                                    <form action="{{ route('clientes.eliminarDocumento', ['cliente' => $cliente->id, 'documento' => $doc->id]) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar {{ $etiqueta }} de este cliente?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Eliminar" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center hover:bg-rose-100 transition-colors">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                                <button type="button" onclick="document.getElementById('upload_{{ $clave }}').click()" title="Subir {{ $etiqueta }}" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors">
                                    <i class="fas fa-upload text-xs"></i>
                                </button>
                                <form action="{{ route('clientes.subirDocumento', $cliente->id) }}" method="POST" enctype="multipart/form-data" class="hidden">
                                    @csrf
                                    <input type="hidden" name="tipo_documento" value="{{ $clave }}">
                                    <input type="file" id="upload_{{ $clave }}" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="this.form.submit()" class="hidden">
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($porcentaje < 100)
                    <div class="mt-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 relative z-10">
                        <p class="text-xs text-amber-700 font-bold flex items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            Faltan {{ $totalMaestro - $cargados }} documento(s) por cargar. Los expedientes de este cliente mostrarán alerta hasta completar esta documentación.
                        </p>
                    </div>
                @else
                    <div class="mt-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 relative z-10">
                        <p class="text-xs text-emerald-700 font-bold flex items-center gap-2">
                            <i class="fas fa-check-circle"></i>
                            Documentación completa. Los expedientes de este cliente pasarán automáticamente la sección "Expediente Maestro" del checklist Art. 36-A.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar: Acciones rápidas -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-black text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-bolt text-amber-500"></i> Acciones Rápidas
                </h3>
                <div class="space-y-2">
                    <a href="{{ route('clientes.edit', $cliente->id) }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-amber-50 transition-colors group">
                        <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                            <i class="fas fa-edit text-sm"></i>
                        </div>
                        <span class="text-sm font-bold text-gray-700 group-hover:text-amber-700">Editar Cliente</span>
                    </a>
                    <a href="{{ route('clientes.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                        <div class="w-9 h-9 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-gray-200 transition-colors">
                            <i class="fas fa-arrow-left text-sm"></i>
                        </div>
                        <span class="text-sm font-bold text-gray-600 group-hover:text-gray-800">Volver al Directorio</span>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-black text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-pie text-indigo-500"></i> Cumplimiento Art. 36-A
                </h3>
                <div class="flex items-center gap-4 mb-3">
                    <div class="relative w-16 h-16">
                        <svg class="w-16 h-16 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke="#e5e7eb" stroke-width="4"></circle>
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke="{{ $porcentaje == 100 ? '#10b981' : '#f59e0b' }}" stroke-width="4" stroke-dasharray="{{ $porcentaje }}, 100" stroke-linecap="round"></circle>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-sm font-black {{ $porcentaje == 100 ? 'text-emerald-600' : 'text-amber-500' }}">{{ $porcentaje }}%</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-600">
                            @if($porcentaje == 100)
                                <span class="text-emerald-600">Completo</span>
                            @elseif($porcentaje >= 60)
                                <span class="text-amber-600">Parcial</span>
                            @else
                                <span class="text-rose-600">Insuficiente</span>
                            @endif
                        </p>
                        <p class="text-[10px] text-gray-400 font-medium mt-0.5">Expediente Maestro</p>
                    </div>
                </div>
                <p class="text-[10px] text-gray-400 leading-relaxed">Estos documentos son compartidos por todos los expedientes de este cliente. Solo se suben una vez.</p>
            </div>

            <div class="bg-emerald-50 rounded-3xl border border-emerald-100 p-6">
                <h3 class="text-sm font-black text-emerald-800 mb-2 flex items-center gap-2">
                    <i class="fas fa-info-circle"></i> Identificador
                </h3>
                <p class="text-xs text-emerald-700 font-medium">ID interno: <span class="font-black">#{{ $cliente->id }}</span></p>
            </div>
        </div>
    </div>
</div>
@endsection
