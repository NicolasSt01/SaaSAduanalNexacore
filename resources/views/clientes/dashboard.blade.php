@extends('layouts.app')

@push('styles')

@endpush

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="row g-4">
        
        <!-- MAIN CONTENT (LEFT) -->
        <div class="col-lg-9">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-0">Dashboard</h2>
                    <p class="text-muted small">Resumen de operaciones y estado fiscal</p>
                </div>
                <!-- Search Box -->
                 <div class="d-none d-md-block" style="width: 250px;">
                     <form action="{{ route('cliente.admindashboard') }}" method="GET">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill"><i class="bi bi-search"></i></span>
                            <input type="text" name="q" class="form-control border-start-0 rounded-end-pill ps-0" placeholder="Buscar pedimento...">
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stats Widgets Row  -->
            <div class="row g-4 mb-4">
                <!-- 1. Trámites del Mes (Enriched) -->
                <div class="col-md-4">
                    <div class="card h-100 p-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                     <h3 class="fw-bold mb-0">{{ $pedimentosMes }}</h3>
                                     <p class="text-muted small mb-0 text-uppercase fw-bold">Trámites (Mes)</p>
                                </div>
                                <!-- Static badge for visuals, controller doesn't send % yet -->
                                <span class="badge bg-success-subtle text-success rounded-pill small"><i class="bi bi-activity me-1"></i> Activo</span>
                            </div>
                            <!-- Micro Sparkline -->
                            <div class="mt-3 micro-chart position-relative" style="height: 50px;">
                                <canvas id="miniChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Semáforo Fiscal (Enriched) -->
                <div class="col-md-4">
                    <div class="card h-100 p-3 bg-dark-green text-white">
                        <div class="card-body">
                            @php
                                $totalSem = $pedimentosVerde + $pedimentosRojo;
                                $pctVerde = $totalSem > 0 ? round(($pedimentosVerde / $totalSem) * 100) : 0;
                            @endphp
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0 fw-bold">{{ $pctVerde }}%</h4>
                                    <p class="small mb-2 text-uppercase fw-bold opacity-75">Libres</p>
                                </div>
                                <div class="text-end opacity-75"><i class="bi bi-shield-check fs-2"></i></div>
                            </div>
                             <div class="progress mb-2" style="height: 6px; background-color: rgba(255,255,255,0.2);">
                                <div class="progress-bar bg-success" style="width: {{ $pctVerde }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between small opacity-75" style="font-size: 0.75rem;">
                                <span><i class="bi bi-check-circle-fill text-success me-1"></i> {{ $pedimentosVerde }} Verdes</span>
                                <span><i class="bi bi-exclamation-circle-fill text-danger me-1"></i> {{ $pedimentosRojo }} Rojos</span>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- 3. Importadores (Enriched) -->
                 <div class="col-md-4">
                    <div class="card h-100 p-3 bg-yellow-soft">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                     <h3 class="fw-bold mb-0">{{ $topImportadores->count() }}</h3>
                                     <p class="small mb-0 text-uppercase fw-bold opacity-75">Importadores</p>
                                </div>
                                <i class="bi bi-people fs-2 opacity-50"></i>
                            </div>
                             <!-- Dynamic Avatar Bubbles -->
                             <div class="avatar-group mt-auto">
                                @foreach($topImportadores->take(4) as $imp)
                                    @php
                                        // Generate initials
                                        $initials = strtoupper(substr($imp->importador ?? 'NA', 0, 2));
                                        // Random color logic based on first char charCode
                                        $colors = ['bg-primary', 'bg-success', 'bg-danger', 'bg-warning text-dark', 'bg-info'];
                                        $bgClass = $colors[ord(substr($imp->importador ?? 'A', 0, 1)) % 5];
                                    @endphp
                                    <div class="avatar {{ $bgClass }} text-white" title="{{ $imp->importador }}">{{ $initials }}</div>
                                @endforeach
                                @if($topImportadores->count() > 4)
                                    <div class="avatar more">+{{ $topImportadores->count() - 4 }}</div>
                                @endif
                            </div>
                             <div class="mt-2 text-end">
                                <small class="opacity-75" style="font-size: 0.7rem;">Activos este mes</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <!-- ROW 2: Pie & Stacked Bar -->
             <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                             <h6 class="fw-bold small mb-3">Semáforo Global</h6>
                             <div class="chart-container-sm"><canvas id="chartSemaforo"></canvas></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                             <h6 class="fw-bold small mb-3">Operaciones por Aduana</h6>
                             <div class="chart-container-sm"><canvas id="chartAduanas"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 3: Histórico Anual (Target: Enriched) -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-body">
                             <h6 class="fw-bold small mb-3">Histórico Anual de Operaciones</h6>
                             <div class="chart-container-md"><canvas id="chartAnual"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ROW 4: Progreso Diario (Full Width) -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-body">
                             <h6 class="fw-bold small mb-3">Progreso Diario del Mes</h6>
                             <div class="chart-container-md"><canvas id="chartDiario"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listas -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    @php
                        $bgImage = null;
                        if($productoEstrella) {
                            $prodName = Str::lower($productoEstrella->nombre_producto);
                            $map = [
                                'aguacate' => 'https://salassys.com/wp-content/uploads/2026/02/aguacate.png',
                                'berries' => 'https://salassys.com/wp-content/uploads/2026/02/Berries-scaled.png',
                                'ejote' => 'https://salassys.com/wp-content/uploads/2026/02/Ejote-scaled.jpg',
                                'zanahoria' => 'https://salassys.com/wp-content/uploads/2026/02/Zanahoria-scaled.png',
                                'limon' => 'https://salassys.com/wp-content/uploads/2026/02/limon.png',
                                'mango' => 'https://salassys.com/wp-content/uploads/2026/02/11479892-scaled.png'
                            ];
                            foreach($map as $key => $url) {
                                if(Str::contains($prodName, $key)) {
                                    $bgImage = $url;
                                    break;
                                }
                            }
                        }
                    @endphp
                     <div class="card h-100 border-0 overflow-hidden position-relative text-white" style="background: linear-gradient(to bottom right, #2c3e50, #000000);">
                        <!-- Dynamic Product Image (Right Side) -->
                        @if($bgImage)
                            <div class="position-absolute end-0 top-0 h-100 w-75" 
                                 style="background-image: url('{{ $bgImage }}'); 
                                        background-size: auto 100%; 
                                        background-repeat: no-repeat; 
                                        background-position: right center; 
                                        opacity: 1;
                                        mask-image: linear-gradient(to right, transparent, black 40%);
                                        -webkit-mask-image: linear-gradient(to right, transparent, black 40%);">
                            </div>
                        @else
                            <i class="bi bi-box-seam position-absolute text-white opacity-10" style="font-size: 5rem; bottom: -1rem; right: -1rem; z-index: 0;"></i>
                        @endif

                        <div class="card-body position-relative d-flex flex-column justify-content-center" style="z-index: 1;">
                            <span class="badge bg-white text-dark align-self-start mb-2">PRODUCTO ESTRELLA</span>
                            <h5 class="fw-bold mb-1" style="text-shadow: 0 2px 4px rgba(0,0,0,0.8);">{{ $productoEstrella ? Str::limit($productoEstrella->nombre_producto, 20) : 'N/A' }}</h5>
                             <small class="opacity-75 mb-3" style="text-shadow: 0 1px 2px rgba(0,0,0,0.8);">{{ $productoEstrella ? $productoEstrella->total . ' Movimientos' : 'Sin datos' }}</small>
                            <a href="#" class="btn btn-sm btn-outline-light rounded-pill py-1 px-3 shadow-sm" style="width: fit-content; backdrop-filter: blur(2px);">Ver Reporte</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                     <div class="card h-100">
                        <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0">Pedimentos Recientes</h6></div>
                        <div class="card-body">
                            <div class="row g-3">
                                @forelse($pedimentosRecientes as $ped)
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-light position-relative h-100">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="badge bg-white border text-dark">{{ $ped->categoria ?? 'Exportación' }}</span>
                                            <small class="text-muted">{{ $ped->created_at ? $ped->created_at->diffForHumans() : '' }}</small>
                                        </div>
                                        <h6 class="fw-bold text-primary mb-1">Pd. {{ $ped->numero_pedimento }}</h6>
                                        <p class="small text-muted mb-0"><i class="bi bi-building"></i> {{ $ped->aduana->nombre_aduana ?? 'N/A' }}</p>
                                        <a href="{{ route('expedientes.showclient', $ped->id) }}" class="stretched-link"></a>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12 text-center text-muted small">No hay datos recientes.</div>
                                @endforelse
                            </div>
                        </div>
                     </div>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDEBAR (Same as before) -->
        <div class="col-lg-3">
            <div class="card border-0 h-100 bg-white">
                <div class="card-body">
                     <!-- Perfil -->
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                         <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width:40px; height:40px;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                         </div>
                         <div><h6 class="mb-0 fw-bold small">{{ Auth::user()->name }}</h6><small class="text-muted">Cliente</small></div>
                    </div>
                    <!-- Calendar HEATMAP -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-capitalize">{{ \Carbon\Carbon::parse($hasta)->locale('es')->monthName }} {{ \Carbon\Carbon::parse($hasta)->year }}</h6>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-2 text-center" style="font-size: 0.7rem;">
                            <span style="width:32px">L</span><span style="width:32px">M</span><span style="width:32px">M</span><span style="width:32px">J</span><span style="width:32px">V</span><span style="width:32px">S</span><span style="width:32px">D</span>
                        </div>
                         @foreach($calendario as $semana)
                        <div class="d-flex justify-content-between mb-2">
                            @foreach($semana as $dia)
                                @php
                                    $activeClass = '';
                                    if ($dia['intensidad'] === 'low') $activeClass = 'active-low';
                                    elseif ($dia['intensidad'] === 'medium') $activeClass = 'active-med';
                                    elseif ($dia['intensidad'] === 'high') $activeClass = 'active-high';
                                    $opacityClass = $dia['es_mes_actual'] ? '' : 'text-muted opacity-25';
                                @endphp
                                <div class="calendar-day {{ $activeClass }} {{ $opacityClass }}" title="{{ $dia['total'] }} operaciones">{{ $dia['dia'] }}</div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                    <!-- AGENDA DETALLADA -->
                    <div>
                         <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Agenda de Hoy</h6>
                            <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $operacionesHoy->count() }}</span>
                        </div>
                        @if($operacionesHoy->count() > 0)
                            <div class="timeline mt-2">
                                @foreach($operacionesHoy->take(3) as $op)
                                    @php
                                        $isRed = in_array($op->modulacion, ['RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO']);
                                        $statusClass = $isRed ? 'status-red' : 'status-green';
                                        $modulacionText = $isRed ? 'Reconocimiento Aduanero' : 'Desaduanamiento Libre';
                                        $pedimentoNum = $op->expediente ? $op->expediente->numero_pedimento : 'N/A';
                                    @endphp
                                    <div class="timeline-item {{ $statusClass }}">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="fw-bold mb-0 small {{ $isRed ? 'text-danger' : '' }}">Factura {{ $op->num_factura }}</h6>
                                            @if($isRed)<small class="text-danger fw-bold" style="font-size:0.6rem">INCIDENCIA</small>@else<small class="text-muted" style="font-size:0.7rem">{{ $op->fecha ? \Carbon\Carbon::parse($op->fecha)->format('H:i') : '' }}</small>@endif
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Ped. {{ $pedimentoNum }} - {{ $modulacionText }}</small>
                                    </div>
                                @endforeach
                            </div>
                            <!-- Show More Button logic same as previously implemented -->
                             @if($operacionesHoy->count() > 3)
                                <button class="btn btn-sm btn-light w-100 text-primary small fw-bold mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#moreAgenda">
                                    <i class="bi bi-plus-lg me-1"></i> Ver {{ $operacionesHoy->count() - 3 }} más
                                </button>
                                <div class="collapse mt-2" id="moreAgenda">
                                    <div class="timeline">
                                        @foreach($operacionesHoy->skip(3) as $op)
                                            @php $pedimentoNum = $op->expediente ? $op->expediente->numero_pedimento : 'N/A'; @endphp
                                             <div class="timeline-item status-green">
                                                <h6 class="fw-bold mb-0 small">Factura {{ $op->num_factura }}</h6>
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Ped. {{ $pedimentoNum }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="text-center text-muted small py-3">No hay operaciones hoy.</p>
                        @endif
                    </div>

                    <!-- WIDGET: Importadores del Mes -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Importadores (Mes)</h6>
                            <i class="bi bi-people text-muted"></i>
                        </div>
                        @if(isset($importadoresMes) && $importadoresMes->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($importadoresMes as $imp)
                                    @php
                                        // Random pastel color for avatar
                                        $colors = ['bg-primary-subtle text-primary', 'bg-success-subtle text-success', 'bg-info-subtle text-info', 'bg-warning-subtle text-warning', 'bg-danger-subtle text-danger'];
                                        $initials = strtoupper(substr($imp->importador ?? 'NA', 0, 2));
                                        $colorClass = $colors[ord(substr($imp->importador ?? 'A', 0, 1)) % 5];
                                    @endphp
                                    <div class="list-group-item px-0 border-0 d-flex align-items-center mb-2 pb-0">
                                        <div class="rounded-circle {{ $colorClass }} d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:36px; height:36px; font-size:0.75rem; font-weight:bold;">
                                            {{ $initials }}
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h6 class="mb-0 small fw-bold text-truncate" title="{{ $imp->importador }}">{{ Str::limit($imp->importador, 18) }}</h6>
                                            <small class="text-muted" style="font-size:0.7rem;">{{ $imp->total }} Operaciones</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-center text-muted small py-2">Sin actividad este mes.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Force Font Injection directly in Blade to override theme issues */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    :root {
        --primary-soft: #eef2ff;
        --secondary-soft: #f8f9fa;
        --success-soft: #d1e7dd;
        --danger-soft: #f8d7da;
        --warning-soft: #fff3cd;
        --dark-green: #0f172a; 
        --card-radius: 1rem;
        --heatmap-low: #d1e7dd;
        --heatmap-med: #198754;
        --heatmap-high: #0d4a3e;
        /* Force Font Family */
        --font-family-base: 'Inter', system-ui, -apple-system, sans-serif !important;
    }
    
    body { background-color: #f3f4f6; font-family: var(--font-family-base); color: #212529; }
    
    /* Enforce font on headers and specific elements */
    h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 { font-family: var(--font-family-base); font-weight: 700; }

    .card { border: none; border-radius: var(--card-radius); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: transform 0.2s, box-shadow 0.2s; }
    .card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
    .bg-yellow-soft { background-color: #FFC107; color: #000; }
    .bg-dark-green { background-color: #0d4a3e; color: #fff; }

    /* Calendar & Timeline */
    .calendar-day { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 0.8rem; cursor: default; margin: 0 auto; color: #6c757d; }
    .calendar-day.active-low { background-color: var(--heatmap-low); color: #0f5132; font-weight: bold; }
    .calendar-day.active-med { background-color: var(--heatmap-med); color: #fff; font-weight: bold; }
    .calendar-day.active-high { background-color: var(--heatmap-high); color: #fff; font-weight: bold; }
    
    .timeline-item { border-left: 2px solid #e5e7eb; padding-left: 1rem; padding-bottom: 1.5rem; position: relative; }
    .timeline-item::before { content: ''; position: absolute; left: -5px; top: 0; width: 8px; height: 8px; border-radius: 50%; background-color: #adb5bd; }
    .timeline-item.status-green::before { background-color: #198754; }
    .timeline-item.status-red::before { background-color: #dc3545; }
    .timeline-item:last-child { border-left: none; padding-bottom: 0; }
    
    /* Visuals */
    .micro-chart { height: 40px; }
    .chart-container-sm { height: 200px; position: relative; }
    .chart-container-md { height: 250px; position: relative; }

    /* Avatar Group */
    .avatar-group { display: flex; align-items: center; }
    .avatar-group .avatar { width: 32px; height: 32px; border-radius: 50%; background-color: #fff; border: 2px solid #fff; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold; color: #555; margin-left: -10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .avatar-group .avatar:first-child { margin-left: 0; }
    .avatar-group .avatar.more { background-color: #e9ecef; color: #495057; }
</style>
{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Data injected from Controller
        const semaforoData = [{{ $pedimentosVerde }}, {{ $pedimentosRojo }}];
        const aduanaLabels = @json($aduanaLabels);
        const aduanaVerdes = @json($aduanaVerdes);
        const aduanaRojos = @json($aduanaRojos);
        const anualLabels = @json($historialLabels);
        const anualValues = @json($historialValues);
        const diarioLabels = @json($diarioLabels);
        const diarioValues = @json($diarioValues);

        // Mini Chart (Sparkline)
        const ctxMini = document.getElementById('miniChart');
        if (ctxMini) {
             new Chart(ctxMini.getContext('2d'), {
                type: 'line',
                data: { labels: anualLabels, datasets: [{ data: anualValues, borderColor: '#0d6efd', backgroundColor: 'rgba(13, 110, 253, 0.1)', borderWidth:2, tension:0.4, pointRadius:0, fill:true }] },
                options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}, tooltip:{enabled:false}}, scales:{x:{display:false}, y:{display:false}} }
             });
        }
        
        if(document.getElementById('chartSemaforo')) {
            new Chart(document.getElementById('chartSemaforo'), {
                type: 'doughnut',
                data: { labels: ['Libre', 'Rojo'], datasets: [{ data: semaforoData, backgroundColor: ['#198754', '#dc3545'], borderWidth: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, cutout: '70%' }
            });
        }
         if(document.getElementById('chartAduanas')) {
            new Chart(document.getElementById('chartAduanas'), {
                type: 'bar',
                data: { labels: aduanaLabels, datasets: [{ label: 'Libre', data: aduanaVerdes, backgroundColor: '#198754' }, { label: 'Rojo', data: aduanaRojos, backgroundColor: '#dc3545' }] },
                options: { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true }, y: { stacked: true, display: false } }, plugins: { legend: { display: false } } }
            });
        }
        // Annual Line: Y-Axis Enabled + Custom Tooltip (Blade Version)
        if(document.getElementById('chartAnual')) {
            new Chart(document.getElementById('chartAnual'), {
                type: 'line',
                data: { 
                    labels: anualLabels, 
                    datasets: [{ 
                        label: 'Operaciones', 
                        data: anualValues, 
                        borderColor: '#0d6efd', 
                        backgroundColor: 'rgba(13, 110, 253, 0.1)', 
                        tension: 0.4, 
                        fill: true, 
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }] 
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    scales: { 
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } }, 
                        y: { 
                            display: true, // Enabled
                            grid: { borderDash: [2, 4], color: '#e5e7eb' }, 
                            ticks: { precision: 0 } 
                        } 
                    }, 
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                             mode: 'index', intersect: false, backgroundColor: 'rgba(255, 255, 255, 0.9)', titleColor: '#000', bodyColor: '#000', borderColor: '#e5e7eb', borderWidth: 1,
                             callbacks: {
                                 label: function(context) { return 'Total: ' + context.parsed.y + ' trámites'; }
                             }
                        }
                    } 
                }
            });
        }
         if(document.getElementById('chartDiario')) {
            new Chart(document.getElementById('chartDiario'), {
                type: 'bar',
                data: { labels: diarioLabels, datasets: [{ label: 'Ops', data: diarioValues, backgroundColor: '#0dcaf0', borderRadius: 2 }] },
                options: { responsive: true, maintainAspectRatio: false, scales: { x: { grid: { display: false } }, y: { display: false } }, plugins: { legend: { display: false } } }
            });
        }
    });

    // Fix avatar margin
    document.querySelectorAll('.avatar-group .avatar').forEach((el, index) => {
        if(index === 0) el.style.marginLeft = '0';
    });
</script>
@endsection
