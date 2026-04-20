<?php

namespace App\Http\Controllers;

use App\Models\Operacion;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\User;
use illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware("auth");
    }
    public function index()
    {
        $usuarios = User::all();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        $tenant = auth()->user()->tenant;
        $allPermisos = User::getAllAvailablePermisos();
        $permisosHabilitados = $tenant->configuracion['permisos'] ?? array_keys($allPermisos);

        return view('usuarios.create', compact('clientes', 'allPermisos', 'permisosHabilitados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'role' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        $tenant = auth()->user()->tenant;
        if ($tenant && !auth()->user()->isSuperAdmin()) {
            $userCount = \App\Models\User::where('tenant_id', $tenant->id)->count();
            if ($userCount >= ($tenant->max_usuarios ?? 0)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Su plan actual no tiene permitido agregar más usuarios. Por favor, contacte a contacto@nexacore.com.mx para adquirir más usuarios para su cuenta.');
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'cliente_id' => $request->cliente_id,
            'password' => bcrypt($request->password),
            'active' => true,
            'permisos' => $request->permisos ?? [],
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $clientes = Cliente::all();
        $tenant = auth()->user()->tenant;
        $allPermisos = User::getAllAvailablePermisos();
        $permisosHabilitados = $tenant->configuracion['permisos'] ?? array_keys($allPermisos);

        return view('usuarios.edit', compact('usuario', 'clientes', 'allPermisos', 'permisosHabilitados'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'role' => 'required',
        ]);

        $usuario->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'cliente_id' => $request->cliente_id,
            'permisos' => $request->permisos ?? [],
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado.');
    }

    public function desactivar(User $usuario)
    {
        $usuario->update(['active' => false]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario desactivado.');
    }
    public function dashboardadmin_OLD(Request $request)
    {
        // Verificar rol solo para este método
        if (!in_array(auth()->user()->role, ['admin'])) {
            $route = config("dashboards.role_routes." . auth()->user()->role, 'home');
            return redirect()->route($route)
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();
        $inicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());
        $fin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->toDateString());

        $inicioyear = Carbon::now()->startOfYear()->toDateString();
        $finyear = Carbon::now()->endOfYear()->toDateString();

        // 1) Labels: 12 meses abreviados (locale respetado si lo tienes configurado)
        $radarLabels = [];
        for ($m = 1; $m <= 12; $m++) {
            $radarLabels[] = Carbon::create(null, $m, 1)->translatedFormat('M'); // Ene, Feb, ...
        }

        // 2) Obtener lista de aduanas dinámicamente (id => nombre)
        $aduanasRows = DB::table('aduanas')->select('id', 'nombre')->get();
        $aduanasMap = [];
        foreach ($aduanasRows as $r) {
            $aduanasMap[(int) $r->id] = $r->nombre;
        }

        // 3) Inicializar estructura de datos: cada aduana => array(12) con ceros
        $radarData = [];
        foreach ($aduanasMap as $id => $nombre) {
            $radarData[$nombre] = array_fill(0, 12, 0);
        }

        // 4) Query agrupada por mes y aduana en el rango pedido
        $datos = DB::table('operaciones')
            ->select(DB::raw('aduana_id, MONTH(fecha_registro) as mes, COUNT(*) as total'))
            //->whereBetween('fecha_registro', [$inicio, $fin]) //Para que funcione con el rango de fechas dado.
            ->whereBetween('fecha_registro', [$inicioyear, $finyear])
            ->groupBy('aduana_id', 'mes')
            ->get();

        foreach ($datos as $d) {
            $nombre = $aduanasMap[(int) $d->aduana_id] ?? ('Aduana ' . $d->aduana_id);
            if (!isset($radarData[$nombre])) {
                // si aparece una aduana no listada, la agregamos
                $radarData[$nombre] = array_fill(0, 12, 0);
            }
            $radarData[$nombre][((int) $d->mes) - 1] = (int) $d->total;
        }

        // Ahora $radarLabels es un array (12 elementos)
        // y $radarData es un array asociativo: ['Reynosa' => [v1..v12], 'Laredo' => [...], ...]




        // ── KPIs ───────────────────────────────────────────────────────────────
        $tramitesHoy = Operacion::whereDate('fecha_registro', $hoy)->count();

        // Histórico total (todas las filas de operaciones)
        //$tramitesTotales = Operacion::count();
        $tramitesTotales = Operacion::whereBetween('fecha_registro', [$inicio, $fin])->count();

        // Clientes activos en el MES ACTUAL (al menos 1 trámite en el mes)
        $clientesActivos = Operacion::whereBetween('fecha_registro', [$inicio, $fin])
            ->distinct('cliente_id')
            ->count('cliente_id');

        // ── Línea con puntos: trámites por día del mes actual ─────────────────
        $porDia = Operacion::where('tenant_id', $tenantId)->select(
            DB::raw('DATE(fecha_registro) as dia'),
            DB::raw('COUNT(*) as total')
        )
            ->whereBetween('fecha_registro', [$inicio, $fin])
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        // Creamos una serie completa del 1 al último día del mes con 0 por defecto
        $periodo = CarbonPeriod::create($inicio, $fin);
        $serie = collect();
        foreach ($periodo as $fecha) {
            $serie[$fecha->format('d-M')] = 0;
        }
        foreach ($porDia as $row) {
            $key = Carbon::parse($row->dia)->format('d-M');
            $serie[$key] = (int) $row->total;
        }
        $tramitesDiasLabels = $serie->keys()->values();   // ['01','02',...]
        $tramitesDiasData = $serie->values();           // [0, 3, 7, ...]

        // ── Distribución por modulación (Verde/Rojo) del mes actual ───────────
        $verdes = Operacion::where('tenant_id', $tenantId)->whereBetween('fecha_registro', [$inicio, $fin])
            ->where('modulacion', 'DESADUANAMIENTO LIBRE')
            ->count();

        $rojos = Operacion::where('tenant_id', $tenantId)->whereBetween('fecha_registro', [$inicio, $fin])
            ->whereIn('modulacion', [
                'RECONOCIMIENTO ADUANERO',
                'RECONOCIMIENTO ADUANERO CONCLUIDO',
            ])
            ->count();

        $modLabels = ['Verde', 'Rojo'];
        $modData = [$verdes, $rojos];

        // ── Top 10 clientes del mes actual ────────────────────────────────────
        $clientesTop = Operacion::where('tenant_id', $tenantId)->select('cliente_id', DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha_registro', [$inicio, $fin])
            ->groupBy('cliente_id')
            ->orderByDesc('total')
            ->take(10)
            ->with('cliente') // para traer nombre_empresa
            ->get();

        $clientesLabels = $clientesTop->map(
            fn($c) => optional($c->cliente)->nombre_empresa ?? 'N/A'
        );
        $clientesData = $clientesTop->pluck('total');
        // (Opcional) Si tu Blade aún muestra remesas/usuarios/productos, puedes dejar que caigan en 0 por sus “?? 0”
        // o calcula aquí sus variables si las necesitas.


        // ── Top 10 productos más exportados del periodo ────────────────────────
        $productosTop = Operacion::where('tenant_id', $tenantId)->select('nombre_producto', DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha_registro', [$inicio, $fin])
            ->whereNotNull('nombre_producto') // Excluir valores nulos
            ->where('nombre_producto', '!=', '') // Excluir valores vacíos
            ->groupBy('nombre_producto')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $productosLabels = $productosTop->pluck('nombre_producto')->map(function ($producto) {
            return ucfirst(strtolower($producto));
        });
        $productosData = $productosTop->pluck('total');

        // ── Análisis de Pareto (Concentración de Negocio) ──────────────────────
        $paretoClientes = Operacion::where('tenant_id', $tenantId)->select('cliente_id', DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha_registro', [$inicio, $fin])
            ->groupBy('cliente_id')
            ->orderByDesc('total')
            ->with('cliente')
            ->get();

        $paretoLabels = [];
        $paretoBarras = [];
        $paretoLinea = [];
        $totalTramites = $paretoClientes->sum('total');
        $acumulado = 0;

        foreach ($paretoClientes as $index => $cliente) {
            $paretoLabels[] = optional($cliente->cliente)->nombre_empresa ?? 'Cliente ' . ($index + 1);
            $paretoBarras[] = (int) $cliente->total;
            $acumulado += (int) $cliente->total;
            $paretoLinea[] = round(($acumulado / $totalTramites) * 100, 2);
        }



        $usuarios = User::all();
        return view('admin.dashboard', compact(
            'usuarios',
            'tramitesHoy',
            'tramitesTotales',
            'clientesActivos',
            'tramitesDiasLabels',
            'tramitesDiasData',
            'modLabels',
            'modData',
            'clientesLabels',
            'clientesData',
            'productosLabels',
            'productosData',
            'paretoLabels',      // ← NUEVO
            'paretoBarras',      // ← NUEVO
            'paretoLinea',       // ← NUEVO
            'inicio',
            'fin',
            'radarLabels',
            'radarData',
            'verdes',
            'rojos'
        ));
    }

    public function configuracionportal()
    {
        return redirect()->route('admin.config');
    }

    public function dashboardadmin(Request $request)
    {
        // Verificar rol solo para este método
        if (!in_array(auth()->user()->role, ['admin'])) {
            $route = config("dashboards.role_routes." . auth()->user()->role, 'home');
            return redirect()->route($route)
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        // OBTENER TENANT ID PARA AISLAMIENTO DE DATOS
        $tenantId = auth()->user()->tenant_id ?? null;

        $hoy = Carbon::today();

        // ══════════════════════════════════════════════════════════════════════
        // NUEVO: Sistema de Filtros Rápidos
        // ══════════════════════════════════════════════════════════════════════
        $filtroRapido = $request->input('filtro_rapido', null);
        $inicio = null;
        $fin = null;
        $filtroActivo = 'Mes Actual'; // Nombre para mostrar en UI
        $tipoAgrupacion = 'día'; // día, semana, mes, trimestre

        if ($filtroRapido) {
            switch ($filtroRapido) {
                case '7dias':
                    $inicio = Carbon::now()->subDays(6)->startOfDay();
                    $fin = Carbon::now()->endOfDay();
                    $filtroActivo = 'Últimos 7 días';
                    $tipoAgrupacion = 'día';
                    break;

                case '30dias':
                    $inicio = Carbon::now()->subDays(29)->startOfDay();
                    $fin = Carbon::now()->endOfDay();
                    $filtroActivo = 'Últimos 30 días';
                    $tipoAgrupacion = 'día';
                    break;

                case '90dias':
                    $inicio = Carbon::now()->subDays(89)->startOfDay();
                    $fin = Carbon::now()->endOfDay();
                    $filtroActivo = 'Últimos 90 días';
                    $tipoAgrupacion = 'semana';
                    break;

                case 'q1':
                    $inicio = Carbon::now()->month(1)->startOfMonth();
                    $fin = Carbon::now()->month(3)->endOfMonth();
                    $filtroActivo = 'Q1 ' . Carbon::now()->year;
                    $tipoAgrupacion = 'semana';
                    break;

                case 'q2':
                    $inicio = Carbon::now()->month(4)->startOfMonth();
                    $fin = Carbon::now()->month(6)->endOfMonth();
                    $filtroActivo = 'Q2 ' . Carbon::now()->year;
                    $tipoAgrupacion = 'semana';
                    break;

                case 'q3':
                    $inicio = Carbon::now()->month(7)->startOfMonth();
                    $fin = Carbon::now()->month(9)->endOfMonth();
                    $filtroActivo = 'Q3 ' . Carbon::now()->year;
                    $tipoAgrupacion = 'semana';
                    break;

                case 'q4':
                    $inicio = Carbon::now()->month(10)->startOfMonth();
                    $fin = Carbon::now()->month(12)->endOfMonth();
                    $filtroActivo = 'Q4 ' . Carbon::now()->year;
                    $tipoAgrupacion = 'semana';
                    break;

                case 'anual_trimestres':
                    // NUEVO: Vista anual agrupada por trimestres
                    $inicio = Carbon::now()->startOfYear();
                    $fin = Carbon::now()->endOfYear();
                    $filtroActivo = 'Año ' . Carbon::now()->year . ' (Por Trimestres)';
                    $tipoAgrupacion = 'trimestre';
                    break;

                case 'mes_actual':
                    $inicio = Carbon::now()->startOfMonth();
                    $fin = Carbon::now()->endOfMonth();
                    $filtroActivo = 'Mes Actual';
                    $tipoAgrupacion = 'día';
                    break;

                case 'mes_anterior':
                    $inicio = Carbon::now()->subMonth()->startOfMonth();
                    $fin = Carbon::now()->subMonth()->endOfMonth();
                    $filtroActivo = 'Mes Anterior';
                    $tipoAgrupacion = 'día';
                    break;

                case 'anio_actual':
                    $inicio = Carbon::now()->startOfYear();
                    $fin = Carbon::now()->endOfYear();
                    $filtroActivo = 'Año Actual';
                    $tipoAgrupacion = 'mes';
                    break;

                case 'anio_anterior':
                    $inicio = Carbon::now()->subYear()->startOfYear();
                    $fin = Carbon::now()->subYear()->endOfYear();
                    $filtroActivo = 'Año Anterior';
                    $tipoAgrupacion = 'mes';
                    break;
            }

            $inicio = $inicio->toDateString();
            $fin = $fin->toDateString();

        } else {
            // Filtro personalizado (fechas manuales) o default
            $inicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());
            $fin = $request->input('fecha_fin', Carbon::now()->endOfMonth()->toDateString());

            // Determinar tipo de agrupación según rango de días
            $diasDiferencia = Carbon::parse($inicio)->diffInDays(Carbon::parse($fin));

            if ($diasDiferencia <= 31) {
                $tipoAgrupacion = 'día';
            } elseif ($diasDiferencia <= 90) {
                $tipoAgrupacion = 'semana';
            } else {
                $tipoAgrupacion = 'mes';
            }

            // Si hay fechas personalizadas, cambiar el nombre del filtro
            if ($request->has('fecha_inicio') || $request->has('fecha_fin')) {
                $filtroActivo = 'Personalizado';
            }
        }

        $inicioyear = Carbon::now()->startOfYear()->toDateString();
        $finyear = Carbon::now()->endOfYear()->toDateString();

        // ══════════════════════════════════════════════════════════════════════
        // Gráfico Radar (Aduanas) - Año completo
        // ══════════════════════════════════════════════════════════════════════
        $radarLabels = [];
        for ($m = 1; $m <= 12; $m++) {
            $radarLabels[] = Carbon::create(null, $m, 1)->translatedFormat('M');
        }

        $aduanasRows = DB::table('aduanas')->select('id', 'nombre')->get();
        $aduanasMap = [];
        foreach ($aduanasRows as $r) {
            $aduanasMap[(int) $r->id] = $r->nombre;
        }

        $radarData = [];
        foreach ($aduanasMap as $id => $nombre) {
            $radarData[$nombre] = array_fill(0, 12, 0);
        }

        $datos = DB::table('operaciones')
            ->where('tenant_id', $tenantId)
            ->select(DB::raw('aduana_id, MONTH(fecha_registro) as mes, COUNT(*) as total'))
            ->whereBetween('fecha_registro', [$inicioyear, $finyear])
            ->groupBy('aduana_id', 'mes')
            ->get();

        foreach ($datos as $d) {
            $nombre = $aduanasMap[(int) $d->aduana_id] ?? ('Aduana ' . $d->aduana_id);
            if (!isset($radarData[$nombre])) {
                $radarData[$nombre] = array_fill(0, 12, 0);
            }
            $radarData[$nombre][((int) $d->mes) - 1] = (int) $d->total;
        }

        // ══════════════════════════════════════════════════════════════════════
        // NUEVO: Gráfico Radar de Operaciones Totales por Mes (Año actual)
        // ══════════════════════════════════════════════════════════════════════
        $operacionesRadarLabels = [];
        for ($m = 1; $m <= 12; $m++) {
            $operacionesRadarLabels[] = Carbon::create(null, $m, 1)->translatedFormat('M');
        }

        $operacionesPorMes = Operacion::where('tenant_id', $tenantId)->select(
            DB::raw('MONTH(fecha_registro) as mes'),
            DB::raw('COUNT(*) as total')
        )
            ->whereBetween('fecha_registro', [$inicioyear, $finyear])
            ->groupBy('mes')
            ->get()
            ->pluck('total', 'mes');

        $operacionesRadarData = [];
        for ($m = 1; $m <= 12; $m++) {
            $operacionesRadarData[] = (int) ($operacionesPorMes[$m] ?? 0);
        }

        // ══════════════════════════════════════════════════════════════════════
        // KPIs
        // ══════════════════════════════════════════════════════════════════════
        $tramitesHoy = Operacion::where('tenant_id', $tenantId)->whereDate('fecha_registro', $hoy)->count();
        $tramitesTotales = Operacion::where('tenant_id', $tenantId)->whereBetween('fecha_registro', [$inicio, $fin])->count();
        $clientesActivos = Operacion::where('tenant_id', $tenantId)->whereBetween('fecha_registro', [$inicio, $fin])
            ->distinct('cliente_id')
            ->count('cliente_id');

        // ══════════════════════════════════════════════════════════════════════
        // MEJORADO: Línea con agregación dinámica (día/semana/mes/trimestre)
        // ══════════════════════════════════════════════════════════════════════
        $tramitesDiasLabels = [];
        $tramitesDiasData = [];

        if ($tipoAgrupacion === 'día') {
            // Agrupar por día
            $porDia = Operacion::where('tenant_id', $tenantId)->select(
                DB::raw('DATE(fecha_registro) as periodo'),
                DB::raw('COUNT(*) as total')
            )
                ->whereBetween('fecha_registro', [$inicio, $fin])
                ->groupBy('periodo')
                ->orderBy('periodo')
                ->get();

            $periodo = CarbonPeriod::create($inicio, $fin);
            $serie = collect();
            foreach ($periodo as $fecha) {
                $serie[$fecha->format('d-M')] = 0;
            }
            foreach ($porDia as $row) {
                $key = Carbon::parse($row->periodo)->format('d-M');
                $serie[$key] = (int) $row->total;
            }
            $tramitesDiasLabels = $serie->keys()->values();
            $tramitesDiasData = $serie->values();

        } elseif ($tipoAgrupacion === 'semana') {
            // Agrupar por semana
            $porSemana = Operacion::where('tenant_id', $tenantId)->select(
                DB::raw('YEAR(fecha) as anio'),
                DB::raw('WEEK(fecha, 1) as semana'),
                DB::raw('COUNT(*) as total')
            )
                ->whereBetween('fecha_registro', [$inicio, $fin])
                ->groupBy('anio', 'semana')
                ->orderBy('anio')
                ->orderBy('semana')
                ->get();

            $labels = [];
            $data = [];
            foreach ($porSemana as $row) {
                $fechaSemana = Carbon::now()
                    ->setISODate($row->anio, $row->semana)
                    ->startOfWeek();

                $labels[] = 'Sem ' . $row->semana . ' (' . $fechaSemana->format('d-M') . ')';
                $data[] = (int) $row->total;
            }
            $tramitesDiasLabels = collect($labels);
            $tramitesDiasData = collect($data);

        } elseif ($tipoAgrupacion === 'trimestre') {
            // NUEVO: Agrupar por trimestre
            $porTrimestre = Operacion::where('tenant_id', $tenantId)->select(
                DB::raw('YEAR(fecha) as anio'),
                DB::raw('QUARTER(fecha) as trimestre'),
                DB::raw('COUNT(*) as total')
            )
                ->whereBetween('fecha_registro', [$inicio, $fin])
                ->groupBy('anio', 'trimestre')
                ->orderBy('anio')
                ->orderBy('trimestre')
                ->get();

            // Inicializar los 4 trimestres con 0
            $trimestreData = [
                'Q1' => 0,
                'Q2' => 0,
                'Q3' => 0,
                'Q4' => 0
            ];

            foreach ($porTrimestre as $row) {
                $trimestreData['Q' . $row->trimestre] = (int) $row->total;
            }

            $tramitesDiasLabels = collect(array_keys($trimestreData));
            $tramitesDiasData = collect(array_values($trimestreData));

        } else { // mes
            // Agrupar por mes
            $porMes = Operacion::where('tenant_id', $tenantId)->select(
                DB::raw('YEAR(fecha) as anio'),
                DB::raw('MONTH(fecha_registro) as mes'),
                DB::raw('COUNT(*) as total')
            )
                ->whereBetween('fecha_registro', [$inicio, $fin])
                ->groupBy('anio', 'mes')
                ->orderBy('anio')
                ->orderBy('mes')
                ->get();

            $labels = [];
            $data = [];
            foreach ($porMes as $row) {
                $fechaMes = Carbon::create($row->anio, $row->mes, 1);
                $labels[] = $fechaMes->translatedFormat('M Y');
                $data[] = (int) $row->total;
            }
            $tramitesDiasLabels = collect($labels);
            $tramitesDiasData = collect($data);
        }

        // ══════════════════════════════════════════════════════════════════════
        // Modulación Verde/Rojo
        // ══════════════════════════════════════════════════════════════════════
        $verdes = Operacion::where('tenant_id', $tenantId)->whereBetween('fecha_registro', [$inicio, $fin])
            ->where('modulacion', 'DESADUANAMIENTO LIBRE')
            ->count();

        $rojos = Operacion::where('tenant_id', $tenantId)->whereBetween('fecha_registro', [$inicio, $fin])
            ->whereIn('modulacion', [
                'RECONOCIMIENTO ADUANERO',
                'RECONOCIMIENTO ADUANERO CONCLUIDO',
            ])
            ->count();

        $modLabels = ['Verde', 'Rojo'];
        $modData = [$verdes, $rojos];

        // ══════════════════════════════════════════════════════════════════════
        // Top 10 Clientes
        // ══════════════════════════════════════════════════════════════════════
        $clientesTop = Operacion::where('tenant_id', $tenantId)->select('cliente_id', DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha_registro', [$inicio, $fin])
            ->groupBy('cliente_id')
            ->orderByDesc('total')
            ->take(10)
            ->with('cliente')
            ->get();

        $clientesLabels = $clientesTop->map(
            fn($c) => optional($c->cliente)->nombre ?? 'N/A'
        );
        $clientesData = $clientesTop->pluck('total');

        // ══════════════════════════════════════════════════════════════════════
        // Top 10 Productos
        // ══════════════════════════════════════════════════════════════════════
        $productosTop = Operacion::where('tenant_id', $tenantId)->select('nombre_producto', DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha_registro', [$inicio, $fin])
            ->whereNotNull('nombre_producto')
            ->where('nombre_producto', '!=', '')
            ->groupBy('nombre_producto')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $productosLabels = $productosTop->pluck('nombre_producto')->map(function ($producto) {
            return ucfirst(strtolower($producto));
        });
        $productosData = $productosTop->pluck('total');

        // ══════════════════════════════════════════════════════════════════════
        // Análisis de Pareto
        // ══════════════════════════════════════════════════════════════════════
        $paretoClientes = Operacion::where('tenant_id', $tenantId)->select('cliente_id', DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha_registro', [$inicio, $fin])
            ->groupBy('cliente_id')
            ->orderByDesc('total')
            ->with('cliente')
            ->get();

        $paretoLabels = [];
        $paretoBarras = [];
        $paretoLinea = [];
        $totalTramites = $paretoClientes->sum('total');
        $acumulado = 0;

        foreach ($paretoClientes as $index => $cliente) {
            $paretoLabels[] = optional($cliente->cliente)->nombre_empresa ?? 'Cliente ' . ($index + 1);
            $paretoBarras[] = (int) $cliente->total;
            $acumulado += (int) $cliente->total;
            $paretoLinea[] = round(($acumulado / $totalTramites) * 100, 2);
        }

        $usuarios = User::all();

        return view('admin.dashboard', compact(
            'usuarios',
            'tramitesHoy',
            'tramitesTotales',
            'clientesActivos',
            'tramitesDiasLabels',
            'tramitesDiasData',
            'modLabels',
            'modData',
            'clientesLabels',
            'clientesData',
            'productosLabels',
            'productosData',
            'paretoLabels',
            'paretoBarras',
            'paretoLinea',
            'inicio',
            'fin',
            'radarLabels',
            'radarData',
            'verdes',
            'rojos',
            'filtroActivo',
            'tipoAgrupacion',
            'filtroRapido',
            'operacionesRadarLabels',
            'operacionesRadarData'
        ));
    }



}
