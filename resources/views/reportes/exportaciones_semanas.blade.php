@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Operaciones por Semanas</h1>

        <form class="mb-4" method="GET" action="{{ route('reportes.demo') }}">
            <div class="row">
                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" 
                           value="{{ $fecha_inicio }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Fecha Fin:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" 
                           value="{{ $fecha_fin }}" class="form-control" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Generar Reporte</button>
                </div>
            </div>
            @error('fecha_fin')
                <div class="text-danger mt-2">{{ $message }}</div>
            @enderror
        </form>

        @if($reporte->count() > 0)
            {{-- Tabla --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>Cliente</th>
                            @php
                                // Obtener todas las semanas únicas de todos los clientes
                                $semanasUnicas = [];
                                foreach($reporte as $cliente) {
                                    $semanasUnicas = array_merge($semanasUnicas, array_keys($cliente['semanas']));
                                }
                                $semanasUnicas = array_unique($semanasUnicas);
                                sort($semanasUnicas);
                            @endphp
                            
                            @foreach($semanasUnicas as $semana)
                                <th class="text-center">{{ $semana }}</th>
                            @endforeach
                            <th class="text-center table-success">Total General</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reporte as $cliente)
                            <tr>
                                <td class="fw-bold">{{ $cliente['cliente'] }}</td>
                                
                                @foreach($semanasUnicas as $semana)
                                    <td class="text-center">
                                        {{ $cliente['semanas'][$semana] ?? 0 }}
                                    </td>
                                @endforeach
                                
                                <td class="text-center fw-bold table-success">
                                    {{ $cliente['total_general'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    {{-- Footer con totales por semana --}}
                    <tfoot class="table-secondary">
                        <tr>
                            <td class="fw-bold">Total por Semana</td>
                            @foreach($semanasUnicas as $semana)
                                <td class="text-center fw-bold">
                                    {{ array_sum(array_column($reporte->toArray(), 'semanas.' . $semana)) }}
                                </td>
                            @endforeach
                            <td class="text-center fw-bold table-warning">
                                {{ array_sum(array_column($reporte->toArray(), 'total_general')) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Gráfica --}}
            <div class="mt-5">
                <h3 class="mb-3">Gráfica de Operaciones por Semanas</h3>
                <canvas id="graficaOperaciones" height="100"></canvas>
            </div>
        @else
            <div class="alert alert-info">
                No se encontraron operaciones para el rango de fechas seleccionado.
            </div>
        @endif
    </div>



    @if($reporte->count() > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('graficaOperaciones').getContext('2d');
        
        // Preparar datos para la gráfica
        const clientes = {!! json_encode(array_column($reporte->toArray(), 'cliente')) !!};
        const semanasUnicas = {!! json_encode($semanasUnicas) !!};
        const datosClientes = {!! json_encode($reporte->toArray()) !!};

        // Crear datasets para cada semana
        const datasets = semanasUnicas.map((semana, index) => {
            const colores = [
                '#0d6efd', '#198754', '#dc3545', '#ffc107', '#6f42c1',
                '#fd7e14', '#20c997', '#e83e8c', '#6610f2', '#0dcaf0'
            ];
            
            return {
                label: semana,
                data: clientes.map((cliente, i) => {
                    return datosClientes[i].semanas[semana] || 0;
                }),
                backgroundColor: colores[index % colores.length] + '80', // 80 para transparencia
                borderColor: colores[index % colores.length],
                borderWidth: 2
            };
        });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: clientes,
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { 
                        display: true,
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Operaciones por Cliente y Semana ({{ $fecha_inicio }} al {{ $fecha_fin }})'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Clientes'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Cantidad de Operaciones'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    @endif
@endsection

@section('scripts')
    
@endsection