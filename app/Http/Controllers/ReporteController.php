<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Operacion;
use App\Models\Cliente;
use Carbon\Carbon;
use DB;
use App\Models\Expediente;
use App\Models\ConceptoAdicional;
use App\Models\Aduana;

class ReporteController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }
    //
    public function index()
    {
        $tenant = auth()->user()->tenant;
        $enabledReports = $tenant ? $tenant->getEnabledReports() : [];
        $allReports = $tenant ? \App\Models\Tenant::getAllAvailableReports() : [];

        return view('reportes.index', compact('allReports', 'enabledReports'));
    }

    /**
     * Muestra la página de upgrade cuando un usuario intenta acceder a un reporte no disponible
     */
    public function upgrade($reporte = null)
    {
        $tenant = auth()->user()->tenant;
        $allReports = \App\Models\Tenant::getAllAvailableReports();

        // Si no se especifica un reporte, mostrar todos los disponibles
        $reportInfo = null;
        if ($reporte && isset($allReports[$reporte])) {
            $reportInfo = $allReports[$reporte];
        }

        return view('reportes.upgrade', compact('allReports', 'reportInfo'));
    }
    public function tramitesAnuales(Request $request)
    {
        $anio = $request->get('anio', now()->year);

        //Agrupar por mes
        $tramites = Operacion::where('tenant_id', auth()->user()->tenant_id)->select(
            DB::raw('MONTH(fecha_cruce_estimada) as mes'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('fecha_cruce_estimada', $anio)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        //Arreglo de 12 meses para llenar (Aunque no haya datos)
        $data = array_fill(1, 12, 0);
        foreach ($tramites as $t) {
            $data[$t->mes] = $t->total;
        }
        return view('reportes.tramites-anuales', [
            'anio' => $anio,
            'data' => $data
        ]);



    }
    public function tramitesComparativos(Request $request)
    {
        // Años que pide el usuario
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        if (!$desde || !$hasta) {
            // Si no envió nada, tomar últimos 4 años
            $hasta = now()->year;
            $desde = $hasta - 3; // 4 años en total
        }
        // Años a comparar 
        $years = range($desde, $hasta);

        // Base array
        $months = range(1, 12);
        $data = [];
        $yearTotals = [];
        foreach ($years as $year) {
            $data[$year] = array_fill(1, 12, 0);
            $yearTotals[$year] = 0;
        }

        // Consultar todos los años en un solo query
        $tramites = Operacion::where('tenant_id', auth()->user()->tenant_id)->select(
            DB::raw('YEAR(fecha_cruce_estimada) as anio'),
            DB::raw('MONTH(fecha_cruce_estimada) as mes'),
            DB::raw('COUNT(*) as total')
        )
            ->whereIn(DB::raw('YEAR(fecha_cruce_estimada)'), $years)
            ->groupBy('anio', 'mes')
            ->get();

        foreach ($tramites as $t) {
            $data[$t->anio][$t->mes] = $t->total;
            $yearTotals[$t->anio] += $t->total;
        }

        $grandTotal = array_sum($yearTotals);

        return view('reportes.tramites-comparativos', [
            'years' => $years,
            'months' => $months,
            'data' => $data,
            'yearTotals' => $yearTotals,
            'grandTotal' => $grandTotal,
            'desde' => $desde,
            'hasta' => $hasta
        ]);
    }

    public function reporteCliente_OLD(Request $request)
    {
        $clienteId = $request->input('cliente_id');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $clientes = Cliente::all();

        // Si no hay cliente seleccionado, no mostrar nada todavía
        if (!$clienteId) {
            return view('reportes.reporte-cliente', compact('clientes', 'clienteId', 'desde', 'hasta'));
        }

        $cliente = Cliente::findOrFail($clienteId);

        // Fechas por defecto
        if (!$desde || !$hasta) {
            $hasta = now()->format('Y-m-d');
            $desde = now()->subYear()->format('Y-m-d');
        }

        // Totales modulaciones
        $total = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->count();

        $greens = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->where('modulacion', 'DESADUANAMIENTO LIBRE')
            ->count();

        $reds = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')
            ->count();

        // Por aduana
        /*
         $porAduana = Operacion::where('tenant_id', auth()->user()->tenant_id)->select('aduana_id', DB::raw('count(*) as total'))
         ->where('cliente_id', $clienteId)
         ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
         ->groupBy('aduana_id')->get();*/
        $porAduana = Operacion::where('tenant_id', auth()->user()->tenant_id)->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
            ->select('aduanas.nombre as nombre', DB::raw('count(*) as total'))
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->groupBy('aduanas.nombre')
            ->get();

        // Desglose por aduana
        $verdesPorAduana = Operacion::where('tenant_id', auth()->user()->tenant_id)->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
            ->select('aduanas.nombre as aduana', DB::raw('count(*) as total'))
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->where('modulacion', 'DESADUANAMIENTO LIBRE')
            ->groupBy('aduanas.nombre')
            ->get();

        $rojosPorAduana = Operacion::where('tenant_id', auth()->user()->tenant_id)->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
            ->select('aduanas.nombre as aduana', DB::raw('count(*) as total'))
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')
            ->groupBy('aduanas.nombre')
            ->get();

        // Histórico por mes
        $historial = Operacion::where('tenant_id', auth()->user()->tenant_id)->select(
            DB::raw('YEAR(fecha_cruce_estimada) as anio'),
            DB::raw('MONTH(fecha_cruce_estimada) as mes'),
            DB::raw('count(*) as total')
        )
            ->where('cliente_id', $clienteId)
            //->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->whereBetween('fecha_cruce_estimada', [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear()
            ])
            ->groupBy('anio', 'mes')->get();

        // Organizarlo para la gráfica
        $meses = range(1, 12);
        $historialMeses = [];
        foreach ($meses as $m) {
            $historialMeses[$m] = $historial->where('mes', $m)->sum('total');
        }

        return view('reportes.reporte-cliente', compact(
            'clientes',
            'clienteId',
            'cliente',
            'desde',
            'hasta',
            'total',
            'greens',
            'reds',
            'porAduana',
            'historialMeses',
            'verdesPorAduana',
            'rojosPorAduana'
        ));
    }

    public function reporteCliente(Request $request)
    {
        $clienteId = $request->input('cliente_id');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        //$clientes = Cliente::all();
        $clientes = Cliente::orderBy('nombre')->get();


        // Si no hay cliente seleccionado, no mostrar nada todavía
        if (!$clienteId) {
            return view('reportes.reporte-cliente', compact('clientes', 'clienteId', 'desde', 'hasta'));
        }

        $cliente = Cliente::findOrFail($clienteId);

        // Fechas por defecto
        if (!$desde || !$hasta) {
            $hasta = now()->format('Y-m-d');
            $desde = now()->subYear()->format('Y-m-d');
        }

        // Totales modulaciones
        $total = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->count();

        $greens = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->where('modulacion', 'DESADUANAMIENTO LIBRE')
            ->count();

        $reds = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')
            ->count();

        // Por aduana
        /*
         $porAduana = Operacion::where('tenant_id', auth()->user()->tenant_id)->select('aduana_id', DB::raw('count(*) as total'))
         ->where('cliente_id', $clienteId)
         ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
         ->groupBy('aduana_id')->get();*/
        $porAduana = Operacion::where('tenant_id', auth()->user()->tenant_id)->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
            ->select('aduanas.nombre as nombre', DB::raw('count(*) as total'))
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->groupBy('aduanas.nombre')
            ->get();

        // Desglose por aduana
        $verdesPorAduana = Operacion::where('tenant_id', auth()->user()->tenant_id)->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
            ->select('aduanas.nombre as aduana', DB::raw('count(*) as total'))
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->where('modulacion', 'DESADUANAMIENTO LIBRE')
            ->groupBy('aduanas.nombre')
            ->get();

        $rojosPorAduana = Operacion::where('tenant_id', auth()->user()->tenant_id)->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
            ->select('aduanas.nombre as aduana', DB::raw('count(*) as total'))
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')
            ->groupBy('aduanas.nombre')
            ->get();

        // Histórico por mes
        $historial = Operacion::where('tenant_id', auth()->user()->tenant_id)->select(
            DB::raw('YEAR(fecha_cruce_estimada) as anio'),
            DB::raw('MONTH(fecha_cruce_estimada) as mes'),
            DB::raw('count(*) as total')
        )
            ->where('cliente_id', $clienteId)
            //->whereBetween('fecha_cruce_estimada', [$desde, $hasta])
            ->whereBetween('fecha_cruce_estimada', [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear()
            ])
            ->groupBy('anio', 'mes')->get();

        // Organizarlo para la gráfica
        $meses = range(1, 12);
        $historialMeses = [];
        foreach ($meses as $m) {
            $historialMeses[$m] = $historial->where('mes', $m)->sum('total');
        }

        // ===============================
// NUEVA SECCIÓN: Trámites por día
// ===============================

        // Periodo seleccionado (mes actual por defecto)
        $periodo = $request->input('periodo', 'actual');

        if ($periodo === 'anterior') {
            $inicioPeriodo = Carbon::now()->subMonth()->startOfMonth();
            $finPeriodo = Carbon::now()->subMonth()->endOfMonth();
        } else {
            $inicioPeriodo = Carbon::now()->startOfMonth();
            $finPeriodo = Carbon::now()->endOfMonth();
        }

        // Obtener conteo real por día
        $rawPorDia = Operacion::where('tenant_id', auth()->user()->tenant_id)->select(
            DB::raw('DATE(fecha_cruce_estimada) as fecha'),
            DB::raw('count(*) as total')
        )
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$inicioPeriodo, $finPeriodo])
            ->groupBy(DB::raw('DATE(fecha_cruce_estimada)'))
            ->pluck('total', 'fecha');

        // Generar todos los días del mes (aunque no tengan trámites)
        $tramitesPorDia = [];

        $cursor = $inicioPeriodo->copy();
        while ($cursor <= $finPeriodo) {
            $fecha = $cursor->format('Y-m-d');

            $tramitesPorDia[] = [
                'fecha_cruce_estimada' => $fecha,
                'total' => $rawPorDia[$fecha] ?? 0
            ];

            $cursor->addDay();
        }
        // ===============================
