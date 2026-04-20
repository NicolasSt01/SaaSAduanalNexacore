@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Header con fecha y filtros -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h3 mb-1 fw-bold">Reporte Diario de Operación</h1>
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar3"></i> 
                        {{ $fechaCarbon->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                    </p>
                </div>
                <div class="col-md-6 text-md-end ">
                    <form method="GET" action="{{ route('reportes.operaciones-diarias') }}" class="d-inline-flex gap-2">
                        <input type="date" 
                               name="fecha" 
                               value="{{ $fecha }}"
                               class="form-control ">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                    </form>
                    <button onclick="window.print()" class="btn btn-secondary btn-sm ms-2">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Banner de progreso -->
    <div class="card mb-4 text-white bg-primary shadow">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="h4 mb-1">PROGRAMA DEL {{ strtoupper($fechaCarbon->format('d-M-Y')) }}</h2>
                    <p class="mb-2 opacity-75">Progreso del día</p>
                    <div class="progress bg-primary-subtle" style="height: 25px;">
                        <div class="progress-bar bg-success" 
                             data-progreso-bar
                             role="progressbar" 
                             style="width: {{ $progresoDelDia }}%;" 
                             aria-valuenow="{{ $progresoDelDia }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <strong data-progreso-porcentaje>{{ $progresoDelDia }}%</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="display-4 fw-bold" data-progreso-display>{{ $progresoDelDia }}%</div>
                    <div class="opacity-75" data-progreso-texto>{{ $completadas }} de {{ $totalRemesas }} completadas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de métricas principales -->
    <div class="row g-3 mb-4">
        
        <!-- Clientes de hoy -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Clientes de hoy</h6>
                            <h2 class="mb-0 fw-bold" data-total-clientes>{{ $totalClientes }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded p-2">
                            <i class="bi bi-people-fill text-primary" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remesas totales -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Remesas</h6>
                            <h2 class="mb-0 fw-bold" data-total-remesas>{{ $totalRemesas }}</h2>
                            <small class="text-muted">
                                <span class="text-success fw-semibold" data-completadas>{{ $completadas }}</span> completadas / 
                                <span class="text-warning fw-semibold" data-pendientes>{{ $pendientes }}</span> pendientes
                            </small>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded p-2">
                            <i class="bi bi-file-earmark-text-fill text-info" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verdes vs Rojos -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Modulación</h6>
                            <h2 class="mb-0">
                                <span class="text-success fw-bold" data-verdes>{{ $verdes }}</span>
                                <span class="text-muted">/</span>
                                <span class="text-danger fw-bold" data-rojos>{{ $rojos }}</span>
                            </h2>
                            <small class="text-muted" data-porcentaje-verdes>{{ $porcentajeVerdes }}% verdes</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded p-2">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado DODAS -->
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem;">Estado DODAS</h6>
                            <h2 class="mb-0 fw-bold" data-finalizadas>{{ $finalizadas }}/{{ $totalDia }}</h2>
                            @if($detenidas > 0)
                            <small class="text-danger fw-semibold" style="display: block;">
                                <i class="bi bi-exclamation-triangle-fill"></i> <span data-detenidas>{{ $detenidas }} detenidas</span>
                            </small>
                            @else
                            <small class="text-danger fw-semibold" style="display: none;">
                                <i class="bi bi-exclamation-triangle-fill"></i> <span data-detenidas>0 detenidas</span>
                            </small>
                            @endif
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded p-2">
                            <i class="bi bi-clock-fill text-warning" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección principal con tabla y gráficos -->
    <div class="row g-3 mb-4">
        
        <!-- Tabla de exportadores (8 columnas) -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-building text-primary"></i> Exportadores del Día
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3">Exportador</th>
                                    <th class="text-center" width="100">Cantidad</th>
                                    <th class="text-center" width="100">R / V</th>
                                    <th class="text-center" width="120">Completadas</th>
                                    <th class="text-center" width="100">Progreso</th>
                                </tr>
                            </thead>
                            <tbody data-exportadores-tbody>
                                @forelse($exportadoresData as $exportador)
                                <tr>
                                    <td class="px-3 align-middle">
                                        <strong>{{ $exportador['nombre'] }}</strong>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-primary rounded-pill">
                                            {{ $exportador['cantidad'] }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="text-danger fw-bold">{{ $exportador['rojos'] }}</span>
                                        <span class="text-muted">/</span>
                                        <span class="text-success fw-bold">{{ $exportador['verdes'] }}</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-success">{{ $exportador['completadas'] }}</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        @php
                                            $progreso = $exportador['cantidad'] > 0 
                                                ? round(($exportador['completadas'] / $exportador['cantidad']) * 100) 
                                                : 0;
                                        @endphp
                                        <div class="progress" style="height: 8px; width: 60px; margin: 0 auto;">
                                            <div class="progress-bar bg-success" 
                                                 style="width: {{ $progreso }}%"
                                                 title="{{ $progreso }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mb-0 mt-2">No hay operaciones registradas para este día</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos (4 columnas) -->
        <div class="col-lg-4">
            <div class="row g-3">
                
                <!-- Gráfico de ciudades -->
                
                <div class="col-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-geo-alt-fill text-primary"></i> Distribución por Aduana
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="ciudadesChart" height="180"></canvas>
                            <div class="mt-3" data-ciudades-detalle>
                                @foreach($ciudadesData as $ciudad)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">{{ $ciudad['ciudad'] }}</span>
                                    <span class="badge bg-primary">{{ $ciudad['porcentaje'] }}%</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico Verdes/Rojos -->
                <div class="col-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-traffic-light-fill text-success"></i> Verdes vs Rojos
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="modulacionChart" height="180"></canvas>
                            <div class="row text-center mt-3">
                                <div class="col-6">
                                    <h3 class="text-success mb-0" data-verdes-chart>{{ $verdes }}</h3>
                                    <small class="text-muted">Verdes</small>
                                </div>
                                <div class="col-6">
                                    <h3 class="text-danger mb-0" data-rojos-chart>{{ $rojos }}</h3>
                                    <small class="text-muted">Rojos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado de DODAS -->
                <div class="col-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-file-earmark-check-fill text-info"></i> DODAS del Día
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="dodasChart" height="180"></canvas>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Finalizadas</span>
                                    <span class="badge bg-success" data-finalizadas-badge>{{ $finalizadas }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Pendientes</span>
                                    <span class="badge bg-primary" data-pendientes-badge>{{ $totalDia - $finalizadas }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Pedimentos por aduana (próximo día) -->
    @if($pedimentosProximos->count() > 0)
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-calendar-plus text-warning"></i> 
                Pedimentos para el {{ $fechaCarbon->copy()->addDay()->format('d-M-Y') }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($pedimentosProximos as $pedimento)
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                        <h3 class="mb-1 fw-bold text-primary">{{ $pedimento['cantidad'] }}</h3>
                        <small class="text-muted">{{ $pedimento['aduana'] }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    @media print {
        .btn, form { display: none !important; }
        .card { break-inside: avoid; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Configuración global de Chart.js
    Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial';
    
    // Variables globales para los gráficos
    let ciudadesChart, modulacionChart, dodasChart;
    
    // Inicializar gráficos
    function initCharts() {
        // Gráfico de ciudades (Donut)
        const ciudadesCtx = document.getElementById('ciudadesChart').getContext('2d');
        ciudadesChart = new Chart(ciudadesCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($ciudadesData->pluck('ciudad')) !!},
                datasets: [{
                    data: {!! json_encode($ciudadesData->pluck('cantidad')) !!},
                    backgroundColor: ['#0d6efd', '#dc3545', '#198754', '#ffc107'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                }
            }
        });

        // Gráfico de modulación (Donut)
        const modulacionCtx = document.getElementById('modulacionChart').getContext('2d');
        modulacionChart = new Chart(modulacionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Verdes', 'Rojos'],
                datasets: [{
                    data: [{{ $verdes }}, {{ $rojos }}],
                    backgroundColor: ['#198754', '#dc3545'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                }
            }
        });

        // Gráfico de DODAS (Donut)
        const dodasCtx = document.getElementById('dodasChart').getContext('2d');
        dodasChart = new Chart(dodasCtx, {
            type: 'doughnut',
            data: {
                labels: ['Finalizadas', 'Pendientes'],
                datasets: [{
                    data: [{{ $finalizadas }}, {{ $totalDia - $finalizadas }}],
                    backgroundColor: ['#198754', '#0d6efd'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
    }

    // Función para actualizar los datos
    async function actualizarDatos() {
        try {
            const fecha = '{{ $fecha }}';
            const response = await fetch(`{{ route('api.reportes.operaciones-diarias') }}?fecha=${fecha}`);
            
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            
            const data = await response.json();
            
            // Actualizar métricas principales
            document.querySelector('[data-total-clientes]').textContent = data.totalClientes;
            document.querySelector('[data-total-remesas]').textContent = data.totalRemesas;
            document.querySelector('[data-completadas]').textContent = data.completadas;
            document.querySelector('[data-pendientes]').textContent = data.pendientes;
            
            // Actualizar progreso del día
            document.querySelector('[data-progreso-porcentaje]').textContent = data.progresoDelDia + '%';
            document.querySelector('[data-progreso-bar]').style.width = data.progresoDelDia + '%';
            document.querySelector('[data-progreso-bar]').setAttribute('aria-valuenow', data.progresoDelDia);
            document.querySelector('[data-progreso-texto]').textContent = data.completadas + ' de ' + data.totalRemesas + ' completadas';
            document.querySelector('[data-progreso-display]').textContent = data.progresoDelDia + '%';
            
            // Actualizar modulación
            document.querySelector('[data-verdes]').textContent = data.verdes;
            document.querySelector('[data-rojos]').textContent = data.rojos;
            document.querySelector('[data-porcentaje-verdes]').textContent = data.porcentajeVerdes + '% verdes';
            
            // Actualizar DODAS
            document.querySelector('[data-finalizadas]').textContent = data.finalizadas + '/' + data.totalDia;
            const detenidasElement = document.querySelector('[data-detenidas]');
            if (data.detenidas > 0) {
                if (detenidasElement) {
                    detenidasElement.textContent = data.detenidas + ' detenidas';
                    detenidasElement.parentElement.style.display = 'block';
                }
            } else {
                if (detenidasElement && detenidasElement.parentElement) {
                    detenidasElement.parentElement.style.display = 'none';
                }
            }
            
            // Actualizar tabla de exportadores
            actualizarTablaExportadores(data.exportadoresData);
            
            // Actualizar gráficos
            actualizarGraficos(data);
            
            // Actualizar ciudades en el detalle
            actualizarCiudadesDetalle(data.ciudadesData);
            
            // Actualizar modulación detalle
            actualizarModulacionDetalle(data);
            
            // Actualizar DODAS detalle
            actualizarDodasDetalle(data);
            
            console.log('✅ Datos actualizados correctamente:', new Date().toLocaleTimeString());
            
        } catch (error) {
            console.error('❌ Error al actualizar:', error);
        }
    }
    
    // Actualizar tabla de exportadores
    function actualizarTablaExportadores(exportadores) {
        const tbody = document.querySelector('[data-exportadores-tbody]');
        
        if (exportadores.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mb-0 mt-2">No hay operaciones registradas para este día</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = exportadores.map(exp => {
            const progreso = exp.cantidad > 0 ? Math.round((exp.completadas / exp.cantidad) * 100) : 0;
            return `
                <tr>
                    <td class="px-3 align-middle">
                        <strong>${exp.nombre}</strong>
                    </td>
                    <td class="text-center align-middle">
                        <span class="badge bg-primary rounded-pill">${exp.cantidad}</span>
                    </td>
                    <td class="text-center align-middle">
                        <span class="text-danger fw-bold">${exp.rojos}</span>
                        <span class="text-muted">/</span>
                        <span class="text-success fw-bold">${exp.verdes}</span>
                    </td>
                    <td class="text-center align-middle">
                        <span class="badge bg-success">${exp.completadas}</span>
                    </td>
                    <td class="text-center align-middle">
                        <div class="progress" style="height: 8px; width: 60px; margin: 0 auto;">
                            <div class="progress-bar bg-success" 
                                 style="width: ${progreso}%"
                                 title="${progreso}%"></div>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    // Actualizar gráficos
    function actualizarGraficos(data) {
        // Actualizar gráfico de ciudades
        ciudadesChart.data.labels = data.ciudadesData.map(c => c.ciudad);
        ciudadesChart.data.datasets[0].data = data.ciudadesData.map(c => c.cantidad);
        ciudadesChart.update();
        
        // Actualizar gráfico de modulación
        modulacionChart.data.datasets[0].data = [data.verdes, data.rojos];
        modulacionChart.update();
        
        // Actualizar gráfico de DODAS
        dodasChart.data.datasets[0].data = [data.finalizadas, data.totalDia - data.finalizadas];
        dodasChart.update();
    }
    
    // Actualizar detalle de ciudades
    function actualizarCiudadesDetalle(ciudades) {
        const container = document.querySelector('[data-ciudades-detalle]');
        container.innerHTML = ciudades.map(ciudad => `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">${ciudad.ciudad}</span>
                <span class="badge bg-primary">${ciudad.porcentaje}%</span>
            </div>
        `).join('');
    }
    
    // Actualizar detalle de modulación
    function actualizarModulacionDetalle(data) {
        document.querySelector('[data-verdes-chart]').textContent = data.verdes;
        document.querySelector('[data-rojos-chart]').textContent = data.rojos;
    }
    
    // Actualizar detalle de DODAS
    function actualizarDodasDetalle(data) {
        document.querySelector('[data-finalizadas-badge]').textContent = data.finalizadas;
        document.querySelector('[data-pendientes-badge]').textContent = data.totalDia - data.finalizadas;
    }
    
    // Inicializar gráficos al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
        console.log('🚀 Sistema de actualización automática iniciado');
        console.log('⏱️  Los datos se actualizarán cada 2 minutos');
    });
    
    // Actualizar cada 2 minutos (120000 ms)
    setInterval(actualizarDatos, 120000);
    
    // También puedes descomentar la siguiente línea para actualizar inmediatamente al cargar
    // setTimeout(actualizarDatos, 5000); // Actualiza a los 5 segundos de cargar
</script>
@endpush
@endsection