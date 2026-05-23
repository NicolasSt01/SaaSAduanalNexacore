@extends('layouts.app')

@section('title', 'Expediente: ' . $expediente->numero_pedimento)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <h1 class="text-3xl font-black text-gray-800 tracking-tight">Expediente: <span class="text-indigo-600">{{ $expediente->numero_pedimento }}</span></h1>
                @php
                    $statusClasses = [
                        'Abierto' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'En proceso' => 'bg-amber-100 text-amber-700 border-amber-200',
                        'Cerrado' => 'bg-gray-100 text-gray-700 border-gray-200',
                        'Cancelado' => 'bg-rose-100 text-rose-700 border-rose-200',
                    ];
                    $statusClass = $statusClasses[$expediente->estado] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest border {{ $statusClass }}">
                    {{ $expediente->estado }}
                </span>
            </div>
            <p class="text-sm text-gray-500 font-medium">Control centralizado del pedimento y sus operaciones asociadas.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('expedientes.downloadAll', $expediente) }}" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
                <i class="fas fa-file-archive text-indigo-500"></i> Descargar ZIP
            </a>
            @if($expediente->estado !== 'Cerrado')
                <button type="button" onclick="openModal('checklistModal')" class="flex items-center gap-2 px-4 py-2 bg-white border border-rose-200 text-rose-600 rounded-xl font-bold text-sm hover:bg-rose-50 transition-all shadow-sm">
                    <i class="fas fa-clipboard-check"></i> Checklist Art. 36-A
                </button>
                <button type="button" onclick="openModal('cerrarFirmaModal')" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                    <i class="fas fa-lock"></i> Cerrar Pedimento
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm animate-fade-in-down">
            <div class="flex">
                <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                <div class="ml-3">
                    <p class="text-sm text-emerald-700 font-bold">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Información Principal -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 overflow-hidden relative">
                <div class="absolute -right-12 -top-12 text-gray-50 dark:text-gray-900/20 opacity-50">
                    <i class="fas fa-info-circle text-9xl"></i>
                </div>
                
                <h3 class="text-xl font-black text-gray-800 dark:text-white mb-6 flex items-center gap-2 relative z-10">
                    <i class="fas fa-id-card text-indigo-600"></i> Detalles Generales
                </h3>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-y-8 gap-x-4 relative z-10">
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Cliente</label>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $expediente->cliente->nombre }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Patente</label>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $expediente->patente->numero_patente }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Aduana</label>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $expediente->aduana->nombre_aduana }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Categoría</label>
                        <p class="text-sm font-bold text-indigo-600">{{ $expediente->categoria }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Clave Pedimento</label>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $expediente->clave_pedimento ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Fecha Pago</label>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $expediente->fecha_pago_pedimento ? $expediente->fecha_pago_pedimento->format('d/m/Y') : 'Pendiente' }}</p>
                    </div>
                </div>

                @if($expediente->observaciones)
                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 relative z-10">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Observaciones</label>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $expediente->observaciones }}</p>
                    </div>
                @endif
            </div>

            <!-- Listado de Operaciones -->
            <div class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h3 class="text-xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-exchange-alt text-indigo-600"></i> Operaciones Asociadas
                    </h3>
                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">{{ $expediente->operaciones->count() }} Operaciones</span>
                </div>

                @forelse($expediente->operaciones as $operacion)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden group">
                        <div class="p-6">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 flex items-center justify-center text-xl shadow-inner">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-800 dark:text-white group-hover:text-indigo-600 transition-colors">Ref: {{ $operacion->referencia }}</h4>
                                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Factura: {{ $operacion->num_factura ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-6">
                                    <div class="text-right hidden sm:block">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-0.5">Modulación</label>
                                        <span class="text-sm font-bold {{ $operacion->modulacion ? 'text-emerald-600' : 'text-amber-500' }}">
                                            {{ $operacion->modulacion ?: 'Sin Modular' }}
                                        </span>
                                    </div>
                                    <div class="text-right hidden sm:block">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-0.5">DODA</label>
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $operacion->num_doda ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button onclick="toggleCollapse('docs-{{ $operacion->id }}')" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors text-gray-400 hover:text-indigo-600" title="Ver Documentos">
                                            <i class="fas fa-folder-open"></i>
                                        </button>
                                        @if($expediente->estado !== 'Cerrado')
                                            <button onclick="openUploadModal({{ $operacion->id }}, '{{ $operacion->referencia }}')" class="p-2 hover:bg-indigo-600 hover:text-white rounded-lg transition-all text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 shadow-sm" title="Subir Documento">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Collapse de Documentos -->
                        <div id="docs-{{ $operacion->id }}" class="hidden border-t border-gray-50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/50">
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @forelse($operacion->documentos as $doc)
                                        <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-lg">
                                                    <i class="fas fa-file-pdf"></i>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate max-w-[150px] sm:max-w-[200px]" title="{{ $doc->nombre }}">
                                                        {{ $doc->nombre }}
                                                    </p>
                                                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">{{ $doc->tipo_documento }}</p>
                                                </div>
                                            </div>
                                            <div class="flex gap-1">
                                                <button onclick="previewDocument('{{ route('documentos.preview', $doc) }}', '{{ $doc->nombre }}')" class="p-2 text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors" title="Vista Previa">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="{{ route('documentos.download', $doc) }}" class="p-2 text-emerald-500 hover:bg-emerald-50 rounded-lg transition-colors" title="Descargar">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                @if(auth()->user()->role === 'admin')
                                                    <form action="{{ route('documentos.destroy', $doc) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" onclick="return confirm('¿Está seguro de eliminar este documento?')" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Eliminar">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-span-full py-4 text-center">
                                            <p class="text-sm text-gray-400 italic">No hay documentos cargados para esta operación.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="w-20 h-20 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4 text-gray-300">
                            <i class="fas fa-box-open text-4xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white">Sin Operaciones</h4>
                        <p class="text-sm text-gray-400 mt-2">No hay operaciones relacionadas con este pedimento.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Columna Lateral -->
        <div class="space-y-8">
            <!-- Estadísticas Documentos -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden relative">
                <div class="absolute -right-8 -top-8 text-indigo-50 dark:text-indigo-900/20 opacity-30">
                    <i class="fas fa-chart-pie text-9xl"></i>
                </div>
                
                <h3 class="text-lg font-black text-gray-800 dark:text-white mb-6 relative z-10">Archivos del Pedimento</h3>
                
                <div class="space-y-4 relative z-10">
                    @php
                        $pedimentoDocs = $expediente->documentos->whereNull('operacion_id');
                    @endphp
                    
                    @forelse($pedimentoDocs as $pdoc)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-700 group">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900 text-indigo-600 flex items-center justify-center text-sm shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    <i class="fas fa-file"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <p class="text-[11px] font-bold text-gray-800 dark:text-gray-200 truncate" title="{{ $pdoc->nombre }}">{{ $pdoc->nombre }}</p>
                                    <span class="text-[9px] font-black uppercase text-gray-400 tracking-wider">{{ $pdoc->tipo_documento ?? 'General' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button onclick="previewDocument('{{ route('documentos.preview', $pdoc) }}', '{{ $pdoc->nombre }}')" class="p-1.5 text-indigo-500 hover:bg-white rounded-lg shadow-sm opacity-0 group-hover:opacity-100 transition-all" title="Ver">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                                <a href="{{ route('documentos.download', $pdoc) }}" class="p-1.5 text-emerald-500 hover:bg-white rounded-lg shadow-sm opacity-0 group-hover:opacity-100 transition-all" title="Descargar">
                                    <i class="fas fa-download text-xs"></i>
                                </a>
                                @if(auth()->user()->role === 'admin')
                                    <form action="{{ route('documentos.destroy', $pdoc) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('¿Está seguro de eliminar este documento?')" class="p-1.5 text-rose-500 hover:bg-white rounded-lg shadow-sm opacity-0 group-hover:opacity-100 transition-all" title="Eliminar">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-dashed border-gray-200 dark:border-gray-600">
                            <p class="text-[11px] font-bold text-gray-400 italic">Sin documentos directos</p>
                        </div>
                    @endforelse
                </div>

                @if($expediente->estado !== 'Cerrado')
                    <button onclick="openModal('agregarDocumentoModal')" class="w-full mt-6 py-3 px-4 bg-white dark:bg-gray-700 border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-2xl text-xs font-black text-gray-400 hover:border-indigo-400 hover:text-indigo-500 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-plus-circle"></i> SUBIR DOC. MAESTRO
                    </button>
                @endif
            </div>

            <!-- Información de Registro -->
            <div class="bg-indigo-600 rounded-3xl p-8 text-white shadow-xl shadow-indigo-100 dark:shadow-none overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 opacity-10">
                    <i class="fas fa-user-check text-8xl"></i>
                </div>
                <h4 class="text-xs font-black uppercase tracking-[0.2em] opacity-60 mb-6">Trazabilidad</h4>
                <div class="space-y-6">
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-pen-nib"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase opacity-60">Registrado por</p>
                            <p class="text-sm font-bold">{{ $expediente->registradoPor->name ?? 'Desconocido' }}</p>
                            <p class="text-[10px] opacity-60">{{ $expediente->created_at->format('d M, Y - H:i') }}</p>
                        </div>
                    </div>
                    @if($expediente->cerradoPor)
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-400/30 flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase opacity-60">Cerrado por</p>
                                <p class="text-sm font-bold">{{ $expediente->cerradoPor->name }}</p>
                                <p class="text-[10px] opacity-60">{{ $expediente->fecha_cierre ? $expediente->fecha_cierre->format('d M, Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     MODALES (Diseño Moderno)
     ============================================== -->

<!-- Modal: Cerrar Pedimento (Premium Design) -->
<div id="cerrarFirmaModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('cerrarFirmaModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100 dark:border-gray-700">
            <div class="bg-indigo-600 px-8 py-6 flex justify-between items-center">
                <h3 class="text-xl font-black text-white flex items-center gap-3">
                    <i class="fas fa-lock"></i> Cierre de Pedimento
                </h3>
                <button onclick="closeModal('cerrarFirmaModal')" class="text-white/70 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form action="{{ route('expedientes.cerrarFirma', $expediente) }}" method="POST" enctype="multipart/form-data" class="p-8">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Nuevo Estado</label>
                            <select name="estado" class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-2xl px-4 py-3 text-sm font-bold text-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="Cerrado">Cerrado</option>
                                <option value="En proceso">Regresar a Proceso</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Fecha Apertura (Referencia)</label>
                            <p class="text-sm font-bold text-gray-800 py-3">{{ $expediente->fecha_apertura ? $expediente->fecha_apertura->format('d/m/Y') : 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Fecha de Pago</label>
                            <input type="date" name="fecha_pago_pedimento" class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-2xl px-4 py-3 text-sm font-bold text-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all" value="{{ date('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Fecha de Cierre</label>
                            <input type="date" name="fecha_cierre" class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-2xl px-4 py-3 text-sm font-bold text-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="space-y-6">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Documento de Pago (PDF)</label>
                        <div id="drop-zone-close" class="border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-3xl p-8 text-center bg-gray-50 dark:bg-gray-700/50 hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer group relative">
                            <input type="file" name="pedimento_pagado" id="pedimento_pagado" class="absolute inset-0 opacity-0 cursor-pointer" accept=".pdf" onchange="handleFileSelect(this, 'status-close')">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 group-hover:text-indigo-500 transition-colors mb-2"></i>
                            <p class="text-sm font-bold text-gray-500 group-hover:text-indigo-600">Pedimento Pagado</p>
                            <p class="text-[10px] text-gray-400 mt-1">Arrastra o haz clic</p>
                            <div id="status-close" class="hidden mt-4 p-3 bg-indigo-600 text-white rounded-xl flex items-center justify-between gap-2 shadow-lg animate-bounce">
                                <span class="text-[10px] font-black uppercase tracking-widest truncate max-w-[150px]" id="close-file-name">Archivo Seleccionado</span>
                                <button type="button" onclick="clearClosingFile()" class="p-1 hover:bg-white/20 rounded-lg transition-colors">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Observaciones de Cierre</label>
                            <textarea name="observaciones_cierre" rows="4" class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-2xl px-4 py-3 text-sm font-medium text-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-gray-400" placeholder="Escribe detalles sobre el cierre..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" onclick="closeModal('cerrarFirmaModal')" class="flex-1 py-4 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 transition-all">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all">
                        Confirmar Cierre <i class="fas fa-check-circle ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Subir Documento a Operación (Drag & Drop) -->
<div id="uploadOpModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('uploadOpModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100 dark:border-gray-700">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h3 class="text-2xl font-black text-gray-800 dark:text-white">Subir Documentos</h3>
                        <p class="text-sm text-gray-500 mt-1 font-bold">Referencia: <span id="op-ref-display" class="text-indigo-600">---</span></p>
                    </div>
                    <button onclick="closeModal('uploadOpModal')" class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div id="drop-zone-op" class="border-4 border-dashed border-gray-100 dark:border-gray-700 rounded-[2rem] p-12 text-center hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer group mb-8">
                    <input type="file" multiple id="op-files" class="hidden" accept=".pdf,.xml,.xls,.xlsx,.doc,.docx,.jpg,.jpeg,.png,.bmp" onchange="handleMultipleFiles(this)">
                    <div class="w-20 h-20 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-3xl flex items-center justify-center text-3xl mx-auto mb-6 group-hover:scale-110 transition-transform shadow-inner">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Arrastra tus archivos</h4>
                    <p class="text-sm text-gray-400">PDF, XML, Excel, Word o Imágenes</p>
                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest mt-4">Máximo 50MB por archivo</p>
                </div>

                <div id="file-list" class="space-y-3 mb-8 max-h-40 overflow-y-auto no-scrollbar">
                    <!-- Dinámico -->
                </div>

                <form id="upload-form-op" onsubmit="uploadFiles(event)">
                    <input type="hidden" name="operacion_id" id="modal-op-id">
                    <input type="hidden" name="id" value="{{ $expediente->id }}">
                    <!-- INC-002: El tipo de documento se asigna individualmente por archivo en la lista -->
                    <button type="submit" id="btn-upload-op" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all disabled:opacity-50">
                        <span id="btn-text">INICIAR CARGA</span>
                        <div id="btn-loader" class="hidden">
                            <i class="fas fa-circle-notch fa-spin"></i> CARGANDO...
                        </div>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Preview de Documento -->
<div id="previewModal" class="fixed inset-0 z-[60] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('previewModal')"></div>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-6xl h-[90vh] flex flex-col">
            <div class="bg-gray-50 dark:bg-gray-900 px-8 py-4 flex justify-between items-center border-b border-gray-100 dark:border-gray-700">
                <h3 id="preview-filename" class="text-lg font-bold text-gray-800 dark:text-white truncate max-w-md">Preview</h3>
                <div class="flex items-center gap-4">
                    <button onclick="closeModal('previewModal')" class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center text-gray-400 hover:text-rose-500 shadow-sm transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="flex-grow p-0">
                <iframe id="preview-iframe" class="w-full h-full border-none" src=""></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Componente para subir documento maestro -->
<div id="agregarDocumentoModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal('agregarDocumentoModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100 dark:border-gray-700">
            <form action="{{ route('documentos.store', $expediente) }}" method="POST" enctype="multipart/form-data" class="p-8">
                @csrf
                <h3 class="text-2xl font-black text-gray-800 dark:text-white mb-6">Subir Documento Maestro</h3>
                
                <div class="space-y-6">
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Nombre del Documento</label>
                        <input type="text" name="nombre_documento" id="nombre_doc_maestro" required class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-2xl px-4 py-3 text-sm font-bold text-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="Ej: Pago de Pedimento, Carta 3.1.8...">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Tipo</label>
                            <select name="tipo_documento" class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-2xl px-4 py-3 text-sm font-bold text-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all">
                                <optgroup label="Requeridos SAT (Exp. Maestro)">
                                    <option value="rfc">Constancia CSF (RFC)</option>
                                    <option value="domicilio">Comprobante de Domicilio</option>
                                    <option value="acta">Acta Constitutiva</option>
                                    <option value="poder">Poder Notarial</option>
                                    <option value="identificacion">Identificación Oficial</option>
                                </optgroup>
                                <optgroup label="Otros">
                                    <option value="Maestro">Maestro (General)</option>
                                    <option value="Sagarpa">Sagarpa</option>
                                    <option value="Contenedor">Contenedor</option>
                                    <option value="Otro" selected>Otro</option>
                                </optgroup>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-2">Fecha</label>
                            <input type="date" name="fecha_documento" class="w-full bg-gray-50 dark:bg-gray-700 border-none rounded-2xl px-4 py-3 text-sm font-bold text-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-3xl p-12 text-center bg-gray-50 dark:bg-gray-700/50 hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer group relative">
                        <input type="file" name="archivo" class="absolute inset-0 opacity-0 cursor-pointer" accept=".pdf" onchange="autoFillName(this)">
                        <i class="fas fa-file-upload text-4xl text-gray-300 group-hover:text-indigo-500 transition-colors mb-2"></i>
                        <p class="text-sm font-bold text-gray-500 group-hover:text-indigo-600">Seleccionar PDF</p>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" onclick="closeModal('agregarDocumentoModal')" class="flex-1 py-4 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 transition-all">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all">
                        GUARDAR <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Checklist Cumplimiento -->
<div id="checklistModal" class="hidden fixed inset-0 z-[60] overflow-y-auto bg-gray-900/60 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen px-4 py-12">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-2xl transform transition-all border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-8 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/40">
                <div>
                    <h3 class="text-2xl font-black text-gray-800 dark:text-white">Checklist de Cumplimiento</h3>
                    <p class="text-xs text-indigo-500 font-black uppercase tracking-widest mt-1">Expediente Digital Art. 36-A L.A.</p>
                </div>
                <button onclick="closeModal('checklistModal')" class="p-2 hover:bg-white rounded-xl transition-all shadow-sm">
                    <i class="fas fa-times text-gray-400"></i>
                </button>
            </div>
            
            <form id="checklistForm" class="p-8 space-y-6">
                 <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                    
                    <!-- GRUPO 1: EXPEDIENTE MAESTRO -->
                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest border-b border-indigo-100 dark:border-indigo-900/50 pb-2 mb-4 flex items-center gap-2">
                        <i class="fas fa-university"></i> 1. Expediente Maestro (Cliente)
                    </p>
                    
                    @foreach(\App\Models\Expediente::MAESTRO_DOCS as $key => $label)
                        <div class="flex items-center justify-between p-3 bg-indigo-50/30 dark:bg-indigo-900/10 rounded-2xl border border-indigo-50 dark:border-indigo-900/30 group transition-all">
                            <div class="flex items-center gap-3">
                                @php 
                                    // INC-019: Documentos maestros se consultan del CLIENTE
                                    $hasFile = $expediente->cliente && $expediente->cliente->documentosMaestros
                                        ? $expediente->cliente->documentosMaestros->where('tipo_documento', $key)->isNotEmpty()
                                        : false;
                                    $isManual = !empty($expediente->checklist_cumplimiento[$key]);
                                @endphp
                                @if($hasFile)
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 shadow-sm" title="Archivo cargado">
                                        <i class="fas fa-check-double text-xs"></i>
                                    </div>
                                @elseif($isManual)
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 shadow-sm" title="Marcado manualmente">
                                        <i class="fas fa-check text-xs"></i>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center text-gray-300 shadow-sm border border-gray-100 dark:border-gray-600">
                                        <i class="fas fa-file-invoice text-xs"></i>
                                    </div>
                                @endif
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $label }}</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer scale-90">
                                <input type="checkbox" name="checklist[{{ $key }}]" value="1" 
                                    {{ $isManual ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                    @endforeach

                    <!-- GRUPO 2: POR OPERACIÓN -->
                    <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest border-b border-amber-100 dark:border-amber-900/50 pb-2 pt-4 mb-4 flex items-center gap-2">
                        <i class="fas fa-truck-loading"></i> 2. Documentos por Operación
                    </p>
                    
                    @php $opCount = $expediente->operaciones()->count(); @endphp

                    @foreach(\App\Models\Expediente::OPERACION_DOCS as $key => $label)
                        @php 
                            $isManual = !empty($expediente->checklist_cumplimiento[$key]);
                            $opsConDoc = $expediente->operaciones()->whereHas('documentos', function($q) use ($key) { $q->where('tipo_documento', $key); })->count();
                            $allOpsHaveIt = ($opCount > 0 && $opsConDoc >= $opCount);
                        @endphp
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-2xl border border-gray-100 dark:border-gray-600 group transition-all">
                            <div class="flex items-center gap-3">
                                @if($allOpsHaveIt && !$isManual)
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 shadow-sm" title="Todas las operaciones cumplidas">
                                        <i class="fas fa-check-double text-xs"></i>
                                    </div>
                                @elseif($isManual)
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 shadow-sm" title="Marcado manualmente">
                                        <i class="fas fa-check text-xs"></i>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center text-gray-300 shadow-sm border border-gray-100 dark:border-gray-600">
                                        <span class="text-[10px] font-black">{{ $opsConDoc }}/{{ $opCount }}</span>
                                    </div>
                                @endif
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $label }}</span>
                                    @if(!$isManual && $opCount > 0)
                                        <span class="text-[9px] text-gray-400 font-medium">{{ $opsConDoc }} de {{ $opCount }} operaciones con archivo</span>
                                    @endif
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer scale-90">
                                <input type="checkbox" name="checklist[{{ $key }}]" value="1" 
                                    {{ $isManual ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" onclick="closeModal('checklistModal')" class="px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-200 transition-all">
                        Cerrar
                    </button>
                    <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-700 hover:scale-[1.02] transition-all shadow-xl shadow-indigo-200 flex items-center gap-2">
                        <i class="fas fa-save shadow-sm"></i> Guardar Progreso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // --- Lógica de Apertura/Cierre de Modales ---
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function toggleCollapse(id) {
        const el = document.getElementById(id);
        if(el) el.classList.toggle('hidden');
    }

    // --- Checklist Cumplimiento ---
    const checklistForm = document.getElementById('checklistForm');
    if(checklistForm) {
        checklistForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Guardando...';
            btn.disabled = true;

            const formData = new FormData(checklistForm);
            
            try {
                const response = await fetch("{{ route('expedientes.updateChecklist', $expediente) }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Checklist Guardado!',
                        text: 'El estado de cumplimiento digital ha sido actualizado.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });
                }
            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo guardar el progreso del checklist.'
                });
            } finally {
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        });
    }

    // --- Lógica de Subida de Documentos (Operations) ---
    let selectedFiles = [];

    function openUploadModal(opId, ref) {
        document.getElementById('modal-op-id').value = opId;
        document.getElementById('op-ref-display').textContent = ref;
        document.getElementById('file-list').innerHTML = '';
        selectedFiles = [];
        openModal('uploadOpModal');
    }

    function handleMultipleFiles(input) {
        const list = document.getElementById('file-list');
        list.innerHTML = '';
        selectedFiles = Array.from(input.files);

        const tipoOptions = `
            <optgroup label="Expediente por Operación">
                <option value="factura">Factura Comercial / Eq.</option>
                <option value="encargo">Encargo Conferido (Electrónico)</option>
                <option value="transporte">Documento de Transporte (BL/Guía/CP)</option>
                <option value="empaque">Lista de Empaque (Packing List)</option>
                <option value="origen">Certificado de Origen</option>
                <option value="rrna">Cumplimiento de RRNA's</option>
                <option value="gastos">Comprobante de Gastos Incrementables</option>
            </optgroup>
            <optgroup label="Documentos Específicos">
                <option value="doda">DODA / PITA</option>
                <option value="cupo">Carta de Cupo Electrónica</option>
                <option value="val">Certificación de Valor (VAL)</option>
            </optgroup>
            <optgroup label="General">
                <option value="otros" selected>Otros / Anexos</option>
            </optgroup>
        `;

        selectedFiles.forEach((file, index) => {
            let icon = 'fa-file-alt';
            let color = 'text-gray-400';
            const ext = file.name.split('.').pop().toLowerCase();

            if(ext === 'pdf') { icon = 'fa-file-pdf'; color = 'text-rose-500'; }
            else if(['xls', 'xlsx', 'csv'].includes(ext)) { icon = 'fa-file-excel'; color = 'text-emerald-500'; }
            else if(['doc', 'docx'].includes(ext)) { icon = 'fa-file-word'; color = 'text-indigo-500'; }
            else if(['png', 'jpg', 'jpeg', 'bmp'].includes(ext)) { icon = 'fa-file-image'; color = 'text-amber-500'; }
            else if(ext === 'xml') { icon = 'fa-file-code'; color = 'text-cyan-500'; }

            list.innerHTML += `
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600">
                    <div class="flex items-center gap-2 overflow-hidden flex-1 min-w-0">
                        <i class="fas ${icon} ${color}"></i>
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200 truncate">${file.name}</span>
                        <span class="text-[9px] font-black text-gray-400 uppercase">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                    </div>
                    <div class="min-w-[180px]">
                        <select name="tipos_documento[]" class="w-full bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-xl px-3 py-2 text-[11px] font-bold text-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 transition-all">
                            ${tipoOptions}
                        </select>
                    </div>
                </div>
            `;
        });
    }

    async function uploadFiles(e) {
        e.preventDefault();
        if (selectedFiles.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Por favor selecciona al menos un archivo' });
            return;
        }

        const btn = document.getElementById('btn-upload-op');
        const btnText = document.getElementById('btn-text');
        const btnLoader = document.getElementById('btn-loader');

        btn.disabled = true;
        btnText.classList.add('hidden');
        btnLoader.classList.remove('hidden');

        const formData = new FormData(e.target);
        selectedFiles.forEach(file => {
            formData.append('archivos[]', file);
        });

        try {
            // INC-002: Usamos store3 (documentos_operacion.store2) para soportar tipos individuales
            const response = await fetch("{{ route('documentos_operacion.store2') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (response.redirected) {
                window.location.href = response.url;
            } else {
                const data = await response.json();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error al subir archivos' });
                }
            }
        } catch (error) {
            console.error(error);
            window.location.reload();
        } finally {
            btn.disabled = false;
            btnText.classList.remove('hidden');
            btnLoader.classList.add('hidden');
        }
    }

    // --- Preview ---
    function previewDocument(url, name) {
        document.getElementById('preview-filename').textContent = name;
        document.getElementById('preview-iframe').src = url;
        openModal('previewModal');
    }

    // --- Helpers ---
    function handleFileSelect(input, statusId) {
        const status = document.getElementById(statusId);
        const nameSpan = document.getElementById('close-file-name');
        if (input.files.length > 0) {
            if(nameSpan) nameSpan.textContent = input.files[0].name;
            status.classList.remove('hidden');
        } else {
            status.classList.add('hidden');
        }
    }

    function clearClosingFile() {
        const input = document.getElementById('pedimento_pagado');
        const status = document.getElementById('status-close');
        input.value = '';
        status.classList.add('hidden');
    }

    function autoFillName(input) {
        if (input.files.length > 0) {
            const fileName = input.files[0].name.split('.').slice(0, -1).join('.');
            document.getElementById('nombre_doc_maestro').value = fileName;
        }
    }

    // Drag & Drop for Operations
    const dropZoneOp = document.getElementById('drop-zone-op');
    const inputOp = document.getElementById('op-files');

    dropZoneOp.addEventListener('click', () => inputOp.click());

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZoneOp.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZoneOp.addEventListener(eventName, () => dropZoneOp.classList.add('bg-indigo-50', 'border-indigo-400'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZoneOp.addEventListener(eventName, () => dropZoneOp.classList.remove('bg-indigo-50', 'border-indigo-400'), false);
    });

    dropZoneOp.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        inputOp.files = dt.files;
        handleMultipleFiles(inputOp);
    });
</script>

<style>
    @keyframes fade-in-down {
        0% { opacity: 0; transform: translateY(-10px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down {
        animation: fade-in-down 0.5s ease-out;
    }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection