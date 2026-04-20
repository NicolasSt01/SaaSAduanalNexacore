@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Gráfico de Operaciones por Cliente</h2>

    <div class="card p-4 shadow-sm">
        <canvas id="graficoBarras"></canvas>
    </div>

    <div class="card p-4 shadow-sm mt-4">
        <canvas id="graficoPastel"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = @json($labels);
    const values = @json($values);

    // Gráfico de Barras
    new Chart(document.getElementById('graficoBarras'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Operaciones',
                data: values,
                borderWidth: 1,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                ],
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } }
        }
    });

    // Gráfico de Pastel
    new Chart(document.getElementById('graficoPastel'), {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                ]
            }]
        }
    });
</script>
@endsection
