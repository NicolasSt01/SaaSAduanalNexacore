@php
    // 📊 KPIs simulados
    $tramitesHoy =  15;
    $tramitesTotales =  1200;
    $clientesActivos =  45;
    $usuariosActivos =  12;

    // 📊 Datos de modulación simulados
    $modLabels = ['Verde', 'Rojo'];
    $modData =  [50, 20];

    // 📊 Clientes simulados
    $clientesLabels =  ['Cliente A', 'Cliente B', 'Cliente C', 'Cliente D', 'Cliente E'];
    $clientesData =  [120, 95, 80, 60, 40];

    // 📊 Productos simulados
    $productosLabels =  ['Aguacate', 'Mango', 'Limón', 'Plátano', 'Papaya'];
    $productosData =  [300, 220, 180, 140, 90];

    // 📊 Usuarios activos simulados
    $usuariosActivosTop = $usuariosActivosTop
        ?? collect([
            (object)['name' => 'Usuario 1', 'total' => 50],
            (object)['name' => 'Usuario 2', 'total' => 40],
            (object)['name' => 'Usuario 3', 'total' => 35],
            (object)['name' => 'Usuario 4', 'total' => 25],
        ]);
        

    // 📊 Evolución diaria simulada
    $lineLabels =  ['01-Ene', '02-Ene', '03-Ene', '04-Ene', '05-Ene', '06-Ene', '07-Ene'];
    $lineData =  [5, 8, 12, 20, 18, 25, 22];

    // 📊 Remesas simuladas
    $remesasCompletadas =  30;
    $remesasPendientes =  10;
    $remesas =  ($remesasCompletadas + $remesasPendientes);
@endphp


@extends('layouts.app')

@section('title', 'Dashboard Administrador')

@section('content')
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0">Dashboard Administrador</h1>
        </div>

        <!-- KPIs -->
        {{-- ... toda tu sección de KPIs se queda igual --}}

        <!-- Gráficos -->
        <div class="row">
            {{-- Evolución de Trámites --}}
            <div class="col-lg-12 mb-3">
                <div class="card shadow-lg rounded-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <span>Evolución de Trámites por Día</span>
                        <select id="mesFiltro" class="form-select form-select-sm w-auto">
                            <option value="2025-08">Agosto 2025</option>
                            <option value="2025-07">Julio 2025</option>
                            <option value="2025-06">Junio 2025</option>
                        </select>
                    </div>
                    <div class="card-body">
                        <canvas id="lineChart"></canvas>
                        @if(empty($lineLabels))
                            <div class="text-center text-muted small mt-2">Sin datos de evolución</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Remesas --}}
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Remesas</h5>
                        <p class="mb-1">Total: <strong>{{ $remesas ?? 0 }}</strong></p>
                        <p class="mb-1 text-success">Completadas: <strong>{{ $remesasCompletadas ?? 0 }}</strong></p>
                        <p class="mb-1 text-warning">Pendientes: <strong>{{ $remesasPendientes ?? 0 }}</strong></p>
                        <canvas id="remesasChart" height="150"></canvas>
                    </div>
                </div>
            </div>

            {{-- Modulación --}}
            <div class="col-lg-4 mb-3">
                <div class="card shadow-lg rounded-4">
                    <div class="card-header bg-white">Modulación</div>
                    <div class="card-body">
                        <canvas id="modulacionChart"></canvas>
                        @if(empty($modLabels))
                            <div class="text-center text-muted small mt-2">Sin datos de modulación</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Clientes --}}
            <div class="col-lg-4 mb-3">
                <div class="card shadow-lg rounded-4">
                    <div class="card-header bg-white">Trámites por Cliente (Top 10)</div>
                    <div class="card-body">
                        <canvas id="clientesChart"></canvas>
                        @if(empty($clientesLabels))
                            <div class="text-center text-muted small mt-2">Sin datos de clientes</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Ranking Usuarios y Productos -->
        <div class="row mt-3">
            <div class="col-lg-6">
                <div class="card shadow-lg rounded-4">
                    <div class="card-header bg-white">Usuarios más Activos</div>
                    <div class="card-body">
                        @if($usuariosActivosTop->isEmpty())
                            <div class="text-center text-muted small">Sin datos de usuarios</div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($usuariosActivosTop as $u)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $u->name ?? 'N/A' }}
                                        <span class="badge bg-primary rounded-pill">{{ $u->total ?? 0 }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card shadow-lg rounded-4">
                    <div class="card-header bg-white">Productos más Exportados (Top 10)</div>
                    <div class="card-body">
                        <canvas id="productosChart"></canvas>
                        @if(empty($productosLabels))
                            <div class="text-center text-muted small mt-2">Sin datos de productos</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔹 Sección de Datos de Prueba (Eliminar luego) -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="alert alert-info">
                    <strong>Datos de prueba activos:</strong> estás viendo gráficos simulados.  
                    Elimina esta sección y el bloque de datos en el controlador cuando tengas registros reales.
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // 🔹 Datos de evolución de trámites (simulados desde backend)
        const lineLabels = {!! json_encode($lineLabels ?? []) !!};
        const lineData = {!! json_encode($lineData ?? []) !!};

        const ctxLine = document.getElementById('lineChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: lineLabels,
                datasets: [{
                    label: 'Trámites',
                    data: lineData,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        // 🔹 Aquí irían tus otros gráficos (remesas, modulación, clientes, productos)
        // ... ya los tienes en tu código original ...
    </script>
@endsection
