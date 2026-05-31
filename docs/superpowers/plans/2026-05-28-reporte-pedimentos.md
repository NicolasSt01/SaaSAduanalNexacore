# Reporte de Pedimentos Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a new "Reporte de Pedimentos" card to /reportes and a full report page at /reportes/pedimentos with filters, KPIs, data table, detail modal, and PDF export.

**Architecture:** Server-side rendered Blade view following existing report patterns. Controller method queries Expediente model with tenant scoping. PDF generated via DomPDF. Filters via GET form. Modal via inline JavaScript.

**Tech Stack:** Laravel 12, Blade, Tailwind CSS 4, DomPDF, Chart.js (optional), Eloquent ORM

---

### Task 1: Register "pedimentos" report in Tenant model

**Files:**
- Modify: `app/Models/Tenant.php:507-567`

- [ ] **Step 1: Add pedimentos entry to getAllAvailableReports()**

In `app/Models/Tenant.php`, inside the `getAllAvailableReports()` method, add a new entry after `'logistica'`:

```php
'pedimentos' => [
    'name' => 'Reporte de Pedimentos',
    'description' => 'Directorio completo de pedimentos y su estado de cumplimiento',
    'icon' => 'fa-file-invoice',
    'color' => 'blue',
    'status' => 'active',
],
```

Place it right before the closing `];` of the return array (after the `'logistica'` entry).

- [ ] **Step 2: Commit**

```bash
git add app/Models/Tenant.php
git commit -m "feat: register pedimentos report in Tenant available reports"
```

---

### Task 2: Add route for Reporte Pedimentos

**Files:**
- Modify: `routes/web.php` (around line 532, after reportePatronesCliente)

- [ ] **Step 1: Add routes**

In `routes/web.php`, inside the `Route::middleware(['auth'])->group(function () {` block for REPORTES (line 484), after the existing report routes, add:

```php
// --- Reporte de Pedimentos ---
Route::get('/reportes/pedimentos', [ReporteController::class, 'reportePedimentos'])
    ->middleware('report.access:pedimentos')
    ->name('reportes.pedimentos');
Route::get('/reportes/pedimentos/pdf', [ReporteController::class, 'reportePedimentosPdf'])
    ->middleware('report.access:pedimentos')
    ->name('reportes.pedimentos.pdf');
```

- [ ] **Step 2: Commit**

```bash
git add routes/web.php
git commit -m "feat: add routes for reporte pedimentos"
```

---

### Task 3: Add pedimentos to report index routeMap

**Files:**
- Modify: `resources/views/reportes/index.blade.php` (around line 78-87)

- [ ] **Step 1: Add to routeMap**

In `resources/views/reportes/index.blade.php`, find the `$routeMap` array and add:

```php
'pedimentos' => 'reportes.pedimentos',
```

The updated `$routeMap` should look like:

```php
$routeMap = [
    'clientes' => 'reportes.cliente',
    'operacion_semanal' => 'reportes.operacion_semanal',
    'remesas' => 'reportes.remesas',
    'clientes_pdf' => 'reportes.cliente.mail',
    'aduanas' => 'reportes.aduanas',
    'patron_clientes' => 'reportes.patrones-cliente',
    'pedimentos' => 'reportes.pedimentos',
    'financiero' => null,
    'logistica' => null,
];
```

- [ ] **Step 2: Add hover border CSS for blue color**

In the `<style>` section at the bottom of the file (around line 176), check if `.hover\:border-blue-300` already exists. If it does, no action needed. If not, add:

```css
.hover\:border-blue-300:hover {
    border-color: rgb(147 197 253);
}
```

(It already exists at line 178, so skip this step.)

- [ ] **Step 3: Commit**

```bash
git add resources/views/reportes/index.blade.php
git commit -m "feat: add pedimentos to report index routeMap"
```

---

### Task 4: Add controller methods for Reporte Pedimentos

**Files:**
- Modify: `app/Http/Controllers/ReporteController.php` (append at end of class, before closing `}`)

- [ ] **Step 1: Add reportePedimentos() method**

Add this method to `ReporteController.php`:

