<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\Operacion;
use App\Models\Expediente;
use App\Models\Cliente;
use App\Models\User;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $hoy = Carbon::today();
        $tenantId = auth()->user()->tenant_id ?? null;

        // Si el usuario no tiene tenant, retornar datos vacíos
        if (!$tenantId) {
            return view('admin.dashboard', [
                'tramitesHoy' => 0,
                'tramitesTotales' => 0,
                'clientesActivos' => 0,
                'usuariosActivos' => 0,
                'remesasCompletadas' => 0,
                'remesasPendientes' => 0,
                'modLabels' => [],
                'modData' => [],
                'clientesLabels' => [],
                'clientesData' => [],
                'productosLabels' => [],
                'productosData' => [],
                'usuariosActivosTop' => collect(),
                'lineLabels' => [],
                'lineData' => [],
            ]);
        }

        // KPIs - FILTRADOS POR TENANT
        $tramitesHoy = Operacion::where('tenant_id', $tenantId)->where('estado', '!=', 'cancelada')
            ->whereDate('fecha_registro', $hoy)->count();
        $tramitesTotales = Operacion::where('tenant_id', $tenantId)->where('estado', '!=', 'cancelada')->count();
        $clientesActivos = Cliente::where('tenant_id', $tenantId)->count();
        $usuariosActivos = User::where('tenant_id', $tenantId)->where('active', 1)->count();

        // Finalizados
        $remesasCompletadas = Operacion::where('tenant_id', $tenantId)->where('estado', '!=', 'cancelada')
            ->whereDate('created_at', $hoy)
            ->where('estado', 'Finalizado')
            ->count();
        // Pendientes (todos los demás)
        $remesasPendientes = Operacion::where('tenant_id', $tenantId)->where('estado', '!=', 'cancelada')
            ->whereDate('created_at', $hoy)
            ->where('estado', '!=', 'Finalizado')
            ->count();

        // Operaciones por Modulación (agrupado, incluyendo null como "Sin dato") - FILTRADO POR TENANT
        $modulacionesAssoc = DB::table('operaciones')->where('estado', '!=', 'cancelada')
            ->where('tenant_id', $tenantId)
            ->select(DB::raw("COALESCE(modulacion,'Sin dato') as modulacion"), DB::raw('COUNT(*) as total'))
            ->groupBy('modulacion')
            ->orderByDesc('total')
            ->pluck('total', 'modulacion')
            ->toArray();

        $modLabels = array_keys($modulacionesAssoc);
        $modData = array_values($modulacionesAssoc);

        // Trámites por cliente (TOP 10) - FILTRADO POR TENANT
        $clientesRows = DB::table('operaciones as e')
            ->where('e.tenant_id', $tenantId)
            ->join('cliente as c', 'c.id', '=', 'e.cliente_id')
            ->select('c.nombre_empresa as cliente', DB::raw('COUNT(*) as total'))
            ->groupBy('c.nombre_empresa')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
        $clientesLabels = $clientesRows->pluck('cliente')->toArray();
        $clientesData = $clientesRows->pluck('total')->toArray();

        // Productos más exportados (TOP 10) - FILTRADO POR TENANT
        $productosRows = DB::table('operaciones')->where('estado', '!=', 'cancelada')
            ->where('tenant_id', $tenantId)
            ->select('nombre_producto', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nombre_producto')
            ->groupBy('nombre_producto')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $productosLabels = $productosRows->pluck('nombre_producto')->toArray();
        $productosData = $productosRows->pluck('total')->toArray();

        // Usuarios más activos (documentadores) - FILTRADO POR TENANT
        $usuariosActivosTop = Operacion::where('tenant_id', $tenantId)->where('estado', '!=', 'cancelada')
            ->select('usuario_registro_id', DB::raw('COUNT(*) as total'))
            ->groupBy('usuario_registro_id')
            ->with('documentador')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
        $usuariosActivosTop = $usuariosActivosTop ?? collect();

        // Ejemplo de evolución diaria de trámites (simulado)
        $lineLabels = collect(range(1, 30))->map(fn($d) => "2025-08-$d")->toArray();
        $lineData = collect(range(1, 30))->map(fn() => rand(5, 50))->toArray();


        return view('admin.dashboard', [
            'tramitesHoy' => $tramitesHoy ?? 0,
            'tramitesTotales' => $tramitesTotales ?? 0,
            'clientesActivos' => $clientesActivos ?? 0,
            'usuariosActivos' => $usuariosActivos ?? 0,
            'modLabels' => $modLabels ?? [],
            'modData' => $modData ?? [],
            'clientesLabels' => $clientesLabels ?? [],
            'clientesData' => $clientesData ?? [],
            'productosLabels' => $productosLabels ?? [],
            'productosData' => $productosData ?? [],
            'usuariosActivosTop' => $usuariosActivosTop ?? collect(),
            'remesasCompletadas' => $remesasCompletadas,
            'remesasPendientes' => $remesasPendientes,
            'lineLabels' => $lineLabels,
            'lineData' => $lineData,
        ]);
    }


    public function indexcliente()
    {
        $user = auth()->user();
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        //$inicioMes = Carbon::now()->addDays(-15);
        $finMes = Carbon::now()->endOfMonth();


        //Datos por defecto.
        $pedimentosMes = 0;
        $pedimentosVerde = 0;
        $pedimentosRojo = 0;

        $operacionesHoy = 0;
        $pedimentosRecientes = 0;

        // Si el usuario está ligado a un cliente, filtramos todo por ese cliente
        $filtroCliente = $user->cliente_id ? ['cliente_id' => $user->cliente_id] : [];



        // Métricas rápidas
        $pedimentosMes = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')->whereBetween('fecha_registro', [$inicioMes, $finMes])->count();

        $pedimentosVerde = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')->whereBetween('fecha_registro', [$inicioMes, $finMes])->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();

        $pedimentosRojo = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')->whereBetween('fecha_registro', [$inicioMes, $finMes])->whereIn('modulacion', [
            'RECONOCIMIENTO ADUANERO',
            'RECONOCIMIENTO ADUANERO CONCLUIDO'
        ])->count();

        // Evolución mensual (últimos 6 meses)
        /*$evolucion = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')->select(
            DB::raw("DATE_FORMAT(fecha, '%Y-%m') as mes"),
            DB::raw("COUNT(*) as total")
        )
            ->where('fecha_registro', '>=', Carbon::now()->subMonths(6))
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        $labels = $evolucion->keys()->map(function ($mes) {
            return Carbon::createFromFormat('Y-m', $mes)->locale('es')->translatedFormat('M Y');
        });

        $values = $evolucion->values();*/
        $evolucion = Operacion::where('cliente_id', $user->cliente_id)->where('estado', '!=', 'cancelada')
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as total")
            ->where('fecha_registro', '>=', now()->subMonths(6))
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        $labels = $evolucion->keys()->map(fn($mes) => Carbon::createFromFormat('Y-m', $mes)->locale('es')->translatedFormat('M Y'));
        $values = $evolucion->values();

        // Operaciones del día
        $operacionesHoy = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')->with(['aduana', 'patente'])
            ->whereDate('fecha_registro', $hoy)
            ->orderBy('fecha_registro', 'desc')
            ->take(5)
            ->get();

        // Pedimentos recientes (expedientes)
        $pedimentosRecientes = Expediente::where($filtroCliente)->where('estado', 'cerrado')
            ->with(['cliente', 'aduana', 'patente'])
            ->orderBy('fecha_apertura', 'desc')
            ->take(4)
            ->get();



        return view('clientes.dashboard', compact(
            'pedimentosMes',
            'pedimentosVerde',
            'pedimentosRojo',
            'labels',
            'values',
            'operacionesHoy',
            'pedimentosRecientes'
        ));
    }
    public function operacionescliente_Original(Request $request)
    {
        $user = Auth::user();

        // Validar cliente_id
        if (!$user->cliente_id) {
            abort(403, 'No tienes un cliente asignado.');
        }

        // Filtro por fechas
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->toDateString());

        $query = Operacion::where('cliente_id', $user->cliente_id)->where('estado', '!=', 'cancelada')
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin]);

        $operaciones = $query->orderBy('fecha_registro', 'desc')->get();

        // Métricas
        $total = $query->count();
        $verdes = $query->where('modulacion', 'DESADUANAMIENTO LIBRE')->count();
        //$rojos = $query->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO'])->count();
        $rojos = Operacion::where('cliente_id', $user->clienteId)->where('estado', '!=', 'cancelada')
            ->whereDate('fecha_registro', today())
            ->whereIn('modulacion', [
                'RECONOCIMIENTO ADUANERO',
                'RECONOCIMIENTO ADUANERO CONCLUIDO',
            ])
            ->count();
        $hoy = Operacion::where('cliente_id', $user->cliente_id)->where('estado', '!=', 'cancelada')
            ->whereDate('fecha_registro', Carbon::today())
            ->count();

        return view('clientes.operaciones', compact('operaciones', 'total', 'verdes', 'rojos', 'hoy', 'fechaInicio', 'fechaFin'));
    }

    public function operacionescliente(Request $request)
    {
        $user = Auth::user();

        if (!$user->cliente_id) {
            abort(403, 'No tienes un cliente asignado.');
        }

        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->toDateString());
        $busqueda = $request->input('busqueda');

        // Base query
        $baseQuery = Operacion::where('cliente_id', $user->cliente_id)->where('estado', '!=', 'cancelada')
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin]);

        // Agregar filtro de búsqueda por número de factura
        if ($busqueda) {
            $baseQuery->where('num_factura', 'LIKE', "%{$busqueda}%");
        }

        //$operaciones = $baseQuery->clone()->orderBy('fecha_registro', 'desc')->get();

        // 🔹 PAGINACIÓN: Cambiar get() por paginate()
        $operaciones = $baseQuery->clone()
            ->with(['aduana', 'patente', 'bodega']) // Cargar relaciones si las necesitas
            ->orderBy('fecha_registro', 'desc')
            ->paginate(5)
            ->appends($request->all()); // Mantener filtros en la paginación

        // Métricas (usar clone para no “consumir” el builder original)
        $total = (clone $baseQuery)->count();

        $verdes = (clone $baseQuery)
            ->where('modulacion', 'DESADUANAMIENTO LIBRE')
            ->count();

        $rojos = (clone $baseQuery)
            ->whereIn('modulacion', [
                'RECONOCIMIENTO ADUANERO',
                'RECONOCIMIENTO ADUANERO CONCLUIDO',
            ])
            ->count();

        $hoy = Operacion::where('cliente_id', $user->cliente_id)->where('estado', '!=', 'cancelada')
            ->whereDate('fecha_registro', Carbon::today())
            ->count();


        // 🔹 Operaciones por Aduana (para barra horizontal)
        $aduanasData = Operacion::select('aduana_id', DB::raw('count(*) as total'))
            ->where('cliente_id', $user->cliente_id)
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
            ->groupBy('aduana_id')
            ->with('aduana') // para traer el nombre
            ->get();

        $aduanasLabels = $aduanasData->map(fn($a) => $a->aduana->nombre_aduana ?? 'N/A');
        $aduanasTotals = $aduanasData->pluck('total');

        // 🔹 Operaciones por Bodega (para pastel)
        $bodegasData = Operacion::select('bodega_id', DB::raw('count(*) as total'))
            ->where('cliente_id', $user->cliente_id)
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
            ->groupBy('bodega_id')
            ->with('bodega')
            ->get();

        $bodegasLabels = $bodegasData->map(fn($b) => 'Bodega ' . $b->bodega->nombre_bodega ?? 'Sin Bodega');
        $bodegasTotals = $bodegasData->pluck('total');

        return view('clientes.operaciones', compact(
            'operaciones',
            'total',
            'verdes',
            'rojos',
            'hoy',
            'fechaInicio',
            'fechaFin',
            'aduanasLabels',
            'aduanasTotals',
            'bodegasLabels',
            'bodegasTotals'
        ));
    }


    public function admincliente_OLD(Request $request)
    {
        $user = auth()->user();
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        //$inicioMes = Carbon::now()->addDays(-15);
        $finMes = Carbon::now()->endOfMonth();


        //Datos por defecto.
        $pedimentosMes = 0;
        $pedimentosVerde = 0;
        $pedimentosRojo = 0;

        $operacionesHoy = 0;
        $pedimentosRecientes = 0;

        // Si el usuario está ligado a un cliente, filtramos todo por ese cliente
        $filtroCliente = $user->cliente_id ? ['cliente_id' => $user->cliente_id] : [];


        // Métricas rápidas
        $pedimentosMes = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')->whereBetween('fecha_registro', [$inicioMes, $finMes])->count();

        $pedimentosVerde = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')->where('modulacion', 'DESADUANAMIENTO LIBRE')->whereBetween('fecha_registro', [$inicioMes, $finMes])->count();

        $pedimentosRojo = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')->whereIn('modulacion', [
            'RECONOCIMIENTO ADUANERO',
            'RECONOCIMIENTO ADUANERO CONCLUIDO'
        ])->whereBetween('fecha_registro', [$inicioMes, $finMes])->count();

        // Definir rango de fechas para la evolución
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        // Si no se proporcionan fechas, usar últimos 6 meses por defecto
        if (!$fechaInicio || !$fechaFin) {
            $fechaInicio = Carbon::now()->subMonths(6)->startOfMonth();
            $fechaFin = Carbon::now()->endOfMonth();
        } else {
            $fechaInicio = Carbon::parse($fechaInicio);
            $fechaFin = Carbon::parse($fechaFin);
        }
        // Evolución mensual con filtro de fechas
        $evolucion = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as total")
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        $labels = $evolucion->keys()->map(fn($mes) => Carbon::createFromFormat('Y-m', $mes)->locale('es')->translatedFormat('M Y'));
        $values = $evolucion->values();


        // Evolución mensual (últimos 6 meses)
        /*$evolucion = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')->select(
            DB::raw("DATE_FORMAT(fecha, '%Y-%m') as mes"),
            DB::raw("COUNT(*) as total")
        )
            ->where('fecha_registro', '>=', Carbon::now()->subMonths(6))
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        $labels = $evolucion->keys()->map(function ($mes) {
            return Carbon::createFromFormat('Y-m', $mes)->locale('es')->translatedFormat('M Y');
        });

        $values = $evolucion->values();*/
        /*$evolucion = Operacion::where('cliente_id', $user->cliente_id)->where('estado', '!=', 'cancelada')
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as total")
            ->where('fecha_registro', '>=', now()->subMonths(6))
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        $labels = $evolucion->keys()->map(fn($mes) => Carbon::createFromFormat('Y-m', $mes)->locale('es')->translatedFormat('M Y'));
        $values = $evolucion->values();*/

        // Operaciones del día
        $operacionesHoy = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')->with(['aduana', 'patente'])
            ->whereDate('fecha_registro', $hoy)
            ->orderBy('fecha_registro', 'desc')
            ->take(5)
            ->get();



        $pedimentosRecientes = Expediente::where($filtroCliente)->where('estado', 'cerrado')
            ->with(['cliente', 'aduana', 'patente'])
            ->orderBy('fecha_apertura', 'desc')
            ->take(4)
            ->get();



        return view('clientes.dashboard', compact(
            'pedimentosMes',
            'pedimentosVerde',
            'pedimentosRojo',
            'labels',
            'values',
            'operacionesHoy',
            'pedimentosRecientes',
            'fechaInicio',
            'fechaFin'
        ));
    }
    public function admincliente_Old2(Request $request)
    {
        $user = auth()->user();
        $filtroCliente = $user->cliente_id ? ['cliente_id' => $user->cliente_id] : [];
        $clienteId = $user->cliente_id;

        // 1. FECHAS
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        if (!$request->input('fecha_inicio') || !$request->input('fecha_fin')) {
            $desde = Carbon::now()->subMonths(11)->startOfMonth(); // 12 meses atrás para historial anual perfecto
            $hasta = Carbon::now()->endOfMonth();
        } else {
            $desde = Carbon::parse($request->input('fecha_inicio'));
            $hasta = Carbon::parse($request->input('fecha_fin'));
        }

        // Rango para histórico anual (Año natural del periodo seleccionado o actual)
        $inicioAnual = $hasta->copy()->startOfYear();
        $finAnual = $hasta->copy()->endOfYear();

        // 2. MÉTRICAS KPI (Mes Actual)
        $pedimentosMes = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->whereBetween('fecha_registro', [$inicioMes, $finMes])
            ->count();

        $pedimentosVerde = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->where('modulacion', 'DESADUANAMIENTO LIBRE')
            ->whereBetween('fecha_registro', [$inicioMes, $finMes])
            ->count();

        $pedimentosRojo = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO'])
            ->whereBetween('fecha_registro', [$inicioMes, $finMes])
            ->count();

        $sobrepesosMes = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->whereBetween('fecha_registro', [$inicioMes, $finMes])
            ->where('sobrepeso', '>', 0)
            ->count();

        // 3. DATOS PARA GRÁFICAS

        // A) Histórico Anual (LINEA) - Últimos 12 meses
        $historialAnualData = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as total")
            ->whereBetween('fecha_registro', [$inicioAnual, $finAnual])
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        // Rellenar meses vacíos
        $historialLabels = [];
        $historialValues = [];
        $tempDate = $inicioAnual->copy();
        while ($tempDate <= $finAnual) {
            $key = $tempDate->format('Y-m');
            $historialLabels[] = $tempDate->locale('es')->translatedFormat('M Y');
            $historialValues[] = $historialAnualData[$key] ?? 0;
            $tempDate->addMonth();
        }

        // B) Progreso Diario (BARRAS) - Mes Actual
        $progresoDiarioData = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->selectRaw("DATE(fecha_registro) as dia, COUNT(*) as total")
            ->whereBetween('fecha_registro', [$inicioMes, $finMes])
            ->groupBy('dia')
            ->orderBy('dia')
            ->pluck('total', 'dia');

        $diarioLabels = [];
        $diarioValues = [];
        $tempDia = $inicioMes->copy();
        while ($tempDia <= $finMes) { // Usamos hasta fin de mes actual o hasta hoy? Usualmente mes completo en eje X
            if ($tempDia > Carbon::now())
                break; // O mostrar hasta hoy
            $key = $tempDia->format('Y-m-d');
            $diarioLabels[] = $tempDia->format('d');
            $diarioValues[] = $progresoDiarioData[$key] ?? 0;
            $tempDia->addDay();
        }

        // C) Semáforo Global (PASTEL) - Acumulado del periodo filtrado o mes actual? Usualmente mes actual para KPIs
        // Ya calculados en $pedimentosVerde y $pedimentosRojo (Mes actual)

        // D) Operaciones por Aduana (STACKED BAR) - Mes actual
        $porAduana = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
            ->whereBetween('operaciones.fecha', [$inicioMes, $finMes])
            ->selectRaw("
                aduanas.nombre_aduana as aduana,
                SUM(CASE WHEN modulacion = 'DESADUANAMIENTO LIBRE' THEN 1 ELSE 0 END) as verdes,
                SUM(CASE WHEN modulacion IN ('RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO') THEN 1 ELSE 0 END) as rojos
            ")
            ->groupBy('aduanas.nombre_aduana')
            ->get();

        $aduanaLabels = $porAduana->pluck('aduana');
        $aduanaVerdes = $porAduana->pluck('verdes');
        $aduanaRojos = $porAduana->pluck('rojos');


        // 4. OTROS DATOS (Top Importers, Calendar, Agenda)
        $topImportadores = Operacion::where('estado', '!=', 'cancelada')->join('importadores', 'operaciones.importador_id', '=', 'importadores.id')
            ->select('importadores.nombre as importador', DB::raw('count(*) as total'))
            ->where('operaciones.cliente_id', $clienteId)
            ->whereBetween('operaciones.fecha', [$desde, $hasta])
            ->groupBy('importadores.nombre')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        // Calendar Heatmap Logic (Reutilizada)
        $calendarioMes = Carbon::today();
        $inicioCal = $calendarioMes->copy()->startOfMonth();
        $finCal = $calendarioMes->copy()->endOfMonth();
        $rawCalendario = Operacion::select(DB::raw('DATE(fecha_registro) as fecha'), DB::raw('count(*) as total'))
            ->where($filtroCliente)
            ->whereBetween('fecha_registro', [$inicioCal, $finCal])
            ->groupBy(DB::raw('DATE(fecha_registro)'))
            ->pluck('total', 'fecha_registro');
        $maxOps = $rawCalendario->max() ?? 1;
        $umbralBajo = max(1, $maxOps * 0.33);
        $umbralMedio = max(1, $maxOps * 0.66);
        $calendario = [];
        $ptr = $inicioCal->copy()->startOfWeek(Carbon::MONDAY);
        $finPtr = $finCal->copy()->endOfWeek(Carbon::SUNDAY);
        while ($ptr <= $finPtr) {
            $semana = [];
            for ($i = 0; $i < 7; $i++) {
                $fechaStr = $ptr->format('Y-m-d');
                $totalDia = $rawCalendario[$fechaStr] ?? 0;
                $intensidad = 'none';
                if ($totalDia > 0) {
                    if ($totalDia <= $umbralBajo)
                        $intensidad = 'low';
                    elseif ($totalDia <= $umbralMedio)
                        $intensidad = 'medium';
                    else
                        $intensidad = 'high';
                }
                $semana[] = [
                    'fecha_registro' => $fechaStr,
                    'dia' => $ptr->day,
                    'total' => $totalDia,
                    'es_mes_actual' => $ptr->month === $inicioCal->month,
                    'intensidad' => $intensidad
                ];
                $ptr->addDay();
            }
            $calendario[] = $semana;
        }

        $operacionesHoy = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->with(['aduana', 'expediente'])
            ->whereDate('fecha_registro', $hoy)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $pedimentosRecientes = Expediente::where($filtroCliente)
            ->where('estado', 'cerrado')
            ->with(['cliente', 'aduana'])
            ->orderBy('fecha_apertura', 'desc')
            ->take(4)
            ->get();

        // 5. WIDGET SIDEBAR: Importadores del Mes (Estrictamente mes actual)
        $importadoresMes = Operacion::where('estado', '!=', 'cancelada')->join('importadores', 'operaciones.importador_id', '=', 'importadores.id')
            ->select('importadores.nombre as importador', DB::raw('count(*) as total'))
            ->where($filtroCliente)
            ->whereBetween('operaciones.fecha', [$inicioMes, $finMes])
            ->groupBy('importadores.nombre')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('clientes.dashboard', compact(
            'pedimentosMes',
            'pedimentosVerde',
            'pedimentosRojo',
            'sobrepesosMes',
            'historialLabels',
            'historialValues', // Para Gráfica Linea Anual
            'diarioLabels',
            'diarioValues', // Para Gráfica Barras Diario
            'aduanaLabels',
            'aduanaVerdes',
            'aduanaRojos', // Para Stacked Bar
            'topImportadores',
            'importadoresMes',
            'calendario',
            'operacionesHoy',
            'pedimentosRecientes',
            'desde',
            'hasta'
        ));
    }
    public function admincliente(Request $request)
    {
        $user = auth()->user();
        $filtroCliente = $user->cliente_id ? ['cliente_id' => $user->cliente_id] : [];
        $clienteId = $user->cliente_id;

        // 1. FECHAS
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        if (!$request->input('fecha_inicio') || !$request->input('fecha_fin')) {
            $desde = Carbon::now()->subMonths(11)->startOfMonth(); // 12 meses atrás para historial anual perfecto
            $hasta = Carbon::now()->endOfMonth();
        } else {
            $desde = Carbon::parse($request->input('fecha_inicio'));
            $hasta = Carbon::parse($request->input('fecha_fin'));
        }

        // Rango para histórico anual (Año natural del periodo seleccionado o actual)
        $inicioAnual = $hasta->copy()->startOfYear();
        $finAnual = $hasta->copy()->endOfYear();

        // 2. MÉTRICAS KPI (Mes Actual)
        $pedimentosMes = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->whereBetween('fecha_registro', [$inicioMes, $finMes])
            ->count();

        $pedimentosVerde = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->where('modulacion', 'DESADUANAMIENTO LIBRE')
            ->whereBetween('fecha_registro', [$inicioMes, $finMes])
            ->count();

        $pedimentosRojo = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->whereIn('modulacion', ['RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO'])
            ->whereBetween('fecha_registro', [$inicioMes, $finMes])
            ->count();

        $sobrepesosMes = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->whereBetween('fecha_registro', [$inicioMes, $finMes])
            ->where('sobrepeso', '>', 0)
            ->count();

        // 3. DATOS PARA GRÁFICAS

        // A) Histórico Anual (LINEA) - Últimos 12 meses
        $historialAnualData = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as total")
            ->whereBetween('fecha_registro', [$inicioAnual, $finAnual])
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        // Rellenar meses vacíos
        $historialLabels = [];
        $historialValues = [];
        $tempDate = $inicioAnual->copy();
        while ($tempDate <= $finAnual) {
            $key = $tempDate->format('Y-m');
            $historialLabels[] = $tempDate->locale('es')->translatedFormat('M Y');
            $historialValues[] = $historialAnualData[$key] ?? 0;
            $tempDate->addMonth();
        }

        // B) Progreso Diario (BARRAS) - Mes Actual
        $progresoDiarioData = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->selectRaw("DATE(fecha_registro) as dia, COUNT(*) as total")
            ->whereBetween('fecha_registro', [$inicioMes, $finMes])
            ->groupBy('dia')
            ->orderBy('dia')
            ->pluck('total', 'dia');

        $diarioLabels = [];
        $diarioValues = [];
        $tempDia = $inicioMes->copy();
        while ($tempDia <= $finMes) { // Usamos hasta fin de mes actual o hasta hoy? Usualmente mes completo en eje X
            if ($tempDia > Carbon::now())
                break; // O mostrar hasta hoy
            $key = $tempDia->format('Y-m-d');
            $diarioLabels[] = $tempDia->format('d');
            $diarioValues[] = $progresoDiarioData[$key] ?? 0;
            $tempDia->addDay();
        }

        // C) Semáforo Global (PASTEL) - Acumulado del periodo filtrado o mes actual? Usualmente mes actual para KPIs
        // Ya calculados en $pedimentosVerde y $pedimentosRojo (Mes actual)

        // D) Operaciones por Aduana (STACKED BAR) - Mes actual
        $porAduana = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->join('aduanas', 'operaciones.aduana_id', '=', 'aduanas.id')
            ->whereBetween('operaciones.fecha', [$inicioMes, $finMes])
            ->selectRaw("
                aduanas.nombre_aduana as aduana,
                SUM(CASE WHEN modulacion = 'DESADUANAMIENTO LIBRE' THEN 1 ELSE 0 END) as verdes,
                SUM(CASE WHEN modulacion IN ('RECONOCIMIENTO ADUANERO', 'RECONOCIMIENTO ADUANERO CONCLUIDO') THEN 1 ELSE 0 END) as rojos
            ")
            ->groupBy('aduanas.nombre_aduana')
            ->get();

        $aduanaLabels = $porAduana->pluck('aduana');
        $aduanaVerdes = $porAduana->pluck('verdes');
        $aduanaRojos = $porAduana->pluck('rojos');


        // 4. OTROS DATOS (Top Importers, Calendar, Agenda)
        $topImportadores = Operacion::where('estado', '!=', 'cancelada')->join('importadores', 'operaciones.importador_id', '=', 'importadores.id')
            ->select('importadores.nombre as importador', DB::raw('count(*) as total'))
            ->where('operaciones.cliente_id', $clienteId)
            ->whereBetween('operaciones.fecha', [$desde, $hasta])
            ->groupBy('importadores.nombre')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        // Calendar Heatmap Logic (Reutilizada)
        $calendarioMes = Carbon::today();
        $inicioCal = $calendarioMes->copy()->startOfMonth();
        $finCal = $calendarioMes->copy()->endOfMonth();
        $rawCalendario = Operacion::select(DB::raw('DATE(fecha_registro) as fecha'), DB::raw('count(*) as total'))
            ->where($filtroCliente)
            ->whereBetween('fecha_registro', [$inicioCal, $finCal])
            ->groupBy(DB::raw('DATE(fecha_registro)'))
            ->pluck('total', 'fecha_registro');
        $maxOps = $rawCalendario->max() ?? 1;
        $umbralBajo = max(1, $maxOps * 0.33);
        $umbralMedio = max(1, $maxOps * 0.66);
        $calendario = [];
        $ptr = $inicioCal->copy()->startOfWeek(Carbon::MONDAY);
        $finPtr = $finCal->copy()->endOfWeek(Carbon::SUNDAY);
        while ($ptr <= $finPtr) {
            $semana = [];
            for ($i = 0; $i < 7; $i++) {
                $fechaStr = $ptr->format('Y-m-d');
                $totalDia = $rawCalendario[$fechaStr] ?? 0;
                $intensidad = 'none';
                if ($totalDia > 0) {
                    if ($totalDia <= $umbralBajo)
                        $intensidad = 'low';
                    elseif ($totalDia <= $umbralMedio)
                        $intensidad = 'medium';
                    else
                        $intensidad = 'high';
                }
                $semana[] = [
                    'fecha_registro' => $fechaStr,
                    'dia' => $ptr->day,
                    'total' => $totalDia,
                    'es_mes_actual' => $ptr->month === $inicioCal->month,
                    'intensidad' => $intensidad
                ];
                $ptr->addDay();
            }
            $calendario[] = $semana;
        }

        $operacionesHoy = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->with(['aduana', 'expediente'])
            ->whereDate('fecha_registro', $hoy)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $pedimentosRecientes = Expediente::where($filtroCliente)
            ->where('estado', 'cerrado')
            ->with(['cliente', 'aduana'])
            ->orderBy('fecha_apertura', 'desc')
            ->take(4)
            ->get();

        // 5. WIDGET SIDEBAR: Importadores del Mes (Estrictamente mes actual)
        $importadoresMes = Operacion::where('estado', '!=', 'cancelada')->join('importadores', 'operaciones.importador_id', '=', 'importadores.id')
            ->select('importadores.nombre as importador', DB::raw('count(*) as total'))
            ->where($filtroCliente)
            ->whereBetween('operaciones.fecha', [$inicioMes, $finMes])
            ->groupBy('importadores.nombre')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // 6. PRODUCTO ESTRELLA (Mas repetido en el periodo)
        $productoEstrella = Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')
            ->whereBetween('fecha_registro', [$inicioMes, $finMes])
            ->select('nombre_producto', DB::raw('count(*) as total'))
            ->groupBy('nombre_producto')
            ->orderByDesc('total')
            ->first();

        return view('clientes.dashboard', compact(
            'pedimentosMes',
            'pedimentosVerde',
            'pedimentosRojo',
            'sobrepesosMes',
            'historialLabels',
            'historialValues', // Para Gráfica Linea Anual
            'diarioLabels',
            'diarioValues', // Para Gráfica Barras Diario
            'aduanaLabels',
            'aduanaVerdes',
            'aduanaRojos', // Para Stacked Bar
            'topImportadores',
            'importadoresMes',
            'calendario',
            'operacionesHoy',
            'pedimentosRecientes',
            'productoEstrella',
            'desde',
            'hasta'
        ));
    }

}
