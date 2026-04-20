@extends('layouts.app')

@section('title', 'Dashboard Administrador')

@section('customcss')

<style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f9;
        }
        .card-custom {
            border-radius: 1rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .card-custom:hover {
            transform: translateY(-5px);
        }
        .stat-card-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            height: 100%;
        }
        .stat-card-body h2 {
            font-size: 2.5rem;
            font-weight: 700;
        }
        .stat-card-body p {
            font-size: 1rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        .bg-blue-custom {
            background-color: #3b82f6 !important;
        }
        .text-blue-custom {
            color: #3b82f6 !important;
        }
        .text-green-custom {
            color: #22c55e !important;
        }
        .text-purple-custom {
            color: #8b5cf6 !important;
        }
        .bg-custom-1 { background-color: #3b82f6; }
        .bg-custom-2 { background-color: #22d3ee; }
        .bg-custom-3 { background-color: #6366f1; }
        .bg-custom-4 { background-color: #8b5cf6; }
        .bg-custom-5 { background-color: #d946ef; }
        
        /* Estilos optimizados para gráficos */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* Indicador de carga */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

@endsection

@section('content')
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0">Dashboard Administrador</h1>
            <!-- Filtro por rango de fechas -->
            <form method="GET" action="{{ route('admin.admindashboard') }}" class="d-flex align-items-center gap-2">
                <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                    value="{{ request('fecha_inicio', $inicio) }}">
                <input type="date" name="fecha_fin" class="form-control form-control-sm"
                    value="{{ request('fecha_fin', $fin) }}">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
            </form>
        </div>

        <!-- KPIs -->
        <div class="row mb-4">
            <!--Nuevos-->
            <!-- Tarjeta de Trámites del Día -->
        <div class="col-md-6 col-lg-3">
            <div class="card card-custom h-100 bg-blue-custom text-white shadow-lg">
                <div class="card-body stat-card-body">
                    <h2 id="total-today">{{ $tramitesHoy ?? 0 }}</h2>
                    <p class="text-white">Trámites del día</p>
                    <div class="small mt-2">
                        <p class="fw-medium text-white">{{ now() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Total de Trámites -->
        <div class="col-md-6 col-lg-3">
            <div class="card card-custom h-100">
                <div class="card-body stat-card-body">
                    <h2 id="total-all" class="text-blue-custom">{{ $tramitesTotales ?? 0 }}</h2>
                    <p class="text-secondary">Total de Trámites</p>
                    <p class="text-muted small mt-2">Trámites desde el inicio.</p>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Clientes Activos -->
        <div class="col-md-6 col-lg-3">
            <div class="card card-custom h-100">
                <div class="card-body stat-card-body">
                    <h2 id="active-customers" class="text-green-custom">{{ $clientesActivos ?? 0 }}</h2>
                    <p class="text-secondary">Clientes Activos</p>
                    <p class="text-muted small mt-2">Número total de clientes activos.</p>
                </div>
            </div>
        </div>

            <!--Fin nuevos -->

            <!-- Trámites del Día -->
            <div class="col-md-4 mb-3">
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
            <!-- Total Trámites -->
            <div class="col-md-4 mb-3">
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
            <!-- Clientes Activos -->
            <div class="col-md-4 mb-3">
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
        </div>

        <!-- Gráficos -->
        <div class="row">
            <!-- Trámites diarios del mes actual (Line Chart) -->
            <div class="col-lg-9 mb-3">
                <div class="card shadow-lg rounded-4">
                    <div class="card-header bg-white">Progreso de Trámites del Mes Actual</div>
                    <div class="card-body">
                        <canvas id="tramitesLineChart" height="250"></canvas>
                        @if(empty($tramitesDiasLabels))
                            <div class="text-center text-muted small mt-2">Sin datos de trámites diarios</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Modulación Verde/Rojo del mes actual -->
            <div class="col-lg-3 mb-3">
                <div class="card shadow-lg rounded-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <span>Modulación del Mes Actual</span>
                        <div class="small">
                            <span class="badge bg-success me-2">Verdes: {{ $verdes }}</span>
                            <span class="badge bg-danger">Rojos: {{ $rojos }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="modulacionChart" height="250"></canvas>
                        @if(empty($modLabels))
                            <div class="text-center text-muted small mt-2">Sin datos de modulación</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 10 Clientes -->
        <div class="row">
            <div class="col-lg-12 mb-3">
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

        <div class="row">
            <!-- Modulación Verde/Rojo del mes actual -->
            <div class="col-lg-3 mb-3">
                <div class="card shadow-lg rounded-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <span>Dispersión de Trámites por Aduana</span>

                    </div>
                    <div class="card-body">
                        <canvas id="aduanaRadarChart" width="50" height="50"></canvas>
                        @if(empty($radarLabels))
                            <div class="text-center text-muted small mt-2">Sin datos</div>
                        @endif
                    </div>
                </div>
            </div>


            <!-- Espacio disponible para otro grafico -->
            
            
        </div>

        


    </div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Datos desde PHP
        const tramitesDiasLabels = {!! json_encode($tramitesDiasLabels ?? []) !!}; // días del mes
        const tramitesDiasData = {!! json_encode($tramitesDiasData ?? []) !!};     // totales por día

        const modLabels = {!! json_encode($modLabels ?? []) !!};
        const modData = {!! json_encode($modData ?? []) !!};

        const clientesLabels = {!! json_encode($clientesLabels ?? []) !!};
        const clientesData = {!! json_encode($clientesData ?? []) !!};

        // Gráfico de línea (progreso de trámites diarios)
        const ctxLine = document.getElementById('tramitesLineChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: tramitesDiasLabels,
                datasets: [{
                    label: 'Trámites diarios',
                    data: tramitesDiasData,
                    fill: false,
                    borderColor: '#0d6efd',
                    backgroundColor: '#0d6efd',
                    tension: 0.3,
                    pointStyle: 'circle',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: 'rgba(13, 110, 253, 0.5)',
                    pointBorderColor: '#fff'

                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        /*
            datasets: [{
                            label: 'Operaciones',
                            data: values,
                            fill: true,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            tension: 0.4,
                            pointBackgroundColor: '#0d6efd',
                            pointBorderColor: '#fff'
                        }]
        */

        // Gráfico Modulación
        const ctxMod = document.getElementById('modulacionChart').getContext('2d');
        new Chart(ctxMod, {
            type: 'doughnut',
            data: {
                labels: modLabels,
                datasets: [{
                    data: modData,
                    backgroundColor: ['#28a745', '#dc3545'] // Verde y Rojo
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

    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const radarLabels = {!! json_encode($radarLabels ?? []) !!};
        const radarDataPHP = {!! json_encode($radarData ?? []) !!};

        // Convertir PHP a datasets
        const colors = ['#dc3545', '#0d6efd', '#198754']; // rojo, azul, verde
        let index = 0;
        const datasets = Object.entries(radarDataPHP).map(([name, data]) => {
            const color = colors[index % colors.length];
            index++;
            return {
                label: name,
                data: data,
                borderColor: color,
                backgroundColor: Chart.helpers.color(color).alpha(0.2).rgbString(),
                fill: true
            };
        });

        new Chart(document.getElementById('aduanaRadarChart').getContext('2d'), {
            type: 'radar',
            data: {
                labels: radarLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    filler: { propagate: false },
                    legend: { position: 'top' },
                    title: { display: true, text: 'Trámites por Aduana (Mes)' }
                },
                elements: { line: { tension: 0.3 } }
            }
        });
    });
</script>


@section('scripts')

@endsection