```php
/**
 * Reporte de Pedimentos - Directorio completo con filtros y KPIs
 */
public function reportePedimentos(Request $request)
{
    $tenantId = auth()->user()->tenant_id;

    // Clientes para el filtro
    $clientes = Cliente::orderBy('nombre')->get();

    // Filtros
    $desde = $request->input('desde');
    $hasta = $request->input('hasta');
    $numeroPedimento = $request->input('numero_pedimento');
    $clienteId = $request->input('cliente_id');
    $estado = $request->input('estado');
    $categoria = $request->input('categoria');

    // Query base
    $query = Expediente::where('tenant_id', $tenantId)
        ->with(['cliente', 'patente', 'aduana', 'operaciones.documentos']);

    // Aplicar filtros
    if ($desde) {
        $query->whereDate('fecha_apertura', '>=', $desde);
    }
    if ($hasta) {
        $query->whereDate('fecha_apertura', '<=', $hasta);
    }
    if ($numeroPedimento) {
        $query->where('numero_pedimento', 'like', '%' . $numeroPedimento . '%');
    }
    if ($clienteId) {
        $query->where('cliente_id', $clienteId);
    }
    if ($estado) {
        $query->where('estado', $estado);
    }
    if ($categoria) {
        $query->where('categoria', $categoria);
    }

    // Paginacion
    $pedimentos = $query->orderByDesc('fecha_apertura')->paginate(15);

    // KPIs
    $totalPedimentos = $pedimentos->total();

    $cumplidos = Expediente::where('tenant_id', $tenantId)
        ->where('estado', 'Cerrado')
        ->when($desde, fn($q) => $q->whereDate('fecha_apertura', '>=', $desde))
        ->when($hasta, fn($q) => $q->whereDate('fecha_apertura', '<=', $hasta))
        ->when($numeroPedimento, fn($q) => $q->where('numero_pedimento', 'like', '%' . $numeroPedimento . '%'))
        ->when($clienteId, fn($q) => $q->where('cliente_id', $clienteId))
        ->when($estado, fn($q) => $q->where('estado', $estado))
        ->when($categoria, fn($q) => $q->where('categoria', $categoria))
        ->count();

    $pendientes = Expediente::where('tenant_id', $tenantId)
        ->whereIn('estado', ['En proceso', 'Abierto'])
        ->when($desde, fn($q) => $q->whereDate('fecha_apertura', '>=', $desde))
        ->when($hasta, fn($q) => $q->whereDate('fecha_apertura', '<=', $hasta))
        ->when($numeroPedimento, fn($q) => $q->where('numero_pedimento', 'like', '%' . $numeroPedimento . '%'))
        ->when($clienteId, fn($q) => $q->where('cliente_id', $clienteId))
        ->when($estado, fn($q) => $q->where('estado', $estado))
        ->when($categoria, fn($q) => $q->where('categoria', $categoria))
        ->count();

    // Pedimentos con documentos faltantes (en la pagina actual)
    $docsFaltantes = 0;
    foreach ($pedimentos->items() as $pedimento) {
        if (!$pedimento->cumplimiento_completo) {
            $docsFaltantes++;
        }
    }

    return view('reportes.reporte-pedimentos', compact(
        'pedimentos',
        'clientes',
        'totalPedimentos',
        'cumplidos',
        'pendientes',
        'docsFaltantes',
        'desde',
        'hasta',
        'numeroPedimento',
        'clienteId',
        'estado',
        'categoria',
    ));
}
```

- [ ] **Step 2: Add reportePedimentosPdf() method**

Add this method to `ReporteController.php`:

