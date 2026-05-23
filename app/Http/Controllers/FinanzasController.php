<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Operacion;
use App\Models\Cliente;
use App\Models\Patente;
use App\Models\Expediente;
use App\Models\Factura;
use App\Models\Documento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Log;

class FinanzasController extends Controller
{
    //
    /**
     * NIVEL 1: Vista principal - Resumen por Cliente-Patente
     */
    public function indexOriginal(Request $request)
    {
        // Valores por defecto
        $yearActual = $request->input('year', now()->year);
        $semanaActual = $request->input('semana', now()->weekOfYear);
        $clienteBusqueda = $request->input('cliente', '');
        $estadoFiltro = $request->input('estado', ''); // Nuevo filtro

        // Calcular fechas de inicio y fin de la semana seleccionada
        $fechaInicio = Carbon::now()
            ->setISODate($yearActual, $semanaActual)
            ->startOfWeek(Carbon::MONDAY);

        $fechaFin = Carbon::now()
            ->setISODate($yearActual, $semanaActual)
            ->endOfWeek(Carbon::SUNDAY);

        // Query base: operaciones de la semana --Agregamos los conceptos adicionales 10/13/2025
        $query = Operacion::with(['cliente', 'patente', 'expediente', 'conceptosAdicionales'])
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin]);

        // Filtro por cliente si se proporciona
        if (!empty($clienteBusqueda)) {
            $query->whereHas('cliente', function ($q) use ($clienteBusqueda) {
                $q->where('nombre_empresa', 'like', '%' . $clienteBusqueda . '%');
            });
        }

        // Obtener todas las operaciones de la semana
        $operaciones = $query->get();

        // Agrupar por Cliente + Patente
        $resumen = $operaciones->groupBy(function ($item) {
            return $item->cliente_id . '-' . $item->patente_id;
        })->map(function ($group) use ($fechaInicio, $fechaFin) {
            $firstItem = $group->first();
            /*->map(function ($group) use ($yearActual, $semanaActual) {
            $firstItem = $group->first();*/

            // Contar rojos
            $rojos = $group->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count();
            $sobrepesos = $group->where('sobrepeso', true)->count();

            // Obtener expedientes únicos de este grupo
            $expedientes = $group->pluck('pedimento_id')->unique()->filter();

            // Obtener las fechas de apertura y cierre de los expedientes
            $expedientesInfo = Expediente::whereIn('id', $expedientes)
                ->select('fecha_apertura', 'fecha_cierre')
                ->get();

            $fechaApertura = $expedientesInfo->min('fecha_apertura');
            $fechaCierre = $expedientesInfo->max('fecha_cierre');

            // Contar cuántos expedientes ya tienen factura
            $expedientesFacturados = Factura::whereIn('pedimento_id', $expedientes)
                ->where('year', now()->year)
                ->where('semana', now()->weekOfYear)
                ->distinct('pedimento_id')
                ->count('pedimento_id');

            // Calcular adicionales (suma de montos adicionales de facturas)
            $adicionales = Factura::whereIn('pedimento_id', $expedientes)
                ->where('year', now()->year)
                ->where('semana', now()->weekOfYear)
                ->sum('monto_adicionales');

            return [
                'cliente_id' => $firstItem->cliente_id,
                'cliente_nombre' => $firstItem->cliente->nombre_empresa ?? 'Sin Cliente',
                'patente_id' => $firstItem->patente_id,
                'patente_numero' => $firstItem->patente->numero_patente ?? 'Sin Patente',
                'total_tramites' => $group->count(),
                'rojos' => $rojos,
                'sobrepesos' => $sobrepesos,
                'adicionales' => $adicionales,
                'fecha_apertura' => $fechaApertura,
                'fecha_cierre' => $fechaCierre,
                'expedientes_count' => $expedientes->count(),
                'expedientes_facturados' => $expedientesFacturados,
            ];
        })->values();//->sortByDesc('total_tramites');

        // Filtrar por estado si se solicita
        if ($estadoFiltro === 'pendiente') {
            $resumen = $resumen->filter(function ($item) {
                return $item['expedientes_facturados'] < $item['expedientes_count'];
            })->values();
        } elseif ($estadoFiltro === 'completado') {
            $resumen = $resumen->filter(function ($item) {
                return $item['expedientes_facturados'] >= $item['expedientes_count'];
            })->values();
        }

        // Ordenar: pendientes primero, luego completados
        $resumen = $resumen->sortBy(function ($item) {
            return $item['expedientes_facturados'] >= $item['expedientes_count'] ? 1 : 0;
        })->values();
        // Lista de años disponibles (últimos 3 años + año actual + próximo)
        $years = range(now()->year - 3, now()->year + 1);

        return view('finanzas.index', compact(
            'resumen',
            'yearActual',
            'semanaActual',
            'fechaInicio',
            'fechaFin',
            'years',
            'clienteBusqueda'
        ));
    }

    public function index_old(Request $request)
    {
        // Valores por defecto
        $yearActual = $request->input('year', now()->year);
        $semanaActual = $request->input('semana', now()->weekOfYear);
        $clienteBusqueda = $request->input('cliente', '');
        $estadoFiltro = $request->input('estado', '');

        // Calcular fechas de inicio y fin de la semana seleccionada
        $fechaInicio = Carbon::now()
            ->setISODate($yearActual, $semanaActual)
            ->startOfWeek(Carbon::MONDAY);

        $fechaFin = Carbon::now()
            ->setISODate($yearActual, $semanaActual)
            ->endOfWeek(Carbon::SUNDAY);

        // Query base: operaciones de la semana CON conceptos adicionales
        $query = Operacion::with(['cliente', 'patente', 'expediente', 'conceptosAdicionales'])
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin]);

        // Filtro por cliente si se proporciona
        if (!empty($clienteBusqueda)) {
            $query->whereHas('cliente', function ($q) use ($clienteBusqueda) {
                $q->where('nombre_empresa', 'like', '%' . $clienteBusqueda . '%');
            });
        }

        // Obtener todas las operaciones de la semana
        $operaciones = $query->get();

        // Agrupar por Cliente + Patente
        $resumen = $operaciones->groupBy(function ($item) {
            return $item->cliente_id . '-' . $item->patente_id;
        })->map(function ($group) use ($fechaInicio, $fechaFin) {
            $firstItem = $group->first();

            // Contar rojos
            $rojos = $group->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count();
            $sobrepesos = $group->where('sobrepeso', true)->count();

            // Obtener expedientes únicos de este grupo
            $expedientes = $group->pluck('pedimento_id')->unique()->filter();

            // 🔹 CALCULAR CONCEPTOS ADICIONALES
            // Agrupar operaciones por camión para no duplicar conceptos de ámbito "camion"
            $camiones = $group->groupBy(function ($op) {
                return $op->fecha . '_' . $op->num_thermo . '_' . $op->codigo_alpha;
            });

            $totalAdicionales = 0;
            $detalleAdicionales = [];

            foreach ($camiones as $operacionesCamion) {
                // Para conceptos de camión, solo tomamos la primera operación
                $conceptosCamion = $operacionesCamion->first()->conceptosAdicionales
                    ->where('ambito', 'camion');

                foreach ($conceptosCamion as $concepto) {
                    $totalAdicionales += $concepto->monto;

                    $tipo = $concepto->tipo_concepto;
                    if (!isset($detalleAdicionales[$tipo])) {
                        $detalleAdicionales[$tipo] = [
                            'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                            'cantidad' => 0,
                            'monto' => 0
                        ];
                    }
                    $detalleAdicionales[$tipo]['cantidad']++;
                    $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
                }
            }

            // Para conceptos por operación, contamos todos
            foreach ($group as $operacion) {
                $conceptosOperacion = $operacion->conceptosAdicionales
                    ->where('ambito', 'operacion');

                foreach ($conceptosOperacion as $concepto) {
                    $totalAdicionales += $concepto->monto;

                    $tipo = $concepto->tipo_concepto;
                    if (!isset($detalleAdicionales[$tipo])) {
                        $detalleAdicionales[$tipo] = [
                            'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                            'cantidad' => 0,
                            'monto' => 0
                        ];
                    }
                    $detalleAdicionales[$tipo]['cantidad']++;
                    $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
                }
            }

            // Obtener las fechas de apertura y cierre de los expedientes
            $expedientesInfo = Expediente::whereIn('id', $expedientes)
                ->select('fecha_apertura', 'fecha_cierre')
                ->get();

            $fechaApertura = $expedientesInfo->min('fecha_apertura');
            $fechaCierre = $expedientesInfo->max('fecha_cierre');

            // Contar cuántos expedientes ya tienen factura
            $expedientesFacturados = Factura::whereIn('pedimento_id', $expedientes)
                ->where('year', now()->year)
                ->where('semana', now()->weekOfYear)
                ->distinct('pedimento_id')
                ->count('pedimento_id');

            // Calcular adicionales de facturas existentes (mantener para compatibilidad)
            $adicionalesFacturas = Factura::whereIn('pedimento_id', $expedientes)
                ->where('year', now()->year)
                ->where('semana', now()->weekOfYear)
                ->sum('monto_adicionales');

            return [
                'cliente_id' => $firstItem->cliente_id,
                'cliente_nombre' => $firstItem->cliente->nombre_empresa ?? 'Sin Cliente',
                'patente_id' => $firstItem->patente_id,
                'patente_numero' => $firstItem->patente->numero_patente ?? 'Sin Patente',
                'total_tramites' => $group->count(),
                'rojos' => $rojos,
                'sobrepesos' => $sobrepesos,
                'adicionales' => $totalAdicionales, // 🔹 Total de conceptos adicionales
                'detalle_adicionales' => $detalleAdicionales, // 🔹 Desglose por tipo
                'adicionales_facturas' => $adicionalesFacturas, // Mantener para referencia
                'fecha_apertura' => $fechaApertura,
                'fecha_cierre' => $fechaCierre,
                'expedientes_count' => $expedientes->count(),
                'expedientes_facturados' => $expedientesFacturados,
            ];
        })->values();

        // Filtrar por estado si se solicita
        if ($estadoFiltro === 'pendiente') {
            $resumen = $resumen->filter(function ($item) {
                return $item['expedientes_facturados'] < $item['expedientes_count'];
            })->values();
        } elseif ($estadoFiltro === 'completado') {
            $resumen = $resumen->filter(function ($item) {
                return $item['expedientes_facturados'] >= $item['expedientes_count'];
            })->values();
        }

        // Ordenar: pendientes primero, luego completados
        $resumen = $resumen->sortBy(function ($item) {
            return $item['expedientes_facturados'] >= $item['expedientes_count'] ? 1 : 0;
        })->values();

        // Lista de años disponibles
        $years = range(now()->year - 3, now()->year + 1);

        return view('finanzas.index', compact(
            'resumen',
            'yearActual',
            'semanaActual',
            'fechaInicio',
            'fechaFin',
            'years',
            'clienteBusqueda'
        ));
    }
    public function index_old2(Request $request)
    {
        try {
            Log::info('🔵 INICIO - FinanzasController@index');
            Log::info('👤 Usuario autenticado:', [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
                'role' => auth()->user()->role,
                ]);
            Log::info('📊 Cargando datos de finanzas...');
            // Valores por defecto
            $yearActual = $request->input('year', now()->year);
            $semanaActual = $request->input('semana', now()->weekOfYear);
            $clienteBusqueda = $request->input('cliente', '');
            $estadoFiltro = $request->input('estado', '');

            // Calcular fechas de inicio y fin de la semana seleccionada
            $fechaInicio = Carbon::now()
                ->setISODate($yearActual, $semanaActual)
                ->startOfWeek(Carbon::MONDAY);

            $fechaFin = Carbon::now()
                ->setISODate($yearActual, $semanaActual)
                ->endOfWeek(Carbon::SUNDAY);

            // Query base: operaciones de la semana CON conceptos adicionales
            $query = Operacion::with(['cliente', 'patente', 'expediente', 'conceptosAdicionales'])
                ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin]);

            // Filtro por cliente si se proporciona
            if (!empty($clienteBusqueda)) {
                $query->whereHas('cliente', function ($q) use ($clienteBusqueda) {
                    $q->where('nombre_empresa', 'like', '%' . $clienteBusqueda . '%');
                });
            }

            // Obtener todas las operaciones de la semana
            $operaciones = $query->get();

            // Agrupar por Cliente + Patente
            $resumen = $operaciones->groupBy(function ($item) {
                return $item->cliente_id . '-' . $item->patente_id;
            })->map(function ($group) use ($fechaInicio, $fechaFin) {
                $firstItem = $group->first();

                // Contar rojos
                $rojos = $group->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count();
                $sobrepesos = $group->where('sobrepeso', true)->count();

                // Obtener expedientes únicos de este grupo
                $expedientes = $group->pluck('pedimento_id')->unique()->filter();

                // 🔹 CALCULAR CONCEPTOS ADICIONALES
                // Agrupar operaciones por camión para no duplicar conceptos de ámbito "camion"
                $camiones = $group->groupBy(function ($op) {
                    return $op->fecha . '_' . $op->num_thermo . '_' . $op->codigo_alpha;
                });

                $totalAdicionales = 0;
                $detalleAdicionales = [];

                foreach ($camiones as $operacionesCamion) {
                    // Para conceptos de camión, solo tomamos la primera operación
                    $conceptosCamion = $operacionesCamion->first()->conceptosAdicionales
                        ->where('ambito', 'camion');

                    foreach ($conceptosCamion as $concepto) {
                        $totalAdicionales += $concepto->monto;

                        $tipo = $concepto->tipo_concepto;
                        if (!isset($detalleAdicionales[$tipo])) {
                            $detalleAdicionales[$tipo] = [
                                'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                                'cantidad' => 0,
                                'monto' => 0
                            ];
                        }
                        $detalleAdicionales[$tipo]['cantidad']++;
                        $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
                    }
                }

                // Para conceptos por operación, contamos todos
                foreach ($group as $operacion) {
                    $conceptosOperacion = $operacion->conceptosAdicionales
                        ->where('ambito', 'operacion');

                    foreach ($conceptosOperacion as $concepto) {
                        $totalAdicionales += $concepto->monto;

                        $tipo = $concepto->tipo_concepto;
                        if (!isset($detalleAdicionales[$tipo])) {
                            $detalleAdicionales[$tipo] = [
                                'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                                'cantidad' => 0,
                                'monto' => 0
                            ];
                        }
                        $detalleAdicionales[$tipo]['cantidad']++;
                        $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
                    }
                }

                // Obtener las fechas de apertura y cierre de los expedientes
                $expedientesInfo = Expediente::whereIn('id', $expedientes)
                    ->select('fecha_apertura', 'fecha_cierre')
                    ->get();

                $fechaApertura = $expedientesInfo->min('fecha_apertura');
                $fechaCierre = $expedientesInfo->max('fecha_cierre');

                // Contar cuántos expedientes ya tienen factura
                $expedientesFacturados = Factura::whereIn('pedimento_id', $expedientes)
                    ->where('year', now()->year)
                    ->where('semana', now()->weekOfYear)
                    ->distinct('pedimento_id')
                    ->count('pedimento_id');

                // Calcular adicionales de facturas existentes (mantener para compatibilidad)
                $adicionalesFacturas = Factura::whereIn('pedimento_id', $expedientes)
                    ->where('year', now()->year)
                    ->where('semana', now()->weekOfYear)
                    ->sum('monto_adicionales');

                return [
                    'cliente_id' => $firstItem->cliente_id,
                    'cliente_nombre' => $firstItem->cliente->nombre_empresa ?? 'Sin Cliente',
                    'patente_id' => $firstItem->patente_id,
                    'patente_numero' => $firstItem->patente->numero_patente ?? 'Sin Patente',
                    'total_tramites' => $group->count(),
                    'rojos' => $rojos,
                    'sobrepesos' => $sobrepesos,
                    'adicionales' => $totalAdicionales, // 🔹 Total de conceptos adicionales
                    'detalle_adicionales' => $detalleAdicionales, // 🔹 Desglose por tipo
                    'adicionales_facturas' => $adicionalesFacturas, // Mantener para referencia
                    'fecha_apertura' => $fechaApertura,
                    'fecha_cierre' => $fechaCierre,
                    'expedientes_count' => $expedientes->count(),
                    'expedientes_facturados' => $expedientesFacturados,
                ];
            })->values();

            // Filtrar por estado si se solicita
            if ($estadoFiltro === 'pendiente') {
                $resumen = $resumen->filter(function ($item) {
                    return $item['expedientes_facturados'] < $item['expedientes_count'];
                })->values();
            } elseif ($estadoFiltro === 'completado') {
                $resumen = $resumen->filter(function ($item) {
                    return $item['expedientes_facturados'] >= $item['expedientes_count'];
                })->values();
            }

            // Ordenar: pendientes primero, luego completados
            $resumen = $resumen->sortBy(function ($item) {
                return $item['expedientes_facturados'] >= $item['expedientes_count'] ? 1 : 0;
            })->values();

            // Lista de años disponibles
            $years = range(now()->year - 3, now()->year + 1);

            Log::info('✅ Datos cargados correctamente');
            Log::info('🎨 Renderizando vista: finanzas.index');

            return view('finanzas.index', compact(
                'resumen',
                'yearActual',
                'semanaActual',
                'fechaInicio',
                'fechaFin',
                'years',
                'clienteBusqueda'
            ));
        } catch (Exception $e) {
            Log::error('❌ ERROR en FinanzasController@index');
            Log::error('📍 Mensaje: ' . $e->getMessage());
            Log::error('📂 Archivo: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('🔍 Stack trace:', ['trace' => $e->getTraceAsString()]);
            
            // Retornar error o redirigir
            return back()->with('error', 'Error al cargar la vista de finanzas: ' . $e->getMessage());
        }

    }
    public function index_OLDDDD(Request $request)
{
    try {
        Log::info('🔵 INICIO - FinanzasController@index');
        Log::info('👤 Usuario autenticado:', [
            'id' => auth()->id(),
            'name' => auth()->user()->name,
            'role' => auth()->user()->role,
        ]);
        Log::info('📊 Cargando datos de finanzas...');
        
        // Valores por defecto
        $yearActual = $request->input('year', now()->year);
        $semanaActual = $request->input('semana', now()->weekOfYear);
        $clienteBusqueda = $request->input('cliente', '');
        $estadoFiltro = $request->input('estado', '');

        // Calcular fechas de inicio y fin de la semana seleccionada
        $fechaInicio = Carbon::now()
            ->setISODate($yearActual, $semanaActual)
            ->startOfWeek(Carbon::MONDAY);

        $fechaFin = Carbon::now()
            ->setISODate($yearActual, $semanaActual)
            ->endOfWeek(Carbon::SUNDAY);

        // Query base: operaciones de la semana CON conceptos adicionales
        $query = Operacion::with(['cliente', 'patente', 'expediente', 'conceptosAdicionales'])
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin]);

        // Filtro por cliente si se proporciona
        if (!empty($clienteBusqueda)) {
            $query->whereHas('cliente', function ($q) use ($clienteBusqueda) {
                $q->where('nombre_empresa', 'like', '%' . $clienteBusqueda . '%');
            });
        }

        // Obtener todas las operaciones de la semana
        $operaciones = $query->get();

        // 🔍 VERIFICAR DATOS FALTANTES ANTES DE AGRUPAR
        $operacionesSinCliente = $operaciones->whereNull('cliente_id');
        $operacionesSinPatente = $operaciones->whereNull('patente_id');
        
        if ($operacionesSinCliente->count() > 0) {
            Log::warning('⚠️ Operaciones sin cliente_id:', [
                'cantidad' => $operacionesSinCliente->count(),
                'ids' => $operacionesSinCliente->pluck('id')->toArray()
            ]);
        }
        
        if ($operacionesSinPatente->count() > 0) {
            Log::warning('⚠️ Operaciones sin patente_id:', [
                'cantidad' => $operacionesSinPatente->count(),
                'ids' => $operacionesSinPatente->pluck('id')->toArray()
            ]);
        }

        // Agrupar por Cliente + Patente
        $resumen = $operaciones->groupBy(function ($item) {
            // 🔹 Usar valores por defecto si faltan datos
            $clienteId = $item->cliente_id ?? 'sin_cliente';
            $patenteId = $item->patente_id ?? 'sin_patente';
            return $clienteId . '-' . $patenteId;
        })->map(function ($group) use ($fechaInicio, $fechaFin) {
            $firstItem = $group->first();

            // Contar rojos
            $rojos = $group->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count();
            $sobrepesos = $group->where('sobrepeso', true)->count();

            // Obtener expedientes únicos de este grupo
            $expedientes = $group->pluck('pedimento_id')->unique()->filter();

            // 🔹 CALCULAR CONCEPTOS ADICIONALES
            $camiones = $group->groupBy(function ($op) {
                return $op->fecha . '_' . $op->num_thermo . '_' . $op->codigo_alpha;
            });

            $totalAdicionales = 0;
            $detalleAdicionales = [];

            foreach ($camiones as $operacionesCamion) {
                $conceptosCamion = $operacionesCamion->first()->conceptosAdicionales
                    ->where('ambito', 'camion');

                foreach ($conceptosCamion as $concepto) {
                    $totalAdicionales += $concepto->monto;

                    $tipo = $concepto->tipo_concepto;
                    if (!isset($detalleAdicionales[$tipo])) {
                        $detalleAdicionales[$tipo] = [
                            'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                            'cantidad' => 0,
                            'monto' => 0
                        ];
                    }
                    $detalleAdicionales[$tipo]['cantidad']++;
                    $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
                }
            }

            foreach ($group as $operacion) {
                $conceptosOperacion = $operacion->conceptosAdicionales
                    ->where('ambito', 'operacion');

                foreach ($conceptosOperacion as $concepto) {
                    $totalAdicionales += $concepto->monto;

                    $tipo = $concepto->tipo_concepto;
                    if (!isset($detalleAdicionales[$tipo])) {
                        $detalleAdicionales[$tipo] = [
                            'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                            'cantidad' => 0,
                            'monto' => 0
                        ];
                    }
                    $detalleAdicionales[$tipo]['cantidad']++;
                    $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
                }
            }

            // Obtener las fechas de apertura y cierre de los expedientes
            $expedientesInfo = Expediente::whereIn('id', $expedientes)
                ->select('fecha_apertura', 'fecha_cierre')
                ->get();

            $fechaApertura = $expedientesInfo->min('fecha_apertura');
            $fechaCierre = $expedientesInfo->max('fecha_cierre');

            // Contar cuántos expedientes ya tienen factura
            $expedientesFacturados = Factura::whereIn('pedimento_id', $expedientes)
                ->where('year', now()->year)
                ->where('semana', now()->weekOfYear)
                ->distinct('pedimento_id')
                ->count('pedimento_id');

            // Calcular adicionales de facturas existentes
            $adicionalesFacturas = Factura::whereIn('pedimento_id', $expedientes)
                ->where('year', now()->year)
                ->where('semana', now()->weekOfYear)
                ->sum('monto_adicionales');

            return [
                'cliente_id' => $firstItem->cliente_id, // 🔹 Puede ser null
                'cliente_nombre' => $firstItem->cliente->nombre_empresa ?? '⚠️ Sin Cliente',
                'patente_id' => $firstItem->patente_id, // 🔹 Puede ser null
                'patente_numero' => $firstItem->patente->numero_patente ?? '⚠️ Sin Patente',
                'total_tramites' => $group->count(),
                'rojos' => $rojos,
                'sobrepesos' => $sobrepesos,
                'adicionales' => $totalAdicionales,
                'detalle_adicionales' => $detalleAdicionales,
                'adicionales_facturas' => $adicionalesFacturas,
                'fecha_apertura' => $fechaApertura,
                'fecha_cierre' => $fechaCierre,
                'expedientes_count' => $expedientes->count(),
                'expedientes_facturados' => $expedientesFacturados,
                'datos_incompletos' => empty($firstItem->cliente_id) || empty($firstItem->patente_id), // 🔹 FLAG
            ];
        })->values();

        // Filtrar por estado si se solicita
        if ($estadoFiltro === 'pendiente') {
            $resumen = $resumen->filter(function ($item) {
                return $item['expedientes_facturados'] < $item['expedientes_count'];
            })->values();
        } elseif ($estadoFiltro === 'completado') {
            $resumen = $resumen->filter(function ($item) {
                return $item['expedientes_facturados'] >= $item['expedientes_count'];
            })->values();
        }

        // Ordenar: pendientes primero, luego completados
        $resumen = $resumen->sortBy(function ($item) {
            return $item['expedientes_facturados'] >= $item['expedientes_count'] ? 1 : 0;
        })->values();

        // Lista de años disponibles
        $years = range(now()->year - 3, now()->year + 1);

        Log::info('✅ Datos cargados correctamente');
        Log::info('📊 Total de grupos: ' . $resumen->count());
        Log::info('⚠️ Grupos con datos incompletos: ' . $resumen->where('datos_incompletos', true)->count());
        Log::info('🎨 Renderizando vista: finanzas.index');

        return view('finanzas.index', compact(
            'resumen',
            'yearActual',
            'semanaActual',
            'fechaInicio',
            'fechaFin',
            'years',
            'clienteBusqueda'
        ));
    } catch (Exception $e) {
        Log::error('❌ ERROR en FinanzasController@index');
        Log::error('📍 Mensaje: ' . $e->getMessage());
        Log::error('📂 Archivo: ' . $e->getFile() . ':' . $e->getLine());
        Log::error('🔍 Stack trace:', ['trace' => $e->getTraceAsString()]);
        
        return back()->with('error', 'Error al cargar la vista de finanzas: ' . $e->getMessage());
    }
}
public function indexNews(Request $request)
{
    try {
        //$anio      = $request->anio ?? now()->year;
    //$semana    = $request->semana ?? null;
    //$clienteId = $request->cliente_id ?? null;
    // ---- FILTROS -----------------------------------------------
        $anio      = $request->input('anio', now()->year);
        $semana    = $request->input('semana',now()->weekOfYear);
        $clienteId = $request->input('cliente_id','');

    // ============================
    //      FILTRO PRINCIPAL
    // ============================
    $operaciones = Operacion::with([
        'cliente',
        'patente',
        'expediente',
        'ConceptosAdicionales'
    ])
    ->when($clienteId, fn($q) => $q->where('cliente_id', $clienteId))
    ->whereYear('fecha_registro', $anio)
    ->when($semana, fn($q) => $q->whereRaw('WEEK(fecha, 1) = ?', [$semana]))
    ->get();

    // ========================================
    // Agrupamos por CLIENTE
    // ========================================
    $porCliente = $operaciones->groupBy(function ($e) {
        return $e->cliente->nombre_empresa ?? 'SIN NOMBRE';
    });

    $resumen = [];

    // ========================================
    // Construimos el RESUMEN DE CADA CLIENTE
    // ========================================
    foreach ($porCliente as $clienteNombre => $lista) {

        // Grupo por PATENTE + PEDIMENTO reales
        $detalle = $lista->groupBy(function ($e) {
            $pat = $e->patente->numero_patente ?? 'N/A';
            $ped = $e->expediente->numero_pedimento ?? 'N/A';
            return "$pat-$ped";
        });

        // ============================
        // Totales por cliente
        // ============================
        $resumen[$clienteNombre]['nombre_empresa'] = $clienteNombre;
        $resumen[$clienteNombre]['totales'] = [
            'patentes'    => $lista->pluck('patente_id')->unique()->count(),
            'pedimentos'  => $lista->pluck('pedimento_id')->unique()->count(),
            'remesas'     => $lista->count(),
            'rojos'       => $lista->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
            'sobrepesos'  => $lista->where('sobrepeso', 1)->count(),
            'taras'       => $lista->flatMap->ConceptosAdicionales
                                          ->where('tipo_concepto', 'Uso de bascula')
                                          ->count(),
            'adicionales' => $lista->flatMap->ConceptosAdicionales
                                   ->where('tipo_concepto', '!=', 'Uso de bascula') // ✅ EXCLUYE básculas
                                   ->count(),
        ];

        // ============================
        // DETALLE por pedimento
        // ============================
        $detalleFinal = collect();

        foreach ($detalle as $comb => $items) {

            $primero = $items->first();

            $detalleFinal->push([
                'id'          => $primero->pedimento_id, // id exportación para facturar
                'patente'     => $primero->patente->numero_patente ?? 'N/A',
                'pedimento'   => $primero->expediente->numero_pedimento ?? 'N/A',
                'remesas'     => $items->count(),
                'rojos'       => $items->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
                'sobrepesos'  => $items->where('sobrepeso', 1)->count(),
                'taras'       => $items->flatMap->ConceptosAdicionales
                                               ->where('tipo_concepto', 'Uso de bascula')
                                               ->count(),
                'adicionales' => $items->flatMap->ConceptosAdicionales
                                   ->where('tipo_concepto', '!=', 'Uso de bascula') // ✅ EXCLUYE básculas
                                   ->count(),
            ]);
        }

        $resumen[$clienteNombre]['detalle'] = $detalleFinal;
    }

    return view('finanzas.index', [
        'anio'      => $anio,
        'semana'    => $semana,
        'clienteId' => $clienteId,
        'clientes'  => \App\Models\Cliente::orderBy('nombre_empresa')->get(),
        'resumen'   => collect($resumen),
    ]);
       } 
       catch (Exception $e) {
        Log::error('❌ ERROR en FinanzasController@index');
        Log::error('📍 Mensaje: ' . $e->getMessage());
        Log::error('📂 Archivo: ' . $e->getFile() . ':' . $e->getLine());
        Log::error('🔍 Stack trace:', ['trace' => $e->getTraceAsString()]);
        
        return back()->with('error', 'Error al cargar la vista de finanzas: ' . $e->getMessage());
       }
    
}

    public function indexNew_OLD(Request $request)
    {
        try {
            // ========================================
            // FILTROS
            // ========================================
            $anio = $request->input('anio', now()->year);
            $semana = $request->input('semana', now()->weekOfYear);
            $clienteId = $request->input('cliente_id', '');
            $numeroPedimento = $request->input('numero_pedimento', ''); // 🆕 Búsqueda por pedimento

            // ========================================
            // CONSULTA BASE: Expedientes (Pedimentos)
            // ========================================
            $expedientes = \App\Models\Expediente::query()
                ->with([
                    'cliente',
                    'patente',
                    'operaciones' => function ($query) {
                        $query->with('ConceptosAdicionales');
                    }
                ]);

            // ========================================
            // LÓGICA DE FILTROS
            // ========================================
            if (!empty($numeroPedimento)) {
                // 🔍 Búsqueda por NÚMERO DE PEDIMENTO (ignora año y semana)
                $expedientes->where('numero_pedimento', 'LIKE', "%{$numeroPedimento}%");
            } else {
                // 📅 Búsqueda por AÑO y SEMANA FISCAL
                $expedientes->whereYear('fecha_apertura', $anio)
                    ->when($semana, fn($q) => $q->whereRaw('WEEK(fecha_apertura, 1) = ?', [$semana]));

                // 👤 Búsqueda por CLIENTE (solo si no hay búsqueda por pedimento)
                if (!empty($clienteId)) {
                    $expedientes->where('cliente_id', $clienteId);
                }
            }

            $expedientes = $expedientes->orderBy('fecha_apertura', 'desc')->get();

            // ========================================
            // CONSTRUIR RESUMEN POR CLIENTE
            // ========================================
            $porCliente = $expedientes->groupBy(function ($expediente) {
                return $expediente->cliente->nombre_empresa ?? 'SIN NOMBRE';
            });

            $resumen = [];

            foreach ($porCliente as $clienteNombre => $listaExpedientes) {

                // ============================
                // TOTALES POR CLIENTE
                // ============================
                $todasLasOperaciones = $listaExpedientes->flatMap->operaciones;

                $resumen[$clienteNombre]['nombre_empresa'] = $clienteNombre;
                $resumen[$clienteNombre]['totales'] = [
                    'patentes' => $listaExpedientes->pluck('patente_id')->unique()->count(),
                    'pedimentos' => $listaExpedientes->count(),
                    'remesas' => $todasLasOperaciones->count(),
                    'rojos' => $todasLasOperaciones->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
                    'sobrepesos' => $todasLasOperaciones->where('sobrepeso', 1)->count(),
                    'taras' => $todasLasOperaciones->flatMap->ConceptosAdicionales
                        ->where('tipo_concepto', 'Uso de bascula')
                        ->count(),
                    'adicionales' => $todasLasOperaciones->flatMap->ConceptosAdicionales
                        ->where('tipo_concepto', '!=', 'Uso de bascula')
                        ->count(),
                ];

                // ============================
                // DETALLE POR PEDIMENTO
                // ============================
                $detalleFinal = collect();

                foreach ($listaExpedientes as $expediente) {
                    $operaciones = $expediente->operaciones;

                    $detalleFinal->push([
                        'id' => $expediente->id, // 🔑 ID del pedimento (expediente)
                        'patente' => $expediente->patente->numero_patente ?? 'N/A',
                        'aduana'=> $expediente->aduana->clave_aduana ?? 'N/A',
                        'pedimento' => $expediente->numero_pedimento ?? 'N/A',
                        'fecha_registro' => $expediente->fecha_cierre ?? null,
                        'remesas' => $operaciones->count(),
                        'rojos' => $operaciones->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
                        'sobrepesos' => $operaciones->where('sobrepeso', 1)->count(),
                        'taras' => $operaciones->flatMap->ConceptosAdicionales
                            ->where('tipo_concepto', 'Uso de bascula')
                            ->count(),
                        'adicionales' => $operaciones->flatMap->ConceptosAdicionales
                            ->where('tipo_concepto', '!=', 'Uso de bascula')
                            ->count(),
                    ]);
                }

                $resumen[$clienteNombre]['detalle'] = $detalleFinal;
            }

            return view('finanzas.index', [
                'anio' => $anio,
                'semana' => $semana,
                'clienteId' => $clienteId,
                'numeroPedimento' => $numeroPedimento, // 🆕 Pasar al view
                'clientes' => \App\Models\Cliente::orderBy('nombre_empresa')->get(),
                'resumen' => collect($resumen),
            ]);

        } catch (Exception $e) {
            Log::error('❌ ERROR en FinanzasController@index');
            Log::error('📍 Mensaje: ' . $e->getMessage());
            Log::error('📂 Archivo: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('🔍 Stack trace:', ['trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Error al cargar la vista de finanzas: ' . $e->getMessage());
        }
    }

    public function indexNew(Request $request)
{
    try {
        // ========================================
        // FILTROS
        // ========================================
        $anio = $request->input('anio', now()->year);
        $semana = $request->input('semana', now()->weekOfYear);
        $clienteId = $request->input('cliente_id', '');
        $numeroPedimento = $request->input('numero_pedimento', ''); // 🆕 Búsqueda por pedimento

        // ========================================
        // CONSULTA BASE: Expedientes (Pedimentos)
        // ========================================
        $expedientes = \App\Models\Expediente::query()
            ->with([
                'cliente',
                'patente',
                'operaciones' => function ($query) {
                    $query->with('ConceptosAdicionales')->where('estado', '!=', 'cancelada');
                }
            ]);

        // ========================================
        // LÓGICA DE FILTROS
        // ========================================
        if (!empty($numeroPedimento)) {
            // 🔍 Búsqueda por NÚMERO DE PEDIMENTO (ignora año y semana)
            $expedientes->where('numero_pedimento', 'LIKE', "%{$numeroPedimento}%");
        } else {
            // 📅 Búsqueda por AÑO y SEMANA FISCAL
            $expedientes->whereYear('fecha_apertura', $anio);
            
            // 🗓️ Si hay semana específica, calculamos el rango de fechas
            if ($semana) {
                $rangoSemana = $this->calcularRangoSemana($anio, $semana);
                if ($rangoSemana) {
                    $expedientes->whereBetween('fecha_apertura', [
                        $rangoSemana['inicio'],
                        $rangoSemana['fin']
                    ]);
                }
            }

            // 👤 Búsqueda por CLIENTE (solo si no hay búsqueda por pedimento)
            if (!empty($clienteId)) {
                $expedientes->where('cliente_id', $clienteId);
            }
        }

        $expedientes = $expedientes->orderBy('fecha_apertura', 'desc')->get();

        // ========================================
        // CONSTRUIR RESUMEN POR CLIENTE
        // ========================================
        $porCliente = $expedientes->groupBy(function ($expediente) {
            return $expediente->cliente->nombre_empresa ?? 'SIN NOMBRE';
        });

        $resumen = [];

        foreach ($porCliente as $clienteNombre => $listaExpedientes) {

            // ============================
            // TOTALES POR CLIENTE
            // ============================
            $todasLasOperaciones = $listaExpedientes->flatMap->operaciones;

            $resumen[$clienteNombre]['nombre_empresa'] = $clienteNombre;
            $resumen[$clienteNombre]['totales'] = [
                'patentes' => $listaExpedientes->pluck('patente_id')->unique()->count(),
                'pedimentos' => $listaExpedientes->count(),
                'remesas' => $todasLasOperaciones->count(),
                'rojos' => $todasLasOperaciones->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
                'sobrepesos' => $todasLasOperaciones->where('sobrepeso', 1)->count(),
                'taras' => $todasLasOperaciones->flatMap->ConceptosAdicionales
                    ->where('tipo_concepto', 'Uso de bascula')
                    ->count(),
                'adicionales' => $todasLasOperaciones->flatMap->ConceptosAdicionales
                    ->where('tipo_concepto', '!=', 'Uso de bascula')
                    ->count(),
            ];

            // ============================
            // DETALLE POR PEDIMENTO
            // ============================
            $detalleFinal = collect();

            foreach ($listaExpedientes as $expediente) {
                $operaciones = $expediente->operaciones;

                $detalleFinal->push([
                    'id' => $expediente->id, // 🔑 ID del pedimento (expediente)
                    'patente' => $expediente->patente->numero_patente ?? 'N/A',
                    'aduana' => $expediente->aduana->clave_aduana ?? 'N/A',
                    'pedimento' => $expediente->numero_pedimento ?? 'N/A',
                    'fecha_registro' => $expediente->fecha_cierre ?? null,
                    'tiene_pedimento_pagado'=> $expediente->documentos()
                        ->where('tipo_documento','Pedimento Pagado')
                        ->exists(),
                    'remesas' => $operaciones->count(),
                    'rojos' => $operaciones->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
                    'sobrepesos' => $operaciones->where('sobrepeso', 1)->count(),
                    'taras' => $operaciones->flatMap->ConceptosAdicionales
                        ->where('tipo_concepto', 'Uso de bascula')
                        ->count(),
                    'adicionales' => $operaciones->flatMap->ConceptosAdicionales
                        ->where('tipo_concepto', '!=', 'Uso de bascula')
                        ->count(),
                ]);
            }

            $resumen[$clienteNombre]['detalle'] = $detalleFinal;
        }

        return view('finanzas.index', [
            'anio' => $anio,
            'semana' => $semana,
            'clienteId' => $clienteId,
            'numeroPedimento' => $numeroPedimento, // 🆕 Pasar al view
            'clientes' => \App\Models\Cliente::orderBy('nombre_empresa')->get(),
            'resumen' => collect($resumen),
        ]);

    } catch (Exception $e) {
        Log::error('❌ ERROR en FinanzasController@index');
        Log::error('📍 Mensaje: ' . $e->getMessage());
        Log::error('📂 Archivo: ' . $e->getFile() . ':' . $e->getLine());
        Log::error('🔍 Stack trace:', ['trace' => $e->getTraceAsString()]);

        return back()->with('error', 'Error al cargar la vista de finanzas: ' . $e->getMessage());
    }
}

/**
 * 📅 Calcula el rango de fechas para una semana específica del año
 * Semana comienza en DOMINGO y termina en SÁBADO
 * 
 * @param int $anio
 * @param int $numeroSemana
 * @return array|null ['inicio' => 'Y-m-d', 'fin' => 'Y-m-d']
 */
private function calcularRangoSemana($anio, $numeroSemana)
{
    try {
        // Primer día del año
        $primerDia = \Carbon\Carbon::create($anio, 1, 1);
        
        // Encontrar el primer domingo del año (o el 1 de enero si cae domingo)
        $primerDomingo = $primerDia->copy();
        if ($primerDomingo->dayOfWeek !== 0) { // 0 = Domingo
            $primerDomingo->next(\Carbon\Carbon::SUNDAY);
        }
        
        // Calcular inicio de la semana solicitada
        if ($numeroSemana == 1) {
            // La semana 1 va desde el 1 de enero hasta el sábado previo al primer domingo
            $inicioSemana = $primerDia->copy();
            $finSemana = $primerDomingo->copy()->subDay(); // Sábado antes del primer domingo
        } else {
            // Las demás semanas: sumar (numeroSemana - 2) semanas al primer domingo
            $inicioSemana = $primerDomingo->copy()->addWeeks($numeroSemana - 2);
            $finSemana = $inicioSemana->copy()->addDays(6); // Domingo + 6 días = Sábado
        }
        
        return [
            'inicio' => $inicioSemana->format('Y-m-d'),
            'fin' => $finSemana->format('Y-m-d')
        ];
        
    } catch (Exception $e) {
        Log::error('❌ ERROR calculando rango de semana', [
            'anio' => $anio,
            'semana' => $numeroSemana,
            'error' => $e->getMessage()
        ]);
        return null;
    }
}



/**
     * 📊 Vista de reporte por rango de semanas
     * Muestra pedimentos y remesas entre dos semanas (puede cruzar años)
     */
    public function reporteRangoSemanas_old(Request $request)
    {
        try {
            // ========================================
            // FILTROS DE RANGO
            // ========================================
            $anioInicio = $request->input('anio_inicio', now()->year);
            $semanaInicio = $request->input('semana_inicio', 1);
            $anioFin = $request->input('anio_fin', now()->year);
            $semanaFin = $request->input('semana_fin', now()->weekOfYear);
            $clienteId = $request->input('cliente_id', '');

            // ========================================
            // CALCULAR RANGO DE FECHAS
            // ========================================
            $rangoInicio = $this->calcularRangoSemana($anioInicio, $semanaInicio);
            $rangoFin = $this->calcularRangoSemana($anioFin, $semanaFin);

            if (!$rangoInicio || !$rangoFin) {
                return back()->with('error', 'Error al calcular el rango de semanas');
            }

            $fechaInicio = $rangoInicio['inicio'];
            $fechaFin = $rangoFin['fin'];

            // ========================================
            // CONSULTA: Expedientes en el rango
            // ========================================
            $expedientes = \App\Models\Expediente::query()
                ->with([
                    'cliente',
                    'patente',
                    'aduana',
                    'operaciones' => function ($query) {
                        $query->with('ConceptosAdicionales');
                    }
                ])
                ->whereBetween('fecha_apertura', [$fechaInicio, $fechaFin]);

            // Filtro por cliente si se especifica
            if (!empty($clienteId)) {
                $expedientes->where('cliente_id', $clienteId);
            }

            $expedientes = $expedientes->orderBy('fecha_apertura', 'asc')->get();

            // ========================================
            // TOTALES GENERALES
            // ========================================
            $todasLasOperaciones = $expedientes->flatMap->operaciones;

            $totalesGenerales = [
                'pedimentos' => $expedientes->count(),
                'remesas' => $todasLasOperaciones->count(),
                'patentes' => $expedientes->pluck('patente_id')->unique()->count(),
                'clientes' => $expedientes->pluck('cliente_id')->unique()->count(),
                'rojos' => $todasLasOperaciones->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
                'sobrepesos' => $todasLasOperaciones->where('sobrepeso', 1)->count(),
                'taras' => $todasLasOperaciones->flatMap->ConceptosAdicionales
                    ->where('tipo_concepto', 'Uso de bascula')
                    ->count(),
                'adicionales' => $todasLasOperaciones->flatMap->ConceptosAdicionales
                    ->where('tipo_concepto', '!=', 'Uso de bascula')
                    ->count(),
            ];

            // ========================================
            // RESUMEN POR CLIENTE
            // ========================================
            $porCliente = $expedientes->groupBy(function ($expediente) {
                return $expediente->cliente->nombre_empresa ?? 'SIN NOMBRE';
            });

            $resumenClientes = [];

            foreach ($porCliente as $clienteNombre => $listaExpedientes) {
                $operacionesCliente = $listaExpedientes->flatMap->operaciones;

                $resumenClientes[$clienteNombre] = [
                    'nombre_empresa' => $clienteNombre,
                    'cliente_id' => $listaExpedientes->first()->cliente_id,
                    'totales' => [
                        'pedimentos' => $listaExpedientes->count(),
                        'remesas' => $operacionesCliente->count(),
                        'patentes' => $listaExpedientes->pluck('patente_id')->unique()->count(),
                        'rojos' => $operacionesCliente->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
                        'sobrepesos' => $operacionesCliente->where('sobrepeso', 1)->count(),
                        'taras' => $operacionesCliente->flatMap->ConceptosAdicionales
                            ->where('tipo_concepto', 'Uso de bascula')
                            ->count(),
                        'adicionales' => $operacionesCliente->flatMap->ConceptosAdicionales
                            ->where('tipo_concepto', '!=', 'Uso de bascula')
                            ->count(),
                    ],
                ];
            }
            $resumenClientes = collect($resumenClientes)->sortByDesc(function ($cliente) {
                return $cliente['totales']['pedimentos'];
            });

            

            // ========================================
            // DETALLE DE PEDIMENTOS
            // ========================================
            $detallePedimentos = [];

            foreach ($expedientes as $expediente) {
                $operaciones = $expediente->operaciones;

                $detallePedimentos[] = [
                    'id' => $expediente->id,
                    'cliente' => $expediente->cliente->nombre_empresa ?? 'N/A',
                    'patente' => $expediente->patente->numero_patente ?? 'N/A',
                    'aduana' => $expediente->aduana->clave_aduana ?? 'N/A',
                    'pedimento' => $expediente->numero_pedimento ?? 'N/A',
                    'fecha_apertura' => $expediente->fecha_apertura,
                    'fecha_cierre' => $expediente->fecha_cierre ?? null,
                    'semana' => $this->obtenerNumeroSemana($expediente->fecha_apertura),
                    'remesas' => $operaciones->count(),
                    'rojos' => $operaciones->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
                    'sobrepesos' => $operaciones->where('sobrepeso', 1)->count(),
                    'taras' => $operaciones->flatMap->ConceptosAdicionales
                        ->where('tipo_concepto', 'Uso de bascula')
                        ->count(),
                    'adicionales' => $operaciones->flatMap->ConceptosAdicionales
                        ->where('tipo_concepto', '!=', 'Uso de bascula')
                        ->count(),
                ];
            }
            $detallePedimentos = collect($detallePedimentos)->sortBy([
                //['cliente', 'asc'],
                ['semana', 'desc'],
                ['fecha_apertura', 'asc']
            ]);


            return view('finanzas.reporte-rango-semanas', [
                'anioInicio' => $anioInicio,
                'semanaInicio' => $semanaInicio,
                'anioFin' => $anioFin,
                'semanaFin' => $semanaFin,
                'clienteId' => $clienteId,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'totalesGenerales' => $totalesGenerales,
                'resumenClientes' => collect($resumenClientes),
                'detallePedimentos' => collect($detallePedimentos),
                'clientes' => \App\Models\Cliente::orderBy('nombre_empresa')->get(),
            ]);

        } catch (Exception $e) {
            Log::error('❌ ERROR en FinanzasController@reporteRangoSemanas');
            Log::error('📍 Mensaje: ' . $e->getMessage());
            Log::error('📂 Archivo: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('🔍 Stack trace:', ['trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Error al cargar el reporte: ' . $e->getMessage());
        }
    }
    
    public function reporteRangoSemanas(Request $request)
    {
        try {
            // ========================================
            // FILTROS DE RANGO
            // ========================================
            $anioInicio = $request->input('anio_inicio', now()->year);
            $semanaInicio = $request->input('semana_inicio', 1);
            $anioFin = $request->input('anio_fin', now()->year);
            $semanaFin = $request->input('semana_fin', now()->weekOfYear);
            $clienteId = $request->input('cliente_id', '');

            // ========================================
            // CALCULAR RANGO DE FECHAS
            // ========================================
            $rangoInicio = $this->calcularRangoSemana($anioInicio, $semanaInicio);
            $rangoFin = $this->calcularRangoSemana($anioFin, $semanaFin);

            if (!$rangoInicio || !$rangoFin) {
                return back()->with('error', 'Error al calcular el rango de semanas');
            }

            $fechaInicio = $rangoInicio['inicio'];
            $fechaFin = $rangoFin['fin'];

            // ========================================
            // CONSULTA: Expedientes en el rango
            // ========================================
            $expedientes = \App\Models\Expediente::query()
                ->with([
                    'cliente',
                    'patente',
                    'aduana',
                    'operaciones' => function ($query) {
                        $query->with('ConceptosAdicionales');
                    }
                ])
                ->whereBetween('fecha_apertura', [$fechaInicio, $fechaFin]);

            // Filtro por cliente si se especifica
            if (!empty($clienteId)) {
                $expedientes->where('cliente_id', $clienteId);
            }

            $expedientes = $expedientes->orderBy('fecha_apertura', 'asc')->get();

            // ========================================
            // TOTALES GENERALES
            // ========================================
            $todasLasOperaciones = $expedientes->flatMap->operaciones;

            $totalesGenerales = [
                'pedimentos' => $expedientes->count(),
                'remesas' => $todasLasOperaciones->count(),
                'patentes' => $expedientes->pluck('patente_id')->unique()->count(),
                'clientes' => $expedientes->pluck('cliente_id')->unique()->count(),
                'rojos' => $todasLasOperaciones->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
                'sobrepesos' => $todasLasOperaciones->where('sobrepeso', 1)->count(),
                'taras' => $todasLasOperaciones->flatMap->ConceptosAdicionales
                    ->where('tipo_concepto', 'Uso de bascula')
                    ->count(),
                'adicionales' => $todasLasOperaciones->flatMap->ConceptosAdicionales
                    ->where('tipo_concepto', '!=', 'Uso de bascula')
                    ->count(),
            ];

            // ========================================
            // RESUMEN POR CLIENTE
            // ========================================
            $porCliente = $expedientes->groupBy(function ($expediente) {
                return $expediente->cliente->nombre_empresa ?? 'SIN NOMBRE';
            });

            $resumenClientes = [];

            foreach ($porCliente as $clienteNombre => $listaExpedientes) {
                $operacionesCliente = $listaExpedientes->flatMap->operaciones;

                $resumenClientes[$clienteNombre] = [
                    'nombre_empresa' => $clienteNombre,
                    'cliente_id' => $listaExpedientes->first()->cliente_id,
                    'totales' => [
                        'pedimentos' => $listaExpedientes->count(),
                        'remesas' => $operacionesCliente->count(),
                        'patentes' => $listaExpedientes->pluck('patente_id')->unique()->count(),
                        'rojos' => $operacionesCliente->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
                        'sobrepesos' => $operacionesCliente->where('sobrepeso', 1)->count(),
                        'taras' => $operacionesCliente->flatMap->ConceptosAdicionales
                            ->where('tipo_concepto', 'Uso de bascula')
                            ->count(),
                        'adicionales' => $operacionesCliente->flatMap->ConceptosAdicionales
                            ->where('tipo_concepto', '!=', 'Uso de bascula')
                            ->count(),
                    ],
                ];
            }
            $resumenClientes = collect($resumenClientes)->sortByDesc(function ($cliente) {
                return $cliente['totales']['pedimentos'];
            });

            // ========================================
// DETALLE DE PEDIMENTOS AGRUPADO POR PATENTE
// ========================================
            $porPatente = $expedientes->groupBy(function ($expediente) {
                return $expediente->patente->numero_patente ?? 'SIN PATENTE';
            });

            $detallesPorPatente = [];

            foreach ($porPatente as $patenteNumero => $listaExpedientes) {
                $pedimentos = [];
                $operacionesPatente = $listaExpedientes->flatMap->operaciones;

                foreach ($listaExpedientes as $expediente) {
                    $operaciones = $expediente->operaciones;

                    $pedimentos[] = [
                        'id' => $expediente->id,
                        'cliente' => $expediente->cliente->nombre_empresa ?? 'N/A',
                        'semana' => $this->obtenerNumeroSemana($expediente->fecha_apertura),
                        'aduana' => $expediente->aduana->clave_aduana ?? 'N/A',
                        'pedimento' => $expediente->numero_pedimento ?? 'N/A',
                        'fecha_apertura' => $expediente->fecha_apertura,
                        'remesas' => $operaciones->count(),
                        'rojos' => $operaciones->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
                        'sobrepesos' => $operaciones->where('sobrepeso', 1)->count(),
                        'taras' => $operaciones->flatMap->ConceptosAdicionales
                            ->where('tipo_concepto', 'Uso de bascula')
                            ->count(),
                        'adicionales' => $operaciones->flatMap->ConceptosAdicionales
                            ->where('tipo_concepto', '!=', 'Uso de bascula')
                            ->count(),
                    ];
                }

                // Totales de esta patente
                $totalesPatente = [
                    'pedimentos' => $listaExpedientes->count(),
                    'remesas' => $operacionesPatente->count(),
                    'rojos' => $operacionesPatente->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
                    'sobrepesos' => $operacionesPatente->where('sobrepeso', 1)->count(),
                    'taras' => $operacionesPatente->flatMap->ConceptosAdicionales
                        ->where('tipo_concepto', 'Uso de bascula')
                        ->count(),
                    'adicionales' => $operacionesPatente->flatMap->ConceptosAdicionales
                        ->where('tipo_concepto', '!=', 'Uso de bascula')
                        ->count(),
                ];

                $detallesPorPatente[$patenteNumero] = [
                    'patente' => $patenteNumero,
                    'totales' => $totalesPatente,
                    'pedimentos' => collect($pedimentos)->sortBy([
                        ['cliente', 'asc'],
                        ['semana', 'asc'],
                        ['fecha_apertura', 'asc']
                    ])->values()
                ];
            }

            // Ordenar patentes por cantidad de pedimentos (mayor a menor)
            $detallesPorPatente = collect($detallesPorPatente)->sortByDesc(function ($detalle) {
                return $detalle['totales']['pedimentos'];
            });


            return view('finanzas.reporte-rango-semanas', [
                'anioInicio' => $anioInicio,
                'semanaInicio' => $semanaInicio,
                'anioFin' => $anioFin,
                'semanaFin' => $semanaFin,
                'clienteId' => $clienteId,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'totalesGenerales' => $totalesGenerales,
                'resumenClientes' => collect($resumenClientes),
                'detallesPorPatente' => $detallesPorPatente,
                'clientes' => \App\Models\Cliente::orderBy('nombre_empresa')->get(),
            ]);

        } catch (Exception $e) {
            Log::error('❌ ERROR en FinanzasController@reporteRangoSemanas');
            Log::error('📍 Mensaje: ' . $e->getMessage());
            Log::error('📂 Archivo: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('🔍 Stack trace:', ['trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Error al cargar el reporte: ' . $e->getMessage());
        }
    }


    /**
     * 🔢 Obtiene el número de semana para una fecha dada
     * Usando la misma lógica de semanas: domingo a sábado, semana 1 contiene el 1 de enero
     * 
     * @param string $fecha
     * @return int
     */
    private function obtenerNumeroSemana($fecha)
    {
        try {
            $fecha = \Carbon\Carbon::parse($fecha);
            $anio = $fecha->year;

            // Primer día del año
            $primerDia = \Carbon\Carbon::create($anio, 1, 1);

            // Encontrar el primer domingo del año O ANTERIOR
            $primerDomingo = $primerDia->copy();
            if ($primerDomingo->dayOfWeek !== 0) { // 0 = Domingo
                $primerDomingo->previous(\Carbon\Carbon::SUNDAY);
            }

            // Si la fecha es antes del 1 de enero, pertenece al año anterior
            if ($fecha->lt($primerDia)) {
                return $this->obtenerNumeroSemana($fecha->subYear()->endOfYear());
            }

            // Calcular diferencia en semanas desde el primer domingo
            $semana = $primerDomingo->diffInWeeks($fecha) + 1;

            return $semana;

        } catch (Exception $e) {
            Log::error('❌ ERROR obteniendo número de semana', [
                'fecha_registro' => $fecha,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }























    /**
     * NIVEL 2: Detalle por Expedientes de un Cliente-Patente
     */
    public function detalleClientePatenteOriginal(Request $request, $clienteId, $patenteId)
    {
        $year = $request->input('year', now()->year);
        $semana = $request->input('semana', now()->weekOfYear);

        // Calcular fechas de la semana
        $fechaInicio = Carbon::now()
            ->setISODate($year, $semana)
            ->startOfWeek(Carbon::MONDAY);

        $fechaFin = Carbon::now()
            ->setISODate($year, $semana)
            ->endOfWeek(Carbon::SUNDAY);

        // Obtener cliente y patente
        $cliente = Cliente::findOrFail($clienteId);
        $patente = Patente::findOrFail($patenteId);

        // Obtener operaciones de la semana para este cliente-patente
        $operaciones = Operacion::with(['expediente'])
            ->where('cliente_id', $clienteId)
            ->where('patente_id', $patenteId)
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
            ->get();

        // Agrupar por expediente
        $expedientes = $operaciones->groupBy('pedimento_id')->map(function ($group) {
            $expediente = $group->first()->expediente;
            $rojos = $group->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count();
            $sobrepesos = $group->where('sobrepeso', true)->count();

            // Verificar si tiene factura con sus documentos
            $factura = Factura::where('pedimento_id', $expediente->id ?? null)
                //->where('year', now()->year)
                //->where('semana', now()->weekOfYear)
                ->with(['documentos'])
                ->first();

            return [
                'pedimento_id' => $expediente->id ?? null,
                'expediente_numero' => $expediente->numero_pedimento ?? 'Sin Expediente',
                'fecha_apertura' => $expediente->fecha_apertura ?? null,
                'fecha_cierre' => $expediente->fecha_cierre ?? null,
                'total_tramites' => $group->count(),
                'rojos' => $rojos,
                'sobrepesos' => $sobrepesos,
                'factura' => $factura,
                'tiene_factura' => $factura !== null,
            ];
        })->values()->sortByDesc('total_tramites');


        return view('finanzas.detalle_cliente_patente', compact(
            'cliente',
            'patente',
            'expedientes',
            'year',
            'semana',
            'fechaInicio',
            'fechaFin'
        ));
    }

    public function detalleClientePatente(Request $request, $clienteId, $patenteId)
    {
        $year = $request->input('year', now()->year);
        $semana = $request->input('semana', now()->weekOfYear);

        // Calcular fechas de la semana
        $fechaInicio = Carbon::now()
            ->setISODate($year, $semana)
            ->startOfWeek(Carbon::MONDAY);

        $fechaFin = Carbon::now()
            ->setISODate($year, $semana)
            ->endOfWeek(Carbon::SUNDAY);

        // Obtener cliente y patente
        $cliente = Cliente::findOrFail($clienteId);
        $patente = Patente::findOrFail($patenteId);

        // Obtener operaciones con conceptos adicionales
        $operaciones = Operacion::with(['expediente', 'conceptosAdicionales'])
            ->where('cliente_id', $clienteId)
            ->where('patente_id', $patenteId)
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
            ->where('estado', '!=', 'cancelada')
            ->get();

        // Agrupar por expediente
        $expedientes = $operaciones->groupBy('pedimento_id')->map(function ($group) {
            $expediente = $group->first()->expediente;
            $rojos = $group->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count();
            $sobrepesos = $group->where('sobrepeso', true)->count();

            // 🔹 CALCULAR CONCEPTOS ADICIONALES POR EXPEDIENTE
            $camiones = $group->groupBy(function ($op) {
                return $op->fecha . '_' . $op->num_thermo . '_' . $op->codigo_alpha;
            });

            $totalAdicionales = 0;
            $detalleAdicionales = [];

            foreach ($camiones as $operacionesCamion) {
                $conceptosCamion = $operacionesCamion->first()->conceptosAdicionales
                    ->where('ambito', 'camion');

                foreach ($conceptosCamion as $concepto) {
                    $totalAdicionales += $concepto->monto;
                    $tipo = $concepto->tipo_concepto;
                    if (!isset($detalleAdicionales[$tipo])) {
                        $detalleAdicionales[$tipo] = [
                            'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                            'cantidad' => 0,
                            'monto' => 0
                        ];
                    }
                    $detalleAdicionales[$tipo]['cantidad']++;
                    $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
                }
            }

            foreach ($group as $operacion) {
                $conceptosOperacion = $operacion->conceptosAdicionales
                    ->where('ambito', 'operacion');

                foreach ($conceptosOperacion as $concepto) {
                    $totalAdicionales += $concepto->monto;
                    $tipo = $concepto->tipo_concepto;
                    if (!isset($detalleAdicionales[$tipo])) {
                        $detalleAdicionales[$tipo] = [
                            'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                            'cantidad' => 0,
                            'monto' => 0
                        ];
                    }
                    $detalleAdicionales[$tipo]['cantidad']++;
                    $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
                }
            }

            // Verificar si tiene factura con sus documentos
            $factura = Factura::where('pedimento_id', $expediente->id ?? null)
                ->with(['documentos'])
                ->first();

            return [
                'pedimento_id' => $expediente->id ?? null,
                'expediente_numero' => $expediente->numero_pedimento ?? 'Sin Expediente',
                'fecha_apertura' => $expediente->fecha_apertura ?? null,
                'fecha_cierre' => $expediente->fecha_cierre ?? null,
                'total_tramites' => $group->count(),
                'rojos' => $rojos,
                'sobrepesos' => $sobrepesos,
                'adicionales' => $totalAdicionales, // 🔹 Total adicionales
                'detalle_adicionales' => $detalleAdicionales, // 🔹 Desglose
                'factura' => $factura,
                'tiene_factura' => $factura !== null,
            ];
        })->values()->sortByDesc('total_tramites');


        return view('finanzas.detalle_cliente_patente', compact(
            'cliente',
            'patente',
            'expedientes',
            'year',
            'semana',
            'fechaInicio',
            'fechaFin'
        ));
    }

    /**
     * NIVEL 3: Operaciones de un Expediente específico
     */
    public function detalleExpedienteOriginal(Request $request, $expedienteId)
    {
        $year = $request->input('year', now()->year);
        $semana = $request->input('semana', now()->weekOfYear);

        // Calcular fechas de la semana
        $fechaInicio = Carbon::now()
            ->setISODate($year, $semana)
            ->startOfWeek(Carbon::MONDAY);

        $fechaFin = Carbon::now()
            ->setISODate($year, $semana)
            ->endOfWeek(Carbon::SUNDAY);

        // Obtener expediente
        $expediente = Expediente::findOrFail($expedienteId);

        // Obtener todas las operaciones del expediente en la semana
        $operaciones = Operacion::with(['cliente', 'patente', 'bodega', 'importador'])
            ->where('pedimento_id', $expedienteId)
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_registro', 'desc')
            ->get();

        // Estadísticas
        $totalTramites = $operaciones->count();
        $rojos = $operaciones->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count();
        $verdes = $operaciones->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
        $sobrepesos = $operaciones->where('sobrepeso', true)->count();

        // Verificar si tiene factura con sus documentos
        $factura = Factura::where('pedimento_id', $expedienteId)
            ->where('year', $year)
            ->where('semana', $semana)
            ->with(['documentos'])
            ->first();

        return view('finanzas.detalle_expediente', compact(
            'expediente',
            'operaciones',
            'year',
            'semana',
            'fechaInicio',
            'fechaFin',
            'totalTramites',
            'rojos',
            'verdes',
            'sobrepesos',
            'factura'
        ));
    }

    public function detalleExpediente_OLD(Request $request, $expedienteId)
{
    $year = $request->input('year', now()->year);
    $semana = $request->input('semana', now()->weekOfYear);

    // Calcular fechas de la semana
    $fechaInicio = Carbon::now()
        ->setISODate($year, $semana)
        ->startOfWeek(Carbon::MONDAY);
    
    $fechaFin = Carbon::now()
        ->setISODate($year, $semana)
        ->endOfWeek(Carbon::SUNDAY);

    // Obtener expediente
    $expediente = Expediente::findOrFail($expedienteId);

    // Obtener todas las operaciones del expediente con conceptos adicionales
    $operaciones = Operacion::with(['cliente', 'patente', 'bodega', 'importador', 'conceptosAdicionales'])
        ->where('pedimento_id', $expedienteId)
        //->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
        ->orderBy('fecha_registro', 'desc')
        ->get();
        

    // Estadísticas
    $totalTramites = $operaciones->count();
    $rojos = $operaciones->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO CONCLUIDO','RECONOCIMIENTO ADUANERO'])->count();
    $verdes = $operaciones->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
    $sobrepesos = $operaciones->where('sobrepeso', true)->count();

    // 🔹 CALCULAR CONCEPTOS ADICIONALES DEL EXPEDIENTE
    $camiones = $operaciones->groupBy(function($op) {
        return $op->fecha . '_' . $op->num_thermo . '_' . $op->codigo_alpha;
    });
    
    $totalAdicionales = 0;
    $detalleAdicionales = [];
    
    foreach ($camiones as $operacionesCamion) {
        $conceptosCamion = $operacionesCamion->first()->conceptosAdicionales
            ->where('ambito', 'camion');
        
        foreach ($conceptosCamion as $concepto) {
            $totalAdicionales += $concepto->monto;
            $tipo = $concepto->tipo_concepto;
            if (!isset($detalleAdicionales[$tipo])) {
                $detalleAdicionales[$tipo] = [
                    'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                    'cantidad' => 0,
                    'monto' => 0
                ];
            }
            $detalleAdicionales[$tipo]['cantidad']++;
            $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
        }
    }
    
    foreach ($operaciones as $operacion) {
        $conceptosOperacion = $operacion->conceptosAdicionales
            ->where('ambito', 'operacion');
        
        foreach ($conceptosOperacion as $concepto) {
            $totalAdicionales += $concepto->monto;
            $tipo = $concepto->tipo_concepto;
            if (!isset($detalleAdicionales[$tipo])) {
                $detalleAdicionales[$tipo] = [
                    'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                    'cantidad' => 0,
                    'monto' => 0
                ];
            }
            $detalleAdicionales[$tipo]['cantidad']++;
            $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
        }
    }

    // Verificar si tiene factura con sus documentos
    $factura = Factura::where('pedimento_id', $expedienteId)
        ->where('year', $year)
        ->where('semana', $semana)
        ->with(['documentos'])
        ->first();

    return view('finanzas.detalle_expediente', compact(
        'expediente',
        'operaciones',
        'year',
        'semana',
        'fechaInicio',
        'fechaFin',
        'totalTramites',
        'rojos',
        'verdes',
        'sobrepesos',
        'totalAdicionales', // 🔹 Nuevo
        'detalleAdicionales', // 🔹 Nuevo
        'factura'
    ));
}

    public function detalleExpediente_NOjala(Request $request, $expedienteId)
{
    $year = $request->input('year', now()->year);
    $semana = $request->input('semana', now()->weekOfYear);

    // Calcular fechas de la semana
    $fechaInicio = Carbon::now()
        ->setISODate($year, $semana)
        ->startOfWeek(Carbon::MONDAY);

    $fechaFin = Carbon::now()
        ->setISODate($year, $semana)
        ->endOfWeek(Carbon::SUNDAY);

    // Obtener expediente
    $expediente = Expediente::findOrFail($expedienteId);

    // Obtener todas las operaciones del expediente con conceptos adicionales
    $operaciones = Operacion::with(['cliente', 'patente', 'bodega', 'importador', 'conceptosAdicionales'])
        ->where('pedimento_id', $expedienteId)
        //->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
        ->orderBy('fecha_registro', 'desc')
        ->get();

    // Estadísticas
    $totalTramites = $operaciones->count();
    
    $rojos = $operaciones->filter(function ($op) {
        return in_array($op->modulacion, [
            'RECONOCIMIENTO ADUANERO',
            'RECONOCIMIENTO ADUANERO CONCLUIDO'
        ]);
    })->count();
    
    $verdes = $operaciones->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
    $sobrepesos = $operaciones->where('sobrepeso', true)->count();

    // 🔹 CONTAR TARAS (Uso de bascula)
    $taras = $operaciones->flatMap->conceptosAdicionales
        ->where('tipo_concepto', 'Uso de bascula')
        ->count();

    // 🔹 CONTAR ADICIONALES (Excluyendo básculas)
    $cantidadAdicionales = $operaciones->flatMap->conceptosAdicionales
        ->where('tipo_concepto', '!=', 'Uso de bascula')
        ->count();

    // 🔹 CALCULAR CONCEPTOS ADICIONALES DEL EXPEDIENTE (Agrupados por camión)
    $camiones = $operaciones->groupBy(function ($op) {
        return $op->fecha . '_' . $op->num_thermo . '_' . $op->codigo_alpha;
    });

    $totalAdicionales = 0;
    $detalleAdicionales = [];

    // Procesar conceptos por camión
    foreach ($camiones as $operacionesCamion) {
        $conceptosCamion = $operacionesCamion->first()->conceptosAdicionales
            ->where('ambito', 'camion');

        foreach ($conceptosCamion as $concepto) {
            $totalAdicionales += $concepto->monto;
            $tipo = $concepto->tipo_concepto;
            
            if (!isset($detalleAdicionales[$tipo])) {
                $detalleAdicionales[$tipo] = [
                    'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                    'cantidad' => 0,
                    'monto' => 0
                ];
            }
            
            $detalleAdicionales[$tipo]['cantidad']++;
            $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
        }
    }

    // Procesar conceptos por operación
    foreach ($operaciones as $operacion) {
        $conceptosOperacion = $operacion->conceptosAdicionales
            ->where('ambito', 'operacion');

        foreach ($conceptosOperacion as $concepto) {
            $totalAdicionales += $concepto->monto;
            $tipo = $concepto->tipo_concepto;
            
            if (!isset($detalleAdicionales[$tipo])) {
                $detalleAdicionales[$tipo] = [
                    'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                    'cantidad' => 0,
                    'monto' => 0
                ];
            }
            
            $detalleAdicionales[$tipo]['cantidad']++;
            $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
        }
    }

    // Verificar si tiene factura con sus documentos
    $factura = Factura::where('pedimento_id', $expedienteId)
        ->where('year', $year)
        ->where('semana', $semana)
        ->with(['documentos'])
        ->first();

    return view('finanzas.detalle_expediente', compact(
        'expediente',
        'operaciones',
        'year',
        'semana',
        'fechaInicio',
        'fechaFin',
        'totalTramites',
        'rojos',
        'verdes',
        'sobrepesos',
        'taras',                    // 🔹 Cantidad de taras
        'cantidadAdicionales',      // 🔹 Cantidad de adicionales (sin básculas)
        'totalAdicionales',         // 🔹 Monto total de adicionales
        'detalleAdicionales',       // 🔹 Detalle por tipo
        'factura'
    ));
}

public function detalleExpediente(Request $request, $expedienteId)
    {
        $year = $request->input('year', now()->year);
        $semana = $request->input('semana', now()->weekOfYear);

        // Calcular fechas de la semana
        $fechaInicio = Carbon::now()
            ->setISODate($year, $semana)
            ->startOfWeek(Carbon::MONDAY);

        $fechaFin = Carbon::now()
            ->setISODate($year, $semana)
            ->endOfWeek(Carbon::SUNDAY);

        // Obtener expediente
        $expediente = Expediente::findOrFail($expedienteId);

        // Obtener todas las operaciones del expediente con conceptos adicionales
        $operaciones = Operacion::with(['cliente', 'patente', 'bodega', 'importador', 'conceptosAdicionales'])
            ->where('pedimento_id', $expedienteId)
            ->where('estado', '!=', 'cancelada')
            //->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_registro', 'desc')
            ->get();

        // Estadísticas
        $totalTramites = $operaciones->count();

        $rojos = $operaciones->filter(function ($op) {
            return in_array($op->modulacion, [
                'RECONOCIMIENTO ADUANERO',
                'RECONOCIMIENTO ADUANERO CONCLUIDO'
            ]);
        })->count();

        $verdes = $operaciones->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
        $sobrepesos = $operaciones->where('sobrepeso', true)->count();

        // 🔹 CONTAR TARAS (Uso de bascula)
        $taras = $operaciones->flatMap->conceptosAdicionales
            ->where('tipo_concepto', 'Uso de bascula')
            ->count();

        // 🔹 CONTAR ADICIONALES (Excluyendo básculas)
        /*$cantidadAdicionales = $operaciones->flatMap->conceptosAdicionales
            ->where('tipo_concepto', '!=', 'Uso de bascula')
            ->count();*/
        $cantidadAdicionales = $operaciones->flatMap->conceptosAdicionales
            ->whereNotIn('tipo_concepto', [
                'Uso de bascula',
                'Permiso de sobrepeso'
            ])
            ->count();


        // 🔹 CALCULAR CONCEPTOS ADICIONALES DEL EXPEDIENTE (Agrupados por camión)
        $camiones = $operaciones->groupBy(function ($op) {
            return $op->fecha . '_' . $op->num_thermo . '_' . $op->codigo_alpha;
        });

        $totalAdicionales = 0;
        $detalleAdicionales = [];

        // Procesar conceptos por camión
        foreach ($camiones as $operacionesCamion) {
            $conceptosCamion = $operacionesCamion->first()->conceptosAdicionales
                ->where('ambito', 'camion');

            foreach ($conceptosCamion as $concepto) {
                $totalAdicionales += $concepto->monto;
                $tipo = $concepto->tipo_concepto;

                if (!isset($detalleAdicionales[$tipo])) {
                    $detalleAdicionales[$tipo] = [
                        'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                        'cantidad' => 0,
                        'monto' => 0
                    ];
                }

                $detalleAdicionales[$tipo]['cantidad']++;
                $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
            }
        }

        // Procesar conceptos por operación
        foreach ($operaciones as $operacion) {
            $conceptosOperacion = $operacion->conceptosAdicionales
                ->where('ambito', 'operacion');

            foreach ($conceptosOperacion as $concepto) {
                $totalAdicionales += $concepto->monto;
                $tipo = $concepto->tipo_concepto;

                if (!isset($detalleAdicionales[$tipo])) {
                    $detalleAdicionales[$tipo] = [
                        'nombre' => ucfirst(str_replace('_', ' ', $tipo)),
                        'cantidad' => 0,
                        'monto' => 0
                    ];
                }

                $detalleAdicionales[$tipo]['cantidad']++;
                $detalleAdicionales[$tipo]['monto'] += $concepto->monto;
            }
        }

        // Verificar si tiene factura con sus documentos
        $factura = Factura::where('pedimento_id', $expedienteId)
            ->where('year', $year)
            ->where('semana', $semana)
            ->with(['documentos'])
            ->first();

        // 🔹 FILTRAR DETALLES ADICIONALES (Excluir "Uso de bascula")
        /*$detalleAdicionalesFiltrados = array_filter($detalleAdicionales, function ($tipo) {
            return $tipo !== 'Uso de bascula';
        }, ARRAY_FILTER_USE_KEY);*/
        $detalleAdicionalesFiltrados = array_filter($detalleAdicionales, function ($tipo) {
            return !in_array($tipo, [
                'Uso de bascula',
                'Permiso de sobrepeso'
            ]);
        }, ARRAY_FILTER_USE_KEY);


        return view('finanzas.detalle_expediente', compact(
            'expediente',
            'operaciones',
            'year',
            'semana',
            'fechaInicio',
            'fechaFin',
            'totalTramites',
            'rojos',
            'verdes',
            'sobrepesos',
            'taras',                    // 🔹 Cantidad de taras
            'cantidadAdicionales',      // 🔹 Cantidad de adicionales (sin básculas)
            'totalAdicionales',         // 🔹 Monto total de adicionales
            'detalleAdicionales',       // 🔹 Detalle por tipo
            'detalleAdicionalesFiltrados',
            'factura'
        ));
    }
    
    


    // ==================== GESTIÓN DE FACTURAS ====================

    /**
     * Guardar o actualizar factura de un expediente
     */
    public function guardarFactura(Request $request)
    {
        $request->validate([
            'pedimento_id' => 'required|exists:expedientes,id',
            'numero_factura' => 'required|string|max:255',
            'fecha_factura' => 'required|date',
            'monto_total' => 'nullable|numeric|min:0',
            'year' => 'required|integer',
            'semana' => 'required|integer|min:1|max:53',
            'cantidad_tramites' => 'required|integer|min:0',
            'cantidad_rojos' => 'required|integer|min:0',
            'cantidad_sobrepesos' => 'required|integer|min:0',
            'monto_adicionales' => 'nullable|numeric|min:0',
            'notas_adicionales' => 'nullable|string',
            'estado' => 'nullable|in:pendiente,facturada,pagada,complemento_pago',
        ]);

        DB::beginTransaction();
        try {
            $expediente = Expediente::findOrFail($request->pedimento_id);

            // Obtener cliente y patente desde la primera operación del expediente
            $primeraOperacion = Operacion::where('pedimento_id', $request->pedimento_id)
                ->first();

            if (!$primeraOperacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron operaciones para este expediente'
                ], 400);
            }

            // Buscar o crear factura
            $factura = Factura::where('pedimento_id', $request->pedimento_id)
                ->where('year', $request->year)
                ->where('semana', $request->semana)
                ->first();

            $datosFactura = [
                'pedimento_id' => $request->pedimento_id,
                'cliente_id' => $primeraOperacion->cliente_id,
                'patente_id' => $primeraOperacion->patente_id,
                'numero_factura' => $request->numero_factura,
                'fecha_factura' => $request->fecha_factura,
                'monto_total' => $request->monto_total,
                'year' => $request->year,
                'semana' => $request->semana,
                'cantidad_tramites' => $request->cantidad_tramites,
                'cantidad_rojos' => $request->cantidad_rojos,
                'cantidad_sobrepesos' => $request->cantidad_sobrepesos,
                'monto_adicionales' => $request->monto_adicionales ?? 0,
                'notas_adicionales' => $request->notas_adicionales,
                'registrado_por' => Auth::id(),
                'estado' => $request->estado ?? 'pendiente',
            ];

            if ($factura) {
                $factura->update($datosFactura);
                $mensaje = 'Factura actualizada correctamente';
            } else {
                $factura = Factura::create($datosFactura);
                $mensaje = 'Factura registrada correctamente';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'factura' => $factura->load('documentos')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subir documento a una factura (PDF, XML, complemento, etc.)
     */
    public function subirDocumentoFacturaOriginalpara1Archivo(Request $request)
    {
        $request->validate([
            'factura_id' => 'required|exists:facturas,id',
            'archivo' => 'required|file|mimes:pdf,xml|max:10240',
            'tipo_documento' => 'required|string|in:factura_pdf,factura_xml,complemento_pago_pdf,complemento_pago_xml',
        ]);

        DB::beginTransaction();
        try {
            $factura = Factura::findOrFail($request->factura_id);
            $expediente = $factura->expediente;

            // Generar nombre del archivo
            $extension = $request->file('archivo')->getClientOriginalExtension();
            $tipoDoc = str_replace(['factura_', 'complemento_pago_'], '', $request->tipo_documento);
            $nombreArchivo = "FACT_{$factura->numero_factura}_{$expediente->numero_pedimento}_{$tipoDoc}.{$extension}";

            // Guardar archivo
            $ruta = $request->file('archivo')->storeAs(
                //"facturas/{$factura->year}/semana_{$factura->semana}",
                "documentos",
                $nombreArchivo,
                //'public'
            );
            //$path = $file->store('documentos');

            // Crear registro en documentos
            $documento = Documento::create([
                'pedimento_id' => $factura->pedimento_id,
                'factura_id' => $factura->id,
                'nombre_documento' => $nombreArchivo,
                'ruta_archivo' => $ruta,
                'tipo_documento' => $request->tipo_documento,
                'fecha_documento' => $factura->fecha_factura,
                'observaciones' => "Documento de factura #{$factura->numero_factura}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Documento subido correctamente',
                'documento' => $documento
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al subir documento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function subirDocumentoFactura(Request $request)
    {
        \Log::info('=== INICIANDO SUBIDA MÚLTIPLE ===');
        \Log::info('Datos recibidos:', $request->all());
        \Log::info(
            'Archivos recibidos:',
            $request->hasFile('archivos') ?
            array_map(function ($file) {
                return $file->getClientOriginalName();
            }, $request->file('archivos')) : ['no_archivos' => true]
        );

        $request->validate([
            'factura_id' => 'required|exists:facturas,id',
            'archivos' => 'required|array|min:1',
            'archivos.*' => 'required|file|mimes:pdf,xml|max:10240',
            'tipos' => 'required|array|min:1',
            'tipos.*' => 'required|string|in:factura_pdf,factura_xml,complemento_pago_pdf,complemento_pago_xml',
        ]);

        // Validar que haya la misma cantidad de archivos y tipos
        if (count($request->file('archivos')) !== count($request->tipos)) {
            return response()->json([
                'success' => false,
                'message' => 'La cantidad de archivos no coincide con la cantidad de tipos'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $factura = Factura::findOrFail($request->factura_id);
            $expediente = $factura->expediente;
            $documentosCreados = [];

            foreach ($request->file('archivos') as $index => $archivo) {
                $tipoDocumento = $request->tipos[$index];
                $extension = $archivo->getClientOriginalExtension();
                $tipoDoc = str_replace(['factura_', 'complemento_pago_'], '', $tipoDocumento);

                // Generar nombre único
                $timestamp = now()->format('YmdHis');
                $nombreArchivo = "FACT_{$factura->numero_factura}_{$expediente->numero_pedimento}_{$tipoDoc}_{$timestamp}.{$extension}";

                // Guardar archivo
                $ruta = $archivo->storeAs("documentos", $nombreArchivo);

                // Crear registro en documentos
                $documento = Documento::create([
                    'pedimento_id' => $factura->pedimento_id,
                    'factura_id' => $factura->id,
                    'nombre_documento' => $nombreArchivo,
                    'ruta_archivo' => $ruta,
                    'tipo_documento' => $tipoDocumento,
                    'fecha_documento' => $factura->fecha_factura,
                    'observaciones' => "Documento de factura #{$factura->numero_factura}",
                ]);

                $documentosCreados[] = $documento;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($documentosCreados) . ' documento(s) subido(s) correctamente',
                'documentos' => $documentosCreados
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al subir documentos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al subir documentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar documento de factura
     */
    public function eliminarDocumentoFactura($documentoId)
    {
        try {
            $documento = Documento::findOrFail($documentoId);

            // Verificar que el documento pertenezca a una factura
            if (!$documento->factura_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este documento no pertenece a una factura'
                ], 400);
            }

            // Eliminar archivo físico
            if ($documento->ruta_archivo) {
                Storage::disk('public')->delete($documento->ruta_archivo);
            }

            $documento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar factura completa y todos sus documentos
     */
    public function eliminarFactura($facturaId)
    {
        DB::beginTransaction();
        try {
            $factura = Factura::findOrFail($facturaId);

            // Obtener y eliminar todos los documentos asociados
            $documentos = Documento::where('factura_id', $facturaId)->get();

            foreach ($documentos as $documento) {
                if ($documento->ruta_archivo) {
                    Storage::disk('public')->delete($documento->ruta_archivo);
                }
                $documento->delete();
            }

            // Eliminar factura
            $factura->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Factura y documentos eliminados correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de factura
     */
    public function cambiarEstadoFactura(Request $request, $facturaId)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,facturada,pagada,complemento_pago',
        ]);

        try {
            $factura = Factura::findOrFail($facturaId);
            $factura->estado = $request->estado;
            $factura->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'factura' => $factura
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar documento
     */
    public function descargarDocumento($documentoId)
    {
        $documento = Documento::findOrFail($documentoId);

        if (!$documento->ruta_archivo || !Storage::disk('public')->exists($documento->ruta_archivo)) {
            abort(404, 'Archivo no encontrado');
        }

        return Storage::disk('public')->download($documento->ruta_archivo, $documento->nombre_documento);
    }

    /**
     * Obtener factura con sus documentos (AJAX)
     */
    public function obtenerFactura($facturaId)
    {
        try {
            $factura = Factura::with(['documentos', 'expediente', 'cliente', 'patente'])
                ->findOrFail($facturaId);

            return response()->json([
                'success' => true,
                'factura' => $factura
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener factura: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Exportar a PDF - Resumen General
     */
    public function exportarPDF(Request $request)
    {
        $year = $request->input('year', now()->year);
        $semana = $request->input('semana', now()->weekOfYear);

        // Calcular fechas
        $fechaInicio = Carbon::now()
            ->setISODate($year, $semana)
            ->startOfWeek(Carbon::MONDAY);

        $fechaFin = Carbon::now()
            ->setISODate($year, $semana)
            ->endOfWeek(Carbon::SUNDAY);

        // Obtener datos
        $operaciones = Operacion::with(['cliente', 'patente', 'expediente'])
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
            ->get();

        $resumen = $operaciones->groupBy(function ($item) {
            return $item->cliente_id . '-' . $item->patente_id;
        })->map(function ($group) {
            $firstItem = $group->first();
            $rojos = $group->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count();
            $sobrepesos = $group->where('sobrepeso', true)->count();

            return [
                'cliente_nombre' => $firstItem->cliente->nombre_empresa ?? 'Sin Cliente',
                'patente_numero' => $firstItem->patente->numero_patente ?? 'Sin Patente',
                'total_tramites' => $group->count(),
                'rojos' => $rojos,
                'sobrepesos' => $sobrepesos,
            ];
        })->values();

        $pdf = Pdf::loadView('finanzas.pdf_resumen', compact(
            'resumen',
            'year',
            'semana',
            'fechaInicio',
            'fechaFin'
        ));

        return $pdf->download("resumen_semana_{$semana}_{$year}.pdf");
    }

    /**
     * Exportar a PDF - Detalle Cliente-Patente
     */
    public function exportarDetalleClientePatentePDF(Request $request, $clienteId, $patenteId)
    {
        $year = $request->input('year', now()->year);
        $semana = $request->input('semana', now()->weekOfYear);

        $fechaInicio = Carbon::now()
            ->setISODate($year, $semana)
            ->startOfWeek(Carbon::MONDAY);

        $fechaFin = Carbon::now()
            ->setISODate($year, $semana)
            ->endOfWeek(Carbon::SUNDAY);

        $cliente = Cliente::findOrFail($clienteId);
        $patente = Patente::findOrFail($patenteId);

        $operaciones = Operacion::with(['expediente'])
            ->where('cliente_id', $clienteId)
            ->where('patente_id', $patenteId)
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
            ->get();

        $expedientes = $operaciones->groupBy('pedimento_id')->map(function ($group) {
            $expediente = $group->first()->expediente;
            $rojos = $group->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count();
            $sobrepesos = $group->where('sobrepeso', true)->count();

            return [
                'expediente_numero' => $expediente->numero_pedimento ?? 'Sin Expediente',
                'fecha_apertura' => $expediente->fecha_apertura ?? null,
                'fecha_cierre' => $expediente->fecha_cierre ?? null,
                'total_tramites' => $group->count(),
                'rojos' => $rojos,
                'sobrepesos' => $sobrepesos,
            ];
        })->values();

        $pdf = Pdf::loadView('finanzas.pdf_detalle_cliente_patente', compact(
            'cliente',
            'patente',
            'expedientes',
            'year',
            'semana',
            'fechaInicio',
            'fechaFin'
        ));

        return $pdf->download("detalle_{$cliente->nombre_empresa}_patente_{$patente->numero_patente}_semana_{$semana}_{$year}.pdf");
    }
    /**
     * Mostrar datos de Factura
     */
    public function show($id)
    {
        // Método 1: Con findOrFail
        $factura = Factura::with(['cliente', 'expediente', 'registradoPor'])->findOrFail($id);
        
        // DEBUG: Verifica que los datos lleguen
        // dd($factura); // Descomenta esto temporalmente
        
        return view('finanzas.show', compact('factura'));
    }
    
    public function modalModulacion($id)
    {
        // dd("ID recibido en modal:", $id);
        $first = Operacion::with(['cliente', 'expediente', 'patente'])->findOrFail($id);

        $registros = Operacion::where('num_thermo', $first->num_thermo)
            ->where('codigo_alpha', $first->codigo_alpha)
            ->where('fecha_registro', $first->fecha)
            ->where('pedimento_id', $first->pedimento_id)
            ->get();

        $estado = ucfirst($first->modulacion ?? 'Sin Modulación');
        $color = match (strtoupper($first->modulacion ?? '')) {
            'DESADUANAMIENTO LIBRE' => 'green',
            'RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO' => 'red',
            default => 'muted'
        };

        $html = view('finanzas.modals.modulacion', compact('registros', 'first', 'estado', 'color'))->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    public function printModulacion($id)
    {
        // dd("ID recibido en print:", $id);
        $first = Operacion::with(['cliente', 'expediente', 'patente'])->findOrFail($id);

        $registros = Operacion::where('num_thermo', $first->num_thermo)
            ->where('codigo_alpha', $first->codigo_alpha)
            ->where('fecha_registro', $first->fecha)
            ->where('pedimento_id', $first->pedimento_id)
            ->get();

        $estado = ucfirst($first->modulacion ?? 'Sin Modulacion');
        $color  = match (strtoupper($first->modulacion ?? '')) {
            'DESADUANAMIENTO LIBRE' => 'green',
            'RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO' => 'red',
            default => 'muted'
        };

        return view('finanzas.modals.modulacion_print', compact('registros', 'first', 'estado', 'color'));
    }
    
    
    
    
    
    
    
}
