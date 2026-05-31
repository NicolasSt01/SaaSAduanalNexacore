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
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            <!-- Tabla (3/4 del ancho) -->
            <div class="lg:col-span-3 flex flex-col bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                style="min-height: 600px;">
                <div class="bg-gray-50 p-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-700"><i class="fas fa-list-ul text-indigo-500 mr-2"></i>
                        Operaciones</h2>
                    <div class="flex items-center gap-3">
                        <button onclick="toggleFilters()" class="bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm transition"><i class="fas fa-filter mr-1"></i> Filtros</button>
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

                <!-- Panel de Filtros -->
                <div id="filterPanel" class="hidden bg-gray-50 border-b border-gray-200 p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Cliente</label>
                            <select id="filter_cliente" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-2 border">
                                <option value="">Todos</option>
                                @foreach($opFiltros['clientes'] as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Referencia</label>
                            <input type="text" id="filter_referencia" placeholder="Buscar..." class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-2 border">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Aduana</label>
                            <select id="filter_aduana" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-2 border">
                                <option value="">Todas</option>
                                @foreach($opFiltros['aduanas'] as $a)
                                    <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Bodega</label>
                            <select id="filter_bodega" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-2 border">
                                <option value="">Todas</option>
                                @foreach($opFiltros['bodegas'] as $b)
                                    <option value="{{ $b->id }}">{{ $b->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Desde</label>
                            <input type="date" id="filter_fecha_desde" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-2 border">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Hasta</label>
                            <input type="date" id="filter_fecha_hasta" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-2 border">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Búsqueda Global</label>
                        <div class="relative">
                            <input type="search" id="filter_q" placeholder="Buscar por cliente, bodega, referencia, pedimento, thermo, alpha, factura, producto..." class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-2 border pl-9">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">La búsqueda por texto ignora los filtros de fecha y busca en todo el histórico del tenant.</p>
                    </div>
                    <div class="flex items-center gap-3 mt-3">
                        <button onclick="applyFilters()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-sm transition"><i class="fas fa-search mr-1"></i> Buscar</button>
                        <button onclick="clearFilters()" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-600 text-xs font-bold px-4 py-2 rounded-lg shadow-sm transition"><i class="fas fa-times mr-1"></i> Limpiar</button>
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">

                    <h3 class="text-sm font-black text-gray-800 mb-3"><i
                            class="fas fa-traffic-light text-indigo-500 mr-2"></i> Monitor de Modulación</h3>
                    <div class="relative h-48 w-full flex items-center justify-center">
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
                        <div class="text-center">
                            <span class="block text-2xl font-black text-gray-400" id="count_canceladas">0</span>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Canceladas</span>
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
                        <h4 class="text-base font-bold text-gray-800 border-b pb-2 flex items-center justify-between"><span><i
                                class="fas fa-list-alt text-indigo-500 mr-2"></i> Información General</span>
                            <a href="#" id="det_editar_link" class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-600 px-2 py-1 rounded font-bold transition border border-indigo-200">Ir a operación <i class="fas fa-external-link-alt ml-1"></i></a>
                        </h4>
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
                            <span id="uploadQueueCount" class="hidden text-xs font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full"></span>
                        </div>

                        <!-- Drag & Drop Zone -->
                        <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/50 transition cursor-pointer mb-3"
                            ondragover="event.preventDefault(); this.classList.add('border-indigo-500','bg-indigo-50')"
                            ondragleave="this.classList.remove('border-indigo-500','bg-indigo-50')"
                            ondrop="handleDrop(event)"
                            onclick="document.getElementById('dragFileInput').click()">
                            <input type="file" id="dragFileInput" class="hidden" multiple accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx" onchange="handleFileSelect(this)">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2 block"></i>
                            <p class="text-sm font-bold text-gray-500">Arrastra archivos aquí</p>
                            <p class="text-xs text-gray-400 mt-1">o haz clic para seleccionar</p>
                        </div>

                        <!-- Cola de archivos pendientes de subir -->
                        <div id="uploadQueue" class="hidden shrink-0 space-y-2 mb-3 max-h-48 overflow-y-auto bg-amber-50/50 rounded-lg p-2 border border-amber-200"></div>

                        <!-- Botón de carga masiva -->
                        <button id="btnUploadAll" onclick="uploadAllFiles()" class="hidden w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition mb-3">
                            <i class="fas fa-cloud-upload-alt mr-1"></i> Cargar <span id="uploadAllCount">0</span> archivos
                        </button>

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

    <!-- Modal Cancelar Operación -->
    <div id="cancelOpModal" class="fixed inset-0 z-[80] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" onclick="closeCancelModal()"></div>
        <div class="flex items-center justify-center min-h-full p-4 text-center">
            <div class="relative bg-white rounded-xl text-left shadow-2xl transform transition-all w-full max-w-md">
                <div class="bg-red-600 px-6 py-4 flex justify-between items-center rounded-t-xl">
                    <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i> Cancelar Operación <span id="cancelOpRef" class="ml-2 bg-red-800 text-red-100 px-2 py-1 rounded text-xs font-mono"></span>
                    </h3>
                    <button type="button" class="text-red-200 hover:text-white transition" onclick="closeCancelModal()"><i class="fas fa-times fa-lg"></i></button>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-600">Esta acción cambiará el estado de la operación a <span class="font-bold text-red-600">CANCELADA</span>. Las operaciones canceladas no se contabilizan en las métricas operativas.</p>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wide">Motivo de Cancelación *</label>
                        <textarea id="cancelMotivo" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm p-2.5 border" placeholder="Describe el motivo por el cual se cancela esta operación..." required></textarea>
                    </div>
                    <div class="flex items-start gap-3 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <input type="checkbox" id="cancelDeleteDocs" class="mt-1 h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                        <label for="cancelDeleteDocs" class="text-sm text-gray-700">
                            <span class="font-bold text-yellow-700">Eliminar documentos adjuntos</span><br>
                            <span class="text-xs text-gray-500">Si marcas esta opción, los documentos subidos a esta operación serán eliminados permanentemente de Cloudflare R2.</span>
                        </label>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex sm:flex-row-reverse gap-3 rounded-b-xl border-t border-gray-200">
                    <button id="btnSubmitCancel" onclick="submitCancelOp()" class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent bg-red-600 px-6 py-2.5 text-base font-bold text-white shadow-sm hover:bg-red-700 transition">
                        <i class="fas fa-ban mr-1"></i> Confirmar Cancelación
                    </button>
                    <button type="button" onclick="closeCancelModal()" class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-base font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
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
        let initialLoadDone = false;
        const urlOpParam = new URLSearchParams(window.location.search).get('op');

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
                    labels: ['Desaduanamiento Libre (Verde)', 'Reconocimiento (Rojo)', 'Canceladas'],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: ['#10b981', '#ef4444', '#9ca3af'],
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

        function getFilterParams() {
            const params = new URLSearchParams();
            const q = document.getElementById('filter_q')?.value?.trim();
            if (q) params.append('q', q);

            // INC-020: Pasar op en carga inicial para incluirlo sin filtro de fecha
            if (!initialLoadDone && urlOpParam) {
                params.append('op', urlOpParam);
            }
            const cliente = document.getElementById('filter_cliente')?.value;
            const referencia = document.getElementById('filter_referencia')?.value?.trim();
            const aduana = document.getElementById('filter_aduana')?.value;
            const bodega = document.getElementById('filter_bodega')?.value;
            const fechaDesde = document.getElementById('filter_fecha_desde')?.value;
            const fechaHasta = document.getElementById('filter_fecha_hasta')?.value;
            const estado = document.getElementById('filter_estado')?.value;
            if (cliente) params.append('cliente_id', cliente);
            if (referencia) params.append('referencia', referencia);
            if (aduana) params.append('aduana_id', aduana);
            if (bodega) params.append('bodega_id', bodega);
            if (fechaDesde) params.append('fecha_desde', fechaDesde);
            if (fechaHasta) params.append('fecha_hasta', fechaHasta);
            if (estado) params.append('estado_filtro', estado);
            return params;
        }

        function fetchLiveData() {
            const params = getFilterParams();
            fetch('{{ route("documentador.liveData") }}?' + params.toString())
                .then(res => res.json())
                .then(data => {
                    globalExpedientes = data.expedientes;
                    globalOperaciones = data.operaciones;

                    // Actualizar contadores y gráfica
                    document.getElementById('count_verdes').innerText = data.grafica.verdes;
                    document.getElementById('count_rojas').innerText = data.grafica.rojas;
                    document.getElementById('count_canceladas').innerText = data.grafica.canceladas || 0;

                    chartInstance.data.datasets[0].data = [data.grafica.verdes, data.grafica.rojas, data.grafica.canceladas || 0];
                    chartInstance.update();

                    // Actualizar Tabla
                    const tbody = document.getElementById('live_ops_table');
                    document.getElementById('ops_count').innerText = data.operaciones.length + " Encontradas";

                    renderOpsTable(data.operaciones);

                    // INC-020: Auto-abrir detalle de operación desde notificación (?op=)
                    if (!initialLoadDone && urlOpParam) {
                        initialLoadDone = true;
                        const targetOp = globalOperaciones.find(o => o.id == urlOpParam);
                        if (targetOp) {
                            setTimeout(() => openDetailsModal(targetOp.id), 500);
                        }
                    }

                    document.getElementById('last_update_time').innerHTML = `<i class="fas fa-sync-alt text-green-500 ${data.operaciones.length > 0 ? 'animate-spin-fast' : ''}"></i> Actualizado: ${formatTime(new Date())}`;
                })
                .catch(err => {
                    console.error("Live Data Error", err);
                    document.getElementById('last_update_time').innerHTML = `<span class="text-red-500"><i class="fas fa-wifi"></i> Conexión Perdida</span>`;
                });
        }

        function renderOpsTable(ops) {
            const tbody = document.getElementById('live_ops_table');
            if (!ops || ops.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-12 text-center text-gray-500 bg-gray-50 border-b border-gray-100"><i class="fas fa-inbox text-4xl mb-3 text-gray-300 block"></i> No hay operaciones para mostrar.</td></tr>`;
                return;
            }
            let rows = '';
            ops.forEach(op => {
                let isCancelled = op.estado === 'cancelada';
                let rowClass = isCancelled ? 'bg-red-50/60 opacity-75' : 'hover:bg-indigo-50/50';

                let dodaBadge = op.doda ? `<span class="bg-gray-100 border border-gray-300 text-gray-800 px-2 py-0.5 rounded text-[11px] font-mono font-bold tracking-tight"><i class="fas fa-qrcode text-gray-400 mr-1"></i>${op.doda}</span>` : `<span class="bg-red-50 text-red-600 px-2 py-0.5 rounded text-xs font-semibold"><i class="fas fa-exclamation-triangle"></i> Sin DODA</span>`;

                let pedimentoBadge = op.pedimento ? `<span class="bg-gray-100 border border-gray-300 text-gray-800 px-2 py-0.5 rounded text-[11px] font-mono font-bold tracking-tight"><i class="fas fa-passport text-gray-400 mr-1"></i>${op.pedimento}</span>` : `<span class="text-gray-400 text-xs">-</span>`;

                let fechaCruce = op.fecha_cruce ? `<span class="text-[10px] text-gray-400 block mt-0.5"><i class="fas fa-calendar-alt mr-0.5"></i>${op.fecha_cruce}</span>` : '';

                let estatusBadge = '';
                if (isCancelled) {
                    estatusBadge = `<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-[10px] font-bold tracking-wider border border-red-300 mt-1 block w-max mx-auto"><i class="fas fa-ban mr-1"></i>CANCELADA</span>`;
                } else if (op.modulacion) {
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
                    <tr class="${rowClass} transition group border-b border-gray-100">
                        <td class="px-4 py-3">
                            <p class="font-bold text-gray-800 font-mono text-sm tracking-tight ${isCancelled ? 'line-through text-red-400' : ''}">${op.referencia}</p>
                            <p class="text-xs text-gray-500 truncate max-w-[150px]" title="${op.cliente_nombre}">${op.cliente_nombre}</p>
                            ${fechaCruce}
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span class="bg-gray-100 border border-gray-300 text-gray-800 px-2 py-0.5 rounded text-[11px] font-mono font-bold tracking-tight"><i class="fas fa-file-invoice text-gray-400 mr-1"></i>${op.factura}</span>
                            ${isCancelled ? `<p class="text-[10px] text-red-500 mt-1 italic" title="${op.motivo_cancelacion || ''}">${op.motivo_cancelacion ? op.motivo_cancelacion.substring(0, 40) + (op.motivo_cancelacion.length > 40 ? '...' : '') : ''}</p>` : ''}
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
                                ${isCancelled ? `<button disabled class="text-gray-300 bg-gray-100 border border-gray-200 px-2.5 py-1.5 rounded shadow-sm cursor-not-allowed" title="Operación cancelada"><i class="fas fa-edit"></i></button>` : `<button onclick="openModal(${op.id}, '${op.referencia}', '${op.cliente_nombre}', '${op.doda || ''}', ${op.pedimento_id || 'null'}, ${op.cliente_id})" class="text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white border border-indigo-200 px-2.5 py-1.5 rounded shadow-sm transition transform group-hover:scale-105" title="Asignar DODA/Pedimento"><i class="fas fa-edit"></i></button>`}
                                ${isCancelled ? `<button onclick="openCancelDetailsModal(${op.id})" class="text-red-500 bg-red-50 hover:bg-red-100 border border-red-200 px-2.5 py-1.5 rounded shadow-sm transition" title="Ver motivo de cancelación"><i class="fas fa-ban"></i></button>` : ''}
                                <button onclick="openDetailsModal(${op.id})" class="text-white bg-indigo-600 hover:bg-indigo-700 px-2.5 py-1.5 rounded shadow-sm transition transform group-hover:scale-105" title="Ver Detalles"><i class="fas fa-eye"></i></button>
                                ${!isCancelled ? `<button onclick="openCancelModal(${op.id}, '${op.referencia}')" class="text-red-400 bg-white hover:bg-red-50 hover:text-red-600 border border-gray-200 hover:border-red-300 px-2.5 py-1.5 rounded shadow-sm transition" title="Cancelar Operación"><i class="fas fa-times-circle"></i></button>` : ''}
                            </div>
                        </td>
                    </tr>`;
            });
            tbody.innerHTML = rows;
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
            const maxSize = 50 * 1024 * 1024; // 50MB
            Array.from(files).forEach(file => {
                if (file.size > maxSize) {
                    alert(`"${file.name}" excede el límite de 50MB`);
                    return;
                }
                filesToUpload.push({
                    file: file,
                    uid: Math.random().toString(36).substring(7),
                    type: 'factura'
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

            const tipoOpts = `
                <optgroup label="Documentos del Art. 36-A (Maestros)">
                    <option value="acta">Acta Constitutiva</option>
                    <option value="poder">Poder Notarial</option>
                    <option value="identificacion">Identificación Oficial</option>
                    <option value="rfc">Constancia CSF (RFC)</option>
                    <option value="domicilio">Comprobante de Domicilio</option>
                </optgroup>
                <optgroup label="Documentos por Operación (Art. 36-A)">
                    <option value="factura">Factura Comercial</option>
                    <option value="encargo">Encargo Conferido</option>
                    <option value="transporte">Documentos de Transporte</option>
                    <option value="empaque">Lista de Empaque</option>
                    <option value="origen">Certificado de Origen</option>
                    <option value="rrna">Cumplimiento RRNA's</option>
                    <option value="gastos">Gastos Incrementables</option>
                    <option value="doda">DODA / PITA</option>
                    <option value="cupo">Carta de Cupo</option>
                    <option value="val">Certificación de Valor</option>
                </optgroup>
                <optgroup label="Otros">
                    <option value="pedimento_pagado">Pedimento Pagado</option>
                    <option value="concepto_adicional">Concepto Adicional</option>
                    <option value="otros">Otros</option>
                </optgroup>`;

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
                                <select onchange="updateFileType('${item.uid}', this)" class="w-full sm:w-44 px-2 py-1.5 text-xs font-bold text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                    ${tipoOpts}
                                </select>
                                <button type="button" onclick="removeFile('${item.uid}')" class="text-red-500 hover:text-red-700 p-2 rounded hover:bg-red-50 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>`;
            });
            list.innerHTML = html;

            // Set selected values after rendering
            const selects = list.querySelectorAll('select');
            filesToUpload.forEach((item, i) => {
                if (selects[i]) {
                    selects[i].value = item.type;
                }
            });
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
            document.getElementById('det_editar_link').href = '{{ url("documentador/operacion") }}/' + opId + '/editar';

            // Cargar documentos bajo demanda vía API dedicada
            fetchModalDocuments(opId);
        }

        function fetchModalDocuments(opId) {
            const list = document.getElementById('details_files_list');
            list.innerHTML = '<div class="text-center py-10 text-gray-400"><i class="fas fa-spinner fa-spin text-3xl mb-3 block text-indigo-400"></i><p class="text-sm">Cargando archivos...</p></div>';

            fetch(`{{ url('documentador/api/operaciones') }}/${opId}/documentos`)
                .then(res => res.json())
                .then(data => {
                    renderModalFiles(data.documentos);
                })
                .catch(() => {
                    list.innerHTML = '<div class="text-center py-10 text-red-400"><p class="text-sm">Error al cargar archivos.</p></div>';
                });
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

            const tipoLabels = {
                'acta': 'Acta Constitutiva', 'poder': 'Poder Notarial',
                'identificacion': 'Identificación Oficial', 'rfc': 'Constancia CSF (RFC)',
                'domicilio': 'Comprobante de Domicilio', 'factura': 'Factura Comercial',
                'encargo': 'Encargo Conferido', 'transporte': 'Documentos de Transporte',
                'empaque': 'Lista de Empaque', 'origen': 'Certificado de Origen',
                'rrna': "Cumplimiento RRNA's", 'gastos': 'Gastos Incrementables',
                'doda': 'DODA / PITA', 'cupo': 'Carta de Cupo', 'val': 'Certificación de Valor',
                'pedimento_pagado': 'Pedimento Pagado', 'concepto_adicional': 'Concepto Adicional',
                'otros': 'Otros',
            };

            let html = '';
            docs.forEach(doc => {
                const tipoLabel = tipoLabels[doc.tipo] || doc.tipo || 'Sin tipo';
                html += `
                    <div class="flex flex-col xl:flex-row items-center justify-between p-3 bg-white border border-gray-200 rounded-lg shadow-sm gap-3 transition hover:shadow-md group">
                        <div class="flex items-center gap-3 overflow-hidden w-full xl:w-auto">
                            <div class="p-2.5 bg-indigo-50 text-indigo-500 rounded text-xl shrink-0"><i class="fas fa-file-alt"></i></div>
                            <div class="truncate">
                                <p class="text-sm font-bold text-gray-700 truncate" title="${doc.nombre}">${doc.nombre}</p>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">${tipoLabel}</span>
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

        // ==========================================
        // DRAG & DROP MULTI-ARCHIVOS
        // ==========================================
        let uploadQueue = [];

        function handleDrop(e) {
            e.preventDefault();
            e.currentTarget.classList.remove('border-indigo-500','bg-indigo-50');
            addFilesToQueue(e.dataTransfer.files);
        }

        function handleFileSelect(input) {
            if (input.files.length) addFilesToQueue(input.files);
            input.value = '';
        }

        function addFilesToQueue(fileList) {
            for (let file of fileList) {
                const id = 'q_' + Date.now() + '_' + Math.random().toString(36).substr(2,5);
                uploadQueue.push({ id, file, tipo: 'otros' });
            }
            renderUploadQueue();
            // Scroll al top del modal para que la cola sea visible
            const modalBody = document.querySelector('#detailsModal .overflow-y-auto');
            if (modalBody) modalBody.scrollTop = 0;
        }

        function renderUploadQueue() {
            const container = document.getElementById('uploadQueue');
            const btn = document.getElementById('btnUploadAll');
            const count = document.getElementById('uploadAllCount');
            const qCount = document.getElementById('uploadQueueCount');

            if (uploadQueue.length === 0) {
                container.classList.add('hidden');
                btn.classList.add('hidden');
                qCount.classList.add('hidden');
                return;
            }

            container.classList.remove('hidden');
            btn.classList.remove('hidden');
            qCount.classList.remove('hidden');
            qCount.textContent = uploadQueue.length + ' pendiente(s)';
            count.textContent = uploadQueue.length;

            const tipoOpts = `<optgroup label="Documentos Art. 36-A (Operación)">
                <option value="factura">Factura Comercial</option>
                <option value="encargo">Encargo Conferido</option>
                <option value="transporte">Documentos de Transporte</option>
                <option value="empaque">Lista de Empaque</option>
                <option value="origen">Certificado de Origen</option>
                <option value="rrna">Cumplimiento RRNA's</option>
                <option value="gastos">Gastos Incrementables</option>
                <option value="doda">DODA / PITA</option>
                <option value="cupo">Carta de Cupo</option>
                <option value="val">Certificación de Valor</option>
                </optgroup><optgroup label="Otros">
                <option value="pedimento_pagado">Pedimento Pagado</option>
                <option value="concepto_adicional">Concepto Adicional</option>
                <option value="otros" selected>Otros</option>
            </optgroup>`;

            container.innerHTML = uploadQueue.map(f => `
                <div class="flex items-center justify-between bg-white rounded-lg p-2.5 border border-gray-200 text-xs gap-2">
                    <span class="font-bold text-gray-700 truncate flex-1" title="${f.file.name}">${f.file.name}</span>
                    <span class="text-gray-400 text-[10px] whitespace-nowrap">${(f.file.size/1024).toFixed(0)} KB</span>
                    <select onchange="updateQueueType('${f.id}', this.value)" class="text-[10px] border-gray-300 rounded p-1 w-28 shrink-0">
                        ${tipoOpts}
                    </select>
                    <button onclick="removeFromQueue('${f.id}')" class="text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button>
                </div>
            `).join('');

            // Set default "otros" for each select
            container.querySelectorAll('select').forEach(s => s.value = 'otros');
        }

        function updateQueueType(id, tipo) {
            const item = uploadQueue.find(f => f.id === id);
            if (item) item.tipo = tipo;
        }

        function removeFromQueue(id) {
            uploadQueue = uploadQueue.filter(f => f.id !== id);
            renderUploadQueue();
        }

        async function uploadAllFiles() {
            if (!uploadQueue.length || !currentDetailsOpId) return;

            const btn = document.getElementById('btnUploadAll');
            btn.disabled = true;
            btn.classList.add('opacity-75');

            let successCount = 0;
            let failCount = 0;
            let errors = [];

            for (let i = 0; i < uploadQueue.length; i++) {
                const f = uploadQueue[i];
                btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i> Subiendo ${i + 1}/${uploadQueue.length}...`;

                const formData = new FormData();
                formData.append('operacion_id', currentDetailsOpId);
                formData.append('archivos[0]', f.file);
                formData.append('tipos_documento[0]', f.tipo);

                try {
                    const res = await fetch('{{ route("documentos_operacion.store2") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: formData
                    });
                    const txt = await res.text();
                    let data;
                    try { data = JSON.parse(txt); }
                    catch(e) { throw new Error('Respuesta no JSON: ' + txt.substring(0, 200)); }

                    if (data.success) {
                        successCount++;
                    } else {
                        failCount++;
                        errors.push(`${f.name}: ${data.message || 'Error desconocido'}`);
                    }
                } catch (err) {
                    failCount++;
                    errors.push(`${f.name}: ${err.message}`);
                }
            }

            uploadQueue = [];
            renderUploadQueue();
            fetchModalDocuments(currentDetailsOpId);
            fetchLiveData();

            btn.disabled = false;
            btn.classList.remove('opacity-75');
            btn.innerHTML = '<i class="fas fa-cloud-upload-alt mr-1"></i> Cargar <span id="uploadAllCount">0</span> archivos';

            let msg = `Subidos: ${successCount} exitosos`;
            if (failCount > 0) {
                msg += `, ${failCount} fallidos`;
                if (errors.length) msg += '\n\nErrores:\n' + errors.join('\n');
            }
            alert(msg);
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
                .then(() => {
                    fetchModalDocuments(currentDetailsOpId);
                    fetchLiveData();
                })
                .catch(err => alert("Error de conexión."));
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

        // ==========================================
        // CANCELAR OPERACIÓN
        // ==========================================
        let cancelOpId = null;

        function openCancelModal(opId, referencia) {
            cancelOpId = opId;
            const modal = document.getElementById('cancelOpModal');
            document.getElementById('cancelOpRef').innerText = referencia;
            document.getElementById('cancelMotivo').value = '';
            document.getElementById('cancelDeleteDocs').checked = false;
            modal.classList.remove('hidden');
        }

        function closeCancelModal() {
            document.getElementById('cancelOpModal').classList.add('hidden');
            cancelOpId = null;
        }

        function submitCancelOp() {
            if (!cancelOpId) return;
            const motivo = document.getElementById('cancelMotivo').value.trim();
            if (!motivo) {
                alert('Debes ingresar un motivo de cancelación.');
                return;
            }
            const deleteDocs = document.getElementById('cancelDeleteDocs').checked;
            const btn = document.getElementById('btnSubmitCancel');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cancelando...';

            fetch(`/documentador/cancelar/${cancelOpId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    motivo_cancelacion: motivo,
                    eliminar_documentos: deleteDocs,
                }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeCancelModal();
                    fetchLiveData();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo cancelar la operación.'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error de conexión al cancelar la operación.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-ban mr-1"></i> Confirmar Cancelación';
            });
        }

        function openCancelDetailsModal(opId) {
            const op = globalOperaciones.find(o => o.id === opId);
            if (!op) return;
            const motivo = op.motivo_cancelacion || 'Sin motivo registrado';
            alert('Motivo de cancelación:\n\n' + motivo);
        }

        // ==========================================
        // FILTROS DE BÚSQUEDA
        // ==========================================
        function applyFilters() {
            fetchLiveData();
        }

        function clearFilters() {
            if (document.getElementById('filter_q')) document.getElementById('filter_q').value = '';
            if (document.getElementById('filter_cliente')) document.getElementById('filter_cliente').value = '';
            if (document.getElementById('filter_referencia')) document.getElementById('filter_referencia').value = '';
            if (document.getElementById('filter_aduana')) document.getElementById('filter_aduana').value = '';
            if (document.getElementById('filter_bodega')) document.getElementById('filter_bodega').value = '';
            if (document.getElementById('filter_fecha_desde')) document.getElementById('filter_fecha_desde').value = '';
            if (document.getElementById('filter_fecha_hasta')) document.getElementById('filter_fecha_hasta').value = '';
            if (document.getElementById('filter_estado')) document.getElementById('filter_estado').value = '';
            fetchLiveData();
        }

        function toggleFilters() {
            const panel = document.getElementById('filterPanel');
            panel.classList.toggle('hidden');
        }
    </script>

    <style>
        .animate-spin-fast {
            animation: spin 1s linear infinite;
        }
    </style>
@endsection