```php
/**
 * PDF del Reporte de Pedimentos
 */
public function reportePedimentosPdf(Request $request)
{
    $tenantId = auth()->user()->tenant_id;

    // Filtros (mismos que reportePedimentos)
    $desde = $request->input('desde');
    $hasta = $request->input('hasta');
    $numeroPedimento = $request->input('numero_pedimento');
    $clienteId = $request->input('cliente_id');
    $estado = $request->input('estado');
    $categoria = $request->input('categoria');

    // Query base - sin paginacion para PDF
    $query = Expediente::where('tenant_id', $tenantId)
        ->with(['cliente', 'patente', 'aduana']);

    if ($desde) {
        $query->whereDate('fecha_apertura', '>=', $desde);
    }
    if ($hasta) {
        $query->whereDate('fecha_apertura', '<=', $hasta);
    }
    if ($numeroPedimento) {
        $query->where('numero_pedimento', 'like', '%' . $numeroPedimento . '%');
    }
    if ($clienteId) {
        $query->where('cliente_id', $clienteId);
    }
    if ($estado) {
        $query->where('estado', $estado);
    }
    if ($categoria) {
        $query->where('categoria', $categoria);
    }

    $pedimentos = $query->orderByDesc('fecha_apertura')->get();

    // KPIs
    $totalPedimentos = $pedimentos->count();
    $cumplidos = $pedimentos->where('estado', 'Cerrado')->count();
    $pendientes = $pedimentos->whereIn('estado', ['En proceso', 'Abierto'])->count();
    $docsFaltantes = $pedimentos->filter(fn($p) => !$p->cumplimiento_completo)->count();

    $datos = [
        'pedimentos' => $pedimentos->map(fn($p) => [
            'numero_pedimento' => $p->numero_pedimento,
            'cliente' => $p->cliente?->nombre ?? 'N/D',
            'categoria' => $p->categoria,
            'estado' => $p->estado,
            'fecha_apertura' => $p->fecha_apertura?->format('d/m/Y') ?? 'N/D',
            'cumplimiento_completo' => $p->cumplimiento_completo,
            'documentos_pendientes' => $p->documentos_pendientes,
        ])->toArray(),
        'kpis' => [
            'total' => $totalPedimentos,
            'cumplidos' => $cumplidos,
            'pendientes' => $pendientes,
            'docs_faltantes' => $docsFaltantes,
        ],
        'filtros' => [
            'desde' => $desde,
            'hasta' => $hasta,
            'numero_pedimento' => $numeroPedimento,
            'estado' => $estado,
            'categoria' => $categoria,
        ],
    ];

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.pdf-pedimentos', compact('datos'));
    $pdf->setPaper('letter', 'portrait');
    return $pdf->download('reporte_pedimentos_' . now()->format('Y_m_d') . '.pdf');
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/ReporteController.php
git commit -m "feat: add reportePedimentos and reportePedimentosPdf controller methods"
```

---

### Task 5: Create the main view reporte-pedimentos.blade.php

**Files:**
- Create: `resources/views/reportes/reporte-pedimentos.blade.php`

- [ ] **Step 1: Create the view file**

Create `resources/views/reportes/reporte-pedimentos.blade.php` with the following content:

