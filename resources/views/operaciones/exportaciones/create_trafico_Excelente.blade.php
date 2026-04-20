@extends('layouts.app')

@section('title', 'Registrar Nueva Operación')

@section('content')
    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <!-- Toast de Éxito -->
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

        <!-- Toast de Error General -->
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

        <!-- Toast de Errores de Validación -->
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

    <div class="container-fluid py-5" style="background-color: #f0f2f5;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="row g-0">
                        <!-- Sección de Imagen Lateral -->
                        <div class="col-lg-5 d-none d-lg-block">
                            <div class="card-body p-0 h-100 position-relative rounded-start-4 overflow-hidden"
                                style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
                                <div
                                    class="d-flex flex-column justify-content-center align-items-center h-100 p-4 text-white text-center">
                                    <h2 class="mb-4 fw-bold">Gestión de Operaciones</h2>
                                    <p class="lead mb-4">
                                        Registra un nuevo trámite de manera rápida y sencilla.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!-- Sección del Formulario -->
                        <div class="col-lg-7">
                            <div class="card-body p-5">
                                <h5 class="card-title text-center text-primary fw-bold mb-4">
                                    <i class="fas fa-file-export me-2"></i> Nueva Operación
                                </h5>
                                {{--<div class="alert alert-info">
                                    <strong>Referencia tentativa:</strong> {{ $referenciaTentativa }}
                                    <br>
                                    <small>La referencia final se asignará al guardar.</small>
                                </div>--}}
                                <hr class="mb-4">
                                <form action="{{ route('operaciones.storetrafico') }}" method="POST">
                                    @csrf
                                    <div class="row g-4">
                                        <!-- Fecha de Cruce -->
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="date"
                                                    class="form-control rounded-pill @error('fecha') is-invalid @enderror"
                                                    id="fecha" name="fecha"
                                                    value="{{ old('fecha', now()->format('Y-m-d')) }}" required>
                                                <label for="fecha"><i class="fas fa-calendar me-2"></i>Fecha de Cruce *</label>
                                                @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        <!-- Cliente -->
                                        <div class="col-md-6">
                                            <div class="form-floating position-relative">
                                                <input type="text"
                                                    class="form-control rounded-pill @error('cliente_id') is-invalid @enderror"
                                                    id="cliente_search" placeholder="Escribe para buscar cliente"
                                                    autocomplete="off" required>
                                                <label for="cliente_search"><i class="fas fa-building me-2"></i>Cliente *</label>
                                                <input type="hidden" name="cliente_id" id="cliente_id"
                                                    value="{{ old('cliente_id') }}">
                                                <div id="cliente_results" class="autocomplete-results"></div>
                                                @error('cliente_id') <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Importador -->
                                        <div class="col-md-6">
                                            <div class="form-floating position-relative">
                                                <input type="text"
                                                    class="form-control rounded-pill @error('importador_id') is-invalid @enderror"
                                                    id="importador_search" placeholder="Escribe para buscar importador"
                                                    autocomplete="off" required>
                                                <label for="importador_search"><i class="fas fa-truck me-2"></i>Importador *</label>
                                                <input type="hidden" name="importador_id" id="importador_id"
                                                    value="{{ old('importador_id') }}">
                                                <div id="importador_results" class="autocomplete-results"></div>
                                                @error('importador_id') <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Producto -->
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text"
                                                    class="form-control rounded-pill @error('nombre_producto') is-invalid @enderror"
                                                    id="nombre_producto" name="nombre_producto"
                                                    value="{{ old('nombre_producto') }}" required>
                                                <label for="nombre_producto"><i class="fas fa-box me-2"></i>Producto *</label>
                                                @error('nombre_producto') <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Aduana -->
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <select
                                                    class="form-select rounded-pill @error('aduana_id') is-invalid @enderror"
                                                    id="aduana_id" name="aduana_id" required>
                                                    <option value="">Seleccione una aduana</option>
                                                    @foreach($aduanas as $aduana)
                                                        <option value="{{ $aduana->id }}" {{ old('aduana_id') == $aduana->id ? 'selected' : '' }}>
                                                            {{ $aduana->nombre_aduana }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="aduana_id"><i class="fas fa-landmark me-2"></i>Aduana de Cruce *</label>
                                                @error('aduana_id') <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Número de Factura -->
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text"
                                                    class="form-control rounded-pill @error('num_factura') is-invalid @enderror"
                                                    id="num_factura" name="num_factura" value="{{ old('num_factura') }}"
                                                    required>
                                                <label for="num_factura"><i class="fas fa-file-invoice me-2"></i>Número de Factura *</label>
                                                @error('num_factura') <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <hr class="my-2">
                                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Campos opcionales</small>
                                        </div>

                                        <!-- Bodega (Opcional) -->
                                        <div class="col-md-6">
                                            <div class="form-floating position-relative">
                                                <input type="text"
                                                    class="form-control rounded-pill @error('bodega_id') is-invalid @enderror"
                                                    id="bodega_search" placeholder="Escribe para buscar bodega"
                                                    autocomplete="off">
                                                <label for="bodega_search"><i class="fas fa-warehouse me-2"></i>Bodega</label>
                                                <input type="hidden" name="bodega_id" id="bodega_id"
                                                    value="{{ old('bodega_id') }}">
                                                <div id="bodega_results" class="autocomplete-results"></div>
                                                @error('bodega_id') <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Número Thermo (Opcional) -->
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text"
                                                    class="form-control rounded-pill @error('num_thermo') is-invalid @enderror"
                                                    id="num_thermo" name="num_thermo" value="{{ old('num_thermo') }}">
                                                <label for="num_thermo"><i class="fas fa-thermometer-half me-2"></i>Número Thermo</label>
                                                @error('num_thermo') <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Código Alpha (Opcional) -->
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text"
                                                    class="form-control rounded-pill @error('codigo_alpha') is-invalid @enderror"
                                                    id="codigo_alpha" name="codigo_alpha" value="{{ old('codigo_alpha') }}">
                                                <label for="codigo_alpha"><i class="fas fa-barcode me-2"></i>Código Alpha</label>
                                                @error('codigo_alpha') <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <!-- Referencia (Opcional) -->
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text"
                                                    class="form-control rounded-pill @error('referencia') is-invalid @enderror"
                                                    id="referencia" name="referencia" value="{{ old('referencia') }}">
                                                <label for="referencia"><i class="bi bi-flag-fill"></i>Referencia</label>
                                                @error('referencia') <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Nota Informativa -->
                                    <div class="alert alert-light border mt-4 rounded-3" role="alert">
                                        <small class="text-muted">
                                            <strong>Nota:</strong> Los campos marcados con (*) son obligatorios. 
                                            Los demás datos se completarán posteriormente por el área de Documentación.
                                        </small>
                                    </div>

                                    <!-- Botones de Acción -->
                                    <div class="mt-4 d-flex justify-content-between">
                                        <a href="{{ route('trafico.index') }}" class="btn btn-secondary rounded-pill px-4">
                                            <i class="fas fa-times me-2"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i> Guardar Operación
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estilos para el autocompletado -->
    <style>
        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
            max-height: 200px;
            overflow-y: auto;
            margin-top: 0.25rem;
        }

        .autocomplete-item {
            padding: .75rem 1.25rem;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        .autocomplete-item:hover {
            background-color: #f8f9fa;
        }
    </style>

    <!-- Script para autocompletado -->
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

                searchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase();
                    resultsDiv.innerHTML = '';
                    resultsDiv.style.display = 'none';

                    if (query.length > 0) {
                        const matches = Object.entries(data)
                            .filter(([id, name]) => name.toLowerCase().includes(query))
                            .sort((a, b) => a[1].localeCompare(b[1]));

                        if (matches.length > 0) {
                            matches.forEach(([id, name]) => {
                                const item = document.createElement('div');
                                item.classList.add('autocomplete-item');
                                item.textContent = name;
                                item.addEventListener('click', () => {
                                    searchInput.value = name;
                                    hiddenInput.value = id;
                                    resultsDiv.style.display = 'none';
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

                document.addEventListener('click', function (e) {
                    if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
                        resultsDiv.style.display = 'none';
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
        });

        // Inicializar Toasts de Bootstrap
        document.addEventListener('DOMContentLoaded', function() {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));
            var toastList = toastElList.map(function(toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
                return toast;
            });
        });
    </script>
@endsection