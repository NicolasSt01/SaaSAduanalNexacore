@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-gray-800">
                Expediente:
                <span class="text-indigo-600">{{ $expediente->numero_pedimento }}</span>
            </h1>
        </div>
    </div>

    {{-- Alertas --}}
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

    @if($errors->any())
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm">
            <div class="flex">
                <i class="fas fa-exclamation-circle text-rose-500 mt-0.5"></i>
                <div class="ml-3">
                    <p class="text-sm font-bold text-rose-700">Por favor, corrige los errores en el formulario.</p>
                    <ul class="mt-1 text-sm text-rose-600 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Información principal --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <h5 class="text-lg font-black text-gray-800 mb-6">
                    <i class="fas fa-info-circle text-indigo-600 mr-2"></i>Información del Expediente
                </h5>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Número de Pedimento</label>
                        <p class="text-sm font-bold text-gray-800">{{ $expediente->numero_pedimento }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Cliente</label>
                        <p class="text-sm font-bold text-gray-800">{{ $expediente->cliente->nombre }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Patente</label>
                        <p class="text-sm font-bold text-gray-800">{{ $expediente->patente->numero_patente }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Aduana</label>
                        <p class="text-sm font-bold text-gray-800">{{ $expediente->aduana->nombre_aduana }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Categoría</label>
                        <p class="text-sm font-bold text-gray-800">
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest border bg-blue-50 text-blue-700 border-blue-200">
                                {{ $expediente->categoria }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Estado</label>
                        <p class="text-sm font-bold text-gray-800">
                            @php
                                $estadoClasses = [
                                    'pendiente' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'Cerrado' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'rechazado' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'en_revision' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'En proceso' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'Abierto' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'Cancelado' => 'bg-rose-50 text-rose-700 border-rose-200',
                                ];
                                $badgeClass = $estadoClasses[$expediente->estado] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest border {{ $badgeClass }}">
                                {{ ucfirst($expediente->estado) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Fecha de Pago</label>
                        <p class="text-sm font-bold text-gray-800">
                            {{ optional($expediente->fecha_pago_pedimento)->format('d/m/Y') ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Observaciones</label>
                    <p class="text-sm font-bold text-gray-800">{{ $expediente->observaciones ?? 'Ninguna' }}</p>
                </div>
            </div>
        </div>

        {{-- Panel de estadísticas --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <h5 class="text-lg font-black text-gray-800 mb-6">
                    <i class="fas fa-chart-pie text-indigo-600 mr-2"></i>Estadísticas de Documentos
                </h5>

                <div class="flex justify-between items-center mb-4">
                    <span class="text-sm text-gray-500 font-medium">Total de documentos</span>
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-indigo-100 text-indigo-700 border border-indigo-200">
                        {{ $expediente->documentos->count() }}
                    </span>
                </div>

                @php
                    $tiposDocumentos = $expediente->documentos->groupBy('tipo_documento');
                    $totalDocs = $expediente->documentos->count();
                @endphp

                @foreach($tiposDocumentos as $tipo => $documentos)
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs text-gray-500 font-medium">{{ $tipo }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-gray-100 text-gray-600 border border-gray-200">
                            {{ $documentos->count() }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5 mb-3 overflow-hidden">
                        <div class="h-full bg-indigo-500 rounded-full transition-all" style="width: {{ $totalDocs > 0 ? ($documentos->count() / $totalDocs) * 100 : 0 }}%;"></div>
                    </div>
                @endforeach

                <div class="mt-6 text-center">
                    <a href="{{ route('expedientes.downloadAll', $expediente) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
                        <i class="fas fa-download"></i> Descargar todos
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Documentos agrupados por operación --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 mt-6">
        <div class="flex justify-between items-center mb-6">
            <h5 class="text-lg font-black text-gray-800">
                <i class="fas fa-file-alt text-indigo-600 mr-2"></i>Documentos del Expediente
            </h5>
        </div>

        @if($expediente->documentos->isEmpty())
            <div class="text-center py-12">
                <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 font-medium">No hay documentos registrados para este expediente.</p>
            </div>
        @else
            @php
                $documentosPorOperacion = $expediente->documentos->groupBy('operacion_id');
            @endphp

            <div id="documentosAccordion" class="space-y-3">
                @foreach($documentosPorOperacion as $exportacionId => $documentos)
                    @php
                        $exportacion = $documentos->first()->exportacion ?? null;
                        $operacionNombre = $exportacion ? $exportacion->nombre_operacion ?? "Operación #{$exportacionId}" : "Sin operación asignada";
                        $accordionId = "collapse-" . ($exportacionId ?? 'sin-operacion');
                    @endphp

                    <div class="border border-gray-200 rounded-2xl overflow-hidden">
                        <button type="button"
                            onclick="toggleAccordion('{{ $accordionId }}', this)"
                            class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors {{ $loop->first ? '' : '' }}"
                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-folder-open text-indigo-600"></i>
                                <span class="text-sm font-bold text-gray-800">{{ $operacionNombre }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 rounded-full text-xs font-black bg-gray-100 text-gray-600 border border-gray-200">
                                    {{ $documentos->count() }} documento(s)
                                </span>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200 accordion-chevron {{ $loop->first ? 'rotate-180' : '' }}"></i>
                            </div>
                        </button>
                        <div id="{{ $accordionId }}" class="border-t border-gray-100 {{ $loop->first ? '' : 'hidden' }}">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="text-[10px] font-black uppercase tracking-widest text-gray-400 px-4 py-3">Tipo</th>
                                            <th class="text-[10px] font-black uppercase tracking-widest text-gray-400 px-4 py-3">Nombre</th>
                                            <th class="text-[10px] font-black uppercase tracking-widest text-gray-400 px-4 py-3">Fecha</th>
                                            <th class="text-[10px] font-black uppercase tracking-widest text-gray-400 px-4 py-3">Tamaño</th>
                                            <th class="text-[10px] font-black uppercase tracking-widest text-gray-400 px-4 py-3">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($documentos as $documento)
                                            <tr class="border-t border-gray-50 hover:bg-indigo-50/30 transition-colors">
                                                <td class="px-4 py-3">
                                                    <span class="px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border bg-gray-50 text-gray-600 border-gray-200">
                                                        {{ $documento->tipo_documento }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm font-bold text-gray-800">{{ $documento->nombre_documento }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $documento->fecha_documento ? $documento->fecha_documento->format('d/m/Y') : 'N/A' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">
                                                    @if($documento->archivo_path && file_exists(storage_path('app/' . $documento->archivo_path)))
                                                        {{ round(filesize(storage_path('app/' . $documento->archivo_path)) / 1024, 1) }} KB
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-1">
                                                        <a href="{{ route('documentos.download', $documento) }}"
                                                            class="inline-flex items-center gap-1 px-2 py-1.5 border border-emerald-200 bg-emerald-50 text-emerald-700 rounded-lg font-bold text-xs hover:bg-emerald-100 transition-all"
                                                            title="Descargar">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <button type="button" title="Vista previa"
                                                            onclick="openPreviewModal('{{ $documento->id }}')"
                                                            class="inline-flex items-center gap-1 px-2 py-1.5 border border-blue-200 bg-blue-50 text-blue-700 rounded-lg font-bold text-xs hover:bg-blue-100 transition-all">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($exportacion)
                                <div class="p-3 bg-gray-50 border-t border-gray-100">
                                    <small class="text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Operación: {{ $exportacion->id ?? 'N/A' }} |
                                        Fecha: {{ $exportacion->fecha ? $exportacion->fecha->format('d/m/Y') : 'N/A' }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-4">
                <button type="button" id="toggleAllAccordions"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fas fa-expand-arrows-alt"></i> Expandir/Contraer todo
                </button>
            </div>
        @endif
    </div>

</div>

{{-- Modal para agregar documento --}}
<div id="agregarDocumentoModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" aria-hidden="true" onclick="closeModal('agregarDocumentoModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="px-6 py-5 bg-indigo-600">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-black text-white">
                        <i class="fas fa-file-upload mr-2"></i>Agregar Documento
                    </h3>
                    <button type="button" onclick="closeModal('agregarDocumentoModal')" class="text-white hover:text-indigo-200 focus:outline-none transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <form action="{{ route('documentos.store', $expediente) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="px-6 py-6 bg-white flex flex-col gap-4">
                    <input type="hidden" name="tipo_documento" value="Otro">
                    <input type="hidden" name="fecha_documento" value="{{ date('Y-m-d') }}">
                    <input type="hidden" id="nombre_documento" name="nombre_documento">

                    <div>
                        <label for="archivo" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Archivo PDF *</label>
                        <input type="file" id="archivo" name="archivo" accept=".pdf" required
                            onchange="document.getElementById('nombre_documento').value = this.files[0]?.name.split('.').slice(0, -1).join('.')"
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                        <p class="text-xs text-gray-400 mt-1">Tamaño máximo: 20MB. El nombre del archivo se usará como nombre del documento.</p>
                    </div>

                    <div>
                        <label for="observaciones" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Observaciones (opcional)</label>
                        <textarea id="observaciones" name="observaciones" rows="2"
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('agregarDocumentoModal')"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all shadow-sm">
                        Guardar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal para vista previa --}}
<div id="previewModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" aria-hidden="true" onclick="closeModal('previewModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
            <div class="px-6 py-5 bg-indigo-600">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-black text-white">
                        <i class="fas fa-eye mr-2"></i>Vista previa del documento
                    </h3>
                    <button type="button" onclick="closeModal('previewModal')" class="text-white hover:text-indigo-200 focus:outline-none transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-4">
                <iframe id="pdf-iframe" src="" width="100%" height="600" class="border-none rounded-xl"></iframe>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('previewModal')"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
                    Cerrar
                </button>
                <a href="#" id="downloadPreview"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all shadow-sm">
                    <i class="fas fa-download"></i> Descargar
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Modal para cerrar firma --}}
<div id="cerrarFirmaModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" aria-hidden="true" onclick="closeModal('cerrarFirmaModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="px-6 py-5 bg-indigo-600">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-black text-white">
                        <i class="fas fa-check-circle mr-2"></i>Cerrar Firma y Actualizar Pago
                    </h3>
                    <button type="button" onclick="closeModal('cerrarFirmaModal')" class="text-white hover:text-indigo-200 focus:outline-none transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <form action="{{ route('expedientes.cerrarFirma', $expediente) }}" method="POST">
                @csrf
                @method('POST')
                <div class="px-6 py-6 bg-white flex flex-col gap-4">
                    <div>
                        <label for="estado" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Nuevo estado</label>
                        <select name="estado" id="estado" required
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                            <option value="En proceso" @selected($expediente->estado == 'En proceso')>En Proceso</option>
                            <option value="Abierto" @selected($expediente->estado == 'Abierto')>Abierto</option>
                            <option value="Cerrado" @selected($expediente->estado == 'Cerrado')>Cerrado</option>
                            <option value="Cancelado" @selected($expediente->estado == 'Cancelado')>Cancelado</option>
                        </select>
                    </div>
                    <div>
                        <label for="fecha_pago_pedimento" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Fecha de Pago</label>
                        <input type="date" id="fecha_pago_pedimento" name="fecha_pago_pedimento"
                            value="{{ old('fecha_pago_pedimento', optional($expediente->fecha_pago_pedimento ?? now())->format('Y-m-d')) }}"
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                    </div>
                    <div>
                        <label for="fecha_cierre" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Fecha de Cierre</label>
                        <input type="date" id="fecha_cierre" name="fecha_cierre"
                            value="{{ old('fecha_cierre', optional($expediente->fecha_cierre ?? now())->format('Y-m-d')) }}"
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">
                    </div>
                    <div>
                        <label for="observaciones_cierre" class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Observaciones (opcional)</label>
                        <textarea name="observaciones" id="observaciones_cierre" rows="2"
                            class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 p-3 border shadow-sm bg-gray-50/50 text-sm">{{ old('observaciones', $expediente->observaciones) }}</textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('cerrarFirmaModal')"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 transition-all shadow-sm">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggleAllAccordions');
    if (toggleButton) {
        let allExpanded = true;
        toggleButton.addEventListener('click', function() {
            const collapses = document.querySelectorAll('#documentosAccordion [id^="collapse-"]');
            const chevrons = document.querySelectorAll('#documentosAccordion .accordion-chevron');

            collapses.forEach(function(collapse) {
                if (allExpanded) {
                    collapse.classList.add('hidden');
                } else {
                    collapse.classList.remove('hidden');
                }
            });

            chevrons.forEach(function(chevron) {
                if (allExpanded) {
                    chevron.classList.remove('rotate-180');
                } else {
                    chevron.classList.add('rotate-180');
                }
            });

            allExpanded = !allExpanded;

            const icon = toggleButton.querySelector('i');
            if (allExpanded) {
                icon.className = 'fas fa-compress-arrows-alt';
            } else {
                icon.className = 'fas fa-expand-arrows-alt';
            }
        });
    }
});
</script>

@push('scripts')
<script>
    function toggleAccordion(id, button) {
        const collapse = document.getElementById(id);
        const chevron = button.querySelector('.accordion-chevron');
        collapse.classList.toggle('hidden');
        if (chevron) {
            chevron.classList.toggle('rotate-180');
        }
    }

    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function openPreviewModal(documentoId) {
        const downloadLink = document.getElementById('downloadPreview');
        downloadLink.href = '/documentos/' + documentoId + '/download';
        const iframe = document.getElementById('pdf-iframe');
        iframe.src = '/documentos/' + documentoId + '/preview#toolbar=0';
        openModal('previewModal');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const archivoInput = document.getElementById('archivo');
        const nombreDocumentoInput = document.getElementById('nombre_documento');
        if (archivoInput && nombreDocumentoInput) {
            archivoInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const fileName = this.files[0].name;
                    const baseName = fileName.split('.').slice(0, -1).join('.');
                    nombreDocumentoInput.value = baseName;
                }
            });
        }

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.fixed.inset-0.z-50').forEach(function(modal) {
                    if (!modal.classList.contains('hidden')) {
                        modal.classList.add('hidden');
                    }
                });
            }
        });
    });
</script>
@endpush

@endsection
