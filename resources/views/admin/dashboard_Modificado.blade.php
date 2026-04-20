@php
    $tramitesHoy = $tramitesHoy ?? 0;
    $tramitesTotales = $tramitesTotales ?? 0;
    $clientesActivos = $clientesActivos ?? 0;
    $usuariosActivos = $usuariosActivos ?? 0;

    $modLabels = $modLabels ?? [];
    $modData = $modData ?? [];

    $clientesLabels = $clientesLabels ?? [];
    $clientesData = $clientesData ?? [];

    $productosLabels = $productosLabels ?? [];
    $productosData = $productosData ?? [];

    // <-- aquí la clave: asegurar que sea una Collection aunque no venga
    $usuariosActivosTop = $usuariosActivosTop ?? collect();
@endphp


@extends('layouts.app')

@section('title', 'Dashboard Administrador')

@section('content')
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0">Dashboard Administrador</h1>
        </div>

        <!-- KPIs -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card shadow-lg rounded-4 border-0">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Trámites del Día</h6>
                            <h3 class="mb-0 fw-bold">{{ $tramitesHoy ?? 0 }}</h3>
                        </div>
                        <div class="ms-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                                <i class="fas fa-calendar-day fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card shadow-lg rounded-4 border-0">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Trámites</h6>
                            <h3 class="mb-0 fw-bold">{{ $tramitesTotales ?? 0 }}</h3>
                        </div>
                        <div class="ms-3">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                                <i class="fas fa-file-export fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card shadow-lg rounded-4 border-0">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Clientes Activos</h6>
                            <h3 class="mb-0 fw-bold">{{ $clientesActivos ?? 0 }}</h3>
                        </div>
                        <div class="ms-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                                <i class="fas fa-building fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card shadow-lg rounded-4 border-0">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Usuarios Activos</h6>
                            <h3 class="mb-0 fw-bold">{{ $usuariosActivos ?? 0 }}</h3>
                        </div>
                        <div class="ms-3">
                            <div class="bg-info bg-opacity-10 text-info rounded-circle p-3">
                                <i class="fas fa-users fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">

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

        <!-- Ranking Usuarios -->
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
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById("remesasChart").getContext("2d");

            new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: ["Completadas", "Pendientes"],
                    datasets: [{
                        data: [{{ $remesasCompletadas ?? 0 }}, {{ $remesasPendientes ?? 0 }}],
                        backgroundColor: ["#28a745", "#ffc107"], // verde y amarillo
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: "bottom" }
                    }
                }
            });
        });
    </script>
    <script>
        // Datos desde PHP con fallback
        const modLabels = {!! json_encode($modLabels ?? []) !!};
        const modData = {!! json_encode($modData ?? []) !!};

        const clientesLabels = {!! json_encode($clientesLabels ?? []) !!};
        const clientesData = {!! json_encode($clientesData ?? []) !!};

        const productosLabels = {!! json_encode($productosLabels ?? []) !!};
        const productosData = {!! json_encode($productosData ?? []) !!};

        // Gráfico Modulación
        const ctxMod = document.getElementById('modulacionChart').getContext('2d');
        new Chart(ctxMod, {
            type: 'doughnut',
            data: {
                labels: modLabels,
                datasets: [{
                    data: modData,
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6c757d']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Gráfico Trámites por Cliente
        const ctxClientes = document.getElementById('clientesChart').getContext('2d');
        new Chart(ctxClientes, {
            type: 'bar',
            data: {
                labels: clientesLabels,
                datasets: [{
                    label: 'Trámites',
                    data: clientesData,
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { beginAtZero: true } }
            }
        });

        // Gráfico Productos más Exportados
        const ctxProd = document.getElementById('productosChart').getContext('2d');
        new Chart(ctxProd, {
            type: 'bar',
            data: {
                labels: productosLabels,
                datasets: [{
                    label: 'Cantidad',
                    data: productosData,
                    backgroundColor: '#20c997'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
@endsection