```blade
@extends('layouts.app')

@section('title', 'Reporte de Pedimentos')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('reportes.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                            <i class="fas fa-chart-bar mr-2"></i> Reportes
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                            <span class="text-sm font-medium text-gray-700">Pedimentos</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Reporte de <span class="text-blue-600">Pedimentos</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Directorio completo de pedimentos y su estado de cumplimiento.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors" id="toggleFilters">
                <i class="fas fa-filter mr-2"></i> Mostrar Filtros
            </button>
            <a href="{{ route('reportes.pedimentos.pdf', request()->query()) }}" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-bold text-red-600 shadow-sm hover:bg-red-100 transition-colors">
                <i class="fas fa-file-pdf mr-2"></i> Exportar PDF
            </a>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-bold text-blue-500 mb-1 uppercase tracking-wider">Total Pedimentos</p>
                <h3 class="text-3xl font-black text-gray-900">{{ $totalPedimentos }}</h3>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 text-2xl">
                <i class="fas fa-file-invoice"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-green-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-bold text-green-500 mb-1 uppercase tracking-wider">Cumplidos</p>
                <h3 class="text-3xl font-black text-gray-900">{{ $cumplidos }}</h3>
            </div>
            <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center text-green-500 text-2xl">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-amber-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-bold text-amber-500 mb-1 uppercase tracking-wider">Pendientes por Cerrar</p>
                <h3 class="text-3xl font-black text-gray-900">{{ $pendientes }}</h3>
            </div>
            <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-500 text-2xl">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-bold text-red-500 mb-1 uppercase tracking-wider">Docs Faltantes</p>
                <h3 class="text-3xl font-black text-gray-900">{{ $docsFaltantes }}</h3>
            </div>
            <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center text-red-500 text-2xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 mb-6 transition-all duration-300 origin-top overflow-hidden" id="filterCard" style="display:none; max-height: 0px; opacity: 0;">
        <div class="p-6 bg-blue-50/30">
            <h4 class="text-blue-800 font-bold mb-4 flex items-center grid-span-full"><i class="fas fa-filter mr-2"></i> Filtros de Búsqueda</h4>
            <form method="GET" action="{{ route('reportes.pedimentos') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label for="numero_pedimento" class="block text-xs font-bold text-gray-700 uppercase mb-1">Pedimento</label>
                        <input type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white border" id="numero_pedimento" name="numero_pedimento" value="{{ $numeroPedimento ?? '' }}" placeholder="Ej. 1234567">
                    </div>

                    <div>
                        <label for="estado" class="block text-xs font-bold text-gray-700 uppercase mb-1">Estado</label>
                        <select class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white border" id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="En proceso" @if(($estado ?? '') == 'En proceso') selected @endif>En proceso</option>
                            <option value="Abierto" @if(($estado ?? '') == 'Abierto') selected @endif>Abierto</option>
                            <option value="Cerrado" @if(($estado ?? '') == 'Cerrado') selected @endif>Cerrado</option>
                            <option value="Cancelado" @if(($estado ?? '') == 'Cancelado') selected @endif>Cancelado</option>
                        </select>
                    </div>

                    <div>
                        <label for="categoria" class="block text-xs font-bold text-gray-700 uppercase mb-1">Categoría</label>
                        <select class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white border" id="categoria" name="categoria">
                            <option value="">Todas</option>
                            <option value="Importacion" @if(($categoria ?? '') == 'Importacion') selected @endif>Importación</option>
                            <option value="Exportacion" @if(($categoria ?? '') == 'Exportacion') selected @endif>Exportación</option>
                            <option value="Rectificaciones" @if(($categoria ?? '') == 'Rectificaciones') selected @endif>Rectificaciones</option>
                        </select>
                    </div>

                    <div>
                        <label for="cliente_id" class="block text-xs font-bold text-gray-700 uppercase mb-1">Cliente</label>
                        <select class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white border" id="cliente_id" name="cliente_id">
                            <option value="">Todos</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" @if(($clienteId ?? '') == $cliente->id) selected @endif>
                                    {{ $cliente->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="fecha_desde" class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha Desde</label>
                        <input type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white border text-gray-600 cursor-pointer" id="fecha_desde" name="desde" value="{{ $desde ?? '' }}">
                    </div>

                    <div>
                        <label for="fecha_hasta" class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha Hasta</label>
                        <input type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white border text-gray-600 cursor-pointer" id="fecha_hasta" name="hasta" value="{{ $hasta ?? '' }}">
                    </div>

                    <div class="col-span-1 md:col-span-2 flex items-end justify-end gap-2 mt-2 lg:mt-0">
                        <a href="{{ route('reportes.pedimentos') }}" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none transition-colors">
                            Limpiar
                        </a>
                        <button type="submit" class="inline-flex justify-center rounded-lg border border-transparent bg-blue-600 px-6 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-search mr-2"></i> Aplicar Filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('partials.alerts')

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex-1">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Pedimento</th>
                        <th class="px-6 py-4">Cliente</th>
                        <th class="px-6 py-4">Categoría</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-center">Docs Faltantes</th>
                        <th class="px-6 py-4">Fecha Apertura</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                @forelse($pedimentos as $pedimento)
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-black text-blue-600">#{{ $pedimento->numero_pedimento }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $pedimento->cliente?->nombre ?? 'N/D' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200">
                                {{ $pedimento->categoria === 'Importacion' ? 'Importación' : ($pedimento->categoria === 'Exportacion' ? 'Exportación' : $pedimento->categoria) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($pedimento->estado == 'Cerrado')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></div> Cerrado
                                </span>
                            @elseif($pedimento->estado == 'Cancelado')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></div> Cancelado
                                </span>
                            @elseif($pedimento->estado == 'Abierto')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-sky-500 mr-1.5"></div> Abierto
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></div> En proceso
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($pedimento->cumplimiento_completo)
                                <div class="flex flex-col items-center gap-1 text-emerald-600 font-bold">
                                    <i class="fas fa-check-circle text-lg"></i>
                                    <span class="text-[9px] uppercase tracking-tighter">Completo</span>
                                </div>
                            @else
                                @php $pendientes = $pedimento->documentos_pendientes; @endphp
                                <div class="flex flex-col items-center gap-1 text-amber-500 font-bold group relative cursor-help">
                                    <i class="fas fa-exclamation-circle text-lg animate-pulse"></i>
                                    <span class="text-[9px] uppercase tracking-tighter">{{ count($pendientes) }} pendientes</span>

                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 hidden group-hover:block w-56 p-3 bg-gray-900/95 backdrop-blur-sm text-white text-[10px] rounded-2xl shadow-2xl z-50 border border-gray-700">
                                        <div class="flex items-center gap-2 font-black border-b border-gray-700 pb-2 mb-2 text-amber-400 uppercase tracking-widest text-[9px]">
                                            <i class="fas fa-clipboard-list"></i> Documentos faltantes
                                        </div>
                                        <ul class="space-y-1.5 font-medium">
                                            @foreach(array_slice($pendientes, 0, 8) as $doc)
                                                <li class="flex items-center gap-2">
                                                    <div class="w-1 h-1 rounded-full bg-amber-500"></div>
                                                    <span class="truncate">{{ $doc }}</span>
                                                </li>
                                            @endforeach
                                            @if(count($pendientes) > 8)
                                                <li class="pl-3 text-gray-400 italic">Y {{ count($pendientes) - 8 }} más...</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $pedimento->fecha_apertura?->format('d/m/Y') ?? 'N/D' }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <button onclick="openPedimentoModal({{ $pedimento->id }}, '{{ addslashes($pedimento->numero_pedimento) }}', '{{ addslashes($pedimento->cliente?->nombre ?? 'N/D') }}', '{{ addslashes($pedimento->patente?->numero ?? 'N/D') }}', '{{ addslashes($pedimento->aduana?->nombre ?? 'N/D') }}', '{{ $pedimento->categoria }}', '{{ $pedimento->estado }}', '{{ $pedimento->fecha_apertura?->format('d/m/Y') ?? 'N/D' }}', '{{ $pedimento->fecha_cierre?->format('d/m/Y') ?? 'N/D' }}', {{ $pedimento->cumplimiento_completo ? 'true' : 'false' }})" class="text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white border border-blue-200 h-8 w-8 flex items-center justify-center rounded-lg shadow-sm transition transform hover:scale-105" title="Ver detalle">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                                <a href="{{ route('expedientes.show', $pedimento) }}" class="text-purple-600 bg-purple-50 hover:bg-purple-600 hover:text-white border border-purple-200 h-8 w-8 flex items-center justify-center rounded-lg shadow-sm transition transform hover:scale-105" title="Ir a expediente">
                                    <i class="fas fa-external-link-alt text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-file-invoice text-5xl mb-3 block opacity-50 text-blue-300"></i>
                            <h3 class="font-black text-xl text-gray-700 mb-1">Sin Pedimentos</h3>
                            <p class="font-medium text-gray-500">No hay pedimentos registrados o tu búsqueda no arrojó resultados.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($pedimentos->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $pedimentos->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal de Detalle --}}
<div id="pedimentoModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="closePedimentoModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
            <div class="flex justify-between items-center mb-5 border-b pb-4">
                <h3 class="text-xl font-bold leading-6 text-gray-900" id="modal-title">
                    <i class="fas fa-file-invoice text-blue-600 mr-2"></i> Detalle del Pedimento
                </h3>
                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" onclick="closePedimentoModal()">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Pedimento</p>
                        <p class="text-lg font-black text-blue-600" id="modal-numero"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Cliente</p>
                        <p class="text-sm font-bold text-gray-900" id="modal-cliente"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Patente</p>
                        <p class="text-sm font-bold text-gray-900" id="modal-patente"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Aduana</p>
                        <p class="text-sm font-bold text-gray-900" id="modal-aduana"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Categoría</p>
                        <p class="text-sm font-bold text-gray-900" id="modal-categoria"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Estado</p>
                        <p class="text-sm font-bold" id="modal-estado"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Fecha Apertura</p>
                        <p class="text-sm font-bold text-gray-900" id="modal-apertura"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Fecha Cierre</p>
                        <p class="text-sm font-bold text-gray-900" id="modal-cierre"></p>
                    </div>
                </div>

                <div id="modal-docs-section" class="border-t pt-4">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-2">Documentos Faltantes</p>
                    <ul id="modal-docs-list" class="space-y-1"></ul>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button onclick="closePedimentoModal()" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                        Cerrar
                    </button>
                    <a id="modal-expediente-link" href="#" class="inline-flex justify-center rounded-lg border border-transparent bg-purple-600 px-6 py-2 text-sm font-bold text-white shadow-sm hover:bg-purple-700 transition-colors">
                        <i class="fas fa-external-link-alt mr-2"></i> Ir a Expediente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.tailwindcss.com"></script>
<script>
    // Toggle filters
    document.addEventListener('DOMContentLoaded', function () {
        const toggleFiltersBtn = document.getElementById('toggleFilters');
        const filterCard = document.getElementById('filterCard');

        toggleFiltersBtn.addEventListener('click', function () {
            if (filterCard.style.display === 'none') {
                filterCard.style.display = 'block';
                void filterCard.offsetWidth;
                filterCard.style.maxHeight = '500px';
                filterCard.style.opacity = '1';
                toggleFiltersBtn.innerHTML = '<i class="fas fa-times mr-2"></i> Ocultar Filtros';
                toggleFiltersBtn.classList.add('bg-gray-100');
            } else {
                filterCard.style.maxHeight = '0px';
                filterCard.style.opacity = '0';
                setTimeout(() => {
                    filterCard.style.display = 'none';
                }, 300);
                toggleFiltersBtn.innerHTML = '<i class="fas fa-filter mr-2"></i> Mostrar Filtros';
                toggleFiltersBtn.classList.remove('bg-gray-100');
            }
        });
    });

    // Modal functions
    function openPedimentoModal(id, numero, cliente, patente, aduana, categoria, estado, apertura, cierre, completo) {
        document.getElementById('modal-numero').textContent = '#' + numero;
        document.getElementById('modal-cliente').textContent = cliente;
        document.getElementById('modal-patente').textContent = patente;
        document.getElementById('modal-aduana').textContent = aduana;

        const catLabels = {'Importacion': 'Importación', 'Exportacion': 'Exportación'};
        document.getElementById('modal-categoria').textContent = catLabels[categoria] || categoria;

        const estadoEl = document.getElementById('modal-estado');
        const estadoColors = {
            'Cerrado': 'text-green-700',
            'Cancelado': 'text-red-700',
            'Abierto': 'text-sky-700',
            'En proceso': 'text-amber-700'
        };
        estadoEl.textContent = estado;
        estadoEl.className = 'text-sm font-bold ' + (estadoColors[estado] || 'text-gray-700');

        document.getElementById('modal-apertura').textContent = apertura;
        document.getElementById('modal-cierre').textContent = cierre !== 'N/D' ? cierre : 'Sin cerrar';

        const docsSection = document.getElementById('modal-docs-section');
        const docsList = document.getElementById('modal-docs-list');
        docsList.innerHTML = '';

        if (completo) {
            docsSection.style.display = 'none';
        } else {
            docsSection.style.display = 'block';
            // Fetch docs via AJAX
            fetch('/expedientes/' + id + '/documentos-pendientes')
                .then(r => r.json())
                .then(docs => {
                    docs.forEach(doc => {
                        const li = document.createElement('li');
                        li.className = 'flex items-center gap-2 text-sm text-amber-700';
                        li.innerHTML = '<i class="fas fa-exclamation-circle text-amber-500"></i> ' + doc;
                        docsList.appendChild(li);
                    });
                })
                .catch(() => {
                    docsList.innerHTML = '<li class="text-sm text-gray-500">No se pudieron cargar los documentos pendientes</li>';
                });
        }

        document.getElementById('modal-expediente-link').href = '/expedientes/' + id;
        document.getElementById('pedimentoModal').classList.remove('hidden');
    }

    function closePedimentoModal() {
        document.getElementById('pedimentoModal').classList.add('hidden');
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePedimentoModal();
    });
</script>
@endsection
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/reportes/reporte-pedimentos.blade.php
git commit -m "feat: create reporte-pedimentos view with KPIs, filters, table, and modal"
```

