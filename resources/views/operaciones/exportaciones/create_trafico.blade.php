@extends('layouts.app')

@section('title', 'Registrar Nueva Operación')

@section('content')
    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        @if(session('success'))
        <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="toast align-items-center text-white bg-warning border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="8000">
            <div class="d-flex">
                <div class="toast-body">
                    <strong><i class="fas fa-exclamation-triangle me-2"></i>Errores de validación:</strong>
                    <ul class="mb-0 mt-2 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        @endif
    </div>

    <div class="container-fluid py-4" style="background-color: #f8f9fa;">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                <!-- Header compacto -->
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary mb-2">
                        <i class="fas fa-file-export me-2"></i>Nueva Operación
                    </h3>
                    <p class="text-muted mb-0">Complete los campos requeridos para registrar la operación</p>
                </div>

                <!-- Card principal más compacto -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('operaciones.storetrafico') }}" method="POST" id="operacionForm">
                            @csrf
                            
                            <!-- Campos Principales en 2 columnas -->
                            <div class="row g-3 mb-3">
                                <!-- Fecha de Cruce -->
                                <div class="col-md-4">
                                    <label for="fecha" class="form-label fw-semibold small">
                                        <i class="fas fa-calendar text-primary me-1"></i>Fecha de Cruce *
                                    </label>
                                    <input type="date"
                                        class="form-control form-control-sm @error('fecha') is-invalid @enderror"
                                        id="fecha" name="fecha"
                                        value="{{ old('fecha', now()->format('Y-m-d')) }}" 
                                        required
                                        tabindex="1">
                                    @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Cliente -->
                                <div class="col-md-4">
                                    <label for="cliente_search" class="form-label fw-semibold small">
                                        <i class="fas fa-building text-primary me-1"></i>Cliente *
                                    </label>
                                    <div class="position-relative">
                                        <input type="text"
                                            class="form-control form-control-sm @error('cliente_id') is-invalid @enderror"
                                            id="cliente_search" 
                                            placeholder="Buscar cliente..."
                                            autocomplete="off" 
                                            required
                                            tabindex="2">
                                        <input type="hidden" name="cliente_id" id="cliente_id" value="{{ old('cliente_id') }}">
                                        <div id="cliente_results" class="autocomplete-results"></div>
                                        @error('cliente_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <!-- Importador -->
                                <div class="col-md-4">
                                    <label for="importador_search" class="form-label fw-semibold small">
                                        <i class="fas fa-truck text-primary me-1"></i>Importador *
                                    </label>
                                    <div class="position-relative">
                                        <input type="text"
                                            class="form-control form-control-sm @error('importador_id') is-invalid @enderror"
                                            id="importador_search" 
                                            placeholder="Buscar importador..."
                                            autocomplete="off" 
                                            required
                                            tabindex="3">
                                        <input type="hidden" name="importador_id" id="importador_id" value="{{ old('importador_id') }}">
                                        <div id="importador_results" class="autocomplete-results"></div>
                                        @error('importador_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <!-- Producto -->
                                <div class="col-md-4">
                                    <label for="nombre_producto" class="form-label fw-semibold small">
                                        <i class="fas fa-box text-primary me-1"></i>Producto *
                                    </label>
                                    <input type="text"
                                        class="form-control form-control-sm @error('nombre_producto') is-invalid @enderror"
                                        id="nombre_producto" 
                                        name="nombre_producto"
                                        placeholder="Nombre del producto"
                                        value="{{ old('nombre_producto') }}" 
                                        required
                                        tabindex="4">
                                    @error('nombre_producto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Aduana -->
                                <div class="col-md-4">
                                    <label for="aduana_id" class="form-label fw-semibold small">
                                        <i class="fas fa-landmark text-primary me-1"></i>Aduana de Cruce *
                                    </label>
                                    <select
                                        class="form-select form-select-sm @error('aduana_id') is-invalid @enderror"
                                        id="aduana_id" 
                                        name="aduana_id" 
                                        required
                                        tabindex="5">
                                        <option value="">Seleccionar...</option>
                                        @foreach($aduanas as $aduana)
                                            <option value="{{ $aduana->id }}" {{ old('aduana_id') == $aduana->id ? 'selected' : '' }}>
                                                {{ $aduana->nombre_aduana }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('aduana_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Número de Factura -->
                                <div class="col-md-4">
                                    <label for="num_factura" class="form-label fw-semibold small">
                                        <i class="fas fa-file-invoice text-primary me-1"></i>No. Factura *
                                    </label>
                                    <input type="text"
                                        class="form-control form-control-sm @error('num_factura') is-invalid @enderror"
                                        id="num_factura" 
                                        name="num_factura" 
                                        placeholder="Número de factura"
                                        value="{{ old('num_factura') }}"
                                        required
                                        tabindex="6">
                                    @error('num_factura') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Campos Opcionales -->
                            <div class="border-top pt-3 mt-2">
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-info-circle me-1"></i>Campos opcionales
                                </p>
                                <div class="row g-3">
                                    <!-- Bodega -->
                                    <div class="col-md-3">
                                        <label for="bodega_search" class="form-label fw-semibold small">
                                            <i class="fas fa-warehouse text-secondary me-1"></i>Bodega
                                        </label>
                                        <div class="position-relative">
                                            <input type="text"
                                                class="form-control form-control-sm @error('bodega_id') is-invalid @enderror"
                                                id="bodega_search" 
                                                placeholder="Buscar bodega..."
                                                autocomplete="off"
                                                tabindex="7">
                                            <input type="hidden" name="bodega_id" id="bodega_id" value="{{ old('bodega_id') }}">
                                            <div id="bodega_results" class="autocomplete-results"></div>
                                            @error('bodega_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <!-- Número Thermo -->
                                    <div class="col-md-3">
                                        <label for="num_thermo" class="form-label fw-semibold small">
                                            <i class="fas fa-thermometer-half text-secondary me-1"></i>No. Thermo
                                        </label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('num_thermo') is-invalid @enderror"
                                            id="num_thermo" 
                                            name="num_thermo" 
                                            placeholder="Número thermo"
                                            value="{{ old('num_thermo') }}"
                                            tabindex="8">
                                        @error('num_thermo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Código Alpha -->
                                    <div class="col-md-3">
                                        <label for="codigo_alpha" class="form-label fw-semibold small">
                                            <i class="fas fa-barcode text-secondary me-1"></i>Código Alpha
                                        </label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('codigo_alpha') is-invalid @enderror"
                                            id="codigo_alpha" 
                                            name="codigo_alpha" 
                                            placeholder="Código alpha"
                                            value="{{ old('codigo_alpha') }}"
                                            tabindex="9">
                                        @error('codigo_alpha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>


                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="mt-4 d-flex justify-content-between align-items-center">
                                <a href="{{ route('trafico.index') }}" class="btn btn-outline-secondary btn-sm px-4" tabindex="12">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm px-4" tabindex="11">
                                    <i class="fas fa-save me-1"></i> Guardar Operación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Atajos de teclado -->
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-keyboard me-1"></i>
                        Usa <kbd>Tab</kbd> para navegar • <kbd>↑</kbd> <kbd>↓</kbd> para seleccionar • <kbd>Enter</kbd> para confirmar
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!--   MODAL DE CONFIRMACIÓN — Operación Registrada               -->
    <!-- ============================================================ -->
    @if(session('operacionCreada'))
    @php $expo = session('operacionCreada'); @endphp
    <div class="modal fade" id="modalOperacionCreada" tabindex="-1" aria-labelledby="modalOperacionCreadaLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <!-- Header -->
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title" id="modalOperacionCreadaLabel">
                        <i class="fas fa-check-circle me-2"></i>Operación Registrada Exitosamente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <div class="alert alert-info d-flex align-items-center mb-3 py-2" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Referencia asignada automáticamente: <strong>{{ $expo['referencia'] }}</strong>. Seleccione la fila o use el botón para copiar al portapapeles.</small>
                    </div>

                    <!-- Tabla copiable -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" id="tablaResumenOperacion" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>#Referencia</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Importador</th>
                                    <th>Producto</th>
                                    <th>Bodega</th>
                                    <th>Factura</th>
                                    <th>Aduana</th>
                                    <th>Patente</th>
                                    <th>Pedimento</th>
                                    <th>Thermo</th>
                                    <th>Alpha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="filaCopiable">
                                    <td>{{ $expo['referencia'] }}</td>
                                    <td>{{ $expo['fecha'] }}</td>
                                    <td>{{ $expo['cliente'] }}</td>
                                    <td>{{ $expo['importador'] }}</td>
                                    <td>{{ $expo['producto'] }}</td>
                                    <td>{{ $expo['bodega'] }}</td>
                                    <td>{{ $expo['factura'] }}</td>
                                    <td>{{ $expo['aduana'] }}</td>
                                    <td>{{ $expo['patente'] }}</td>
                                    <td>{{ $expo['pedimento'] }}</td>
                                    <td>{{ $expo['thermo'] }}</td>
                                    <td>{{ $expo['alpha'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer justify-content-between">
                    <!--<button type="button" class="btn btn-outline-primary btn-sm px-4" id="btnCopiarExcel">
                        <i class="fas fa-copy me-1"></i> Copiar para Excel
                    </button>-->
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        .autocomplete-results {
            position: absolute;
            top: calc(100% + 2px);
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: none;
            max-height: 240px;
            overflow-y: auto;
        }

        .autocomplete-item {
            padding: .625rem 1rem;
            cursor: pointer;
            transition: all 0.15s ease;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }

        .autocomplete-item:last-child {
            border-bottom: none;
        }

        .autocomplete-item:hover {
            background-color: #f8f9fa;
        }

        .autocomplete-item.active {
            background-color: #0d6efd;
            color: white;
        }

        .autocomplete-item.active:hover {
            background-color: #0d6efd;
        }

        /* Mejorar el enfoque visual */
        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        /* Animación suave para los dropdowns */
        .autocomplete-results {
            animation: slideDown 0.2s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        kbd {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
            background-color: #e9ecef;
            border-radius: 0.25rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const clientesData = @json($clientes->mapWithKeys(function ($item) {
                return [$item['id'] => $item['nombre_empresa']];
            }));
            const importadoresData = @json($importadores->mapWithKeys(function ($item) {
                return [$item['id'] => $item['nombre']];
            }));
            const bodegasData = @json($bodegas->mapWithKeys(function ($item) {
                return [$item['id'] => $item['nombre_bodega']];
            }));

            function setupAutocomplete(searchInputId, hiddenInputId, resultsDivId, data) {
                const searchInput = document.getElementById(searchInputId);
                const hiddenInput = document.getElementById(hiddenInputId);
                const resultsDiv = document.getElementById(resultsDivId);
                let currentFocus = -1;
                let matchedItems = [];

                searchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase();
                    resultsDiv.innerHTML = '';
                    resultsDiv.style.display = 'none';
                    currentFocus = -1;
                    matchedItems = [];

                    if (query.length > 0) {
                        const matches = Object.entries(data)
                            .filter(([id, name]) => name.toLowerCase().includes(query))
                            .sort((a, b) => {
                                const aStarts = a[1].toLowerCase().startsWith(query);
                                const bStarts = b[1].toLowerCase().startsWith(query);
                                if (aStarts && !bStarts) return -1;
                                if (!aStarts && bStarts) return 1;
                                return a[1].localeCompare(b[1]);
                            });

                        if (matches.length > 0) {
                            matchedItems = matches;
                            matches.forEach(([id, name], index) => {
                                const item = document.createElement('div');
                                item.classList.add('autocomplete-item');
                                item.textContent = name;
                                item.dataset.id = id;
                                item.dataset.name = name;
                                item.dataset.index = index;
                                
                                item.addEventListener('click', () => {
                                    selectItem(name, id);
                                });
                                
                                resultsDiv.appendChild(item);
                            });
                            resultsDiv.style.display = 'block';
                        }
                    }

                    const isMatch = Object.values(data).some(name => name.toLowerCase() === query);
                    if (!isMatch) {
                        hiddenInput.value = '';
                    }
                });

                searchInput.addEventListener('keydown', function (e) {
                    const items = resultsDiv.getElementsByClassName('autocomplete-item');
                    
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        currentFocus++;
                        if (currentFocus >= items.length) currentFocus = 0;
                        addActive(items);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        currentFocus--;
                        if (currentFocus < 0) currentFocus = items.length - 1;
                        addActive(items);
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (currentFocus > -1 && items[currentFocus]) {
                            const name = items[currentFocus].dataset.name;
                            const id = items[currentFocus].dataset.id;
                            selectItem(name, id);
                        }
                    } else if (e.key === 'Escape') {
                        resultsDiv.style.display = 'none';
                        currentFocus = -1;
                    }
                });

                function addActive(items) {
                    removeActive(items);
                    if (currentFocus >= items.length) currentFocus = 0;
                    if (currentFocus < 0) currentFocus = items.length - 1;
                    if (items[currentFocus]) {
                        items[currentFocus].classList.add('active');
                        items[currentFocus].scrollIntoView({ block: 'nearest' });
                    }
                }

                function removeActive(items) {
                    for (let i = 0; i < items.length; i++) {
                        items[i].classList.remove('active');
                    }
                }

                function selectItem(name, id) {
                    searchInput.value = name;
                    hiddenInput.value = id;
                    resultsDiv.style.display = 'none';
                    currentFocus = -1;
                    
                    // Enfocar el siguiente campo
                    const currentTabIndex = parseInt(searchInput.getAttribute('tabindex'));
                    const nextField = document.querySelector(`[tabindex="${currentTabIndex + 1}"]`);
                    if (nextField) {
                        nextField.focus();
                    }
                }

                document.addEventListener('click', function (e) {
                    if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
                        resultsDiv.style.display = 'none';
                        currentFocus = -1;
                    }
                });

                const initialId = hiddenInput.value;
                if (initialId && data[initialId]) {
                    searchInput.value = data[initialId];
                }
            }

            setupAutocomplete('cliente_search', 'cliente_id', 'cliente_results', clientesData);
            setupAutocomplete('importador_search', 'importador_id', 'importador_results', importadoresData);
            setupAutocomplete('bodega_search', 'bodega_id', 'bodega_results', bodegasData);

            // Inicializar Toasts
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));
            var toastList = toastElList.map(function(toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
                return toast;
            });

            // Enfocar el primer campo al cargar
            document.getElementById('fecha').focus();

            // ============================================================
            //   MODAL DE CONFIRMACIÓN — Mostrar automáticamente
            // ============================================================
            @if(session('operacionCreada'))
            (function() {
                const modalEl = document.getElementById('modalOperacionCreada');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }

                // ============================================================
                //   BOTÓN COPIAR PARA EXCEL — Sin bordes, formato TSV
                // ============================================================
                const btnCopiar = document.getElementById('btnCopiarExcel');
                if (btnCopiar) {
                    btnCopiar.addEventListener('click', function() {
                        const fila = document.getElementById('filaCopiable');
                        if (!fila) return;

                        const celdas = fila.querySelectorAll('td');
                        const valores = Array.from(celdas).map(td => td.textContent.trim());
                        const textoTSV = valores.join('\t');

                        // Generar HTML sin bordes para que Excel lo pegue limpio
                        const htmlSinBordes = '<table style="border:none;border-collapse:collapse;">' +
                            '<tr>' + valores.map(v =>
                                '<td style="border:none;padding:2px 4px;">' + v + '</td>'
                            ).join('') + '</tr></table>';

                        let copiado = false;

                        // Método 1: ClipboardItem API (si está disponible y en HTTPS)
                        if (navigator.clipboard && window.ClipboardItem) {
                            try {
                                const blobText = new Blob([textoTSV], { type: 'text/plain' });
                                const blobHtml = new Blob([htmlSinBordes], { type: 'text/html' });
                                const item = new ClipboardItem({
                                    'text/plain': blobText,
                                    'text/html': blobHtml
                                });
                                navigator.clipboard.write([item]).then(() => {
                                    mostrarExito();
                                }).catch(() => {
                                    copiarConFallback(textoTSV);
                                });
                                return;
                            } catch(e) {
                                // Si falla, usar fallback
                            }
                        }

                        // Método 2: Fallback con textarea oculto (funciona en HTTP)
                        copiarConFallback(textoTSV);
                    });
                }

                function copiarConFallback(texto) {
                    const textarea = document.createElement('textarea');
                    textarea.value = texto;
                    textarea.style.position = 'fixed';
                    textarea.style.left = '-9999px';
                    textarea.style.top = '-9999px';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    textarea.select();
                    textarea.setSelectionRange(0, texto.length);

                    try {
                        const ok = document.execCommand('copy');
                        if (ok) {
                            mostrarExito();
                        } else {
                            mostrarError();
                        }
                    } catch (err) {
                        mostrarError();
                    }

                    document.body.removeChild(textarea);
                }

                function mostrarExito() {
                    const btn = document.getElementById('btnCopiarExcel');
                    if (!btn) return;
                    const textoOriginal = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check me-1"></i> ¡Copiado!';
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-success');
                    setTimeout(() => {
                        btn.innerHTML = textoOriginal;
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-outline-primary');
                    }, 2000);
                }

                function mostrarError() {
                    // Seleccionar texto manualmente como último recurso
                    const fila = document.getElementById('filaCopiable');
                    if (fila) {
                        const range = document.createRange();
                        range.selectNodeContents(fila);
                        const sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                    }
                    alert('Texto seleccionado. Use Ctrl+C para copiar.');
                }

                // Permitir seleccionar toda la fila con un click
                const fila = document.getElementById('filaCopiable');
                if (fila) {
                    fila.style.cursor = 'pointer';
                    fila.title = 'Click para seleccionar toda la fila';
                    fila.addEventListener('click', function() {
                        const range = document.createRange();
                        range.selectNodeContents(fila);
                        const sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                    });
                }
            })();
            @endif
        });
    </script>
@endsection