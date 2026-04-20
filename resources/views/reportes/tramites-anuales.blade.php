@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Trámites {{ $anio }} por mes</h1>

        <form class="mb-3" method="GET" action="{{ route('reportes.tramites-anuales') }}">
            <label for="anio">Seleccionar Año:</label>
            <select name="anio" id="anio" onchange="this.form.submit()" class="form-select w-auto d-inline-block">
                @for ($i = now()->year; $i >= now()->year - 5; $i--)
                    <option value="{{ $i }}" {{ $anio == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </form>

        {{-- Tabla --}}
        <table class="table table-bordered text-center">
            <thead class="table-primary">
                <tr>
                    <th>Mes</th>
                    <th>Total Trámites</th>
                </tr>
            </thead>
            <tbody>
                @foreach (range(1, 12) as $mes)
                    <tr>
                        <td>{{ \Carbon\Carbon::create()->month($mes)->translatedFormat('F') }}</td>
                        <td>{{ $data[$mes] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Gráfica --}}
        <canvas id="grafica" height="100"></canvas>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('grafica').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_map(fn($m) => \Carbon\Carbon::create()->month($m)->translatedFormat('F'), range(1, 12))) !!},
                datasets: [{
                    label: 'Trámites {{ $anio }}',
                    data: {!! json_encode(array_values($data)) !!},
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    title: {
                        display: true,
                        text: 'Trámites mensuales {{ $anio }}'
                    }
                }
            }
        });
    </script>


@endsection

@section('scripts')

@endsection