// NUEVA SECCIÓN: Calendario mensual
// ===============================

        $mesCalendario = $request->input('mes_calendario', now()->format('Y-m'));

        $inicioMes = Carbon::createFromFormat('Y-m', $mesCalendario)->startOfMonth();
        $finMes = Carbon::createFromFormat('Y-m', $mesCalendario)->endOfMonth();

        // Conteo real por día
        $rawCalendario = Operacion::where('tenant_id', auth()->user()->tenant_id)->select(
            DB::raw('DATE(fecha_cruce_estimada) as fecha'),
            DB::raw('count(*) as total')
        )
            ->where('cliente_id', $clienteId)
            ->whereBetween('fecha_cruce_estimada', [$inicioMes, $finMes])
            ->groupBy(DB::raw('DATE(fecha_cruce_estimada)'))
            ->pluck('total', 'fecha');

        // Generar estructura tipo calendario (semanas)
        $calendario = [];
        $inicioCalendario = $inicioMes->copy()->startOfWeek(Carbon::MONDAY);
        $finCalendario = $finMes->copy()->endOfWeek(Carbon::SUNDAY);

        $cursor = $inicioCalendario->copy();

        while ($cursor <= $finCalendario) {
            $semana = [];

            for ($i = 0; $i < 7; $i++) {
                $fecha = $cursor->format('Y-m-d');

                $semana[] = [
                    'fecha' => $fecha,
                    'dia' => $cursor->day,
                    'mes' => $cursor->month,
                    'total' => $rawCalendario[$fecha] ?? 0,
                    'actual' => $cursor->month === $inicioMes->month
                ];

                $cursor->addDay();
            }

            $calendario[] = $semana;
        }



        return view('reportes.reporte-cliente', compact(
            'clientes',
            'clienteId',
            'cliente',
            'desde',
            'hasta',
            'total',
            'greens',
            'reds',
            'porAduana',
            'historialMeses',
            'verdesPorAduana',
            'rojosPorAduana',
            'tramitesPorDia',
            'periodo',
            'calendario',
            'mesCalendario'

        ));
    }


    public function operacionesDiarias_old(Request $request)
    {
        $fecha = $request->input('fecha_cruce_estimada', now()->format('Y-m-d'));
        $fechaCarbon = Carbon::parse($fecha);

        // Operaciones del día
        $operacionesHoy = Operacion::where('tenant_id', auth()->user()->tenant_id)->with(['cliente', 'aduana'])
            ->whereDate('fecha_cruce_estimada', $fechaCarbon)
            ->get();

        // Estadísticas generales
        $totalClientes = $operacionesHoy->pluck('cliente_id')->unique()->count();
        $totalRemesas = $operacionesHoy->count();
        $completadas = $operacionesHoy->where('estado', 'terminado')->count();
        $pendientes = $operacionesHoy->whereIn('estado', ['pendiente', 'proceso'])->count();

        // Progreso del día
        $progresoDelDia = $totalRemesas > 0 ? round(($completadas / $totalRemesas) * 100, 2) : 0;

        // Modulación (Verdes/Rojos)
        $verdes = $operacionesHoy->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
        $rojos = $operacionesHoy->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count();
        $porcentajeVerdes = $totalRemesas > 0 ? round(($verdes / $totalRemesas) * 100, 2) : 0;

        // Exportadores del día (agrupados)
        $exportadoresData = $operacionesHoy->groupBy('cliente_id')->map(function ($grupo) {
            $cliente = $grupo->first()->cliente;
            return [
                'nombre' => $cliente->nombre ?? 'Sin nombre',
                'cantidad' => $grupo->count(),
                'rojos' => $grupo->where('modulacion', 'RECONOCIMIENTO ADUANERO CONCLUIDO')->count(),
                'verdes' => $grupo->where('modulacion', 'DESADUANAMIENTO LIBRE')->count(),
                'completadas' => $grupo->where('estado', 'terminado')->count(),
            ];
        })->sortByDesc('cantidad')->values();

        // Aduanas del día
        $aduanasData = $operacionesHoy->groupBy('aduana_id')->map(function ($grupo) {
            $aduana = $grupo->first()->aduana;
            return [
                'nombre' => $aduana->nombre ?? 'Sin aduana',
                'cantidad' => $grupo->count(),
            ];
        })->sortByDesc('cantidad');

        // Ciudades (Laredo/Reynosa) - asumiendo que está en la aduana
        $ciudadesData = $operacionesHoy->groupBy(function ($exp) {
            return $exp->aduana->nombre ?? 'Otra';
        })->map(function ($grupo, $ciudad) {
            return [
                'ciudad' => $ciudad,
                'cantidad' => $grupo->count(),
                'porcentaje' => 0 // se calculará en la vista
            ];
        });

        // Calcular porcentajes de ciudades
        if ($totalRemesas > 0) {
            $ciudadesData = $ciudadesData->map(function ($item) use ($totalRemesas) {
                $item['porcentaje'] = round(($item['cantidad'] / $totalRemesas) * 100, 1);
                return $item;
            });
        }

        // Estado de DODAS
        $finalizadas = $operacionesHoy->where('estado', 'terminado')->count();
        $totalDia = $totalRemesas;

        // Detenidas hasta el momento (puedes agregar lógica específica)
        $detenidas = 0; // Ajusta según tu lógica

        // Pedimentos por aduana (para el día siguiente)
        $pedimentosProximos = Operacion::where('tenant_id', auth()->user()->tenant_id)->with('aduana')
            ->whereDate('fecha_cruce_estimada', $fechaCarbon->copy()->addDay())
            ->get()
            ->groupBy('aduana_id')
            ->map(function ($grupo) {
                return [
                    'aduana' => $grupo->first()->aduana->nombre ?? 'Sin aduana',
                    'cantidad' => $grupo->count()
                ];
            });

        return view('reportes.operaciones-diarias', compact(
            'fecha_cruce_estimada',
            'fechaCarbon',
            'totalClientes',
            'totalRemesas',
            'completadas',
            'pendientes',
            'progresoDelDia',
            'verdes',
            'rojos',
            'porcentajeVerdes',
            'exportadoresData',
            'aduanasData',
            'ciudadesData',
            'finalizadas',
            'totalDia',
            'detenidas',
            'pedimentosProximos',
            'operacionesHoy'
        ));
    }
    public function operacionesDiariasApi_old(Request $request)
    {
        $fecha = $request->input('fecha_cruce_estimada', now()->format('Y-m-d'));
        $fechaCarbon = Carbon::parse($fecha);

        // Operaciones del día
        $operacionesHoy = Operacion::where('tenant_id', auth()->user()->tenant_id)->with(['cliente', 'aduana'])
            ->whereDate('fecha_cruce_estimada', $fechaCarbon)
            ->get();

        // Estadísticas generales
        $totalClientes = $operacionesHoy->pluck('cliente_id')->unique()->count();
        $totalRemesas = $operacionesHoy->count();
        $completadas = $operacionesHoy->where('estado', 'terminado')->count();
        $pendientes = $operacionesHoy->whereIn('estado', ['pendiente', 'proceso'])->count();

        // Progreso del día
        $progresoDelDia = $totalRemesas > 0 ? round(($completadas / $totalRemesas) * 100, 2) : 0;

        // Modulación (Verdes/Rojos)
        $verdes = $operacionesHoy->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
        $rojos = $operacionesHoy->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO'])->count();
        $porcentajeVerdes = $totalRemesas > 0 ? round(($verdes / $totalRemesas) * 100, 2) : 0;

        // Exportadores del día (agrupados)
        $exportadoresData = $operacionesHoy->groupBy('cliente_id')->map(function ($grupo) {
            $cliente = $grupo->first()->cliente;
            return [
                'nombre' => $cliente->nombre ?? 'Sin nombre',
                'cantidad' => $grupo->count(),
                'rojos' => $grupo->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO'])->count(),
                'verdes' => $grupo->where('modulacion', 'DESADUANAMIENTO LIBRE')->count(),
                'completadas' => $grupo->where('estado', 'terminado')->count(),
            ];
        })->sortByDesc('cantidad')->values();

        // Ciudades (Laredo/Reynosa)
        $ciudadesData = $operacionesHoy->groupBy(function ($exp) {
            return $exp->aduana->nombre ?? 'Otra';
        })->map(function ($grupo, $ciudad) use ($totalRemesas) {
            $cantidad = $grupo->count();
            return [
                'ciudad' => $ciudad,
                'cantidad' => $cantidad,
                'porcentaje' => $totalRemesas > 0 ? round(($cantidad / $totalRemesas) * 100, 1) : 0
            ];
        })->values();

        // Estado de DODAS
        $finalizadas = $operacionesHoy->where('estado', 'terminado')->count();
        $totalDia = $totalRemesas;
        $detenidas = 0;

        // Pedimentos por aduana (para el día siguiente)
        $pedimentosProximos = Operacion::where('tenant_id', auth()->user()->tenant_id)->with('aduana')
            ->whereDate('fecha_cruce_estimada', $fechaCarbon->copy()->addDay())
            ->get()
            ->groupBy('aduana_id')
            ->map(function ($grupo) {
                return [
                    'aduana' => $grupo->first()->aduana->nombre ?? 'Sin aduana',
                    'cantidad' => $grupo->count()
                ];
            })->values();

        return response()->json([
            'totalClientes' => $totalClientes,
            'totalRemesas' => $totalRemesas,
            'completadas' => $completadas,
            'pendientes' => $pendientes,
            'progresoDelDia' => $progresoDelDia,
            'verdes' => $verdes,
            'rojos' => $rojos,
            'porcentajeVerdes' => $porcentajeVerdes,
            'exportadoresData' => $exportadoresData,
            'ciudadesData' => $ciudadesData,
            'finalizadas' => $finalizadas,
            'totalDia' => $totalDia,
            'detenidas' => $detenidas,
            'pedimentosProximos' => $pedimentosProximos,
        ]);
    }
    public function operacionesDiarias_OLDDD(Request $request)
    {
        $fecha = $request->input('fecha_cruce_estimada', now()->format('Y-m-d'));
        $fechaCarbon = Carbon::parse($fecha);

        // Operaciones del día
        $operacionesHoy = Operacion::where('tenant_id', auth()->user()->tenant_id)->with(['cliente', 'aduana'])
            ->whereDate('fecha_cruce_estimada', $fechaCarbon)
            ->get();

        // Estadísticas generales
        $totalClientes = $operacionesHoy->pluck('cliente_id')->unique()->count();
        $totalRemesas = $operacionesHoy->count();
        $completadas = $operacionesHoy->where('estado', 'terminado')->count();
        $pendientes = $operacionesHoy->whereIn('estado', ['pendiente', 'proceso'])->count();

        // Total de camiones (agrupados por codigo_alpha y num_thermo)
        $totalCamiones = $operacionesHoy
            ->filter(function ($exp) {
                // Filtrar solo registros que tengan ambos valores
                return !empty($exp->codigo_alpha) && !empty($exp->num_thermo);
            })
            ->groupBy(function ($exp) {
                // Agrupar por la combinación de alpha + thermo
                return $exp->codigo_alpha . '-' . $exp->num_thermo;
            })
            ->count();

        // Progreso del día
        $progresoDelDia = $totalRemesas > 0 ? round(($completadas / $totalRemesas) * 100, 2) : 0;

        // Modulación (Verdes/Rojos)
        $verdes = $operacionesHoy->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
        $rojos = $operacionesHoy->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO'])->count();
        $porcentajeVerdes = $totalRemesas > 0 ? round(($verdes / $totalRemesas) * 100, 2) : 0;

        // Exportadores del día (agrupados) - MODIFICADO PARA INCLUIR PENDIENTES
        $exportadoresData = $operacionesHoy->groupBy('cliente_id')->map(function ($grupo) {
            $cliente = $grupo->first()->cliente;

            // Obtener trámites pendientes o en proceso para este exportador
            $tramitesPendientes = $grupo->whereIn('estado', ['pendiente', 'proceso'])->map(
                function ($exp) {
                    return [
                        'referencia' => $exp->referencia ?? $exp->numero_pedimento ?? 'REF-' . $exp->id,
                        'estado' => ucfirst($exp->estado),
                        'aduana' => $exp->aduana->nombre ?? 'N/A'
                    ];
                }
            )->values()->toArray();

            return [
                'nombre' => $cliente->nombre ?? 'Sin nombre',
                'cantidad' => $grupo->count(),
                'rojos' => $grupo->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO'])->count(),
                'verdes' => $grupo->where('modulacion', 'DESADUANAMIENTO LIBRE')->count(),
                'completadas' => $grupo->where('estado', 'terminado')->count(),
                'pendientes' => $tramitesPendientes, // NUEVO CAMPO
            ];
        })->sortByDesc('cantidad')->values();

        // Aduanas del día
        $aduanasData = $operacionesHoy->groupBy('aduana_id')->map(function ($grupo) {
            $aduana = $grupo->first()->aduana;
            return [
                'nombre' => $aduana->nombre ?? 'Sin aduana',
                'cantidad' => $grupo->count(),
            ];
        })->sortByDesc('cantidad');

        // Ciudades (Laredo/Reynosa) - asumiendo que está en la aduana
        $ciudadesData = $operacionesHoy->groupBy(function ($exp) {
            return $exp->aduana->nombre ?? 'Otra';
        })->map(function ($grupo, $ciudad) {
            return [
                'ciudad' => $ciudad,
                'cantidad' => $grupo->count(),
                'porcentaje' => 0 // se calculará después
            ];
        });

        // Calcular porcentajes de ciudades
        if ($totalRemesas > 0) {
            $ciudadesData = $ciudadesData->map(function ($item) use ($totalRemesas) {
                $item['porcentaje'] = round(($item['cantidad'] / $totalRemesas) * 100, 1);
                return $item;
            });
        }

        // Estado de DODAS
        $finalizadas = $operacionesHoy->where('estado', 'terminado')->count();
        $totalDia = $totalRemesas;

        // Detenidas hasta el momento (puedes agregar lógica específica)
        $detenidas = 0; // Ajusta según tu lógica

        // Pedimentos por aduana (para el día siguiente)
        $pedimentosProximos = Operacion::where('tenant_id', auth()->user()->tenant_id)->with('aduana')
            ->whereDate('fecha_cruce_estimada', $fechaCarbon->copy()->addDay())
            ->get()
            ->groupBy('aduana_id')
            ->map(function ($grupo) {
                return [
                    'aduana' => $grupo->first()->aduana->nombre ?? 'Sin aduana',
                    'cantidad' => $grupo->count()
                ];
            });

        // Obtener total de camiones unicos
        $camiones = $operacionesHoy
            ->filter(function ($exp) {
                return !empty($exp->codigo_alpha) && !empty($exp->num_thermo);
            })
            ->groupBy(function ($exp) {
                return $exp->codigo_alpha . '-' . $exp->num_thermo;
            });

        $totalCamiones2 = $camiones->count();

        // Camiones Verdes
        $camionesVerdes = $camiones->filter(function ($grupo) {
            return $grupo->contains(
                function ($exp) {
                    return $exp->modulacion === 'DESADUANAMIENTO LIBRE';
                }
            );
        })->count();

        //Camiones Rojos
        $camionesRojos = $camiones->filter(function ($grupo) {
            return $grupo->contains(
                function ($exp) {
                    return in_array($exp->modulacion, [
                        'RECONOCIMIENTO ADUANERO',
                        'RECONOCIMIENTO ADUANERO CONCLUIDO'
                    ]);
                }
            );
        })->count();



        return view('reportes.operaciones-diarias', compact(
            'fecha_cruce_estimada',
            'fechaCarbon',
            'totalClientes',
            'totalRemesas',
            'totalCamiones', // NUEVO
            'completadas',
            'pendientes',
            'progresoDelDia',
            'verdes',
            'rojos',
            'porcentajeVerdes',
            'exportadoresData',
            'aduanasData',
            'ciudadesData',
            'finalizadas',
            'totalDia',
            'detenidas',
            'pedimentosProximos',
            'operacionesHoy',
            'totalCamiones2',
            'camionesVerdes',
            'camionesRojos',

        ));
    }

    public function operacionesDiariasApi_OLDDD(Request $request)
    {
        $fecha = $request->input('fecha_cruce_estimada', now()->format('Y-m-d'));
        $fechaCarbon = Carbon::parse($fecha);

        // Operaciones del día
        $operacionesHoy = Operacion::where('tenant_id', auth()->user()->tenant_id)->with(['cliente', 'aduana'])
            ->whereDate('fecha_cruce_estimada', $fechaCarbon)
            ->get();

        // Estadísticas generales
        $totalClientes = $operacionesHoy->pluck('cliente_id')->unique()->count();
        $totalRemesas = $operacionesHoy->count();
        $completadas = $operacionesHoy->where('estado', 'terminado')->count();
        $pendientes = $operacionesHoy->whereIn('estado', ['pendiente', 'proceso'])->count();

        // Total de camiones (agrupados por codigo_alpha y num_thermo)
        $totalCamiones = $operacionesHoy
            ->filter(function ($exp) {
                return !empty($exp->codigo_alpha) && !empty($exp->num_thermo);
            })
            ->groupBy(function ($exp) {
                return $exp->codigo_alpha . '-' . $exp->num_thermo;
            })
            ->count();

        // Progreso del día
        $progresoDelDia = $totalRemesas > 0 ? round(($completadas / $totalRemesas) * 100, 2) : 0;

        // Modulación (Verdes/Rojos)
        $verdes = $operacionesHoy->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
        $rojos = $operacionesHoy->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO'])->count();
        $porcentajeVerdes = $totalRemesas > 0 ? round(($verdes / $totalRemesas) * 100, 2) : 0;

        // Exportadores del día (agrupados) - MODIFICADO PARA INCLUIR PENDIENTES
        $exportadoresData = $operacionesHoy->groupBy('cliente_id')->map(function ($grupo) {
            $cliente = $grupo->first()->cliente;

            // Obtener trámites pendientes o en proceso para este exportador
            $tramitesPendientes = $grupo->whereIn('estado', ['pendiente', 'proceso'])->map(
                function ($exp) {
                    return [
                        'referencia' => $exp->referencia ?? $exp->numero_pedimento ?? 'REF-' . $exp->id,
                        'estado' => ucfirst($exp->estado),
                        'aduana' => $exp->aduana->nombre ?? 'N/A'
                    ];
                }
            )->values()->toArray();

            return [
                'nombre' => $cliente->nombre ?? 'Sin nombre',
                'cantidad' => $grupo->count(),
                'rojos' => $grupo->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO'])->count(),
                'verdes' => $grupo->where('modulacion', 'DESADUANAMIENTO LIBRE')->count(),
                'completadas' => $grupo->where('estado', 'terminado')->count(),
                'pendientes' => $tramitesPendientes, // NUEVO CAMPO
            ];
        })->sortByDesc('cantidad')->values();

        // Ciudades (Laredo/Reynosa)
        $ciudadesData = $operacionesHoy->groupBy(function ($exp) {
            return $exp->aduana->nombre ?? 'Otra';
        })->map(function ($grupo, $ciudad) use ($totalRemesas) {
            $cantidad = $grupo->count();
            return [
                'ciudad' => $ciudad,
                'cantidad' => $cantidad,
                'porcentaje' => $totalRemesas > 0 ? round(($cantidad / $totalRemesas) * 100, 1) : 0
            ];
        })->values();

        // Estado de DODAS
        $finalizadas = $operacionesHoy->where('estado', 'terminado')->count();
        $totalDia = $totalRemesas;
        $detenidas = 0;

        // Pedimentos por aduana (para el día siguiente)
        $pedimentosProximos = Operacion::where('tenant_id', auth()->user()->tenant_id)->with('aduana')
            ->whereDate('fecha_cruce_estimada', $fechaCarbon->copy()->addDay())
            ->get()
            ->groupBy('aduana_id')
            ->map(function ($grupo) {
                return [
                    'aduana' => $grupo->first()->aduana->nombre ?? 'Sin aduana',
                    'cantidad' => $grupo->count()
                ];
            })->values();

        // Obtener total de camiones unicos
        $camiones = $operacionesHoy
            ->filter(function ($exp) {
                return !empty($exp->codigo_alpha) && !empty($exp->num_thermo);
            })
            ->groupBy(function ($exp) {
                return $exp->codigo_alpha . '-' . $exp->num_thermo;
            });

        $totalCamiones2 = $camiones->count();

        // Camiones Verdes
        $camionesVerdes = $camiones->filter(function ($grupo) {
            return $grupo->contains(
                function ($exp) {
                    return $exp->modulacion === 'DESADUANAMIENTO LIBRE';
                }
            );
        })->count();

        //Camiones Rojos
        $camionesRojos = $camiones->filter(function ($grupo) {
            return $grupo->contains(
                function ($exp) {
                    return in_array($exp->modulacion, [
                        'RECONOCIMIENTO ADUANERO',
                        'RECONOCIMIENTO ADUANERO CONCLUIDO'
                    ]);
                }
            );
        })->count();

        return response()->json([
            'totalClientes' => $totalClientes,
            'totalRemesas' => $totalRemesas,
            'totalCamiones' => $totalCamiones, // NUEVO
            'completadas' => $completadas,
            'pendientes' => $pendientes,
            'progresoDelDia' => $progresoDelDia,
            'verdes' => $verdes,
            'rojos' => $rojos,
            'porcentajeVerdes' => $porcentajeVerdes,
            'exportadoresData' => $exportadoresData,
            'ciudadesData' => $ciudadesData,
            'finalizadas' => $finalizadas,
            'totalDia' => $totalDia,
            'detenidas' => $detenidas,
            'pedimentosProximos' => $pedimentosProximos,
            'totalCamiones2' => $totalCamiones2,
            'camionesVerdes' => $camionesVerdes,
            'camionesRojos' => $camionesRojos,
        ]);
    }

    public function operacionesDiarias(Request $request)
    {
        $fecha = $request->input('fecha_cruce_estimada', now()->format('Y-m-d'));
        $fechaCarbon = Carbon::parse($fecha);

        //Calcular el inicio y fin de la semana actual.
        $inicioSemanaActual = $fechaCarbon->copy()->startOfWeek(Carbon::MONDAY);
        $finSemanaActual = $fechaCarbon->copy()->endOfWeek(Carbon::SUNDAY);

        //Calcular el inicio y fin de la semana anterior.
        $inicioSemanaPasada = $inicioSemanaActual->copy()->subWeek();
        $finSemanaPasada = $finSemanaActual->copy()->subWeek();

        // Datos para la gráfica - Semana Actual (Lunes a Domingo)
        $tramitesSemanaActual = [];
        for ($i = 0; $i < 7; $i++) {
            $diaActual = $inicioSemanaActual->copy()->addDays($i);
            $count = Operacion::where('tenant_id', auth()->user()->tenant_id)->whereDate('fecha_cruce_estimada', $diaActual)->count();
            $tramitesSemanaActual[] = [
                'dia' => $diaActual->locale('es')->isoFormat('ddd'), // Lun, Mar, Mié, etc.
                'fecha_cruce_estimada' => $diaActual->format('Y-m-d'),
                'cantidad' => $count
            ];
        }
        // Datos para la gráfica - Semana Pasada (Lunes a Domingo)
        $tramitesSemanaPasada = [];
        for ($i = 0; $i < 7; $i++) {
            $diaPasado = $inicioSemanaPasada->copy()->addDays($i);
            $count = Operacion::where('tenant_id', auth()->user()->tenant_id)->whereDate('fecha_cruce_estimada', $diaPasado)->count();
            $tramitesSemanaPasada[] = [
                'dia' => $diaPasado->locale('es')->isoFormat('ddd'),
                'fecha_cruce_estimada' => $diaPasado->format('Y-m-d'),
                'cantidad' => $count
            ];
        }


        // Operaciones del día
        $operacionesHoy = Operacion::where('tenant_id', auth()->user()->tenant_id)->with(['cliente', 'aduana'])
            ->whereDate('fecha_cruce_estimada', $fechaCarbon)
            ->get();

        // Estadísticas generales
        $totalClientes = $operacionesHoy->pluck('cliente_id')->unique()->count();
        $totalRemesas = $operacionesHoy->count();
        $completadas = $operacionesHoy->where('estado', 'terminado')->count();
        $pendientes = $operacionesHoy->whereIn('estado', ['pendiente', 'proceso'])->count();

        // Total de camiones (agrupados por codigo_alpha y num_thermo)
        $totalCamiones = $operacionesHoy
            ->filter(function ($exp) {
                // Filtrar solo registros que tengan ambos valores
                return !empty($exp->codigo_alpha) && !empty($exp->num_thermo);
            })
            ->groupBy(function ($exp) {
                // Agrupar por la combinación de alpha + thermo
                return $exp->codigo_alpha . '-' . $exp->num_thermo;
            })
            ->count();

        // Progreso del día
        $progresoDelDia = $totalRemesas > 0 ? round(($completadas / $totalRemesas) * 100, 2) : 0;

        // Modulación (Verdes/Rojos)
        $verdes = $operacionesHoy->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
        $rojos = $operacionesHoy->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO'])->count();
        $porcentajeVerdes = $totalRemesas > 0 ? round(($verdes / $totalRemesas) * 100, 2) : 0;

        // Exportadores del día (agrupados) - MODIFICADO PARA INCLUIR PENDIENTES
        $exportadoresData = $operacionesHoy->groupBy('cliente_id')->map(function ($grupo) {
            $cliente = $grupo->first()->cliente;

            // Obtener trámites pendientes o en proceso para este exportador
            $tramitesPendientes = $grupo->whereIn('estado', ['pendiente', 'proceso'])->map(
                function ($exp) {
                    return [
                        'referencia' => $exp->referencia ?? $exp->numero_pedimento ?? 'REF-' . $exp->id,
                        'estado' => ucfirst($exp->estado),
                        'aduana' => $exp->aduana->nombre ?? 'N/A'
                    ];
                }
            )->values()->toArray();

            return [
                'nombre' => $cliente->nombre ?? 'Sin nombre',
                'cantidad' => $grupo->count(),
                'rojos' => $grupo->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO'])->count(),
                'verdes' => $grupo->where('modulacion', 'DESADUANAMIENTO LIBRE')->count(),
                'completadas' => $grupo->where('estado', 'terminado')->count(),
                'pendientes' => $tramitesPendientes, // NUEVO CAMPO
            ];
        })->sortByDesc('cantidad')->values();

        // Aduanas del día
        $aduanasData = $operacionesHoy->groupBy('aduana_id')->map(function ($grupo) {
            $aduana = $grupo->first()->aduana;
            return [
                'nombre' => $aduana->nombre ?? 'Sin aduana',
                'cantidad' => $grupo->count(),
            ];
        })->sortByDesc('cantidad');

        // Ciudades (Laredo/Reynosa) - asumiendo que está en la aduana
        $ciudadesData = $operacionesHoy->groupBy(function ($exp) {
            return $exp->aduana->nombre ?? 'Otra';
        })->map(function ($grupo, $ciudad) {
            return [
                'ciudad' => $ciudad,
                'cantidad' => $grupo->count(),
                'porcentaje' => 0 // se calculará después
            ];
        });

        // Calcular porcentajes de ciudades
        if ($totalRemesas > 0) {
            $ciudadesData = $ciudadesData->map(function ($item) use ($totalRemesas) {
                $item['porcentaje'] = round(($item['cantidad'] / $totalRemesas) * 100, 1);
                return $item;
            });
        }

        // Estado de DODAS
        $finalizadas = $operacionesHoy->where('estado', 'terminado')->count();
        $totalDia = $totalRemesas;

        // Detenidas hasta el momento (puedes agregar lógica específica)
        $detenidas = 0; // Ajusta según tu lógica

        // Pedimentos por aduana (para el día siguiente)
        $pedimentosProximos = Operacion::where('tenant_id', auth()->user()->tenant_id)->with('aduana')
            ->whereDate('fecha_cruce_estimada', $fechaCarbon->copy()->addDay())
            ->get()
            ->groupBy('aduana_id')
            ->map(function ($grupo) {
                return [
                    'aduana' => $grupo->first()->aduana->nombre ?? 'Sin aduana',
                    'cantidad' => $grupo->count()
                ];
            });

        // Obtener total de camiones unicos
        $camiones = $operacionesHoy
            ->filter(function ($exp) {
                return !empty($exp->codigo_alpha) && !empty($exp->num_thermo);
            })
            ->groupBy(function ($exp) {
                return $exp->codigo_alpha . '-' . $exp->num_thermo;
            });

        $totalCamiones2 = $camiones->count();

        // Camiones Verdes
        $camionesVerdes = $camiones->filter(function ($grupo) {
            return $grupo->contains(
                function ($exp) {
                    return $exp->modulacion === 'DESADUANAMIENTO LIBRE';
                }
            );
        })->count();

        //Camiones Rojos
        $camionesRojos = $camiones->filter(function ($grupo) {
            return $grupo->contains(
                function ($exp) {
                    return in_array($exp->modulacion, [
                        'RECONOCIMIENTO ADUANERO',
                        'RECONOCIMIENTO ADUANERO CONCLUIDO'
                    ]);
                }
            );
        })->count();



        return view('reportes.operaciones-diarias', compact(
            'fecha_cruce_estimada',
            'fechaCarbon',
            'totalClientes',
            'totalRemesas',
            'totalCamiones', // NUEVO
            'completadas',
            'pendientes',
            'progresoDelDia',
            'verdes',
            'rojos',
            'porcentajeVerdes',
            'exportadoresData',
            'aduanasData',
            'ciudadesData',
            'finalizadas',
            'totalDia',
            'detenidas',
            'pedimentosProximos',
            'operacionesHoy',
            'totalCamiones2',
            'camionesVerdes',
            'camionesRojos',
            'tramitesSemanaActual',
            'tramitesSemanaPasada'
        ));
    }

    public function operacionesDiariasApi(Request $request)
    {
        $fecha = $request->input('fecha_cruce_estimada', now()->format('Y-m-d'));
        $fechaCarbon = Carbon::parse($fecha);

        //Calcular el inicio y fin de la semana actual.
        $inicioSemanaActual = $fechaCarbon->copy()->startOfWeek(Carbon::MONDAY);
        $finSemanaActual = $fechaCarbon->copy()->endOfWeek(Carbon::SUNDAY);

        //Calcular el inicio y fin de la semana anterior.
        $inicioSemanaPasada = $inicioSemanaActual->copy()->subWeek();
        $finSemanaPasada = $finSemanaActual->copy()->subWeek();

        // Datos para la gráfica - Semana Actual (Lunes a Domingo)
        $tramitesSemanaActual = [];
        for ($i = 0; $i < 7; $i++) {
            $diaActual = $inicioSemanaActual->copy()->addDays($i);
            $count = Operacion::where('tenant_id', auth()->user()->tenant_id)->whereDate('fecha_cruce_estimada', $diaActual)->count();
            $tramitesSemanaActual[] = [
                'dia' => $diaActual->locale('es')->isoFormat('ddd'), // Lun, Mar, Mié, etc.
                'fecha_cruce_estimada' => $diaActual->format('Y-m-d'),
                'cantidad' => $count
            ];
        }
        // Datos para la gráfica - Semana Pasada (Lunes a Domingo)
        $tramitesSemanaPasada = [];
        for ($i = 0; $i < 7; $i++) {
            $diaPasado = $inicioSemanaPasada->copy()->addDays($i);
            $count = Operacion::where('tenant_id', auth()->user()->tenant_id)->whereDate('fecha_cruce_estimada', $diaPasado)->count();
            $tramitesSemanaPasada[] = [
                'dia' => $diaPasado->locale('es')->isoFormat('ddd'),
                'fecha_cruce_estimada' => $diaPasado->format('Y-m-d'),
                'cantidad' => $count
            ];
        }

        // Operaciones del día
        $operacionesHoy = Operacion::where('tenant_id', auth()->user()->tenant_id)->with(['cliente', 'aduana'])
            ->whereDate('fecha_cruce_estimada', $fechaCarbon)
            ->get();

        // Estadísticas generales
        $totalClientes = $operacionesHoy->pluck('cliente_id')->unique()->count();
        $totalRemesas = $operacionesHoy->count();
        $completadas = $operacionesHoy->where('estado', 'terminado')->count();
        $pendientes = $operacionesHoy->whereIn('estado', ['pendiente', 'proceso'])->count();

        // Total de camiones (agrupados por codigo_alpha y num_thermo)
        $totalCamiones = $operacionesHoy
            ->filter(function ($exp) {
                return !empty($exp->codigo_alpha) && !empty($exp->num_thermo);
            })
            ->groupBy(function ($exp) {
                return $exp->codigo_alpha . '-' . $exp->num_thermo;
            })
            ->count();

        // Progreso del día
        $progresoDelDia = $totalRemesas > 0 ? round(($completadas / $totalRemesas) * 100, 2) : 0;

        // Modulación (Verdes/Rojos)
        $verdes = $operacionesHoy->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
        $rojos = $operacionesHoy->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO'])->count();
        $porcentajeVerdes = $totalRemesas > 0 ? round(($verdes / $totalRemesas) * 100, 2) : 0;

        // Exportadores del día (agrupados) - MODIFICADO PARA INCLUIR PENDIENTES
        $exportadoresData = $operacionesHoy->groupBy('cliente_id')->map(function ($grupo) {
            $cliente = $grupo->first()->cliente;

            // Obtener trámites pendientes o en proceso para este exportador
            $tramitesPendientes = $grupo->whereIn('estado', ['pendiente', 'proceso'])->map(
                function ($exp) {
                    return [
                        'referencia' => $exp->referencia ?? $exp->numero_pedimento ?? 'REF-' . $exp->id,
                        'estado' => ucfirst($exp->estado),
                        'aduana' => $exp->aduana->nombre ?? 'N/A'
                    ];
                }
            )->values()->toArray();

            return [
                'nombre' => $cliente->nombre ?? 'Sin nombre',
                'cantidad' => $grupo->count(),
                'rojos' => $grupo->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO CONCLUIDO', 'RECONOCIMIENTO ADUANERO'])->count(),
                'verdes' => $grupo->where('modulacion', 'DESADUANAMIENTO LIBRE')->count(),
                'completadas' => $grupo->where('estado', 'terminado')->count(),
                'pendientes' => $tramitesPendientes, // NUEVO CAMPO
            ];
        })->sortByDesc('cantidad')->values();

        // Ciudades (Laredo/Reynosa)
        $ciudadesData = $operacionesHoy->groupBy(function ($exp) {
            return $exp->aduana->nombre ?? 'Otra';
        })->map(function ($grupo, $ciudad) use ($totalRemesas) {
            $cantidad = $grupo->count();
            return [
                'ciudad' => $ciudad,
                'cantidad' => $cantidad,
                'porcentaje' => $totalRemesas > 0 ? round(($cantidad / $totalRemesas) * 100, 1) : 0
            ];
        })->values();

        // Estado de DODAS
        $finalizadas = $operacionesHoy->where('estado', 'terminado')->count();
        $totalDia = $totalRemesas;
        $detenidas = 0;

        // Pedimentos por aduana (para el día siguiente)
        $pedimentosProximos = Operacion::where('tenant_id', auth()->user()->tenant_id)->with('aduana')
            ->whereDate('fecha_cruce_estimada', $fechaCarbon->copy()->addDay())
            ->get()
            ->groupBy('aduana_id')
            ->map(function ($grupo) {
                return [
                    'aduana' => $grupo->first()->aduana->nombre ?? 'Sin aduana',
                    'cantidad' => $grupo->count()
                ];
            })->values();

        // Obtener total de camiones unicos
        $camiones = $operacionesHoy
            ->filter(function ($exp) {
                return !empty($exp->codigo_alpha) && !empty($exp->num_thermo);
            })
            ->groupBy(function ($exp) {
                return $exp->codigo_alpha . '-' . $exp->num_thermo;
            });

        $totalCamiones2 = $camiones->count();

        // Camiones Verdes
        $camionesVerdes = $camiones->filter(function ($grupo) {
            return $grupo->contains(
                function ($exp) {
                    return $exp->modulacion === 'DESADUANAMIENTO LIBRE';
                }
            );
        })->count();

        //Camiones Rojos
        $camionesRojos = $camiones->filter(function ($grupo) {
            return $grupo->contains(
                function ($exp) {
                    return in_array($exp->modulacion, [
                        'RECONOCIMIENTO ADUANERO',
                        'RECONOCIMIENTO ADUANERO CONCLUIDO'
                    ]);
                }
            );
        })->count();

        return response()->json([
            'totalClientes' => $totalClientes,
            'totalRemesas' => $totalRemesas,
            'totalCamiones' => $totalCamiones, // NUEVO
            'completadas' => $completadas,
            'pendientes' => $pendientes,
            'progresoDelDia' => $progresoDelDia,
            'verdes' => $verdes,
            'rojos' => $rojos,
            'porcentajeVerdes' => $porcentajeVerdes,
            'exportadoresData' => $exportadoresData,
            'ciudadesData' => $ciudadesData,
            'finalizadas' => $finalizadas,
            'totalDia' => $totalDia,
            'detenidas' => $detenidas,
            'pedimentosProximos' => $pedimentosProximos,
            'totalCamiones2' => $totalCamiones2,
            'camionesVerdes' => $camionesVerdes,
            'camionesRojos' => $camionesRojos,
            'tramitesSemanaActual' => $tramitesSemanaActual,
            'tramitesSemanaPasada' => $tramitesSemanaPasada
        ]);
    }





    /**
     * Genera un reporte de operaciones por semanas, ordenando los clientes alfabéticamente.
     */
    public function operacionesPorSemanas2(Request $request)
    {
        // 1. Validación de Fechas
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ], [
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser igual o posterior a la fecha inicio.'
        ]);

        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;

        // 2. Consulta y Ordenamiento de Clientes
        $reporte = Cliente::with([
            'operaciones' => function ($query) use ($fecha_inicio, $fecha_fin) {
                // Relación: Filtra operaciones dentro del rango de fechas y agrupa por semana
                $query->whereBetween('fecha_cruce_estimada', [$fecha_inicio, $fecha_fin])
                    ->select([
                        'cliente_id',
                        // Utilizamos WEEK(fecha_cruce_estimada, 3) para la semana (lunes como primer día, semana 1 con 4 días)
                        DB::raw('WEEK(fecha_cruce_estimada, 3) as semana'),
                        DB::raw('COUNT(*) as total_operaciones')
                    ])
                    ->groupBy('cliente_id', DB::raw('WEEK(fecha_cruce_estimada, 3)'));
            }
        ])
            // Solo incluye Clientes que tengan Operaciones en el rango de fechas
            ->whereHas('operaciones', function ($query) use ($fecha_inicio, $fecha_fin) {
                $query->whereBetween('fecha_cruce_estimada', [$fecha_inicio, $fecha_fin]);
            })
            // ⭐ MODIFICACIÓN: Ordena los clientes alfabéticamente por nombre
            ->orderBy('nombre', 'asc')
            ->get()

            // 3. Formateo de los Resultados
            ->map(function ($cliente) {
                // Mapea las operaciones a un array de semanas
                $semanas = $cliente->operaciones->mapWithKeys(
                    function ($operacion) {
                    return ['Semana ' . $operacion->semana => (int) $operacion->total_operaciones];
                }
                )->toArray();

                return [
                    'cliente' => $cliente->nombre,
                    'semanas' => $semanas,
                    'total_general' => array_sum($semanas) // Suma de todas las operaciones por cliente
                ];
            });

        // 4. Retorna la Vista
        return view('reportes.operaciones_semanas', compact('reporte', 'fecha_inicio', 'fecha_fin'));
    }


    public function expsem(Request $request)
    {
        // 1. Validación de Fechas
        /*$request->validate([
         'fecha_inicio' => 'required|date',
         'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
         ], [
         'fecha_fin.after_or_equal' => 'La fecha fin debe ser igual o posterior a la fecha inicio.'
         ]);
         */
        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;

        // 2. Consulta y Ordenamiento de Clientes
        $reporte = Cliente::with([
            'operaciones' => function ($query) use ($fecha_inicio, $fecha_fin) {
                // Relación: Filtra operaciones dentro del rango de fechas y agrupa por semana
                $query->whereBetween('fecha_cruce_estimada', [$fecha_inicio, $fecha_fin])
                    ->select([
                        'cliente_id',
                        // Utilizamos WEEK(fecha_cruce_estimada, 3) para la semana (lunes como primer día, semana 1 con 4 días)
                        DB::raw('WEEK(fecha_cruce_estimada, 3) as semana'),
                        DB::raw('COUNT(*) as total_operaciones')
                    ])
                    ->groupBy('cliente_id', DB::raw('WEEK(fecha_cruce_estimada, 3)'));
            }
        ])
            // Solo incluye Clientes que tengan Operaciones en el rango de fechas
            ->whereHas('operaciones', function ($query) use ($fecha_inicio, $fecha_fin) {
                $query->whereBetween('fecha_cruce_estimada', [$fecha_inicio, $fecha_fin]);
            })
            // ⭐ MODIFICACIÓN: Ordena los clientes alfabéticamente por nombre
            ->orderBy('nombre', 'asc')
            ->get()

            // 3. Formateo de los Resultados
            ->map(function ($cliente) {
                // Mapea las operaciones a un array de semanas
                $semanas = $cliente->operaciones->mapWithKeys(
                    function ($operacion) {
                    return ['Semana ' . $operacion->semana => (int) $operacion->total_operaciones];
                }
                )->toArray();

                return [
                    'cliente' => $cliente->nombre,
                    'semanas' => $semanas,
                    'total_general' => array_sum($semanas) // Suma de todas las operaciones por cliente
                ];
            });

        // 4. Retorna la Vista
        return view('reportes.operaciones_semanas', compact('reporte', 'fecha_inicio', 'fecha_fin'));
    }


    /**
     * Reporte de Remesas
     */







    public function reporteRemesas(Request $request)
    {
        // Obtener filtros del request o valores por defecto
        $anio = $request->get('year', now()->year);
        $mes = $request->get('month', '');
        $clienteId = $request->get('cliente_id', '');

        $anioActual = $anio;

        // ✅ CORRECCIÓN: Determinar el contexto del año
        $esAnioCerrado = $anio < now()->year;
        $esAnioActual = $anio == now()->year;

        // ✅ CORRECCIÓN: Calcular el mes hasta donde mostrar
        if ($esAnioCerrado && empty($mes)) {
            // Año pasado sin filtro de mes: mostrar TODOS los 12 meses
            $mesActual = 12;
            $mostrarSemanas = false;
        } elseif ($esAnioActual && empty($mes)) {
            // Año actual sin filtro de mes: mostrar hasta el mes actual
            $mesActual = now()->month;
            $mostrarSemanas = true;
        } else {
            // Hay filtro de mes específico: usar ese mes
            $mesActual = (int) $mes;
            $mostrarSemanas = true;
        }

        /**
         * 1️⃣ Meses cerrados (anteriores al mes actual/filtrado)
         */
        $remesasPorMes = Operacion::where('tenant_id', auth()->user()->tenant_id)->selectRaw("
        MONTH(fecha_cruce_estimada) as mes_numero,
        MONTHNAME(fecha_cruce_estimada) as mes,
        COUNT(*) as total
    ")
            ->whereYear('fecha_cruce_estimada', $anioActual)
            ->when($esAnioCerrado && empty($mes), function ($query) {
                // Si es año cerrado sin filtro, traer TODOS los meses
                return $query;
            }, function ($query) use ($mesActual, $mostrarSemanas) {
                // Si vamos a mostrar semanas, excluir el mes actual (usar <)
                // Si hay filtro de mes específico, incluir ese mes (usar <=)
                if ($mostrarSemanas) {
                    return $query->whereMonth('fecha_cruce_estimada', '<', $mesActual);
                } else {
                    return $query->whereMonth('fecha_cruce_estimada', '<=', $mesActual);
                }
            })
            ->when($clienteId, function ($query) use ($clienteId) {
                return $query->where('cliente_id', $clienteId);
            })
            ->groupBy('mes_numero', 'mes')
            ->orderBy('mes_numero')
            ->get();

        /**
         * 2️⃣ Semanas del mes actual (solo si aplica)
         */
        $remesasSemanaMesActual = collect([]);

        if ($mostrarSemanas) {
            $remesasSemanaMesActual = Operacion::where('tenant_id', auth()->user()->tenant_id)->selectRaw("
            WEEK(fecha_cruce_estimada, 1)
            - WEEK(DATE_SUB(fecha_cruce_estimada, INTERVAL DAYOFMONTH(fecha_cruce_estimada)-1 DAY), 1)
            + 1 as semana_mes,
            COUNT(*) as total
        ")
                ->whereYear('fecha_cruce_estimada', $anioActual)
                ->whereMonth('fecha_cruce_estimada', $mesActual)
                ->when($clienteId, function ($query) use ($clienteId) {
                    return $query->where('cliente_id', $clienteId);
                })
                ->groupBy('semana_mes')
                ->orderBy('semana_mes')
                ->get();
        }

        /**
         * 3️⃣ Formatear resultado final
         */
        $reporte = [];
        $reporteDetalle = [];
        $datosOrganizados = []; // Para la gráfica con colores
        $totalesPorMes = []; // Para acumular totales por mes

        // Procesar meses
        foreach ($remesasPorMes as $mesData) {
            $reporte[] = [
                'label' => ucfirst($mesData->mes),
                'total' => $mesData->total,
                'tipo' => 'mes',
            ];

            $reporteDetalle[] = (object) [
                'fecha_formateada' => ucfirst($mesData->mes) . ' ' . $anioActual,
                'cliente_nombre' => $clienteId ? $this->getNombreCliente($clienteId) : 'Todos los clientes',
                'cantidad' => $mesData->total,
                'monto_total' => 0,
                'monto_promedio' => 0,
                'tipo' => 'mes'
            ];

            $datosOrganizados[] = [
                'label' => ucfirst($mesData->mes),
                'total' => $mesData->total,
                'tipo' => 'mes',
                'mes_numero' => $mesData->mes_numero
            ];
        }

        // Procesar semanas (solo si aplica)
        if ($mostrarSemanas && $remesasSemanaMesActual->isNotEmpty()) {
            $nombreMes = \Carbon\Carbon::create($anioActual, $mesActual, 1)->translatedFormat('F');
            $mesKey = $anioActual . '-' . $mesActual;

            // Calcular total acumulado del mes
            $totalAcumuladoMes = $remesasSemanaMesActual->sum('total');
            $totalesPorMes[$mesKey] = $totalAcumuladoMes;

            foreach ($remesasSemanaMesActual as $semana) {
                $labelSemana = 'Semana ' . $semana->semana_mes . ' ' . $nombreMes;

                $reporte[] = [
                    'label' => $labelSemana,
                    'total' => $semana->total,
                    'tipo' => 'semana',
                ];

                $reporteDetalle[] = (object) [
                    'fecha_formateada' => $labelSemana,
                    'cliente_nombre' => $clienteId ? $this->getNombreCliente($clienteId) : 'Todos los clientes',
                    'cantidad' => $semana->total,
                    'monto_total' => 0,
                    'monto_promedio' => 0,
                    'tipo' => 'semana'
                ];

                $datosOrganizados[] = [
                    'label' => $labelSemana,
                    'total' => $semana->total,
                    'tipo' => 'semana',
                    'mes_key' => $mesKey,
                    'semana_num' => $semana->semana_mes,
                    'mes_numero' => $mesActual
                ];
            }
        }

        /**
         * 4️⃣ Preparar datos adicionales para la vista
         */
        // Obtener clientes para el filtro
        $clientes = Cliente::orderBy('nombre')->get();

        // Calcular estadísticas
        $totalRemesas = collect($reporte)->sum('total');

        // Contar clientes activos
        if ($clienteId) {
            $clientesActivos = 1;
        } else {
            $clientesActivos = Operacion::where('tenant_id', auth()->user()->tenant_id)->whereYear('fecha_cruce_estimada', $anioActual)
                ->when(!$esAnioCerrado || !empty($mes), function ($query) use ($mesActual) {
                    return $query->whereMonth('fecha_cruce_estimada', '<=', $mesActual);
                })
                ->distinct('cliente_id')
                ->count('cliente_id');
        }

        // Obtener años disponibles para el select
        $aniosDisponibles = Operacion::where('tenant_id', auth()->user()->tenant_id)->selectRaw('YEAR(fecha_cruce_estimada) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($aniosDisponibles->isEmpty()) {
            $aniosDisponibles = collect([now()->year]);
        }

        return view('reportes.remesas', compact(
            'reporte',
            'reporteDetalle',
            'datosOrganizados',
            'totalesPorMes',
            'totalRemesas',
            'clientesActivos',
            'clientes',
            'aniosDisponibles',
            'anio',
            'mes',
            'clienteId'
        ));
    }

    /**
     * Helper para obtener nombre del cliente (legacy - replaced)
     */

    /**
     * Obtener nombre del cliente
     */
    private function getNombreCliente($clienteId)
    {
        $cliente = Cliente::find($clienteId);
        return $cliente ? $cliente->nombre : 'Cliente no encontrado';
    }

    /**
     * Exportar a Excel
     */
    public function exportarExcel(Request $request)
    {
        // Obtener datos filtrados
        $datos = $this->obtenerDatosParaExportar($request);

        // Aquí implementarías la exportación a Excel
        // usando Laravel Excel o similar
        // return Excel::download(new RemesasExport($datos), 'remesas.xlsx');

        return back()->with('success', 'Exportación a Excel en desarrollo');
    }

    /**
     * Exportar a PDF
     */
    public function exportarPDF(Request $request)
    {
        // Obtener datos filtrados
        $datos = $this->obtenerDatosParaExportar($request);

        // Aquí implementarías la generación de PDF
        // usando DomPDF o similar
        // $pdf = PDF::loadView('reportes.pdf.remesas', $datos);
        // return $pdf->download('remesas.pdf');

        return back()->with('success', 'Exportación a PDF en desarrollo');
    }

    /**
     * Obtener datos para exportar (reutilizable para Excel/PDF)
     */
    private function obtenerDatosParaExportar(Request $request)
    {
        $anio = $request->get('year', now()->year);
        $mes = $request->get('month', '');
        $clienteId = $request->get('cliente_id', '');

        $query = Operacion::where('tenant_id', auth()->user()->tenant_id)->with(['cliente', 'aduana', 'patente'])
            ->whereYear('fecha_cruce_estimada', $anio);

        if ($mes) {
            $query->whereMonth('fecha_cruce_estimada', $mes);
        }

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        return $query->orderBy('fecha_cruce_estimada')->get();
    }

    /**
     * Obtener estadísticas en tiempo real (para AJAX)
     */
    public function obtenerEstadisticas(Request $request)
    {
        $anio = $request->get('year', now()->year);
        $mes = $request->get('month', '');
        $clienteId = $request->get('cliente_id', '');

        $query = Operacion::where('tenant_id', auth()->user()->tenant_id)->whereYear('fecha_cruce_estimada', $anio);

        if ($mes) {
            $query->whereMonth('fecha_cruce_estimada', $mes);
        }

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }

        return response()->json([
            'total' => $query->count(),
            'clientes_unicos' => $query->distinct('cliente_id')->count('cliente_id'),
            'promedio_diario' => $this->calcularPromedioDiario($query->get())
        ]);
    }
    /**
     * Calcular promedio diario de remesas
     */
    private function calcularPromedioDiario($remesas)
    {
        if ($remesas->isEmpty()) {
            return 0;
        }

        $fechasUnicas = $remesas->groupBy(function ($item) {
            return $item->fecha_cruce_estimada ? $item->fecha_cruce_estimada->format('Y-m-d') : null;
        })->whereNotNull()->count();

        return $fechasUnicas > 0 ? round($remesas->count() / $fechasUnicas, 2) : 0;
    }

    public function operacionSemanal(Request $request)
    {
        /* =====================================================
         1️⃣ Determinar rango de fechas (semana o rango)
         ===================================================== */

        if ($request->filled(['fecha_inicio', 'fecha_fin'])) {
            $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
        } else {
            // Semana actual (lunes a domingo)
            $fechaInicio = Carbon::now()->startOfWeek(Carbon::MONDAY);
            $fechaFin = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        }

        /* =====================================================
         2️⃣ Query base de operaciones de la semana
         ===================================================== */

        $operacionesQuery = Operacion::where('tenant_id', auth()->user()->tenant_id)
            ->whereBetween('fecha_cruce_estimada', [$fechaInicio, $fechaFin]);

        $operaciones = (clone $operacionesQuery)->get();

        /* =====================================================
         3️⃣ Operaciones totales / verdes / rojas
         ===================================================== */

        $operacionesTotales = $operaciones->count();

        $operacionesVerdes = $operaciones->where(
            'modulacion',
            'DESADUANAMIENTO LIBRE'
        )->count();

        $operacionesRojas = $operaciones->whereIn(
            'modulacion',
            ['RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO']
        )->count();

        /* =====================================================
         4️⃣ Operaciones por día (lunes a domingo)
         ===================================================== */

        $operacionesPorDia = $operaciones
            ->filter(fn($item) => $item->fecha_cruce_estimada !== null)
            ->groupBy(fn($item) => $item->fecha_cruce_estimada->format('Y-m-d'))
            ->map->count();

        // Preparar datos completos para la gráfica (7 días de la semana)
        $diasSemana = [];
        $operacionesPorDiaCompleto = [];
        $currentDate = $fechaInicio->copy();

        while ($currentDate <= $fechaFin) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayName = $currentDate->translatedFormat('D');
            $dayNumber = $currentDate->day;

            $diasSemana[] = [
                'date' => $dateStr,
                'day_name' => $dayName,
                'day_number' => $dayNumber,
                'count' => $operacionesPorDia[$dateStr] ?? 0,
            ];

            $operacionesPorDiaCompleto[] = $operacionesPorDia[$dateStr] ?? 0;

            $currentDate->addDay();
        }

        /* =====================================================
         5️⃣ Camiones únicos (num_thermo + codigo_alpha + fecha)
         ===================================================== */

        $camiones = $operaciones
            ->filter(fn($item) => $item->fecha_cruce_estimada !== null)
            ->groupBy(function ($item) {
                return $item->num_thermo . '|' . $item->codigo_alpha . '|' . $item->fecha_cruce_estimada->format('Y-m-d');
            });

        $totalCamiones = $camiones->count();

        $camionesRojos = $camiones->filter(function ($grupo) {
            return $grupo->contains(
                fn($exp) =>
                in_array($exp->modulacion, [
                    'RECONOCIMIENTO ADUANERO',
                    'RECONOCIMIENTO ADUANERO CONCLUIDO'
                ])
            );
        })->count();

        $camionesVerdes = $totalCamiones - $camionesRojos;

        /* =====================================================
         6️⃣ Clientes que cruzaron en la semana
         ===================================================== */

        $clientesSemana = $operaciones
            ->groupBy('cliente_id')
            ->map(function ($grupo) use ($operacionesTotales) {
                return [
                    'cliente' => optional($grupo->first()->cliente)->nombre,
                    'total' => $grupo->count(),
                    'porcentaje' => $operacionesTotales > 0
                        ? round(($grupo->count() / $operacionesTotales) * 100, 2)
                        : 0
                ];
            })
            ->sortByDesc('total')
            ->values();

        /* =====================================================
         7️⃣ Permisos de sobrepeso
         ===================================================== */

        //$totalSobrepeso = $operaciones
        //    ->where('sobrepeso', true)
        //    ->count();

        /* =====================================================
         8️⃣ Básculas (ConceptoAdicional)
         ===================================================== */

        //$totalBasculas = ConceptoAdicional::whereHas('operacion', function ($q) use ($fechaInicio, $fechaFin) {
        //    $q->whereBetween('fecha_cruce_estimada', [$fechaInicio, $fechaFin]);
        //})
        //    ->where('tipo_concepto', 'Uso de bascula')
        //    ->count();

        /* =====================================================
         9️⃣ Pedimentos por aduana y patente
         ===================================================== */

        $pedimentosPorAduanaPatente = Expediente::whereHas('operaciones', function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha_cruce_estimada', [$fechaInicio, $fechaFin]);
        })
            ->select(
                'aduana_id',
                'patente_id',
                DB::raw('COUNT(DISTINCT numero_pedimento) as total_pedimentos')
            )
            ->groupBy('aduana_id', 'patente_id')
            ->with(['aduana', 'patente'])
            ->get();

        /* =====================================================
         🔁 Retorno a la vista
         ===================================================== */

        return view('reportes.operacion_semanal', [
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,

            // Operaciones
            'operacionesTotales' => $operacionesTotales,
            'operacionesVerdes' => $operacionesVerdes,
            'operacionesRojas' => $operacionesRojas,
            'operacionesPorDia' => $operacionesPorDia,
            'operacionesPorDiaCompleto' => $operacionesPorDiaCompleto,
            'diasSemana' => $diasSemana,

            // Camiones
            'totalCamiones' => $totalCamiones,
            'camionesVerdes' => $camionesVerdes,
            'camionesRojos' => $camionesRojos,

            // Clientes
            'clientesSemana' => $clientesSemana,

            // Extras
            //'totalSobrepeso' => $totalSobrepeso,
            //'totalBasculas' => $totalBasculas,

            'totalSobrepeso' => 0,
            'totalBasculas' => 0,

            // Pedimentos
            'pedimentosPorAduanaPatente' => $pedimentosPorAduanaPatente,
            'metaIdealDiaria' => auth()->user()->tenant->configuracion['meta_ideal_diaria'] ?? 33,
            'metaBuenaDiaria' => auth()->user()->tenant->configuracion['meta_buena_diaria'] ?? 27,
            'metaMalaDiaria' => auth()->user()->tenant->configuracion['meta_mala_diaria'] ?? 25,
        ]);
    }






    public function reporteGerencia(Request $request)
    {
        /* =====================================================
         1️⃣ FILTROS RÁPIDOS - Determinar período
         ===================================================== */

        $tipoFiltro = $request->input('tipo_filtro', 'semana_actual');

        switch ($tipoFiltro) {
            case 'semana_actual':
                $fechaInicio = Carbon::now()->startOfWeek(Carbon::MONDAY);
                $fechaFin = Carbon::now()->endOfWeek(Carbon::SUNDAY);
                break;

            case 'semana_anterior':
                $fechaInicio = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeek();
                $fechaFin = Carbon::now()->endOfWeek(Carbon::SUNDAY)->subWeek();
                break;

            case 'mes_actual':
                $fechaInicio = Carbon::now()->startOfMonth();
                $fechaFin = Carbon::now()->endOfMonth();
                break;

            case 'mes_anterior':
                $fechaInicio = Carbon::now()->subMonth()->startOfMonth();
                $fechaFin = Carbon::now()->subMonth()->endOfMonth();
                break;

            case 'personalizado':
                if ($request->filled(['fecha_inicio', 'fecha_fin'])) {
                    $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
                    $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
                } else {
                    $fechaInicio = Carbon::now()->startOfWeek(Carbon::MONDAY);
                    $fechaFin = Carbon::now()->endOfWeek(Carbon::SUNDAY);
                }
                break;

            default:
                $fechaInicio = Carbon::now()->startOfWeek(Carbon::MONDAY);
                $fechaFin = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        }

        /* =====================================================
         2️⃣ CALCULAR PERÍODO ANTERIOR (PARA COMPARATIVA)
         ===================================================== */
        $diasEnPeriodo = $fechaInicio->diffInDays($fechaFin) + 1;


        switch ($tipoFiltro) {

            case 'semana_actual':
            case 'semana_anterior':
                // Semana calendario (lunes a domingo)
                $fechaInicioAnterior = $fechaInicio->copy()->subWeek();
                $fechaFinAnterior = $fechaFin->copy()->subWeek();
                break;

            case 'mes_actual':
            case 'mes_anterior':
                // Mes calendario completo
                $fechaInicioAnterior = $fechaInicio->copy()->subMonth()->startOfMonth();
                $fechaFinAnterior = $fechaFin->copy()->subMonth()->endOfMonth();
                break;

            case 'personalizado':
                // Mismo número de días hacia atrás
                $diasEnPeriodo = $fechaInicio->diffInDays($fechaFin) + 1;

                $fechaInicioAnterior = $fechaInicio->copy()->subDays($diasEnPeriodo);
                $fechaFinAnterior = $fechaFin->copy()->subDays($diasEnPeriodo);
                break;

            default:
                // Fallback: mismo tamaño
                $diasEnPeriodo = $fechaInicio->diffInDays($fechaFin) + 1;

                $fechaInicioAnterior = $fechaInicio->copy()->subDays($diasEnPeriodo);
                $fechaFinAnterior = $fechaFin->copy()->subDays($diasEnPeriodo);
        }

        /* =====================================================
         3️⃣ QUERY BASE - PERÍODO ACTUAL
         ===================================================== */

        $operacionesQuery = Operacion::where('tenant_id', auth()->user()->tenant_id)
            ->whereBetween('fecha_cruce_estimada', [$fechaInicio, $fechaFin]);

        $operaciones = (clone $operacionesQuery)->get();

        /* =====================================================
         4️⃣ QUERY BASE - PERÍODO ANTERIOR
         ===================================================== */

        $operacionesAnteriores = Operacion::where('tenant_id', auth()->user()->tenant_id)->whereBetween('fecha_cruce_estimada', [$fechaInicioAnterior, $fechaFinAnterior])->get();

        /* =====================================================
         5️⃣ KPIs PRINCIPALES - PERÍODO ACTUAL
         ===================================================== */

        $operacionesTotales = $operaciones->count();
        $operacionesVerdes = $operaciones->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
        $operacionesRojas = $operaciones->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO'])->count();

        /* =====================================================
         6️⃣ KPIs PRINCIPALES - PERÍODO ANTERIOR
         ===================================================== */

        $operacionesTotalesAnterior = $operacionesAnteriores->count();
        $operacionesVerdesAnterior = $operacionesAnteriores->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
        $operacionesRojasAnterior = $operacionesAnteriores->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO'])->count();

        /* =====================================================
         7️⃣ OPERACIONES POR DÍA - PERÍODO ACTUAL
         ===================================================== */

        $operacionesPorDia = $operaciones
            ->filter(fn($item) => $item->fecha_cruce_estimada !== null)
            ->groupBy(fn($item) => $item->fecha_cruce_estimada->format('Y-m-d'))
            ->map->count();

        /* =====================================================
         8️⃣ OPERACIONES POR DÍA - PERÍODO ANTERIOR
         ===================================================== */

        $operacionesPorDiaAnterior = $operacionesAnteriores
            ->filter(fn($item) => $item->fecha_cruce_estimada !== null)
            ->groupBy(fn($item) => $item->fecha_cruce_estimada->format('Y-m-d'))
            ->map->count();

        /* =====================================================
         9️⃣ CAMIONES ÚNICOS - PERÍODO ACTUAL
         ===================================================== */

        $camiones = $operaciones->filter(fn($item) => $item->fecha_cruce_estimada !== null)->groupBy(function ($item) {
            return $item->num_thermo . '|' . $item->codigo_alpha . '|' . $item->fecha_cruce_estimada->format('Y-m-d');
        });

        $totalCamiones = $camiones->count();

        $camionesRojos = $camiones->filter(function ($grupo) {
            return $grupo->contains(
                fn($exp) =>
                in_array($exp->modulacion, [
                    'RECONOCIMIENTO ADUANERO',
                    'RECONOCIMIENTO ADUANERO CONCLUIDO'
                ])
            );
        })->count();

        $camionesVerdes = $totalCamiones - $camionesRojos;

        /* =====================================================
         🔟 CAMIONES ÚNICOS - PERÍODO ANTERIOR
         ===================================================== */

        $camionesAnterior = $operacionesAnteriores->filter(fn($item) => $item->fecha_cruce_estimada !== null)->groupBy(function ($item) {
            return $item->num_thermo . '|' . $item->codigo_alpha . '|' . $item->fecha_cruce_estimada->format('Y-m-d');
        });

        $totalCamionesAnterior = $camionesAnterior->count();

        $camionesRojosAnterior = $camionesAnterior->filter(function ($grupo) {
            return $grupo->contains(
                fn($exp) =>
                in_array($exp->modulacion, [
                    'RECONOCIMIENTO ADUANERO',
                    'RECONOCIMIENTO ADUANERO CONCLUIDO'
                ])
            );
        })->count();

        $camionesVerdesAnterior = $totalCamionesAnterior - $camionesRojosAnterior;

        /* =====================================================
         1️⃣1️⃣ CLIENTES ACTIVOS - PERÍODO ACTUAL
         ===================================================== */

        $clientesSemana = $operaciones
            ->groupBy('cliente_id')
            ->map(function ($grupo) use ($operacionesTotales) {
                return [
                    'cliente_id' => $grupo->first()->cliente_id,
                    'cliente' => optional($grupo->first()->cliente)->nombre ?? 'Sin Cliente',
                    'total' => $grupo->count(),
                    'porcentaje' => $operacionesTotales > 0 ? round(($grupo->count() / $operacionesTotales) * 100, 2) : 0
                ];
            })
            ->sortByDesc('total')
            ->values();

        /* =====================================================
         1️⃣2️⃣ CLIENTES ACTIVOS - PERÍODO ANTERIOR
         ===================================================== */

        $clientesSemanaAnterior = $operacionesAnteriores
            ->groupBy('cliente_id')
            ->map(function ($grupo) use ($operacionesTotalesAnterior) {
                return [
                    'cliente_id' => $grupo->first()->cliente_id,
                    'cliente' => optional($grupo->first()->cliente)->nombre ?? 'Sin Cliente',
                    'total' => $grupo->count(),
                    'porcentaje' => $operacionesTotalesAnterior > 0 ? round(($grupo->count() / $operacionesTotalesAnterior) * 100, 2) : 0
                ];
            })
            ->sortByDesc('total')
            ->values();

        /* =====================================================
         1️⃣3️⃣ COMPARATIVA DE CLIENTES (actual vs anterior)
         ===================================================== */

        $clientesComparativa = $clientesSemana->map(function ($clienteActual) use ($clientesSemanaAnterior) {
            $clienteAnterior = $clientesSemanaAnterior->firstWhere('cliente_id', $clienteActual['cliente_id']);

            return [
                'cliente' => $clienteActual['cliente'],
                'actual' => $clienteActual['total'],
                'anterior' => $clienteAnterior ? $clienteAnterior['total'] : 0,
                'diferencia' => $clienteActual['total'] - ($clienteAnterior ? $clienteAnterior['total'] : 0),
                'porcentaje_cambio' => $clienteAnterior && $clienteAnterior['total'] > 0
                    ? round((($clienteActual['total'] - $clienteAnterior['total']) / $clienteAnterior['total']) * 100, 1)
                    : ($clienteActual['total'] > 0 ? 100 : 0)
            ];
        });

        /* =====================================================
         1️⃣4️⃣ SOBREPESO - PERÍODO ACTUAL
         ===================================================== */

        $totalSobrepeso = $operaciones->where('sobrepeso', true)->count();

        /* =====================================================
         1️⃣5️⃣ SOBREPESO - PERÍODO ANTERIOR
         ===================================================== */

        $totalSobrepesoAnterior = $operacionesAnteriores->where('sobrepeso', true)->count();

        /* =====================================================
         1️⃣6️⃣ BÁSCULAS - PERÍODO ACTUAL
         ===================================================== */

        $totalBasculas = ConceptoAdicional::whereHas('operacion', function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha_cruce_estimada', [$fechaInicio, $fechaFin]);
        })
            ->where('tipo_concepto', 'Uso de bascula')
            ->count();

        /* =====================================================
         1️⃣7️⃣ BÁSCULAS - PERÍODO ANTERIOR
         ===================================================== */

        $totalBasculasAnterior = ConceptoAdicional::whereHas('operacion', function ($q) use ($fechaInicioAnterior, $fechaFinAnterior) {
            $q->whereBetween('fecha_cruce_estimada', [$fechaInicioAnterior, $fechaFinAnterior]);
        })
            ->where('tipo_concepto', 'Uso de bascula')
            ->count();

        /* =====================================================
         1️⃣8️⃣ PEDIMENTOS POR ADUANA Y PATENTE - PERÍODO ACTUAL
         ===================================================== */

        $pedimentosPorAduanaPatente = Expediente::whereHas('operaciones', function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha_cruce_estimada', [$fechaInicio, $fechaFin]);
        })
            ->select(
                'aduana_id',
                'patente_id',
                DB::raw('COUNT(DISTINCT numero_pedimento) as total_pedimentos')
            )
            ->groupBy('aduana_id', 'patente_id')
            ->with(['aduana', 'patente'])
            ->get();

        /* =====================================================
         1️⃣9️⃣ DISTRIBUCIÓN POR ADUANA - PERÍODO ACTUAL
         ===================================================== */

        $operacionesPorAduana = $operaciones
            ->groupBy('aduana_id')
            ->map(function ($grupo) use ($operacionesTotales) {
                return [
                    'aduana' => optional($grupo->first()->aduana)->nombre ?? 'Sin Aduana',
                    'total' => $grupo->count(),
                    'porcentaje' => $operacionesTotales > 0 ? round(($grupo->count() / $operacionesTotales) * 100, 2) : 0,
                    'verdes' => $grupo->where('modulacion', 'DESADUANAMIENTO LIBRE')->count(),
                    'rojas' => $grupo->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO'])->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        /* =====================================================
         2️⃣0️⃣ MATRIZ: MODULACIÓN × ADUANA - PERÍODO ACTUAL
         ===================================================== */

        $modulacionPorAduana = $operaciones
            ->groupBy('aduana_id')
            ->map(function ($grupo) {
                $aduana = optional($grupo->first()->aduana)->nombre ?? 'Sin Aduana';
                $total = $grupo->count();
                $verdes = $grupo->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
                $rojas = $grupo->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO'])->count();

                return [
                    'aduana' => $aduana,
                    'total' => $total,
                    'verde' => $verdes,
                    'roja' => $rojas,
                    'porcentaje_verde' => $total > 0 ? round(($verdes / $total) * 100, 1) : 0,
                    'porcentaje_roja' => $total > 0 ? round(($rojas / $total) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('total')
            ->values();

        /* =====================================================
         2️⃣1️⃣ METAS 2026
         ===================================================== */

        // Metas diarias (desde configuración del tenant)
        $tenantConfig = auth()->user()->tenant->configuracion ?? [];
        $metaIdealDiaria = $tenantConfig['meta_ideal_diaria'] ?? 33;
        $metaBuenaDiaria = $tenantConfig['meta_buena_diaria'] ?? 27;
        $metaMalaDiaria = $tenantConfig['meta_mala_diaria'] ?? 25;

        // Metas mensuales
        $metaIdealMensual = $tenantConfig['meta_ideal_mensual'] ?? 1000;
        $metaBuenaMensual = $tenantConfig['meta_buena_mensual'] ?? 800;
        $metaMalaMensual = $tenantConfig['meta_mala_mensual'] ?? 750;

        // Proyecciones (líneas de referencia en gráfica diaria)
        $proyeccion1 = $tenantConfig['proyeccion_1'] ?? 40;
        $proyeccion2 = $tenantConfig['proyeccion_2'] ?? 50;
        $metaMediaDiaria = $tenantConfig['meta_media_diaria'] ?? 80;
        $metaAltaDiaria = $tenantConfig['meta_alta_diaria'] ?? 100;

        /* =====================================================
         2️⃣2️⃣ DATOS PARA VISTA ANUAL (12 MESES)
         ===================================================== */

        $anioActual = Carbon::now()->year;
        $anioAnterior = $anioActual - 1;

        // Operaciones por mes del año actual
        $operacionesPorMesActual = Operacion::where('tenant_id', auth()->user()->tenant_id)->selectRaw("MONTH(fecha_cruce_estimada) as mes, COUNT(*) as total")
            ->whereYear('fecha_cruce_estimada', $anioActual)
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        // Operaciones por mes del año anterior
        $operacionesPorMesAnterior = Operacion::where('tenant_id', auth()->user()->tenant_id)->selectRaw("MONTH(fecha_cruce_estimada) as mes, COUNT(*) as total")
            ->whereYear('fecha_cruce_estimada', $anioAnterior)
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        // Crear array completo de 12 meses con datos (rellenar con 0 si no hay datos)
        $totalesMesesActual = [];
        $totalesMesesAnterior = [];

        for ($i = 1; $i <= 12; $i++) {
            $totalesMesesActual[] = $operacionesPorMesActual->get($i, 0);
            $totalesMesesAnterior[] = $operacionesPorMesAnterior->get($i, 0);
        }

        // Calcular promedio mensual del año actual (solo meses con datos)
        $mesesConDatos = collect($totalesMesesActual)->filter(fn($val) => $val > 0);
        $promedioMensual = $mesesConDatos->count() > 0 ? round($mesesConDatos->avg(), 1) : 0;

        /* =====================================================
         🔁 RETORNO A LA VISTA
         ===================================================== */

        return view('reportes.reporte_gerencia', compact(
            // Fechas
            'fechaInicio',
            'fechaFin',
            'fechaInicioAnterior',
            'fechaFinAnterior',
            'tipoFiltro',
            'diasEnPeriodo',

            // KPIs período actual
            'operacionesTotales',
            'operacionesVerdes',
            'operacionesRojas',
            'totalCamiones',
            'camionesVerdes',
            'camionesRojos',
            'totalSobrepeso',
            'totalBasculas',

            // KPIs período anterior
            'operacionesTotalesAnterior',
            'operacionesVerdesAnterior',
            'operacionesRojasAnterior',
            'totalCamionesAnterior',
            'camionesVerdesAnterior',
            'camionesRojosAnterior',
            'totalSobrepesoAnterior',
            'totalBasculasAnterior',

            // Operaciones por día
            'operacionesPorDia',
            'operacionesPorDiaAnterior',

            // Clientes
            'clientesSemana',
            'clientesSemanaAnterior',
            'clientesComparativa',

            // Distribuciones
            'operacionesPorAduana',
            'modulacionPorAduana',
            'pedimentosPorAduanaPatente',

            // Metas
            'metaIdealDiaria',
            'metaBuenaDiaria',
            'metaMalaDiaria',
            'metaIdealMensual',
            'metaBuenaMensual',
            'metaMalaMensual',
            'totalesMesesActual',
            'totalesMesesAnterior',
            'anioActual',
            'anioAnterior',
            'promedioMensual',

            // Proyecciones
            'proyeccion1',
            'proyeccion2',
            'metaMediaDiaria',
            'metaAltaDiaria',
        ));
    }



    public function reportePatronesCliente(Request $request)
    {
        /* =====================================================
         1️⃣ FILTROS - Mes actual por defecto
         ===================================================== */

        $mesActual = $request->input('mes', now()->month);
        $anioActual = $request->input('anio', now()->year);

        // Primer y último día del mes seleccionado
        $fechaInicio = Carbon::create($anioActual, $mesActual, 1)->startOfDay();
        $fechaFin = Carbon::create($anioActual, $mesActual, 1)->endOfMonth()->endOfDay();

        // Mismo mes pero del año anterior (para rangos de referencia)
        $fechaInicioReferencia = Carbon::create($anioActual - 1, $mesActual, 1)->startOfDay();
        $fechaFinReferencia = Carbon::create($anioActual - 1, $mesActual, 1)->endOfMonth()->endOfDay();

        /* =====================================================
         2️⃣ TOP 15 CLIENTES (desde enero 2025 hasta hoy)
         ===================================================== */

        $top15Clientes = Operacion::where('tenant_id', auth()->user()->tenant_id)->selectRaw('cliente_id, COUNT(*) as total')
            ->whereBetween('fecha_cruce_estimada', [
                Carbon::create(2025, 1, 1),
                now()
            ])
            ->whereNotNull('cliente_id')
            ->groupBy('cliente_id')
            ->orderByDesc('total')
            //->limit(15)
            ->pluck('cliente_id');

        /* =====================================================
         3️⃣ OPERACIONES DEL MES ACTUAL (solo top 15)
         ===================================================== */

        $operacionesMesActual = Operacion::where('tenant_id', auth()->user()->tenant_id)->whereBetween('fecha_cruce_estimada', [$fechaInicio, $fechaFin])
            ->whereIn('cliente_id', $top15Clientes)
            ->with('cliente')
            ->get();

        /* =====================================================
         4️⃣ OPERACIONES DEL MES ANTERIOR (para rangos)
         ===================================================== */

        $operacionesMesReferencia = Operacion::where('tenant_id', auth()->user()->tenant_id)->whereBetween('fecha_cruce_estimada', [$fechaInicioReferencia, $fechaFinReferencia])
            ->whereIn('cliente_id', $top15Clientes)
            ->get();

        /* =====================================================
         5️⃣ CALCULAR RANGOS POR CLIENTE (del mes de referencia)
         ===================================================== */

        $rangosPorCliente = [];

        foreach ($top15Clientes as $clienteId) {
            // Operaciones del cliente en el mes de referencia (año anterior)
            $operacionesCliente = $operacionesMesReferencia->where('cliente_id', $clienteId);

            if ($operacionesCliente->isEmpty()) {
                // Cliente nuevo - sin historial
                $rangosPorCliente[$clienteId] = [
                    'minimo' => 0,
                    'promedio' => 0,
                    'maximo' => 0,
                    'es_nuevo' => true
                ];
            } else {
                // Agrupar por día y contar operaciones
                $operacionesPorDia = $operacionesCliente
                    ->groupBy(fn($item) => $item->fecha_cruce_estimada ? $item->fecha_cruce_estimada->format('Y-m-d') : null)
                    ->whereNotNull()
                    ->map->count();

                $rangosPorCliente[$clienteId] = [
                    'minimo' => $operacionesPorDia->min(),
                    'promedio' => round($operacionesPorDia->avg(), 1),
                    'maximo' => $operacionesPorDia->max(),
                    'es_nuevo' => false
                ];
            }
        }

        /* =====================================================
         6️⃣ CONSTRUIR PATRONES POR CLIENTE
         ===================================================== */

        $patronesCliente = [];

        foreach ($top15Clientes as $clienteId) {
            $cliente = \App\Models\Cliente::find($clienteId);

            if (!$cliente)
                continue;

            // Operaciones del cliente en el mes actual
            $operacionesCliente = $operacionesMesActual->where('cliente_id', $clienteId);

            // Agrupar por día
            $operacionesPorDia = $operacionesCliente
                ->groupBy(fn($item) => $item->fecha_cruce_estimada ? $item->fecha_cruce_estimada->format('Y-m-d') : null)
                ->whereNotNull()
                ->map(function ($grupo) {
                    return [
                        'fecha_cruce_estimada' => $grupo->first()->fecha_cruce_estimada,
                        'cantidad' => $grupo->count()
                    ];
                });

            // Construir array de todos los días del mes
            $diasDelMes = [];
            $totalMes = 0;

            for ($dia = 1; $dia <= $fechaFin->day; $dia++) {
                $fechaDia = Carbon::create($anioActual, $mesActual, $dia);
                $fechaDiaStr = $fechaDia->format('Y-m-d');

                $cantidad = $operacionesPorDia->get($fechaDiaStr)['cantidad'] ?? 0;
                $totalMes += $cantidad;

                // Determinar color según rangos
                $rangos = $rangosPorCliente[$clienteId];
                $colorData = $this->asignarColorSegunRango($cantidad, $rangos);

                $diasDelMes[] = [
                    'fecha_cruce_estimada' => $fechaDiaStr,
                    'dia_semana' => $fechaDia->locale('es')->dayName,
                    'dia_numero' => $dia,
                    'cantidad' => $cantidad,
                    'color' => $colorData['color'],
                    'estado' => $colorData['estado'],
                    'es_hoy' => $fechaDia->isToday()
                ];
            }

            // Obtener productos únicos del cliente en el mes
            $productosCliente = $operacionesCliente
                ->pluck('nombre_producto')
                ->filter() // Eliminar nulos
                ->unique()
                ->take(5) // Máximo 5 productos para no saturar
                ->values();

            $patronesCliente[] = [
                'cliente_id' => $clienteId,
                'cliente_nombre' => $cliente->nombre,
                'rangos' => $rangosPorCliente[$clienteId],
                'dias' => $diasDelMes,
                'total_mes' => $totalMes,
                'productos' => $productosCliente
            ];
        }

        // Ordenar por total del mes (descendente)
        $patronesCliente = collect($patronesCliente)->sortByDesc('total_mes')->values();

        // Agregar número de ranking
        $patronesCliente = $patronesCliente->map(function ($cliente, $index) {
            $cliente['ranking'] = $index + 1; // 🆕 AGREGAR RANKING
            return $cliente;
        });

        /* =====================================================
         7️⃣ RETORNO A LA VISTA
         ===================================================== */

        return view('reportes.patrones_cliente', compact(
            'patronesCliente',
            'mesActual',
            'anioActual',
            'fechaInicio',
            'fechaFin'
        ))->with('controlador', $this);
        ;
    }

    /* =====================================================
     🎨 FUNCIÓN AUXILIAR PARA PATRONES-CLIENTE - Asignar color según rangos
     ===================================================== */

    private function asignarColorSegunRango($cantidad, $rangos)
    {
        // Sin operaciones
        if ($cantidad == 0) {
            return [
                'color' => 'gris',
                'estado' => 'sin-operaciones'
            ];
        }
        // Cliente nuevo - todo en verde
        if ($rangos['es_nuevo']) {
            return [
                'color' => 'verde',
                'estado' => 'nuevo'
            ];
        }



        // Calcular umbrales
        $promedio = $rangos['promedio'];
        $maximo = $rangos['maximo'];

        // Rojo: por debajo del promedio
        if ($cantidad < $promedio) {
            return [
                'color' => 'rojo',
                'estado' => 'bajo'
            ];
        }

        // Amarillo: entre promedio y máximo
        if ($cantidad >= $promedio && $cantidad < $maximo) {
            return [
                'color' => 'amarillo',
                'estado' => 'medio'
            ];
        }

        // Verde: igual o superior al máximo
        return [
            'color' => 'verde',
            'estado' => 'alto'
        ];
    }


    public function obtenerIconoProducto($nombreProducto)
    {
        // Normalizar nombre (minúsculas, sin acentos)
        $producto = strtolower(trim($nombreProducto));
        $producto = $this->removerAcentos($producto);

        // Mapeo de productos a emojis
        $mapeo = [
            // Verduras
            'zanahoria' => '🥕',
            'brocoli' => '🥦',
            'lechuga' => '🥬',
            'pepino' => '🥒',
            'tomate' => '🍅',
            'chile' => '🌶️',
            'cebolla' => '🧅',
            'esparrago' => '🌱',
            'espinaca' => '🥬',
            'calabaza' => '🎃',
            'berenjena' => '🍆',
            'maiz' => '🌽',

            // Frutas
            'aguacate' => '🥑',
            'fresa' => '🍓',
            'frambuesa' => '🫐',
            'arandano' => '🫐',
            'mora' => '🫐',
            'berry' => '🫐',
            'berries' => '🫐',
            'mango' => '🥭',
            'papaya' => '🍈',
            'melon' => '🍈',
            'sandia' => '🍉',
            'uva' => '🍇',
            'platano' => '🍌',
            'manzana' => '🍎',
            'naranja' => '🍊',
            'limon' => '🍋',
            'pina' => '🍍',
            'coco' => '🥥',
            'kiwi' => '🥝',
            'cereza' => '🍒',
            'durazno' => '🍑',
            'pera' => '🍐',

            // Otros
            'flor' => '🌸',
            'planta' => '🌿',
            'hierba' => '🌿',
            'carton' => '📦',
            'empaque' => '📦',

        ];
        //🫐🥑🍎🍐🍊🍋🍓🍇🍉🍌🍋‍🟩🍈🍒🍑🥭🍆🍅🥝🥥🍍🫛🥦🥬🥒🥕🌽🫑🌶️🧄🧅🥔🍠🫚📦

        // Buscar coincidencias parciales
        foreach ($mapeo as $keyword => $emoji) {
            if (strpos($producto, $keyword) !== false) {
                return $emoji;
            }
        }

        // Si no encuentra coincidencia, retornar icono genérico
        return '📦';
    }

    public function removerAcentos($cadena)
    {
        $acentos = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'ñ' => 'n',
            'Ñ' => 'N'
        ];

        return strtr($cadena, $acentos);
    }


    public function calendarioPrimerasOperaciones(Request $request)
    {
        $año = $request->get('año', date('Y'));

        // Obtener la fecha de la primera exportación de cada cliente
        // IMPORTANTE: Usar Eloquent para que el global scope de tenant se aplique
        $tenantId = auth()->user()->tenant_id;
        $primerasOperaciones = Operacion::where('tenant_id', auth()->user()->tenant_id)->join('cliente', 'operaciones.cliente_id', '=', 'cliente.id')
            ->select(
                'operaciones.cliente_id',
                'cliente.nombre as cliente_nombre',
                DB::raw('MIN(operaciones.fecha_cruce_estimada) as primera_operacion')
            )
            ->whereNull('cliente.deleted_at')
            ->groupBy('operaciones.cliente_id', 'cliente.nombre')
            ->get();

        // Organizar por año y mes
        $calendarioClientes = [];

        foreach ($primerasOperaciones as $operacion) {
            $fecha = \Carbon\Carbon::parse($operacion->primera_operacion);
            $añoOperacion = $fecha->year;
            $mes = $fecha->month;

            if (!isset($calendarioClientes[$añoOperacion])) {
                $calendarioClientes[$añoOperacion] = [];
            }

            if (!isset($calendarioClientes[$añoOperacion][$mes])) {
                $calendarioClientes[$añoOperacion][$mes] = [];
            }

            $calendarioClientes[$añoOperacion][$mes][] = [
                'cliente' => $operacion->cliente_nombre,
                'fecha_cruce_estimada' => $operacion->primera_operacion
            ];
        }

        // Ordenar años de más reciente a más antiguo
        krsort($calendarioClientes);

        return view('reportes.calendario-primeras-operaciones', [
            'calendarioClientes' => $calendarioClientes,
            'añoSeleccionado' => $año
        ]);
    }

    /**
     * Reporte comparativo por Aduana
     */
    public function reporteAduanas(Request $request)
    {
        $aduanaId = $request->input('aduana_id');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $clientesExcluidos = $request->input('clientes_excluidos', []);
        $comparacionId = $request->input('comparacion_id');

        $aduanas = Aduana::orderBy('nombre')->get();

        // Si no hay aduana seleccionada, solo mostrar formulario
        if (!$aduanaId) {
            return view('reportes.reporte-aduanas', compact('aduanas', 'aduanaId', 'fechaInicio', 'fechaFin', 'clientesExcluidos', 'comparacionId'));
        }

        $aduana = Aduana::findOrFail($aduanaId);

        // Fechas por defecto (último año)
        if (!$fechaInicio || !$fechaFin) {
            $fechaFin = now()->format('Y-m-d');
            $fechaInicio = now()->subYear()->format('Y-m-d');
        }

        // Obtener TODOS los clientes con operaciones en esta aduana/rango (para el filtro de checkboxes)
        // NOTA: El join bypassa el global scope de Cliente, pero Operacion ya filtra por tenant
        $clientesDisponibles = Operacion::where('operaciones.tenant_id', auth()->user()->tenant_id)->join('cliente', 'operaciones.cliente_id', '=', 'cliente.id')
            ->where('operaciones.aduana_id', $aduanaId)
            ->whereBetween('operaciones.fecha_cruce_estimada', [$fechaInicio, $fechaFin])
            ->whereNull('cliente.deleted_at')
            ->select('cliente.id', 'cliente.nombre')
            ->distinct()
            ->orderBy('cliente.nombre')
            ->get();

        // Query base con exclusión de clientes
        $queryBase = Operacion::where('operaciones.tenant_id', auth()->user()->tenant_id)->where('operaciones.aduana_id', $aduanaId)
            ->whereBetween('operaciones.fecha_cruce_estimada', [$fechaInicio, $fechaFin]);

        if (!empty($clientesExcluidos)) {
            $queryBase = $queryBase->whereNotIn('cliente_id', $clientesExcluidos);
        }

        // KPI: Total operaciones
        $totalOperaciones = (clone $queryBase)->count();

        // KPI: Total pedimentos (expedientes únicos con numero_pedimento)
        $queryPedimentos = Expediente::where('aduana_id', $aduanaId)
            ->whereNotNull('numero_pedimento')
            ->where('numero_pedimento', '!=', '')
            ->where(function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_pago_pedimento', [$fechaInicio, $fechaFin])
                    ->orWhereBetween('fecha_apertura', [$fechaInicio, $fechaFin]);
            });

        if (!empty($clientesExcluidos)) {
            $queryPedimentos = $queryPedimentos->whereNotIn('cliente_id', $clientesExcluidos);
        }

        $totalPedimentos = (clone $queryPedimentos)->distinct('numero_pedimento')->count('numero_pedimento');

        // KPI: Clientes activos (únicos)
        $totalClientes = (clone $queryBase)->distinct('cliente_id')->count('cliente_id');

        // Desglose por cliente
        $desglosePorCliente = (clone $queryBase)
            ->join('cliente', 'operaciones.cliente_id', '=', 'cliente.id')
            ->whereNull('cliente.deleted_at')
            ->select(
                'cliente.id as cliente_id',
                'cliente.nombre',
                DB::raw('COUNT(*) as operaciones')
            )
            ->groupBy('cliente.id', 'cliente.nombre')
            ->orderByDesc('operaciones')
            ->get();

        // Agregar pedimentos por cliente
        foreach ($desglosePorCliente as $item) {
            $qPed = Expediente::where('aduana_id', $aduanaId)
                ->where('cliente_id', $item->cliente_id)
                ->whereNotNull('numero_pedimento')
                ->where('numero_pedimento', '!=', '')
                ->where(function ($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('fecha_pago_pedimento', [$fechaInicio, $fechaFin])
                        ->orWhereBetween('fecha_apertura', [$fechaInicio, $fechaFin]);
                });
            $item->pedimentos = $qPed->distinct('numero_pedimento')->count('numero_pedimento');
        }

        // Gráfico: Operaciones por mes (aduana principal)
        $operacionesPorMes = (clone $queryBase)
            ->select(
                DB::raw('MONTH(fecha_cruce_estimada) as mes'),
                DB::raw('YEAR(fecha_cruce_estimada) as anio'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('anio', 'mes')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();

        // Gráfico: Pedimentos por mes (aduana principal)
        $pedimentosPorMes = (clone $queryPedimentos)
            ->select(
                DB::raw('MONTH(COALESCE(fecha_pago_pedimento, fecha_apertura)) as mes'),
                DB::raw('YEAR(COALESCE(fecha_pago_pedimento, fecha_apertura)) as anio'),
                DB::raw('COUNT(DISTINCT numero_pedimento) as total')
            )
            ->groupBy('anio', 'mes')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();

        // ====== ADUANA DE COMPARACIÓN (sombra) ======
        $aduanaComparacion = null;
        $compOperacionesPorMes = collect();
        $compPedimentosPorMes = collect();

        if ($comparacionId && $comparacionId != $aduanaId) {
            $aduanaComparacion = Aduana::find($comparacionId);

            if ($aduanaComparacion) {
                $compOperacionesPorMes = Operacion::where('tenant_id', auth()->user()->tenant_id)->where('aduana_id', $comparacionId)
                    ->whereBetween('fecha_cruce_estimada', [$fechaInicio, $fechaFin])
                    ->select(
                        DB::raw('MONTH(fecha_cruce_estimada) as mes'),
                        DB::raw('YEAR(fecha_cruce_estimada) as anio'),
                        DB::raw('COUNT(*) as total')
                    )
                    ->groupBy('anio', 'mes')
                    ->orderBy('anio')
                    ->orderBy('mes')
                    ->get();

                $compPedimentosPorMes = Expediente::where('aduana_id', $comparacionId)
                    ->whereNotNull('numero_pedimento')
                    ->where('numero_pedimento', '!=', '')
                    ->where(function ($q) use ($fechaInicio, $fechaFin) {
                        $q->whereBetween('fecha_pago_pedimento', [$fechaInicio, $fechaFin])
                            ->orWhereBetween('fecha_apertura', [$fechaInicio, $fechaFin]);
                    })
                    ->select(
                        DB::raw('MONTH(COALESCE(fecha_pago_pedimento, fecha_apertura)) as mes'),
                        DB::raw('YEAR(COALESCE(fecha_pago_pedimento, fecha_apertura)) as anio'),
                        DB::raw('COUNT(DISTINCT numero_pedimento) as total')
                    )
                    ->groupBy('anio', 'mes')
                    ->orderBy('anio')
                    ->orderBy('mes')
                    ->get();
            }
        }

        // Preparar datos para Chart.js — generar labels del rango
        $inicio = Carbon::parse($fechaInicio)->startOfMonth();
        $fin = Carbon::parse($fechaFin)->endOfMonth();
        $labelsGrafico = [];
        $dataOperaciones = [];
        $dataPedimentos = [];
        $dataCompOperaciones = [];
        $dataCompPedimentos = [];

        $cursor = $inicio->copy();
        while ($cursor <= $fin) {
            $mes = $cursor->month;
            $anio = $cursor->year;
            $labelsGrafico[] = $cursor->translatedFormat('M Y');

            $op = $operacionesPorMes->first(fn($item) => $item->mes == $mes && $item->anio == $anio);
            $dataOperaciones[] = $op ? $op->total : 0;

            $ped = $pedimentosPorMes->first(fn($item) => $item->mes == $mes && $item->anio == $anio);
            $dataPedimentos[] = $ped ? $ped->total : 0;

            // Comparación
            $compOp = $compOperacionesPorMes->first(fn($item) => $item->mes == $mes && $item->anio == $anio);
            $dataCompOperaciones[] = $compOp ? $compOp->total : 0;

            $compPed = $compPedimentosPorMes->first(fn($item) => $item->mes == $mes && $item->anio == $anio);
            $dataCompPedimentos[] = $compPed ? $compPed->total : 0;

            $cursor->addMonth();
        }

        return view('reportes.reporte-aduanas', compact(
            'aduanas',
            'aduana',
            'aduanaId',
            'fechaInicio',
            'fechaFin',
            'clientesExcluidos',
            'clientesDisponibles',
            'comparacionId',
            'aduanaComparacion',
            'totalOperaciones',
            'totalPedimentos',
            'totalClientes',
            'desglosePorCliente',
            'labelsGrafico',
            'dataOperaciones',
            'dataPedimentos',
            'dataCompOperaciones',
            'dataCompPedimentos'
        ));
    }




}