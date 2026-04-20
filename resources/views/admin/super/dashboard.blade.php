@extends('layouts.admin')

@section('header_title', 'Dashboard Principal (NexaCore)')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <h3 class="text-2xl text-gray-800 font-bold border-b-2 border-indigo-500 pb-2 inline-block">Monitor en Tiempo Real</h3>
    <div class="flex items-center gap-2">
        <span class="flex h-3 w-3 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
        </span>
        <span class="text-xs text-gray-500 font-mono tracking-wider uppercase group-hover:text-indigo-600 transition" id="last-update">ACTUALIZANDO...</span>
    </div>
</div>

<!-- KPIs -->
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center hover:shadow-md transition">
        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mb-4 text-xl">
            <i class="fas fa-cubes"></i>
        </div>
        <p class="text-gray-500 text-sm mb-1 uppercase tracking-wide">Operaciones Totales</p>
        <span class="text-3xl font-bold text-gray-800" id="kpi-total_operaciones">0</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center hover:shadow-md transition">
        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4 text-xl">
            <i class="fas fa-boxes"></i>
        </div>
        <p class="text-gray-500 text-sm mb-1 uppercase tracking-wide">Operaciones (Hoy)</p>
        <span class="text-3xl font-bold text-gray-800" id="kpi-operaciones_hoy">0</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center hover:shadow-md transition">
        <div class="w-12 h-12 bg-green-50 text-green-600 rounded-full flex items-center justify-center mb-4 text-xl">
            <i class="fas fa-users"></i>
        </div>
        <p class="text-gray-500 text-sm mb-1 uppercase tracking-wide">Usuarios Activos</p>
        <span class="text-3xl font-bold text-gray-800" id="kpi-usuarios_activos">0</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center text-center hover:shadow-md transition">
        <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center mb-4 text-xl">
            <i class="fas fa-building"></i>
        </div>
        <p class="text-gray-500 text-sm mb-1 uppercase tracking-wide">Agencias (Tenants)</p>
        <span class="text-3xl font-bold text-gray-800" id="kpi-tenants_activos">0</span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
        <h4 class="text-gray-700 font-bold mb-4 flex items-center gap-2"><i class="fas fa-chart-line text-indigo-500"></i> Operaciones de la Semana Actual</h4>
        <div class="h-64 w-full">
            <canvas id="opsSemanaChart"></canvas>
        </div>
    </div>

    <!-- Comms KPIs -->
    <div class="flex flex-col gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition">
            <div class="flex gap-4 items-center">
                <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center text-xl">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <h5 class="text-gray-800 font-bold text-lg" id="kpi-emails_hoy">0</h5>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Emails Enviados Hoy</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:shadow-md transition">
            <div class="flex gap-4 items-center">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center text-xl">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div>
                    <h5 class="text-gray-800 font-bold text-lg" id="kpi-whatsapp_hoy">0</h5>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">WhatsApp Enviados Hoy</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('opsSemanaChart').getContext('2d');
        let opsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
                datasets: [{
                    label: 'Operaciones',
                    data: [0, 0, 0, 0, 0, 0, 0],
                    borderColor: '#4F46E5', // Indigo 600
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4F46E5',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#f3f4f6' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        function formatTime(date) {
            return date.getHours().toString().padStart(2, '0') + ':' + 
                   date.getMinutes().toString().padStart(2, '0') + ':' + 
                   date.getSeconds().toString().padStart(2, '0');
        }

        function fetchLiveData() {
            fetch('/nexacore-admin/dashboard/data')
                .then(res => res.json())
                .then(res => {
                    // Actualizar KPIs
                    Object.keys(res.kpis).forEach(key => {
                        const el = document.getElementById('kpi-' + key);
                        if(el && el.innerText !== res.kpis[key]) {
                            el.innerText = res.kpis[key];
                            // Pequeña animación de cambio
                            el.classList.add('text-indigo-600');
                            setTimeout(() => el.classList.remove('text-indigo-600'), 500);
                        }
                    });

                    // Actualizar Gráfico
                    opsChart.data.labels = res.chart.labels;
                    opsChart.data.datasets[0].data = res.chart.data;
                    opsChart.update();

                    document.getElementById('last-update').innerText = 'ACTUALIZADO: ' + formatTime(new Date());
                })
                .catch(err => console.error("Error fetching live data", err));
        }

        // Ejecutar inmediatamente
        fetchLiveData();

        // Polling cada 5 segundos
        setInterval(fetchLiveData, 5000);
    });
</script>
@endsection
