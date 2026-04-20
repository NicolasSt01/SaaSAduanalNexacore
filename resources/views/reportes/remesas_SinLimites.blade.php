@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- CABECERA CON TÍTULO Y BOTONES DE ACCIÓN --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Reporte de Remesas</h1>
            <p class="mb-0 text-muted">Visualización y análisis de remesas</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" id="btnExportExcel">
                <i class="fas fa-file-excel me-2"></i>Exportar Excel
            </button>
            <button type="button" class="btn btn-danger" id="btnExportPDF">
                <i class="fas fa-file-pdf me-2"></i>Exportar PDF
            </button>
        </div>
    </div>

    {{-- TARJETA PRINCIPAL --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros de Búsqueda</h6>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetFilters">
                <i class="fas fa-redo me-1"></i>Limpiar Filtros
            </button>
        </div>

        <div class="card-body">
            {{-- FORMULARIO DE FILTROS --}}
            <form method="GET" action="{{ route('reportes.remesas') }}" id="filterForm" class="row g-3 mb-4">
                @csrf
                
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Año</label>
                    <select name="year" class="form-select">
                        @foreach(range(date('Y')-5, date('Y')) as $year)
                            <option value="{{ $year }}" {{ request('year', date('Y')) == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Mes</label>
                    <select name="month" class="form-select">
                        <option value="">Todos los meses</option>
                        @foreach([
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ] as $num => $name)
                            <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Cliente</label>
                    <select name="cliente_id" class="form-select">
                        <option value="">Todos los clientes</option>
                        @foreach($clientes ?? [] as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre_empresa }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrar
                    </button>
                </div>
            </form>

            {{-- ESTADÍSTICAS RÁPIDAS --}}
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                            Total Remesas
                        </div>
                        <div class="h5 mb-0 fw-bold text-gray-800">
                            {{ number_format($totalRemesas ?? 0, 0) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">
                            Clientes Activos
                        </div>
                        <div class="h5 mb-0 fw-bold text-gray-800">
                            {{ $clientesActivos ?? 0 }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">
                            Períodos Analizados
                        </div>
                        <div class="h5 mb-0 fw-bold text-gray-800">
                            {{ count($reporte ?? []) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>

    {{-- GRÁFICA --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h6 class="m-0 font-weight-bold text-primary">Gráfica de Remesas</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-download me-1"></i>Exportar Gráfica
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" id="exportChartPNG">PNG</a></li>
                    <li><a class="dropdown-item" href="#" id="exportChartJPEG">JPEG</a></li>
                    <li><a class="dropdown-item" href="#" id="exportChartPDF">PDF</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="graficaRemesas" height="300"></canvas>
            </div>
        </div>
    </div>

    {{-- TABLA DE DATOS --}}
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalle de Remesas</h6>
        <div class="d-flex gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" class="form-control" placeholder="Buscar..." id="searchTable">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped" id="remesasTable">
                <thead>
                    <tr class="table-dark">
                        <th>Período</th>
                        <th>Cliente</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reporteDetalle ?? [] as $item)
                        <tr>
                            <td>{{ $item->fecha_formateada ?? 'N/A' }}</td>
                            <td>{{ $item->cliente_nombre ?? 'N/A' }}</td>
                            <td class="text-end">{{ number_format($item->cantidad, 0) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $item->tipo == 'mes' ? 'bg-primary' : 'bg-success' }}">
                                    {{ $item->tipo == 'mes' ? 'Mensual' : 'Semanal' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">Activo</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-database fa-2x mb-3"></i>
                                <p>No hay datos disponibles</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(!empty($reporteDetalle))
                    <tfoot class="table-secondary">
                        <tr>
                            <td colspan="2" class="fw-bold">Total General</td>
                            <td class="text-end fw-bold">{{ number_format($totalRemesas ?? 0, 0) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

</div>
@endsection

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }
    
    .table th {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
// Inicializar gráfica
const ctx = document.getElementById('graficaRemesas').getContext('2d');
let chartInstance = null;

function initializeChart() {
    const labels = @json(collect($reporte ?? [])->pluck('label'));
    const data = @json(collect($reporte ?? [])->pluck('total'));
    const colors = generateChartColors(data.length);
    
    if (chartInstance) {
        chartInstance.destroy();
    }
    
    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total de Remesas',
                data: data,
                backgroundColor: colors,
                borderColor: colors.map(color => color.replace('0.8', '1')),
                borderWidth: 2,
                borderRadius: 5,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Remesas: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    },
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Función para generar colores del gráfico
function generateChartColors(count) {
    const colors = [];
    const hueStep = 360 / count;
    
    for (let i = 0; i < count; i++) {
        const hue = i * hueStep;
        colors.push(`hsla(${hue}, 70%, 60%, 0.8)`);
    }
    
    return colors;
}

// Exportar a Excel
document.getElementById('btnExportExcel').addEventListener('click', function() {
    const table = document.getElementById('remesasTable');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Remesas"});
    XLSX.writeFile(wb, `reporte_remesas_${new Date().toISOString().split('T')[0]}.xlsx`);
});

// Exportar a PDF
document.getElementById('btnExportPDF').addEventListener('click', function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Título
    doc.setFontSize(16);
    doc.text('Reporte de Remesas', 14, 15);
    
    // Información del reporte
    doc.setFontSize(10);
    doc.text(`Generado: ${new Date().toLocaleDateString()}`, 14, 25);
    doc.text(`Filtros aplicados: ${getCurrentFilters()}`, 14, 30);
    doc.text(`Total Remesas: ${document.querySelector('.h5.mb-0.fw-bold.text-gray-800').textContent}`, 14, 35);
    
    // Tabla
    doc.autoTable({
        html: '#remesasTable',
        startY: 45,
        theme: 'striped',
        headStyles: { fillColor: [41, 128, 185] },
        footStyles: { fillColor: [52, 152, 219] },
        didDrawPage: function(data) {
            doc.setFontSize(10);
            doc.text(`Página ${data.pageNumber}`, data.settings.margin.left, doc.internal.pageSize.height - 10);
        }
    });
    
    doc.save(`reporte_remesas_${new Date().toISOString().split('T')[0]}.pdf`);
});

// Exportar gráfica
document.getElementById('exportChartPNG').addEventListener('click', function(e) {
    e.preventDefault();
    const link = document.createElement('a');
    link.download = `grafica_remesas_${new Date().toISOString().split('T')[0]}.png`;
    link.href = document.getElementById('graficaRemesas').toDataURL('image/png');
    link.click();
});

document.getElementById('exportChartJPEG').addEventListener('click', function(e) {
    e.preventDefault();
    const link = document.createElement('a');
    link.download = `grafica_remesas_${new Date().toISOString().split('T')[0]}.jpg`;
    link.href = document.getElementById('graficaRemesas').toDataURL('image/jpeg', 0.9);
    link.click();
});

// Buscar en tabla
document.getElementById('searchTable').addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#remesasTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// Resetear filtros
document.getElementById('btnResetFilters').addEventListener('click', function() {
    document.getElementById('filterForm').reset();
    document.getElementById('filterForm').submit();
});

// Obtener filtros actuales para PDF
function getCurrentFilters() {
    const form = document.getElementById('filterForm');
    const year = form.querySelector('[name="year"]').value;
    const month = form.querySelector('[name="month"]').value;
    const cliente = form.querySelector('[name="cliente_id"]').value;
    
    let filters = [];
    if (year) filters.push(`Año: ${year}`);
    if (month) filters.push(`Mes: ${month}`);
    if (cliente) filters.push(`Cliente: ${form.querySelector('[name="cliente_id"]').options[form.querySelector('[name="cliente_id"]').selectedIndex].text}`);
    
    return filters.join(', ') || 'Sin filtros';
}

// Inicializar gráfica cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    
    // Agregar tooltips a los botones
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush