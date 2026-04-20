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
                                            <i class="fas fa-thermometer-half text-secondary me-1"></i>No. Economico
                                        </label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('num_thermo') is-invalid @enderror"
                                            id="num_thermo" 
                                            name="num_thermo" 
                                            placeholder="Número Economico"
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

                                    <!-- Referencia -->
                                    <div class="col-md-3">
                                        <label for="referencia" class="form-label fw-semibold small">
                                            <i class="bi bi-flag-fill text-secondary me-1"></i>Referencia
                                        </label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('referencia') is-invalid @enderror"
                                            id="referencia" 
                                            name="referencia" 
                                            placeholder="Referencia"
                                            value="{{ old('referencia') }}"
                                            tabindex="10">
                                        @error('referencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
        });
    </script>
@endsection