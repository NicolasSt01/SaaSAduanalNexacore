@extends('layouts.app')

@section('title', 'Enviar Reporte por Correo')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-800 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-envelope-paper"></i>
                </div>
                Enviar Reporte de <span class="text-indigo-600">Operaciones</span>
            </h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Genera y envía reportes personalizados a tus clientes</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-emerald-500 mr-3"></i>
                    <p class="text-sm text-emerald-700 font-bold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p class="text-sm text-red-700 font-bold">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Tabs -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="border-b border-gray-100">
                <nav class="flex">
                    <button onclick="switchTab('individual')" id="tab-individual"
                        class="tab-btn active px-6 py-4 text-sm font-bold border-b-2 border-indigo-600 text-indigo-600 transition">
                        <i class="fas fa-user mr-2"></i>Envío Individual
                    </button>
                    <button onclick="switchTab('masivo')" id="tab-masivo"
                        class="tab-btn px-6 py-4 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition">
                        <i class="fas fa-users mr-2"></i>Envío Masivo
                    </button>
                </nav>
            </div>

            <!-- TAB 1: ENVÍO INDIVIDUAL -->
            <div id="content-individual" class="tab-content p-6">
                <form method="POST" action="{{ route('reportes.cliente.mail.enviar') }}" id="formReporte">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Cliente -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-building text-indigo-500 mr-1"></i> Cliente
                            </label>
                            <select name="cliente_id" id="cliente_id"
                                class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 font-bold"
                                required>
                                <option value="">Seleccione un cliente</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" data-nombre="{{ $cliente->nombre }}">
                                        {{ $cliente->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Desde -->
                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-calendar-day text-indigo-500 mr-1"></i> Desde
                            </label>
                            <input type="date" name="desde" id="desde"
                                class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 font-bold"
                                value="{{ $desde }}" required>
                        </div>

                        <!-- Hasta -->
                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-calendar-check text-indigo-500 mr-1"></i> Hasta
                            </label>
                            <input type="date" name="hasta" id="hasta"
                                class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 font-bold"
                                value="{{ $hasta }}" required>
                        </div>
                    </div>

                    <!-- Selector de Contacto del Directorio -->
                    <div id="contacto-section" class="mb-6 hidden">
                        <label class="block text-sm font-bold text-gray-600 mb-1.5">
                            <i class="fas fa-address-book text-indigo-500 mr-1"></i> Contacto a enviar (Directorio)
                        </label>
                        <select name="contacto_id" id="contacto_id"
                            class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 font-bold">
                            <option value="">Seleccione un contacto del directorio</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Selecciona a quién del directorio del cliente se enviará el
                            reporte</p>
                    </div>

                    <hr class="my-6 border-gray-100">

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="mostrarVistaPrevia()"
                            class="px-5 py-2.5 rounded-xl text-sm font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition">
                            <i class="fas fa-eye mr-1"></i> Vista Previa
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                            <i class="fas fa-paper-plane mr-1"></i> Enviar Reporte
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: ENVÍO MASIVO CON CONTACTOS -->
            <div id="content-masivo" class="tab-content p-6 hidden">
                <form method="POST" action="{{ route('reportes.cliente.mail.enviar-masivo') }}" id="formReporteMasivo">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Desde -->
                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-calendar-day text-indigo-500 mr-1"></i> Desde
                            </label>
                            <input type="date" name="desde" id="desde_masivo"
                                class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 font-bold"
                                value="{{ $desde }}" required>
                        </div>

                        <!-- Hasta -->
                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1.5">
                                <i class="fas fa-calendar-check text-indigo-500 mr-1"></i> Hasta
                            </label>
                            <input type="date" name="hasta" id="hasta_masivo"
                                class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-4 py-2.5 font-bold"
                                value="{{ $hasta }}" required>
                        </div>
                    </div>

                    <!-- Selección de Clientes -->
                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-sm font-bold text-indigo-800">
                                <i class="fas fa-list-check mr-1"></i> Seleccionar Clientes y Contactos
                            </h3>
                            <div class="flex gap-2">
                                <button type="button" onclick="seleccionarTodos()"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold text-indigo-600 bg-white hover:bg-indigo-100 transition">
                                    <i class="fas fa-check-double mr-1"></i> Todos
                                </button>
                                <button type="button" onclick="deseleccionarTodos()"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold text-gray-600 bg-white hover:bg-gray-100 transition">
                                    <i class="fas fa-times mr-1"></i> Ninguno
                                </button>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border border-indigo-100 max-h-[600px] overflow-y-auto">
                            <div class="p-4 space-y-3" id="clientes-masivos-list">
                                @foreach($clientes as $cliente)
                                    <div class="cliente-item border border-gray-200 rounded-xl p-4"
                                        data-cliente-id="{{ $cliente->id }}">
                                        <!-- Cliente Header -->
                                        <label class="flex items-center gap-3 cursor-pointer mb-3">
                                            <input type="checkbox"
                                                class="cliente-checkbox w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 border-gray-300"
                                                value="{{ $cliente->id }}"
                                                onchange="toggleClienteContactos({{ $cliente->id }})">
                                            <div class="flex-1">
                                                <p class="text-sm font-bold text-gray-800">{{ $cliente->nombre }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $cliente->correo_contacto_principal ?? 'Sin correo principal' }}
                                                </p>
                                            </div>
                                        </label>

                                        <!-- Contactos del Directorio (se cargan dinámicamente) -->
                                        <div id="contactos-{{ $cliente->id }}"
                                            class="hidden ml-8 mt-3 pt-3 border-t border-gray-100">
                                            <p class="text-xs font-bold text-gray-600 mb-2">
                                                <i class="fas fa-address-book mr-1"></i> Contactos del Directorio:
                                            </p>
                                            <div class="space-y-2 contactos-container" data-cliente-id="{{ $cliente->id }}">
                                                <p class="text-xs text-gray-400 loading-text">Cargando contactos...</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="submit"
                            class="px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition">
                            <i class="fas fa-paper-plane mr-1"></i> Enviar a Seleccionados
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Vista Previa (Tailwind CSS) -->
    <div id="modalVistaPrevia" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" onclick="cerrarModal()"></div>

        <!-- Modal Panel -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-7xl max-h-[90vh] overflow-hidden">
                <!-- Header -->
                <div
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <i class="fas fa-eye"></i>
                        Vista Previa del Reporte
                    </h3>
                    <button onclick="cerrarModal()" class="text-white hover:text-gray-200 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="bg-gray-50 overflow-y-auto max-h-[calc(90vh-140px)] p-6">
                    <div id="contenidoVistaPrevia">
                        <div class="text-center py-12">
                            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600">
                            </div>
                            <p class="mt-4 text-gray-500 font-medium">Cargando datos del reporte...</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-white border-t border-gray-100 px-6 py-4 flex justify-end gap-3">
                    <button onclick="cerrarModal()"
                        class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        <i class="fas fa-times mr-1"></i> Cerrar
                    </button>
                    <button onclick="descargarPDF()"
                        class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition">
                        <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
                    </button>
                    <button onclick="enviarDesdeModal()"
                        class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                        <i class="fas fa-paper-plane mr-1"></i> Confirmar y Enviar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        let chartInstances = {};
        let datosActuales = null;
        let contactosCache = {};

        // Funciones del Modal (Tailwind CSS)
        function abrirModal() {
            document.getElementById('modalVistaPrevia').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModal() {
            document.getElementById('modalVistaPrevia').classList.add('hidden');
            document.body.style.overflow = '';
            Object.values(chartInstances).forEach(chart => {
                if (chart && typeof chart.destroy === 'function') chart.destroy();
            });
            chartInstances = {};
            datosActuales = null;
        }

        // Cambiar tabs
        function switchTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('active', 'border-indigo-600', 'text-indigo-600');
                el.classList.add('border-transparent', 'text-gray-500');
            });

            document.getElementById(`content-${tab}`).classList.remove('hidden');
            document.getElementById(`tab-${tab}`).classList.add('active', 'border-indigo-600', 'text-indigo-600');
            document.getElementById(`tab-${tab}`).classList.remove('border-transparent', 'text-gray-500');
        }

        // Cargar contactos cuando se selecciona un cliente (individual)
        document.getElementById('cliente_id').addEventListener('change', async function () {
            const clienteId = this.value;
            const contactoSection = document.getElementById('contacto-section');
            const contactoSelect = document.getElementById('contacto_id');

            if (!clienteId) {
                contactoSection.classList.add('hidden');
                return;
            }

            await cargarContactosCliente(clienteId, contactoSelect);
            contactoSection.classList.remove('hidden');
        });

        async function cargarContactosCliente(clienteId, selectElement) {
            if (!contactosCache[clienteId]) {
                try {
                    const response = await fetch(`/api/directorio/cliente/${clienteId}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });

                    if (!response.ok) {
                        console.error('Error HTTP:', response.status);
                        contactosCache[clienteId] = [];
                        return;
                    }

                    const data = await response.json();
                    contactosCache[clienteId] = data.contactos || [];
                } catch (error) {
                    console.error('Error cargando contactos:', error);
                    contactosCache[clienteId] = [];
                }
            }

            const contactos = contactosCache[clienteId];
            selectElement.innerHTML = '<option value="">Seleccione un contacto del directorio</option>';

            if (contactos.length === 0) {
                selectElement.innerHTML += '<option value="" disabled>No hay contactos disponibles</option>';
            } else {
                contactos.forEach(contacto => {
                    const activoIndicator = contacto.activo ? '' : ' ⚠️ Inactivo';
                    selectElement.innerHTML += `<option value="${contacto.id}" data-email="${contacto.correo}">${contacto.nombre} - ${contacto.puesto} (${contacto.correo})${activoIndicator}</option>`;
                });
            }
        }

        // Vista previa - MODAL
        async function mostrarVistaPrevia() {
            const clienteId = document.getElementById('cliente_id').value;
            const desde = document.getElementById('desde').value;
            const hasta = document.getElementById('hasta').value;

            if (!clienteId || !desde || !hasta) {
                alert('Por favor complete todos los campos');
                return;
            }

            abrirModal();

            document.getElementById('contenidoVistaPrevia').innerHTML = `
                        <div class="text-center py-12">
                            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                            <p class="mt-4 text-gray-500 font-medium">Cargando datos del reporte...</p>
                        </div>
                    `;

            try {
                const response = await fetch('{{ route("reportes.cliente.mail.preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ cliente_id: clienteId, desde: desde, hasta: hasta })
                });

                const data = await response.json();

                if (data.success) {
                    mostrarVistaPreviaHTML(data.datos);
                } else {
                    document.getElementById('contenidoVistaPrevia').innerHTML = `
                                <div class="text-center py-12 text-red-600">
                                    <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
                                    <p class="font-bold">${data.message || 'Error al cargar los datos'}</p>
                                </div>
                            `;
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('contenidoVistaPrevia').innerHTML = `
                            <div class="text-center py-12 text-red-600">
                                <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
                                <p class="font-bold">Error al cargar la vista previa</p>
                            </div>
                        `;
            }
        }

        function mostrarVistaPreviaHTML(datos) {
            datosActuales = datos;

            document.getElementById('contenidoVistaPrevia').innerHTML = `
                        <div class="max-w-5xl mx-auto">
                            <!-- Header del Reporte -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h2 class="text-2xl font-black text-gray-800">Reporte de Operaciones</h2>
                                        <p class="text-sm text-gray-500 mt-1">${datos.cliente.nombre}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400">Período</p>
                                        <p class="text-sm font-bold text-gray-800">${datos.periodo.desde} - ${datos.periodo.hasta}</p>
                                    </div>
                                </div>

                                <!-- KPIs -->
                                <div class="grid grid-cols-4 gap-4">
                                    <div class="bg-indigo-50 rounded-xl p-4 text-center">
                                        <p class="text-xs text-indigo-600 font-bold uppercase">Total</p>
                                        <p class="text-3xl font-black text-indigo-600">${datos.estadisticas.total}</p>
                                    </div>
                                    <div class="bg-emerald-50 rounded-xl p-4 text-center">
                                        <p class="text-xs text-emerald-600 font-bold uppercase">Verdes</p>
                                        <p class="text-3xl font-black text-emerald-600">${datos.estadisticas.greens}</p>
                                    </div>
                                    <div class="bg-rose-50 rounded-xl p-4 text-center">
                                        <p class="text-xs text-rose-600 font-bold uppercase">Rojos</p>
                                        <p class="text-3xl font-black text-rose-600">${datos.estadisticas.reds}</p>
                                    </div>
                                    <div class="bg-amber-50 rounded-xl p-4 text-center">
                                        <p class="text-xs text-amber-600 font-bold uppercase">Sobrepesos</p>
                                        <p class="text-3xl font-black text-amber-600">${datos.estadisticas.sobrepesos || 0}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Gráficas -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                    <h3 class="text-sm font-bold text-gray-800 mb-4">Operaciones por Aduana</h3>
                                    <div class="h-[250px] relative overflow-hidden">
                                        <canvas id="chartAduana"></canvas>
                                    </div>
                                </div>
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                                    <h3 class="text-sm font-bold text-gray-800 mb-4">Histórico Mensual</h3>
                                    <div class="h-[250px] relative overflow-hidden">
                                        <canvas id="chartHistorial"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- Heatmap Calendario -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-sm font-bold text-gray-800">
                                        <i class="fas fa-calendar-alt text-indigo-500 mr-2"></i>Actividad Diaria
                                    </h3>
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-3 h-3 rounded-full bg-gray-100"></div>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase">Sin cruces</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-3 h-3 rounded-full bg-indigo-100"></div>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase">Baja</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-3 h-3 rounded-full bg-indigo-600"></div>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase">Alta</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-7 gap-2">
                                    ${['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'].map(d => `
                                        <div class="text-center text-[10px] font-black text-gray-400 uppercase mb-2">${d}</div>
                                    `).join('')}
                                    
                                    ${datos.calendario.map(semana => semana.map(dia => {
                                        let bgColor = 'bg-gray-50 text-gray-300';
                                        let intensity = '';
                                        
                                        if (dia.actual) {
                                            if (dia.total === 0) {
                                                bgColor = 'bg-gray-100 text-gray-400';
                                            } else if (dia.total <= 3) {
                                                bgColor = 'bg-indigo-50 text-indigo-600';
                                                intensity = 'border border-indigo-100';
                                            } else if (dia.total <= 8) {
                                                bgColor = 'bg-indigo-100 text-indigo-700';
                                            } else {
                                                bgColor = 'bg-indigo-600 text-white';
                                            }
                                        }

                                        return `
                                            <div class="aspect-square flex flex-col items-center justify-center rounded-xl transition-all duration-300 ${bgColor} ${intensity} ${dia.actual ? 'hover:scale-105 cursor-default' : 'opacity-30'}">
                                                <span class="text-xs font-black">${dia.dia}</span>
                                                ${dia.total > 0 && dia.actual ? `<span class="text-[9px] font-bold mt-0.5 opacity-80">${dia.total}</span>` : ''}
                                            </div>
                                        `;
                                    }).join('')).join('')}
                                </div>
                            </div>
                        </div>
                    `;

            renderCharts(datos);
        }

        function renderCharts(datos) {
            // Destruir instancias previas si existen
            Object.values(chartInstances).forEach(chart => {
                if (chart && typeof chart.destroy === 'function') chart.destroy();
            });
            chartInstances = {};

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { font: { weight: 'bold', size: 10 } }
                    },
                    x: {
                        ticks: { font: { weight: 'bold', size: 10 } }
                    }
                }
            };

            // Chart Aduana
            const ctxAduana = document.getElementById('chartAduana');
            if (ctxAduana && datos.porAduana && datos.porAduana.length > 0) {
                chartInstances.aduana = new Chart(ctxAduana, {
                    type: 'bar',
                    data: {
                        labels: datos.porAduana.map(a => a.nombre),
                        datasets: [{
                            label: 'Operaciones',
                            data: datos.porAduana.map(a => a.total),
                            backgroundColor: 'rgba(79, 70, 229, 0.8)',
                            borderRadius: 8
                        }]
                    },
                    options: commonOptions
                });
            }

            // Chart Histórico
            const ctxHistorial = document.getElementById('chartHistorial');
            if (ctxHistorial && datos.historialMeses) {
                const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                chartInstances.historial = new Chart(ctxHistorial, {
                    type: 'line',
                    data: {
                        labels: meses,
                        datasets: [{
                            label: 'Operaciones',
                            data: Object.values(datos.historialMeses),
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: commonOptions
                });
            }
        }

        // Descargar PDF
        async function descargarPDF() {
            if (!datosActuales) {
                alert('No hay datos cargados');
                return;
            }

            try {
                const response = await fetch('{{ route("reportes.cliente.mail.pdf") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ datos: datosActuales })
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `reporte_${datosActuales.cliente.nombre.replace(/\s+/g, '_')}_${datosActuales.periodo.desde}_${datosActuales.periodo.hasta}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                } else {
                    alert('Error al generar el PDF');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al generar el PDF');
            }
        }

        // Enviar desde modal
        function enviarDesdeModal() {
            cerrarModal();
            document.getElementById('formReporte').submit();
        }

        // ==========================================
        // ENVÍO MASIVO - Selección de clientes y contactos
        // ==========================================

        async function toggleClienteContactos(clienteId) {
            const checkbox = document.querySelector(`.cliente-checkbox[value="${clienteId}"]`);
            const contactosDiv = document.getElementById(`contactos-${clienteId}`);

            if (checkbox.checked) {
                contactosDiv.classList.remove('hidden');
                await cargarContactosMasivos(clienteId);
            } else {
                contactosDiv.classList.add('hidden');
                // Desmarcar todos los contactos
                contactosDiv.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            }
        }

        async function cargarContactosMasivos(clienteId) {
            const container = document.querySelector(`.contactos-container[data-cliente-id="${clienteId}"]`);

            if (!contactosCache[clienteId]) {
                try {
                    const response = await fetch(`/api/directorio/cliente/${clienteId}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    contactosCache[clienteId] = data.contactos || [];
                } catch (error) {
                    console.error('Error:', error);
                    contactosCache[clienteId] = [];
                }
            }

            const contactos = contactosCache[clienteId];

            if (contactos.length === 0) {
                container.innerHTML = '<p class="text-xs text-gray-400">No hay contactos en el directorio</p>';
            } else {
                container.innerHTML = contactos.map(contacto => `
                            <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                                <input type="checkbox" name="contactos[${clienteId}][]" value="${contacto.correo}" 
                                    class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 border-gray-300">
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-gray-800">${contacto.nombre}</p>
                                    <p class="text-xs text-gray-500">${contacto.correo} ${contacto.activo ? '' : '⚠️ Inactivo'}</p>
                                </div>
                            </label>
                        `).join('');
            }
        }

        function seleccionarTodos() {
            document.querySelectorAll('.cliente-checkbox').forEach(cb => {
                cb.checked = true;
                toggleClienteContactos(cb.value);
            });
        }

        function deseleccionarTodos() {
            document.querySelectorAll('.cliente-checkbox').forEach(cb => {
                cb.checked = false;
                toggleClienteContactos(cb.value);
            });
        }
    </script>
@endsection