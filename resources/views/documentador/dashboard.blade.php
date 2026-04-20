@extends('layouts.app')

@section('title', 'Dashboard - Documentador (Live)')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Panel Operativo <span
                        class="bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full align-middle ml-2"><i
                            class="fas fa-satellite-dish animate-pulse"></i> EN VIVO</span></h1>
                <p class="text-sm text-gray-500 mt-1">Supervisión en tiempo real de operaciones de documentación</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400 font-mono tracking-wider" id="last_update_time">Conectando...</span>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Lista de Operaciones -->
            <div class="lg:col-span-2 flex flex-col bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                style="min-height: 600px;">
                <div class="bg-gray-50 p-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-700"><i class="fas fa-list-ul text-indigo-500 mr-2"></i>
                        Operaciones del Día</h2>
                    <div class="flex items-center gap-3">
                        @if($botEnabled && !$botAutomatic)
                            <!-- Botón de consulta manual de modulación -->
                            <button onclick="consultarModulacionManual()" id="btn_consulta_modulacion"
                                class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm transition flex items-center gap-2">
                                <i class="fas fa-robot"></i>
                                <span>Consultar Modulación</span>
                            </button>
                        @endif
                        <button onclick="openCreateModal()"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm transition"><i
                                class="fas fa-plus mr-1"></i> Nueva Operación</button>
                        <span
                            class="bg-white border border-gray-300 text-gray-700 text-xs font-bold px-3 py-1 rounded-full shadow-sm"
                            id="ops_count">0 Encontradas</span>
                    </div>
                </div>

                <div
                    class="flex-1 overflow-x-auto lg:overflow-x-visible overflow-y-auto p-0 border border-gray-100 rounded-b-xl w-full">
                    <table class="w-full text-left border-collapse">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap lg:whitespace-normal">Ref / Cliente</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap lg:whitespace-normal">Facturas</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap lg:whitespace-normal">DODA / Pedimento</th>
                            <th class="px-4 py-3 font-semibold text-center whitespace-nowrap lg:whitespace-normal">Estatus
                            </th>
                            <th class="px-4 py-3 font-semibold text-center whitespace-nowrap lg:whitespace-normal">Acciones
                            </th>
                        </tr>
                        <tbody id="live_ops_table" class="divide-y divide-gray-100 text-sm">
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center text-gray-400">
                                    <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-400"></i>
                                    <p>Cargando operaciones en vivo...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sidebar (KPIs + Gráfica) -->
            <div class="flex flex-col gap-6">
                <!-- Gráfica Modulaciones -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="text-md font-bold text-gray-700 border-b pb-3 mb-4"><i
                            class="fas fa-traffic-light text-indigo-500 mr-2"></i> Monitor de Modulación</h3>
                    <div class="relative h-64 w-full flex items-center justify-center">
                        <canvas id="modulacionChart"></canvas>
                    </div>
                    <div class="flex justify-center gap-6 mt-4">
                        <div class="text-center">
                            <span class="block text-2xl font-black text-green-500" id="count_verdes">0</span>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Verdes</span>
                        </div>
                        <div class="text-center">
                            <span class="block text-2xl font-black text-red-500" id="count_rojas">0</span>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Rojas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Update DODA & Pedimento -->
    <div id="updateModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" id="modalBackdrop"></div>

        <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
            <div
                class="relative bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full ring-1 ring-gray-200">
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2" id="modal-title">
                        <i class="fas fa-edit"></i> Asignar DODA y Pedimento
                    </h3>
                    <button type="button" class="text-indigo-200 hover:text-white transition" onclick="closeModal()">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>
                <form id="updateForm" onsubmit="submitUpdate(event)">
                    @csrf
                    <div class="px-6 py-5 space-y-5">
                        <input type="hidden" id="op_id" name="id">

                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 flex items-center gap-3 mb-2">
                            <div class="bg-indigo-100 text-indigo-600 p-2 rounded max-w-max"><i class="fas fa-box"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Referencia / Cliente
                                </p>
                                <p class="text-sm font-bold text-gray-800" id="modal_ref_display">CRS0000 - Cliente XYZ</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Número de DODA</label>
                            <input type="text" id="num_doda" name="num_doda" placeholder="Ej. DODA-12345"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border uppercase">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Pedimento Asignado</label>
                            <select id="pedimento_id" name="pedimento_id"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border bg-white">
                                <option value="">-- Seleccionar Pedimento --</option>
                                <!-- Llenado dinámicamente -->
                            </select>
                            <p class="text-xs text-gray-400 mt-1"><i class="fas fa-info-circle"></i> Solo se muestran
                                pedimentos abiertos del cliente asociado a esta operación.</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex sm:flex-row-reverse gap-3">
                        <button type="submit" id="btn_submit"
                            class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent bg-indigo-600 px-5 py-2.5 text-base font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:text-sm transition">
                            Guardar Cambios
                        </button>
                        <button type="button"
                            class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-base font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:text-sm transition"
                            onclick="closeModal()">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Create Operation -->
    <div id="createModal" class="fixed inset-0 z-[60] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" id="createModalBackdrop">
        </div>
        <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
            <div
                class="relative bg-white rounded-xl text-left shadow-2xl transform transition-all sm:my-8 max-w-4xl w-full ring-1 ring-gray-200 max-h-[90vh] flex flex-col">
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center shrink-0 rounded-t-xl">
                    <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i> Registrar Nueva Operación
                    </h3>
                    <button type="button" class="text-indigo-200 hover:text-white transition" onclick="closeCreateModal()">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>

                <form id="createForm" onsubmit="submitCreate(event)" class="flex-1 overflow-y-auto p-6"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1 tracking-wide uppercase">Fecha de Cruce
                                *</label>
                            <input type="date" name="fecha_cruce_estimada" required value="{{ date('Y-m-d') }}"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1 tracking-wide uppercase">Cliente
                                *</label>
                            <select name="cliente_id" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border bg-white">
                                <option value="">Seleccione un Cliente</option>
                                @foreach($opFiltros['clientes'] as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1 tracking-wide uppercase">Importador
                                *</label>
                            <select name="importador_id" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border bg-white">
                                <option value="">Seleccione un Importador</option>
                                @foreach($opFiltros['importadores'] as $imp)
                                    <option value="{{ $imp->id }}">{{ $imp->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1 tracking-wide uppercase">Aduana
                                *</label>
                            <select name="aduana_id" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border bg-white">
                                <option value="">Seleccione una Aduana</option>
                                @foreach($opFiltros['aduanas'] as $aduana)
                                    <option value="{{ $aduana->id }}">{{ $aduana->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1 tracking-wide uppercase">Bodega</label>
                            <select name="bodega_id"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border bg-white">
                                <option value="">Seleccione Bodega</option>
                                @foreach($opFiltros['bodegas'] as $bodega)
                                    <option value="{{ $bodega->id }}">{{ $bodega->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1 tracking-wide uppercase">Factura
                                *</label>
                            <input type="text" name="num_factura" required placeholder="Ej. F-10293"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="block text-xs font-bold text-gray-700 mb-1 tracking-wide uppercase">Nombre del
                                Producto *</label>
                            <input type="text" name="nombre_producto" required placeholder="Descripción breve"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1 tracking-wide uppercase">Thermo
                                (Opcional)</label>
                            <input type="text" name="num_thermo" placeholder="Num. Thermo"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1 tracking-wide uppercase">Código Alpha
                                (Opcional)</label>
                            <input type="text" name="codigo_alpha" placeholder="Alpha"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200">

                    <h4 class="text-md font-bold text-gray-800 mb-3"><i class="fas fa-file-upload text-indigo-500 mr-2"></i>
                        Documentos Adjuntos</h4>

                    <div id="dropzone"
                        class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center bg-gray-50 hover:bg-gray-100 transition cursor-pointer mb-4">
                        <i class="fas fa-cloud-upload-alt text-4xl text-indigo-400 mb-3 block"></i>
                        <p class="text-sm font-bold text-gray-600 mb-1">Haz clic o arrastra archivos aquí</p>
                        <p class="text-xs text-gray-400">PDF, JPG, PNG, DOC (Máx 20MB por archivo)</p>
                        <input type="file" id="fileInput" multiple class="hidden"
                            accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx">
                    </div>

                    <div id="fileList" class="space-y-3">
                    </div>

                    <div
                        class="mt-8 bg-gray-50 -mx-6 -mb-6 px-6 py-4 flex sm:flex-row-reverse gap-3 rounded-b-xl border-t border-gray-200 shrink-0">
                        <button type="submit" id="btn_submit_create"
                            class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent bg-indigo-600 px-6 py-2.5 text-base font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:text-sm transition">
                            Guardar Operación
                        </button>
                        <button type="button"
                            class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-base font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:text-sm transition"
                            onclick="closeCreateModal()">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Details Operation -->
    <div id="detailsModal" class="fixed inset-0 z-[70] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" id="detailsModalBackdrop">
        </div>
        <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
            <div class="relative bg-white rounded-xl text-left shadow-2xl transform transition-all sm:my-8 max-w-6xl w-full ring-1 ring-gray-200 flex flex-col"
                style="max-height: 85vh;">
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center shrink-0 rounded-t-xl">
                    <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2">
                        <i class="fas fa-info-circle"></i> Detalles de Operación <span id="det_ref"
                            class="ml-2 bg-indigo-800 text-indigo-100 px-2 py-1 rounded text-xs font-mono tracking-wider shadow-sm"></span>
                    </h3>
                    <button type="button" class="text-indigo-200 hover:text-white transition" onclick="closeDetailsModal()">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-0 flex flex-col lg:flex-row">
                    <!-- Left info -->
                    <div class="p-6 lg:w-1/2 border-r border-gray-100 space-y-5">
                        <h4 class="text-base font-bold text-gray-800 border-b pb-2"><i
                                class="fas fa-list-alt text-indigo-500 mr-2"></i> Información General</h4>
                        <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
                            <div class="col-span-2 sm:col-span-1">
                                <span class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Cliente</span>
                                <span class="block text-gray-800 font-medium" id="det_cliente"></span>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <span
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Importador</span>
                                <span class="block text-gray-800 font-medium" id="det_importador"></span>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <span class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Aduana</span>
                                <span class="block text-gray-800 font-medium" id="det_aduana"></span>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <span class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Bodega</span>
                                <span class="block text-gray-800 font-medium" id="det_bodega"></span>
                            </div>
                            <div class="col-span-2">
                                <span class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Producto</span>
                                <span class="block text-gray-800 font-medium" id="det_producto"></span>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <span class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Factura</span>
                                <span
                                    class="block text-gray-800 font-mono text-[13px] bg-gray-50 px-2 py-1 rounded border border-gray-200 inline-block mt-1"
                                    id="det_factura"></span>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <span
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Transporte</span>
                                <div class="mt-1 flex items-center gap-2">
                                    <span
                                        class="text-gray-800 font-mono text-[13px] bg-gray-50 px-2 py-1 rounded border border-gray-200 inline-block"
                                        title="Num. Thermo"><i class="fas fa-truck text-xs text-gray-400 mr-1"></i><span
                                            id="det_thermo"></span></span>
                                    <span
                                        class="text-gray-800 font-mono text-[13px] bg-gray-50 px-2 py-1 rounded border border-gray-200 inline-block"
                                        title="Código Alpha"><i class="fas fa-barcode text-xs text-gray-400 mr-1"></i><span
                                            id="det_alpha"></span></span>
                                </div>
                            </div>
                        </div>

                        <h4 class="text-base font-bold text-gray-800 border-b pb-2 pt-2"><i
                                class="fas fa-file-contract text-indigo-500 mr-2"></i> Documentación Aduanera</h4>
                        <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
                            <div class="col-span-2 sm:col-span-1">
                                <span class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Pedimento</span>
                                <span
                                    class="block text-indigo-700 font-mono text-[13px] font-bold bg-indigo-50 px-2 py-1 rounded border border-indigo-100 inline-block mt-1"
                                    id="det_pedimento"></span>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <span class="block text-xs font-bold text-gray-500 uppercase tracking-wide">DODA</span>
                                <span
                                    class="block text-indigo-700 font-mono text-[13px] font-bold bg-indigo-50 px-2 py-1 rounded border border-indigo-100 inline-block mt-1"
                                    id="det_doda"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Files -->
                    <div class="p-6 lg:w-1/2 bg-gray-50 mt-4 lg:mt-0 flex flex-col">
                        <div class="flex justify-between items-center mb-4 border-b pb-3 border-gray-200 gap-4">
                            <h4 class="text-base font-bold text-gray-800 shrink-0"><i
                                    class="fas fa-folder-open text-indigo-500 mr-2"></i> Archivos Adjuntos</h4>
                            <div class="flex-1 text-right">
                                <input type="file" id="inlineFileInput" class="hidden"
                                    accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx" onchange="uploadSingleFile(this)">
                                <button type="button" onclick="document.getElementById('inlineFileInput').click()"
                                    class="bg-indigo-600 text-white hover:bg-indigo-700 px-3 py-1.5 rounded shadow-sm transition text-xs font-bold cursor-pointer">
                                    <i class="fas fa-upload mr-1"></i> Subir Archivo
                                </button>
                            </div>
                        </div>

                        <div id="details_files_list" class="space-y-3 overflow-y-auto pr-2 flex-1 relative min-h-[250px]">
                            <!-- Archivos cargados por js -->
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 rounded-b-xl border-t border-gray-200 text-right">
                    <button type="button"
                        class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-base font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition"
                        onclick="closeDetailsModal()">Cerrar Detalles</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal DODA Error -->
    <div id="dodaErrorModal" class="fixed inset-0 z-[80] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>
        <div class="flex items-center justify-center min-h-full p-4 text-center">
            <div class="relative bg-white rounded-xl text-left shadow-2xl transform transition-all w-full max-w-md">
                <div class="bg-red-600 px-6 py-4 flex justify-between items-center rounded-t-xl">
                    <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i> Inconsistencia en DODA
                    </h3>
                    <button type="button" class="text-red-200 hover:text-white transition" onclick="closeDodaErrorModal()">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div class="flex items-start mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-excel text-red-500 text-3xl"></i>
                        </div>
                        <div class="ml-4 w-full">
                            <h4 class="text-sm font-bold text-gray-900 mb-1">Se detectaron los siguientes errores de
                                validación con el portal de SOIA:</h4>
                            <div id="dodaErrorContent"
                                class="text-sm text-gray-600 bg-red-50 p-3 rounded border border-red-100 mt-2">
                                <!-- Error list injected here -->
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button"
                            class="inline-flex justify-center rounded-lg border border-transparent bg-red-600 px-5 py-2 text-sm font-bold text-white hover:bg-red-700 shadow-sm transition"
                            onclick="closeDodaErrorModal()">Entendido</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tailwind UI via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let globalExpedientes = {};
        let globalOperaciones = [];
        let chartInstance = null;
        let autoRefresh = null;

        document.addEventListener('DOMContentLoaded', () => {
            initChart();
            fetchLiveData();

            // Polling cada 6 segundos
            autoRefresh = setInterval(fetchLiveData, 6000);

            // Click outside modal to close
            document.getElementById('modalBackdrop').addEventListener('click', closeModal);
        });

        function openDodaErrorModal(encodedError) {
            let decoded = decodeURIComponent(encodedError);
            document.getElementById('dodaErrorContent').innerHTML = decoded;
            document.getElementById('dodaErrorModal').classList.remove('hidden');
        }

        function closeDodaErrorModal() {
            document.getElementById('dodaErrorModal').classList.add('hidden');
        }

        function initChart() {
            const ctx = document.getElementById('modulacionChart').getContext('2d');
            chartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Desaduanamiento Libre (Verde)', 'Reconocimiento (Rojo)'],
                    datasets: [{
                        data: [0, 0],
                        backgroundColor: ['#10b981', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return ' ' + context.raw + ' Operaciones';
                                }
                            }
                        }
                    }
                }
            });
        }

        function formatTime(date) {
            return date.getHours().toString().padStart(2, '0') + ':' +
                date.getMinutes().toString().padStart(2, '0') + ':' +
                date.getSeconds().toString().padStart(2, '0');
        }

        function fetchLiveData() {
            fetch('{{ route("documentador.liveData") }}')
                .then(res => res.json())
                .then(data => {
                    globalExpedientes = data.expedientes;
                    globalOperaciones = data.operaciones;

                    // Actualizar contadores y gráfica
                    document.getElementById('count_verdes').innerText = data.grafica.verdes;
                    document.getElementById('count_rojas').innerText = data.grafica.rojas;

                    chartInstance.data.datasets[0].data = [data.grafica.verdes, data.grafica.rojas];
                    chartInstance.update();

                    // Actualizar Tabla
                    const tbody = document.getElementById('live_ops_table');
                    document.getElementById('ops_count').innerText = data.operaciones.length + " Encontradas";

                    if (data.operaciones.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-12 text-center text-gray-500 bg-gray-50 border-b border-gray-100"><i class="fas fa-inbox text-4xl mb-3 text-gray-300 block"></i> No tienes operaciones asignadas el día de hoy.</td></tr>`;
                    } else {
                        let rows = '';
                        data.operaciones.forEach(op => {
                            let dodaBadge = op.doda ? `<span class="bg-gray-100 border border-gray-300 text-gray-800 px-2 py-0.5 rounded text-[11px] font-mono font-bold tracking-tight"><i class="fas fa-qrcode text-gray-400 mr-1"></i>${op.doda}</span>` : `<span class="bg-red-50 text-red-600 px-2 py-0.5 rounded text-xs font-semibold"><i class="fas fa-exclamation-triangle"></i> Sin DODA</span>`;

                            let pedimentoBadge = op.pedimento ? `<span class="bg-gray-100 border border-gray-300 text-gray-800 px-2 py-0.5 rounded text-[11px] font-mono font-bold tracking-tight"><i class="fas fa-passport text-gray-400 mr-1"></i>${op.pedimento}</span>` : `<span class="text-gray-400 text-xs">-</span>`;

                            let estatusBadge = '';
                            if (op.modulacion) {
                                if (op.modulacion === 'DESADUANAMIENTO LIBRE') {
                                    estatusBadge = `<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-[10px] font-bold tracking-wider border border-green-200 mt-1 block w-max mx-auto">${op.modulacion}</span>`;
                                } else if (op.modulacion === 'ERROR DODA NO COINCIDE') {
                                    let tooltipInfo = "El número de DODA / Pedimento ingresado no corresponde.";
                                    if (op.bot_logs && op.bot_logs.length > 0) {
                                        let lastLog = op.bot_logs[op.bot_logs.length - 1];
                                        if (lastLog.errores && lastLog.errores.length > 0) {
                                            tooltipInfo = lastLog.errores.join('<br>');
                                        }
                                    }
                                    let encodedError = encodeURIComponent(tooltipInfo);
                                    estatusBadge = `<button type="button" onclick="openDodaErrorModal('${encodedError}')" class="bg-red-100 text-red-800 px-2 py-1 rounded text-[10px] font-bold tracking-wider border border-red-300 mt-1 block w-max mx-auto shadow-sm hover:bg-red-200 transition"><i class="fas fa-exclamation-circle mr-1"></i>${op.modulacion}</button>`;
                                } else {
                                    estatusBadge = `<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-[10px] font-bold tracking-wider border border-red-200 mt-1 block w-max mx-auto">${op.modulacion}</span>`;
                                }
                            } else if (op.pedimento && op.doda) {
                                estatusBadge = `<span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-[10px] font-bold tracking-wider border border-yellow-200 mt-1 block w-max mx-auto">EN ESPERA DE MODULACIÓN</span>`;
                            } else if (op.estado === 'proceso') {
                                estatusBadge = `<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-[10px] font-bold tracking-wider border border-blue-200 mt-1 block w-max mx-auto">EN PROCESO</span>`;
                            } else {
                                estatusBadge = `<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-[10px] font-bold tracking-wider border border-gray-200 mt-1 block w-max mx-auto">${op.estado.toUpperCase()}</span>`;
                            }

                            rows += `
                                <tr class="hover:bg-indigo-50/50 transition group border-b border-gray-100">
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-gray-800 font-mono text-sm tracking-tight">${op.referencia}</p>
                                        <p class="text-xs text-gray-500 truncate max-w-[150px]" title="${op.cliente_nombre}">${op.cliente_nombre}</p>
                                    </td>
                                    <td class="px-4 py-3 align-middle">
                                        <span class="bg-gray-100 border border-gray-300 text-gray-800 px-2 py-0.5 rounded text-[11px] font-mono font-bold tracking-tight"><i class="fas fa-file-invoice text-gray-400 mr-1"></i>${op.factura}</span>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="flex flex-col gap-1.5 items-start mt-0.5">
                                            ${dodaBadge}
                                            ${pedimentoBadge}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center align-middle">
                                        ${estatusBadge}
                                    </td>
                                    <td class="px-4 py-3 text-center align-middle">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="openModal(${op.id}, '${op.referencia}', '${op.cliente_nombre}', '${op.doda || ''}', ${op.pedimento_id || 'null'}, ${op.cliente_id})" class="text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white border border-indigo-200 px-2.5 py-1.5 rounded shadow-sm transition transform group-hover:scale-105" title="Asignar DODA/Pedimento">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="openDetailsModal(${op.id})" class="text-white bg-indigo-600 hover:bg-indigo-700 px-2.5 py-1.5 rounded shadow-sm transition transform group-hover:scale-105" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                `;
                        });
                        tbody.innerHTML = rows;
                    }

                    document.getElementById('last_update_time').innerHTML = `<i class="fas fa-sync-alt text-green-500 ${data.operaciones.length > 0 ? 'animate-spin-fast' : ''}"></i> Actualizado: ${formatTime(new Date())}`;
                })
                .catch(err => {
                    console.error("Live Data Error", err);
                    document.getElementById('last_update_time').innerHTML = `<span class="text-red-500"><i class="fas fa-wifi"></i> Conexión Perdida</span>`;
                });
        }

        // Funciones del Modal
        function openModal(id, referencia, clienteNombre, currentDoda, pedimentoId, clienteId) {
            // Pausar polling temporalmente
            clearInterval(autoRefresh);

            const modal = document.getElementById('updateModal');
            modal.classList.remove('hidden');

            document.getElementById('op_id').value = id;
            document.getElementById('modal_ref_display').innerText = `${referencia} - ${clienteNombre}`;
            document.getElementById('num_doda').value = currentDoda !== 'null' ? currentDoda : '';

            // Construir opciones de Pedimento según el cliente
            const select = document.getElementById('pedimento_id');
            let optionsHtml = '<option value="">-- Seleccionar Pedimento --</option>';

            if (globalExpedientes[clienteId]) {
                globalExpedientes[clienteId].forEach(exp => {
                    let selected = (exp.id === pedimentoId) ? 'selected' : '';
                    optionsHtml += `<option value="${exp.id}" ${selected}>Pedimento #${exp.numero_pedimento}</option>`;
                });
            }

            select.innerHTML = optionsHtml;
        }

        function closeModal() {
            document.getElementById('updateModal').classList.add('hidden');
            document.getElementById('updateForm').reset();

            // Reanudar polling
            autoRefresh = setInterval(fetchLiveData, 6000);
        }

        function submitUpdate(e) {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('btn_submit');
            const originalText = btn.innerHTML;

            btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Guardando...`;
            btn.disabled = true;

            const formData = new FormData(form);
            const opId = document.getElementById('op_id').value;

            fetch(`/documentador/update-doda/${opId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        fetchLiveData(); // Actualizar tabla inmediatamente
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Error de conexión al guardar.");
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }

        // Modal de Nueva Operación y Drag & Drop
        let filesToUpload = [];

        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
            filesToUpload = [];
            renderFileList();
            document.getElementById('createForm').reset();
            document.querySelector('input[name="fecha_registro"]').value = new Date().toISOString().split('T')[0];
            clearInterval(autoRefresh);
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
            autoRefresh = setInterval(fetchLiveData, 6000);
        }

        // Lógica Drag & Drop
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('fileInput');

        dropzone.addEventListener('click', () => fileInput.click());

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('border-indigo-500', 'bg-indigo-50');
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('border-indigo-500', 'bg-indigo-50');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-indigo-500', 'bg-indigo-50');
            if (e.dataTransfer.files.length > 0) {
                handleFiles(e.dataTransfer.files);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFiles(e.target.files);
                fileInput.value = '';
            }
        });

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                filesToUpload.push({
                    file: file,
                    uid: Math.random().toString(36).substring(7),
                    type: 'otros' // default
                });
            });
            renderFileList();
        }

        function removeFile(uid) {
            filesToUpload = filesToUpload.filter(f => f.uid !== uid);
            renderFileList();
        }

        function updateFileType(uid, selectElem) {
            let f = filesToUpload.find(x => x.uid === uid);
            if (f) f.type = selectElem.value;
        }

        function renderFileList() {
            const list = document.getElementById('fileList');
            if (filesToUpload.length === 0) {
                list.innerHTML = '';
                return;
            }

            let html = '';
            filesToUpload.forEach(item => {
                let size = (item.file.size / 1024 / 1024).toFixed(2);
                html += `
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-3 bg-white border border-gray-200 rounded-lg shadow-sm gap-3">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="p-2 bg-indigo-100 text-indigo-600 rounded text-xl"><i class="fas fa-file-alt"></i></div>
                                <div class="truncate">
                                    <p class="text-sm font-bold text-gray-700 truncate">${item.file.name}</p>
                                    <p class="text-xs text-gray-400">${size} MB</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                                <select onchange="updateFileType('${item.uid}', this)" class="w-full sm:w-40 px-2 py-1.5 text-xs font-bold text-gray-700 bg-gray-50 border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="Factura" ${item.type === 'Factura' ? 'selected' : ''}>Factura</option>
                                    <option value="DODA" ${item.type === 'DODA' ? 'selected' : ''}>DODA</option>
                                    <option value="Guía" ${item.type === 'Guía' ? 'selected' : ''}>Guía</option>
                                    <option value="Permiso" ${item.type === 'Permiso' ? 'selected' : ''}>Permiso</option>
                                    <option value="Pedimento" ${item.type === 'Pedimento' ? 'selected' : ''}>Pedimento</option>
                                    <option value="otros" ${item.type === 'otros' ? 'selected' : ''}>Otros</option>
                                </select>
                                <button type="button" onclick="removeFile('${item.uid}')" class="text-red-500 hover:text-red-700 p-2 rounded hover:bg-red-50 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
            });
            list.innerHTML = html;
        }

        function submitCreate(e) {
            e.preventDefault();

            const form = e.target;
            const btn = document.getElementById('btn_submit_create');
            const originalText = btn.innerHTML;

            btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Guardando...`;
            btn.disabled = true;

            const formData = new FormData(form);

            // Append files manually
            filesToUpload.forEach((item, index) => {
                formData.append(`archivos[${index}]`, item.file);
                formData.append(`tipos_archivos[${index}]`, item.type);
            });

            fetch('{{ route("documentador.storeOperacion") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(async res => {
                    const data = await res.json();
                    if (data.success) {
                        // Notifica y cierra modal
                        closeCreateModal();
                        fetchLiveData();
                    } else {
                        if (res.status === 422) {
                            let errs = Object.values(data.errors).flat().join('\\n');
                            alert("Revisa los campos:\\n" + errs);
                        } else {
                            alert("Error: " + (data.message || 'Error al guardar'));
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Error de conexión al procesar la operación.");
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }

        // Modal de Detalles y Archivos
        let currentDetailsOpId = null;

        function openDetailsModal(opId) {
            clearInterval(autoRefresh);
            currentDetailsOpId = opId;
            const op = globalOperaciones.find(o => o.id === opId);
            if (!op) return;

            document.getElementById('detailsModal').classList.remove('hidden');
            document.getElementById('det_ref').innerText = op.referencia;
            document.getElementById('det_cliente').innerText = op.cliente_nombre;
            document.getElementById('det_importador').innerText = op.importador;
            document.getElementById('det_aduana').innerText = op.aduana;
            document.getElementById('det_bodega').innerText = op.bodega || 'N/A';
            document.getElementById('det_producto').innerText = op.producto || 'N/A';
            document.getElementById('det_factura').innerText = op.factura;
            document.getElementById('det_thermo').innerText = op.thermo || 'N/A';
            document.getElementById('det_alpha').innerText = op.alpha || 'N/A';
            document.getElementById('det_pedimento').innerText = op.pedimento || 'S/A';
            document.getElementById('det_doda').innerText = op.doda || 'S/A';

            renderModalFiles(op.documentos);
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.add('hidden');
            autoRefresh = setInterval(fetchLiveData, 6000);
        }

        function renderModalFiles(docs) {
            const list = document.getElementById('details_files_list');
            if (!docs || docs.length === 0) {
                list.innerHTML = `<div class="text-center py-10 text-gray-400">
                        <i class="fas fa-inbox text-4xl mb-3 block text-gray-300"></i>
                        <p class="text-sm">Sin archivos adjuntos.</p>
                    </div>`;
                return;
            }

            let html = '';
            docs.forEach(doc => {
                html += `
                    <div class="flex flex-col xl:flex-row items-center justify-between p-3 bg-white border border-gray-200 rounded-lg shadow-sm gap-3 transition hover:shadow-md group">
                        <div class="flex items-center gap-3 overflow-hidden w-full xl:w-auto">
                            <div class="p-2.5 bg-indigo-50 text-indigo-500 rounded text-xl shrink-0"><i class="fas fa-file-alt"></i></div>
                            <div class="truncate">
                                <p class="text-sm font-bold text-gray-700 truncate" title="${doc.nombre}">${doc.nombre}</p>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">${doc.tipo}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 w-full xl:w-auto mt-2 xl:mt-0 justify-end">
                            <a href="${doc.preview_url}" target="_blank" class="text-gray-500 hover:text-indigo-600 bg-gray-50 hover:bg-indigo-50 border border-gray-200 p-2 rounded transition" title="Vista Previa">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="${doc.download_url}" target="_blank" class="text-gray-500 hover:text-green-600 bg-gray-50 hover:bg-green-50 border border-gray-200 p-2 rounded transition" title="Descargar">
                                <i class="fas fa-download"></i>
                            </a>
                            <button type="button" onclick="deleteDocumentModal(${doc.id})" class="text-gray-500 hover:text-red-600 bg-gray-50 hover:bg-red-50 border border-gray-200 p-2 rounded transition" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>`;
            });
            list.innerHTML = html;
        }

        function uploadSingleFile(input) {
            if (!input.files || input.files.length === 0) return;
            if (!currentDetailsOpId) return;

            const file = input.files[0];
            const formData = new FormData();
            formData.append('operacion_id', currentDetailsOpId);
            formData.append('archivos[0]', file);
            formData.append('tipos_documento[0]', 'otros');

            // Show uploading state
            input.disabled = true;

            fetch('{{ route("documentos_operacion.store2") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fetchLiveDataForModal();
                    } else {
                        alert("Error al subir: " + (data.message || ''));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Error al subir archivo.");
                })
                .finally(() => {
                    input.disabled = false;
                    input.value = '';
                });
        }

        function deleteDocumentModal(docId) {
            if (!confirm("¿Seguro que deseas eliminar este archivo?")) return;

            fetch(`/documentos/${docId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
                .then(res => {
                    fetchLiveDataForModal();
                })
                .catch(err => alert("Error de conexión."));
        }

        function fetchLiveDataForModal() {
            return fetch('{{ route("documentador.liveData") }}')
                .then(res => res.json())
                .then(data => {
                    globalOperaciones = data.operaciones;
                    const op = globalOperaciones.find(o => o.id === currentDetailsOpId);
                    if (op) {
                        renderModalFiles(op.documentos);
                    }
                    fetchLiveData(); // refresh rest of the page UI as well in background
                });
        }

        // ==========================================
        // CONSULTA MANUAL DE MODULACIÓN
        // ==========================================
        function consultarModulacionManual() {
            if (!confirm('¿Deseas consultar la modulación de las operaciones pendientes?\n\nEsta acción puede tardar unos segundos.')) {
                return;
            }

            const btn = document.getElementById('btn_consulta_modulacion');
            const originalContent = btn.innerHTML;

            // Deshabilitar botón y mostrar loading
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Consultando...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');

            // Mostrar modal de carga
            showModulacionLoadingModal();

            // Hacer la petición al endpoint del bot
            fetch('/api/bot/doda/ejecutar?token={{ env("CHECK_TRAFICO_TOKEN") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                }
            })
                .then(response => response.json())
                .then(data => {
                    hideModulacionLoadingModal();

                    if (data.success) {
                        showModulacionSuccessModal(data);
                        // Recargar los datos de la página
                        setTimeout(() => {
                            fetchLiveData();
                        }, 2000);
                    } else {
                        showModulacionErrorModal(data.message || 'Error al consultar la modulación');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    hideModulacionLoadingModal();
                    showModulacionErrorModal('Error de conexión. Intenta de nuevo más tarde.');
                })
                .finally(() => {
                    // Restaurar botón
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                });
        }

        function showModulacionLoadingModal() {
            const modalHtml = `
                    <div id="modulacionLoadingModal" class="fixed inset-0 z-[90] flex items-center justify-center">
                        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
                        <div class="relative bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4 text-center">
                            <div class="mb-4">
                                <i class="fas fa-robot text-6xl text-indigo-500 animate-bounce"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Consultando Modulación</h3>
                            <p class="text-sm text-gray-500 mb-4">El SOIA-Bot está consultando el estado de tus operaciones en el sistema del SAT...</p>
                            <div class="flex justify-center">
                                <i class="fas fa-spinner fa-spin text-3xl text-indigo-600"></i>
                            </div>
                            <p class="text-xs text-gray-400 mt-3">Esto puede tardar unos segundos</p>
                        </div>
                    </div>
                `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        function hideModulacionLoadingModal() {
            const modal = document.getElementById('modulacionLoadingModal');
            if (modal) {
                modal.remove();
            }
        }

        function showModulacionSuccessModal(data) {
            const modalHtml = `
                    <div id="modulacionSuccessModal" class="fixed inset-0 z-[90] flex items-center justify-center">
                        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
                        <div class="relative bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
                            <div class="text-center mb-4">
                                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                                    <i class="fas fa-check text-3xl text-green-600"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">¡Consulta Completada!</h3>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4 mb-4 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Operaciones consultadas:</span>
                                    <span class="font-bold text-gray-800">${data.total_consultadas || 0}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Cambios detectados:</span>
                                    <span class="font-bold text-indigo-600">${data.total_cambios || 0}</span>
                                </div>
                                ${data.total_errores > 0 ? `
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Errores:</span>
                                    <span class="font-bold text-red-600">${data.total_errores}</span>
                                </div>
                                ` : ''}
                            </div>

                            <p class="text-xs text-gray-500 mb-4">La página se actualizará automáticamente para mostrar los cambios.</p>

                            <button onclick="closeModulacionSuccessModal()" 
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-lg transition">
                                <i class="fas fa-check mr-1"></i> Entendido
                            </button>
                        </div>
                    </div>
                `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        function closeModulacionSuccessModal() {
            const modal = document.getElementById('modulacionSuccessModal');
            if (modal) {
                modal.remove();
            }
        }

        function showModulacionErrorModal(message) {
            const modalHtml = `
                    <div id="modulacionErrorModal" class="fixed inset-0 z-[90] flex items-center justify-center">
                        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
                        <div class="relative bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
                            <div class="text-center mb-4">
                                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                                    <i class="fas fa-exclamation-triangle text-3xl text-red-600"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Error en la Consulta</h3>
                            </div>

                            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                                <p class="text-sm text-red-700">${message}</p>
                            </div>

                            <button onclick="closeModulacionErrorModal()" 
                                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-lg transition">
                                <i class="fas fa-times mr-1"></i> Cerrar
                            </button>
                        </div>
                    </div>
                `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        function closeModulacionErrorModal() {
            const modal = document.getElementById('modulacionErrorModal');
            if (modal) {
                modal.remove();
            }
        }
    </script>

    <style>
        .animate-spin-fast {
            animation: spin 1s linear infinite;
        }
    </style>
@endsection