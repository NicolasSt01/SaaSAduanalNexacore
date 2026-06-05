<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\AddonContratado;
use App\Models\ConfiguracionFacturacion;
use App\Models\Tenant;
use App\Mail\InstruccionesPagoAddonMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AddonsController extends Controller
{
    public function catalogo()
    {
        $addons = Addon::withCount(['contratados as activos_count' => function ($q) {
            $q->where('estado', 'activo');
        }])->orderBy('tipo')->orderBy('nombre')->get();

        return view('admin.suscripciones.addons', compact('addons'));
    }

    public function storeAddon(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:reporte,plantilla_email,plantilla_whatsapp,feature,recurso_extra',
            'identificador' => 'required|string|max:100|unique:addons,identificador',
            'precio_mensual' => 'required|numeric|min:0',
        ]);

        Addon::create($request->all());

        return redirect()->route('admin.suscripciones.addons')->with('success', 'Add-on creado exitosamente.');
    }

    public function updateAddon(Request $request, Addon $addon)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:reporte,plantilla_email,plantilla_whatsapp,feature,recurso_extra',
            'identificador' => 'required|string|max:100|unique:addons,identificador,' . $addon->id,
            'precio_mensual' => 'required|numeric|min:0',
        ]);

        $addon->update($request->all());

        return redirect()->route('admin.suscripciones.addons')->with('success', 'Add-on actualizado.');
    }

    public function destroyAddon(Addon $addon)
    {
        if ($addon->contratados()->where('estado', 'activo')->exists()) {
            return redirect()->route('admin.suscripciones.addons')
                ->with('error', 'No se puede eliminar: hay tenants con este add-on activo.');
        }
        $addon->delete();
        return redirect()->route('admin.suscripciones.addons')->with('success', 'Add-on eliminado.');
    }

    public function contratados(Request $request)
    {
        $query = AddonContratado::with(['tenant', 'addon'])->latest();
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        $contratados = $query->paginate(20);
        $tenants = Tenant::orderBy('nombre_empresa')->get();

        return view('admin.suscripciones.addons-contratados', compact('contratados', 'tenants'));
    }

    public function contratar(Request $request, Tenant $tenant)
    {
        $request->validate([
            'addon_id' => 'required|exists:addons,id',
        ]);

        $addon = Addon::findOrFail($request->addon_id);
        $config = ConfiguracionFacturacion::get();
        $iva = $config->iva_porcentaje;
        $montoIva = round($addon->precio_mensual * ($iva / 100), 2);
        $montoTotal = $addon->precio_mensual + $montoIva;

        $contratado = AddonContratado::create([
            'tenant_id' => $tenant->id,
            'addon_id' => $addon->id,
            'estado' => 'pendiente_pago',
            'monto_base' => $addon->precio_mensual,
            'monto_iva' => $montoIva,
            'monto_total' => $montoTotal,
            'referencia_pago' => AddonContratado::generarReferencia(),
        ]);

        if ($tenant->correo_admin) {
            try {
                Mail::to($tenant->correo_admin)->send(new InstruccionesPagoAddonMail($contratado, $config));
            } catch (\Exception $e) {
                \Log::error("Error enviando instrucciones de pago add-on", ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('admin.tenants.show', $tenant->id)
            ->with('success', "Add-on '{$addon->nombre}' contratado. Referencia: {$contratado->referencia_pago}.");
    }

    public function aprobarPago(AddonContratado $addonContratado)
    {
        $addonContratado->aprobar();

        return redirect()->back()->with('success', "Add-on '{$addonContratado->addon->nombre}' activado para {$addonContratado->tenant->nombre_empresa}.");
    }

    public function rechazarPago(Request $request, AddonContratado $addonContratado)
    {
        $addonContratado->rechazar($request->motivo);

        return redirect()->back()->with('success', "Pago de add-on rechazado.");
    }
}
