@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Encabezado de bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h3 mb-0">Bienvenido(a), <span class="text-primary">{{ Auth::user()->name }}</span></h1>
                    <p class="text-muted mb-0">Aquí puedes consultar el estado de tus pedimentos y analíticas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de métricas rápidas -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Pedimentos este mes</h5>
                    <div class="d-flex align-items-center">
                        <h2 class="mb-0">--</h2>
                        <span class="badge bg-primary ms-2">+0%</span>
                    </div>
                    <small class="text-muted">vs mes anterior</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card border-start border-success border-4 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Pedimentos en verde</h5>
                    <h2 class="mb-0">--</h2>
                    <small class="text-muted">últimos 30 días</small>
                </div>
            </div>
        </div>

        <!--<div class="col-md-3 mb-3 mb-md-0">
            <div class="card border-start border-warning border-4 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Pedimentos en amarillo</h5>
                    <h2 class="mb-0">--</h2>
                    <small class="text-muted">últimos 30 días</small>
                </div>
            </div>
        </div>
-->

        <div class="col-md-3">
            <div class="card border-start border-danger border-4 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Pedimentos en rojo</h5>
                    <h2 class="mb-0">--</h2>
                    <small class="text-muted">últimos 30 días</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico y últimos pedimentos -->
    <div class="row mb-4">
        <!-- Gráfico de evolución -->
        <div class="col-lg-8 mb-3 mb-lg-0">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Evolución de Operaciones</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <!-- Aquí irá el gráfico -->
                        <p class="text-center text-muted my-5">Gráfico de evolución mensual</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimos pedimentos -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Operacion del dia</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">Ver todos</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <!-- Ejemplo de items -->
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong>#12345678</strong>
                                <span class="badge bg-success">Completado</span>
                            </div>
                            <small class="text-muted">Aduana: Tijuana</small>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong>#87654321</strong>
                                <span class="badge bg-success">completado</span>
                            </div>
                            <small class="text-muted">Aduana: Veracruz</small>
                        </div>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong>#11223344</strong>
                                <span class="badge bg-danger">Rojo</span>
                            </div>
                            <small class="text-muted">Aduana: México</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de pedimentos recientes -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pedimentos recientes</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
                        <button class="btn btn-sm btn-outline-primary ms-2">Exportar</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>N° Pedimento</th>
                                    <th>Fecha Pago</th>
                                    <th>Aduana</th>
                                    <th>Patente</th>
                                    <th>Categoría</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Ejemplo de filas -->
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
    </div>
</div>
@endsection