@extends('layouts.app')

@section('content')
<div class="container py-4">

    <!-- Bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0 rounded-3">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1">Bienvenido(a), <span class="text-primary fw-bold">{{ Auth::user()->name }}</span></h1>
                        <p class="text-muted mb-0">Aquí puedes consultar el estado de tus pedimentos y analíticas</p>
                    </div>
                    <i class="bi bi-graph-up text-primary display-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 rounded-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Pedimentos este mes</h6>
                            <h3 class="mb-0">--</h3>
                            <small class="text-success">+0% vs mes anterior</small>
                        </div>
                        <div class="bg-primary text-white rounded-circle p-3">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 rounded-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Pedimentos en verde</h6>
                            <h3 class="mb-0">--</h3>
                            <small class="text-muted">últimos 30 días</small>
                        </div>
                        <div class="bg-success text-white rounded-circle p-3">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 rounded-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Pedimentos en rojo</h6>
                            <h3 class="mb-0">--</h3>
                            <small class="text-muted">últimos 30 días</small>
                        </div>
                        <div class="bg-danger text-white rounded-circle p-3">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico + operaciones -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100 rounded-3">
                <div class="card-header bg-white border-0 fw-bold">Evolución de Operaciones</div>
                <div class="card-body">
                    <canvas id="operacionesChart" height="120"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100 rounded-3">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center fw-bold">
                    Operaciones del día
                    <a href="#" class="btn btn-sm btn-outline-primary">Ver todos</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>#12345678</strong><br>
                                <small class="text-muted">Aduana: Tijuana</small>
                            </div>
                            <span class="badge bg-success">Completado</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>#87654321</strong><br>
                                <small class="text-muted">Aduana: Veracruz</small>
                            </div>
                            <span class="badge bg-success">Completado</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>#11223344</strong><br>
                                <small class="text-muted">Aduana: México</small>
                            </div>
                            <span class="badge bg-danger">Rojo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de pedimentos -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center fw-bold">
            Pedimentos recientes
            <div>
                <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
                <button class="btn btn-sm btn-outline-primary ms-2">Exportar</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>N° Pedimento</th>
                            <th>Fecha Pago</th>
                            <th>Aduana</th>
                            <th>Patente</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>12345678</td>
                            <td>15/08/2023</td>
                            <td>Tijuana</td>
                            <td>1234</td>
                            <td>Importación</td>
                            <td><span class="badge bg-success">Verde</span></td>
                            <td><a href="#" class="btn btn-sm btn-outline-primary">Detalle</a></td>
                        </tr>
                        <tr>
                            <td>87654321</td>
                            <td>10/08/2023</td>
                            <td>Veracruz</td>
                            <td>5678</td>
                            <td>Exportación</td>
                            <td><span class="badge bg-success">Verde</span></td>
                            <td><a href="#" class="btn btn-sm btn-outline-primary">Detalle</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Script gráfico -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('operacionesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            datasets: [{
                label: 'Operaciones',
                data: [12, 19, 14, 20, 16, 22],
                fill: true,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                pointBackgroundColor: '#0d6efd',
                pointBorderColor: '#fff'
            }]
        },
        options: {
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
@endsection
