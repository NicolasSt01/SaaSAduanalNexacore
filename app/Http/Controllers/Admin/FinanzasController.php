<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Pago;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class FinanzasController extends Controller
{
    public function dashboard()
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $ingresosMes = Pago::whereBetween('fecha_pago', [$monthStart, $monthEnd])->sum('monto');
        $tenantsActivos = Tenant::where('estado', 'activo')->count();
        $tenantsMorosos = Tenant::where('saldo_pendiente', '>', 0)->where('estado', '!=', 'suspendido')->count();
        $tenantsSuspendidos = Tenant::where('estado', 'suspendido')->count();
        $proximosVencer = Tenant::where('estado', 'activo')
            ->whereNotNull('fecha_corte')
            ->where('saldo_pendiente', '>', 0)
            ->whereDate('fecha_corte', '>=', now())
            ->whereDate('fecha_corte', '<=', now()->addDays(7))
            ->count();
        $totalFacturado = Factura::whereBetween('created_at', [$monthStart, $monthEnd])->sum('monto');

        $pagosRecientes = Pago::with('tenant')->latest()->take(10)->get();
        $ingresosPorMes = Pago::selectRaw("DATE_FORMAT(fecha_pago, '%Y-%m') as mes, SUM(monto) as total")
            ->where('fecha_pago', '>=', now()->subMonths(6))
            ->groupBy('mes')->orderBy('mes')->get();

        return view('admin.finanzas.dashboard', compact(
            'ingresosMes', 'tenantsActivos', 'tenantsMorosos', 'tenantsSuspendidos',
            'proximosVencer', 'totalFacturado', 'pagosRecientes', 'ingresosPorMes'
        ));
    }

    // ========== PLANES ==========

    public function planes()
    {
        $planes = Plan::withCount('tenants')->get();
        return view('admin.finanzas.planes', compact('planes'));
    }

    public function storePlan(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_mensual' => 'required|numeric|min:0',
            'max_usuarios' => 'required|integer|min:1',
            'max_operaciones_mes' => 'nullable|integer|min:0',
            'max_documentos_mes' => 'nullable|integer|min:0',
        ]);

        Plan::create($request->all());

        return redirect()->route('admin.finanzas.planes')->with('success', 'Plan creado exitosamente.');
    }

    public function updatePlan(Request $request, Plan $plan)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_mensual' => 'required|numeric|min:0',
            'max_usuarios' => 'required|integer|min:1',
            'max_operaciones_mes' => 'nullable|integer|min:0',
            'max_documentos_mes' => 'nullable|integer|min:0',
        ]);

        $plan->update($request->all());

        return redirect()->route('admin.finanzas.planes')->with('success', 'Plan actualizado.');
    }

    public function destroyPlan(Plan $plan)
    {
        if ($plan->tenants()->count() > 0) {
            return redirect()->route('admin.finanzas.planes')->with('error', 'No se puede eliminar: hay tenants asignados a este plan.');
        }
        $plan->delete();
        return redirect()->route('admin.finanzas.planes')->with('success', 'Plan eliminado.');
    }

    // ========== PAGOS ==========

    public function pagos(Request $request)
    {
        $query = Pago::with('tenant')->latest('fecha_pago');
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        $pagos = $query->paginate(20);
        $tenants = Tenant::orderBy('nombre_empresa')->get();
        return view('admin.finanzas.pagos', compact('pagos', 'tenants'));
    }

    public function storePago(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'metodo' => 'required|string',
            'periodo_inicio' => 'nullable|string',
            'periodo_fin' => 'nullable|string',
            'notas' => 'nullable|string',
        ]);

        $pago = Pago::create($request->all());

        $tenant = Tenant::find($request->tenant_id);
        $tenant->saldo_pendiente = max(0, $tenant->saldo_pendiente - $request->monto);
        $tenant->ultimo_pago_fecha = $request->fecha_pago;
        $tenant->save();

        $folio = 'F-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        $periodo = $request->periodo_inicio && $request->periodo_fin
            ? $request->periodo_inicio . ' a ' . $request->periodo_fin
            : now()->format('F Y');

        Factura::create([
            'tenant_id' => $tenant->id,
            'pago_id' => $pago->id,
            'folio' => $folio,
            'periodo' => $periodo,
            'monto' => $request->monto,
            'estado' => 'pagada',
        ]);

        return redirect()->route('admin.finanzas.pagos')->with('success', "Pago registrado. Factura {$folio} generada.");
    }

    // ========== FACTURAS ==========

    public function facturas(Request $request)
    {
        $query = Factura::with('tenant')->latest();
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        $facturas = $query->paginate(20);
        $tenants = Tenant::orderBy('nombre_empresa')->get();
        return view('admin.finanzas.facturas', compact('facturas', 'tenants'));
    }

    public function descargarFactura(Factura $factura)
    {
        $pdf = Pdf::loadView('admin.finanzas.pdf-factura', compact('factura'));
        return $pdf->download("Factura_{$factura->folio}.pdf");
    }
}
