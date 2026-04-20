@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Crosspoint yearly by month</h1>

        {{-- -Filtros de Busqueda --}}
        <form method="GET" action="{{ route('reportes.tramites-comparativos') }}" class="mb-3">
            <div class="row g-3 align-items-end">
                <div class="col-auto">
                    <label for="desde" class="form-label">Desde Año</label>
                    <input type="number" name="desde" id="desde" class="form-control" value="{{ $desde }}">
                </div>
                <div class="col-auto">
                    <label for="hasta" class="form-label">Hasta Año</label>
                    <input type="number" name="hasta" id="hasta" class="form-control" value="{{ $hasta }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary">Filtrar</button>
                </div>
            </div>
        </form>

        {{-- Tabla comparativa --}}
         <div class="row">
        {{-- Columna izquierda: tabla más angosta --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-body p-2">
                    <table class="table table-sm table-bordered text-center align-middle mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th>Mes</th>
                                @foreach ($years as $year)
                                    <th>{{ $year }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($months as $m)
                                <tr>
                                    <td>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</td>
                                    @foreach ($years as $year)
                                        <td>{{ $data[$year][$m] }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th>TOTALES</th>
                                @foreach ($years as $year)
                                    <th>{{ $yearTotals[$year] }}</th>
                                @endforeach
                            </tr>
                            <tr class="table-dark text-white">
                                <th>GRAND TOTAL</th>
                                <th colspan="{{ count($years) }}">{{ $grandTotal }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Columna derecha: gráfica más ancha --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    {{-- Gráfica comparativa --}}
                    <canvas id="grafica" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

        {{-- Gráfica comparativa --}}
        {{--<canvas id="grafica" height="100"></canvas>--}}
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('grafica').getContext('2d');

        const labels = {!! json_encode(array_map(fn($m) => \Carbon\Carbon::create()->month($m)->translatedFormat('F'), $months)) !!};

        // Preparar datasets para cada año
        const datasets = [
            @foreach ($years as $year)
                        {
                    label: '{{ $year }}',
                    data: {!! json_encode(array_values($data[$year])) !!},
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                },
            @endforeach
            ];

        // Colores diferentes por año
        const palette = ['#0d6efd', '#198754', '#dc3545', '#ffc107', '#6f42c1'];
        datasets.forEach((d, i) => {
            d.borderColor = palette[i % palette.length];
            d.backgroundColor = palette[i % palette.length];
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: 'Comparativo Mensual de Trámites'
                    }
                }
            }
        });
    </script>


@endsection

@section('scripts')

@endsection