---

### Task 6: Create the PDF view pdf-pedimentos.blade.php

**Files:**
- Create: `resources/views/reportes/pdf-pedimentos.blade.php`

- [ ] **Step 1: Create the PDF view**

Create `resources/views/reportes/pdf-pedimentos.blade.php`:

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Pedimentos</title>
    <style>
        @page { margin: 15mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #1a1a2e; }

        .header-border { border-bottom: 2px solid #2563eb; padding-bottom: 8px; margin-bottom: 12px; }
        .company-name { font-size: 12pt; font-weight: bold; color: #2563eb; }
        .report-type { font-size: 9pt; color: #555; }

        h2 { font-size: 10pt; font-weight: bold; color: #2563eb; border-bottom: 1px solid #d0d7e2; padding-bottom: 3px; margin-top: 16px; margin-bottom: 8px; }

        table.stats { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.stats td { width: 25%; padding: 6px; border: 1px solid #d0d7e2; background: #f8f9fb; text-align: center; vertical-align: middle; }
        .stat-num { font-size: 16pt; font-weight: bold; }
        .stat-lbl { font-size: 6.5pt; color: #555; text-transform: uppercase; }
        .blue { color: #2563eb; } .green { color: #16a34a; } .amber { color: #d97706; } .red { color: #dc2626; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 7.5pt; }
        table.data th { background: #2563eb; color: #fff; padding: 4px 5px; text-align: left; font-size: 7pt; }
        table.data td { padding: 3px 5px; border-bottom: 1px solid #e8ecf1; }
        table.data tr:nth-child(even) td { background: #f8f9fb; }
        .center { text-align: center; }

        .badge { padding: 1px 5px; border-radius: 4px; font-size: 6.5pt; font-weight: bold; }
        .bg-green { background: #dcfce7; color: #16a34a; }
        .bg-amber { background: #fef3c7; color: #d97706; }
        .bg-red { background: #fee2e2; color: #dc2626; }
        .bg-sky { background: #e0f2fe; color: #0284c7; }

        .footer { margin-top: 16px; border-top: 1px solid #d0d7e2; padding-top: 6px; text-align: center; font-size: 6.5pt; color: #999; }

        .filtros { font-size: 7pt; color: #777; margin-bottom: 8px; }
    </style>
</head>
<body>

<div class="header-border">
    <table style="width:100%;border:none;"><tr>
        <td style="width:60px;border:none;vertical-align:middle;">
            <div style="width:50px;height:50px;border:2px solid #2563eb;text-align:center;font-size:22pt;font-weight:bold;color:#2563eb;line-height:50px;">N</div>
        </td>
        <td style="border:none;vertical-align:middle;">
            <div class="company-name">NexaCore Aduanal</div>
            <div class="report-type">Reporte de Pedimentos</div>
            <div class="filtros">
                @if($datos['filtros']['desde'] || $datos['filtros']['hasta'])
                    <b>Periodo:</b> {{ $datos['filtros']['desde'] ? \Carbon\Carbon::parse($datos['filtros']['desde'])->format('d/m/Y') : 'Inicio' }} — {{ $datos['filtros']['hasta'] ? \Carbon\Carbon::parse($datos['filtros']['hasta'])->format('d/m/Y') : 'Actual' }}
                @endif
                @if($datos['filtros']['estado']) | <b>Estado:</b> {{ $datos['filtros']['estado'] }} @endif
                @if($datos['filtros']['categoria']) | <b>Categoría:</b> {{ $datos['filtros']['categoria'] }} @endif
                @if($datos['filtros']['numero_pedimento']) | <b>Pedimento:</b> {{ $datos['filtros']['numero_pedimento'] }} @endif
                <br><b>Generado:</b> {{ now()->format('d/m/Y H:i') }}
            </div>
        </td>
    </tr></table>
</div>

<h2>Resumen</h2>
<table class="stats">
    <tr>
        <td><span class="stat-num blue">{{ $datos['kpis']['total'] }}</span><br><span class="stat-lbl">Total Pedimentos</span></td>
        <td><span class="stat-num green">{{ $datos['kpis']['cumplidos'] }}</span><br><span class="stat-lbl">Cumplidos</span></td>
        <td><span class="stat-num amber">{{ $datos['kpis']['pendientes'] }}</span><br><span class="stat-lbl">Pendientes</span></td>
        <td><span class="stat-num red">{{ $datos['kpis']['docs_faltantes'] }}</span><br><span class="stat-lbl">Docs Faltantes</span></td>
    </tr>
</table>

<h2>Detalle de Pedimentos</h2>
<table class="data">
    <thead>
        <tr>
            <th style="width:6%;">#</th>
            <th style="width:14%;">Pedimento</th>
            <th style="width:18%;">Cliente</th>
            <th style="width:10%;">Categoría</th>
            <th style="width:10%;">Estado</th>
            <th style="width:12%;">Fecha Apertura</th>
            <th style="width:30%;">Docs Faltantes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($datos['pedimentos'] as $p)
        @php
            $catLabel = $p['categoria'] === 'Importacion' ? 'Importación' : ($p['categoria'] === 'Exportacion' ? 'Exportación' : $p['categoria']);
            $estadoBadge = match($p['estado']) {
                'Cerrado' => '<span class="badge bg-green">Cerrado</span>',
                'Cancelado' => '<span class="badge bg-red">Cancelado</span>',
                'Abierto' => '<span class="badge bg-sky">Abierto</span>',
                default => '<span class="badge bg-amber">En proceso</span>',
            };
            $docsText = $p['cumplimiento_completo'] ? '<span class="badge bg-green">Completo</span>' : implode(', ', array_slice($p['documentos_pendientes'], 0, 3)) . (count($p['documentos_pendientes']) > 3 ? '...' : '');
        @endphp
        <tr>
            <td class="center" style="font-weight:bold;color:#2563eb;">{{ $loop->iteration }}</td>
            <td style="font-weight:bold;">{{ $p['numero_pedimento'] }}</td>
            <td>{{ $p['cliente'] }}</td>
            <td>{{ $catLabel }}</td>
            <td class="center">{!! $estadoBadge !!}</td>
            <td>{{ $p['fecha_apertura'] }}</td>
            <td>{!! $docsText !!}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">NexaCore Aduanal — Reporte generado el {{ now()->format('d/m/Y H:i') }} — Confidencial</div>

</body>
</html>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/reportes/pdf-pedimentos.blade.php
git commit -m "feat: create pdf-pedimentos view for PDF export"
```

---

### Task 7: Add API endpoint for pending documents

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/ExpedienteController.php`

- [ ] **Step 1: Add route**

In `routes/web.php`, inside the expedientes routes block (around line 262), add:

```php
Route::get('/expedientes/{expediente}/documentos-pendientes', [ExpedienteController::class, 'documentosPendientes'])
    ->name('expedientes.documentos-pendientes');
```

- [ ] **Step 2: Add controller method**

In `app/Http/Controllers/ExpedienteController.php`, add:

```php
/**
 * Retorna los documentos pendientes de un expediente como JSON
 */
public function documentosPendientes(Expediente $expediente)
{
    // Verificar tenant
    if ($expediente->tenant_id !== auth()->user()->tenant_id) {
        abort(403);
    }

    return response()->json($expediente->documentos_pendientes);
}
```

- [ ] **Step 3: Commit**

```bash
git add routes/web.php app/Http/Controllers/ExpedienteController.php
git commit -m "feat: add endpoint for expediente documentos pendientes"
```

---

### Task 8: Self-review and verification

- [ ] **Step 1: Verify all spec requirements are covered**

| Spec Requirement | Task |
|-----------------|------|
| Register report in Tenant | Task 1 |
| Add route | Task 2 |
| Add to routeMap | Task 3 |
| Controller method with filters | Task 4 |
| View with KPIs (Total, Cumplidos, Pendientes, Docs Faltantes) | Task 5 |
| Filters (fecha, pedimento, cliente, estado, categoría) | Task 5 |
| Table with docs faltantes tooltip | Task 5 |
| Modal with detail + link to expediente | Task 5 |
| PDF export of full table | Task 6 |
| API endpoint for pending docs | Task 7 |

- [ ] **Step 2: Placeholder scan** — No placeholders found. All code is complete.

- [ ] **Step 3: Type/signature consistency** — All methods use `Request $request`, all views use consistent variable names, all routes follow existing naming patterns.
