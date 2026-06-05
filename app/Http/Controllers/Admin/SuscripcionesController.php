<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanCustom;
use App\Models\Suscripcion;
use App\Models\ConfiguracionFacturacion;
use App\Models\Tenant;
use App\Mail\InstruccionesPagoMail;
use App\Mail\PagoAprobadoMail;
use App\Mail\PagoRechazadoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SuscripcionesController extends Controller
{
    public function dashboard()
    {
        $suscripcionesActivas = Suscripcion::where('estado', 'activa')->count();
        $pagosPendientes = Suscripcion::where('estado', 'pendiente_aprobacion')->count();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $ingresosMes = Suscripcion::where('estado', 'activa')
            ->whereBetween('approved_at', [$monthStart, $monthEnd])
            ->sum('monto_total');
        $proximosVencer = Suscripcion::where('estado', 'activa')
            ->where('fecha_fin', '>=', now())
            ->where('fecha_fin', '<=', now()->addDays(7))
            ->count();

        $pendientesRecientes = Suscripcion::with(['tenant', 'plan'])
            ->where('estado', 'pendiente_aprobacion')
            ->latest()->take(10)->get();

        $ingresosPorMes = Suscripcion::where('estado', 'activa')
            ->where('approved_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(approved_at, '%Y-%m') as mes, SUM(monto_total) as total")
            ->groupBy('mes')->orderBy('mes')->get();

        return view('admin.suscripciones.dashboard', compact(
            'suscripcionesActivas', 'pagosPendientes', 'ingresosMes',
            'proximosVencer', 'pendientesRecientes', 'ingresosPorMes'
        ));
    }

    public function planes()
    {
        $planes = PlanCustom::withCount(['suscripciones as tenants_activos' => function ($q) {
            $q->where('estado', 'activa');
        }])->orderBy('nombre')->get();

        return view('admin.suscripciones.planes', compact('planes'));
    }

    public function storePlan(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_base' => 'required|numeric|min:0',
            'max_usuarios' => 'required|integer|min:1',
            'max_operaciones_mes' => 'nullable|integer|min:0',
            'max_documentos_mes' => 'nullable|integer|min:0',
            'max_modulaciones_mes' => 'nullable|integer|min:0',
            'reportes_habilitados' => 'nullable|array',
            'features_habilitadas' => 'nullable|array',
        ]);

        PlanCustom::create($request->all());

        return redirect()->route('admin.suscripciones.planes')->with('success', 'Plan creado exitosamente.');
    }

    public function updatePlan(Request $request, PlanCustom $plan)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_base' => 'required|numeric|min:0',
            'max_usuarios' => 'required|integer|min:1',
            'max_operaciones_mes' => 'nullable|integer|min:0',
            'max_documentos_mes' => 'nullable|integer|min:0',
            'max_modulaciones_mes' => 'nullable|integer|min:0',
            'reportes_habilitados' => 'nullable|array',
            'features_habilitadas' => 'nullable|array',
        ]);

        $plan->update($request->all());

        return redirect()->route('admin.suscripciones.planes')->with('success', 'Plan actualizado.');
    }

    public function destroyPlan(PlanCustom $plan)
    {
        if ($plan->suscripciones()->where('estado', 'activa')->exists()) {
            return redirect()->route('admin.suscripciones.planes')
                ->with('error', 'No se puede eliminar: hay suscripciones activas con este plan.');
        }
        $plan->delete();
        return redirect()->route('admin.suscripciones.planes')->with('success', 'Plan eliminado.');
    }

    public function index(Request $request)
    {
        $query = Suscripcion::with(['tenant', 'plan'])->latest();
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        $suscripciones = $query->paginate(20);
        $tenants = Tenant::orderBy('nombre_empresa')->get();

        return view('admin.suscripciones.index', compact('suscripciones', 'tenants'));
    }

    public function crearSuscripcion(Request $request, Tenant $tenant)
    {
        $request->validate([
            'plan_custom_id' => 'required|exists:planes_custom,id',
        ]);

        $plan = PlanCustom::findOrFail($request->plan_custom_id);
        $config = ConfiguracionFacturacion::get();
        $iva = $config->iva_porcentaje;
        $montoIva = round($plan->precio_base * ($iva / 100), 2);
        $montoTotal = $plan->precio_base + $montoIva;

        $suscripcion = Suscripcion::create([
            'tenant_id' => $tenant->id,
            'plan_custom_id' => $plan->id,
            'estado' => 'pendiente_pago',
            'monto_base' => $plan->precio_base,
            'monto_iva' => $montoIva,
            'monto_total' => $montoTotal,
            'referencia_pago' => Suscripcion::generarReferencia(),
        ]);

        if ($tenant->correo_admin) {
            try {
                Mail::to($tenant->correo_admin)->send(new InstruccionesPagoMail($suscripcion, $config));
            } catch (\Exception $e) {
                \Log::error("Error enviando instrucciones de pago", ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('admin.tenants.show', $tenant->id)
            ->with('success', "Suscripción creada. Referencia: {$suscripcion->referencia_pago}. Email enviado al cliente.");
    }

    public function aprobarPago(Suscripcion $suscripcion)
    {
        $suscripcion->aprobar();
        $config = ConfiguracionFacturacion::get();

        if ($suscripcion->tenant->correo_admin) {
            try {
                Mail::to($suscripcion->tenant->correo_admin)->send(new PagoAprobadoMail($suscripcion, $config));
            } catch (\Exception $e) {
                \Log::error("Error enviando confirmación de pago", ['error' => $e->getMessage()]);
            }
        }

        return redirect()->back()->with('success', "Pago aprobado. Suscripción activada para {$suscripcion->tenant->nombre_empresa}.");
    }

    public function rechazarPago(Request $request, Suscripcion $suscripcion)
    {
        $request->validate(['motivo' => 'required|string']);
        $suscripcion->rechazar($request->motivo);
        $config = ConfiguracionFacturacion::get();

        if ($suscripcion->tenant->correo_admin) {
            try {
                Mail::to($suscripcion->tenant->correo_admin)->send(new PagoRechazadoMail($suscripcion, $config, $request->motivo));
            } catch (\Exception $e) {
                \Log::error("Error enviando rechazo de pago", ['error' => $e->getMessage()]);
            }
        }

        return redirect()->back()->with('success', "Pago rechazado. Se notificó al cliente.");
    }

    public function configuracion()
    {
        $config = ConfiguracionFacturacion::get();
        return view('admin.suscripciones.configuracion', compact('config'));
    }

    public function updateConfiguracion(Request $request)
    {
        $request->validate([
            'empresa_nombre' => 'required|string|max:255',
            'empresa_rfc' => 'nullable|string|max:20',
            'banco_nombre' => 'nullable|string|max:255',
            'banco_clabe' => 'nullable|string|max:20',
            'banco_cuenta' => 'nullable|string|max:20',
            'banco_referencia_prefix' => 'nullable|string|max:10',
            'iva_porcentaje' => 'required|integer|min:0|max:100',
            'email_notificaciones' => 'nullable|email',
            'logo_url' => 'nullable|url',
            'notas_legales' => 'nullable|string',
        ]);

        $config = ConfiguracionFacturacion::get();
        $config->update($request->all());

        return redirect()->route('admin.suscripciones.configuracion')->with('success', 'Configuración de facturación actualizada.');
    }
}
