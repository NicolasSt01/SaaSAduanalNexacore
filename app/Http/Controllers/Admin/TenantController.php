<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Plan;
use App\Services\TenantCapabilityService;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::withCount(['users'])->paginate(15);
        return view('admin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('admin.tenants.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            'slug' => 'required|unique:tenants|alpha_dash',
            'correo_admin' => 'required|email',
            'plan' => 'required|in:basico,profesional,enterprise',
            'estado' => 'required|in:activo,inactivo,suspendido'
        ]);

        $data = $request->all();

        // Asignar fechas por defecto
        $data['fecha_inicio'] = now();
        $data['fecha_vencimiento'] = now()->addDays(30);

        // Asignar límites por defecto según el plan si no vienen en el request
        if (!isset($data['max_usuarios'])) {
            $data['max_usuarios'] = match ($data['plan']) {
                'basico' => 5,
                'profesional' => 20,
                'enterprise' => 100,
                default => 5
            };
        }

        if (!isset($data['max_operaciones_mes'])) {
            $data['max_operaciones_mes'] = match ($data['plan']) {
                'basico' => 100,
                'profesional' => 500,
                'enterprise' => 5000,
                default => 100
            };
        }

        Tenant::create($data);

        return redirect()->route('admin.tenants.index')->with('success', 'Agencia creada con éxito.');
    }

    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            'slug' => 'required|alpha_dash|unique:tenants,slug,' . $tenant->id,
            'correo_admin' => 'required|email',
            'plan' => 'required|in:basico,profesional,enterprise',
            'estado' => 'required|in:activo,inactivo,suspendido'
        ]);

        $tenant->update($request->all());

        return redirect()->route('admin.tenants.index')->with('success', 'Agencia actualizada con éxito.');
    }

    public function show(Tenant $tenant)
    {
        $tenant->load('users');

        $planes = Plan::where('activo', true)->orderBy('nombre')->get();

        $mesActual = \Carbon\Carbon::now()->startOfMonth();
        $finMes = \Carbon\Carbon::now()->endOfMonth();

        // Métricas de cobro (Mes Actual) para el Tenant
        $opsMes = \App\Models\Operacion::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$mesActual, $finMes])
            ->count();

        $docsMes = \Illuminate\Support\Facades\DB::table('documentos')
            ->where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$mesActual, $finMes])
            ->count();

        $notificacionesMes = \Illuminate\Support\Facades\DB::table('notificaciones')
            ->where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$mesActual, $finMes])
            ->select('tipo', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();

        $emailsMes = $notificacionesMes['email'] ?? 0;
        $whatsappMes = $notificacionesMes['whatsapp'] ?? 0;

        $allPermisos = \App\Models\User::getAllAvailablePermisos();

        return view('admin.tenants.show', compact('tenant', 'opsMes', 'docsMes', 'emailsMes', 'whatsappMes', 'allPermisos', 'planes'));
    }

    public function updateConfig(Request $request, Tenant $tenant)
    {
        $request->validate([
            'max_usuarios' => 'required|integer|min:1',
            'renta_mensual' => 'required|numeric|min:0',
            'dias_gracia' => 'required|integer|min:0',
            'permisos' => 'nullable|array',
            'plan_id' => 'nullable|exists:planes,id',
            'fecha_corte' => 'nullable|date',
        ]);

        $config = $tenant->configuracion ?? [];
        $config['renta_mensual'] = $request->renta_mensual;
        $config['dias_gracia'] = $request->dias_gracia;
        $config['permisos'] = $request->permisos ?? [];

        $tenant->configuracion = $config;
        $tenant->max_usuarios = $request->max_usuarios;
        $tenant->renta_mensual = $request->renta_mensual;
        $tenant->periodo_gracia_dias = $request->dias_gracia;
        $tenant->plan_id = $request->plan_id;
        $tenant->fecha_corte = $request->fecha_corte;
        $tenant->saldo_pendiente = $request->renta_mensual;
        $tenant->save();

        return redirect()->route('admin.tenants.show', $tenant->id)->with('success', 'Configuración de facturación y permisos actualizada.');
    }

    public function toggleUserStatus($tenant_id, $user_id)
    {
        $user = \App\Models\User::withoutGlobalScopes()->findOrFail($user_id);

        if ($user->tenant_id != $tenant_id) {
            return redirect()->back()->with('error', 'Acceso denegado: El usuario no pertenece a esta agencia.');
        }

        $user->active = !$user->active;
        $user->save();

        return redirect()->route('admin.tenants.show', $tenant_id)->with('success', 'El estado del usuario ha sido actualizado correctamente.');
    }

    public function toggleStatus(Tenant $tenant)
    {
        if ($tenant->isActive()) {
            $tenant->suspend();
            $message = "Agencia {$tenant->nombre_empresa} suspendida. Todos sus accesos han sido bloqueados.";
        } else {
            $tenant->reactivate();
            $message = "Agencia {$tenant->nombre_empresa} reactivada. Todos sus accesos han sido restaurados.";
        }

        return redirect()->route('admin.tenants.show', $tenant->id)->with('success', $message);
    }

    public function createUser(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,admin_n2,documentador',
        ]);

        $password = Str::random(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($password),
            'role' => $request->role,
            'tenant_id' => $tenant->id,
            'active' => true,
            'must_change_password' => true,
        ]);

        try {
            Mail::to($user->email)->send(new WelcomeMail($user, $password, $tenant));
        } catch (\Exception $e) {
            \Log::error('Error enviando email de bienvenida', ['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.tenants.show', $tenant->id)
            ->with('success', "Usuario {$user->name} creado exitosamente. Se envió email de bienvenida.");
    }

    /**
     * Mostrar página de configuración de capacidades del tenant.
     */
    public function capabilities(Tenant $tenant)
    {
        $capabilityService = new TenantCapabilityService();
        $usageSummary = $capabilityService->getTenantUsageSummary($tenant);
        $nearLimitResources = $capabilityService->getNearLimitResources($tenant, 70);

        return view('admin.tenants.capabilities', compact('tenant', 'usageSummary', 'nearLimitResources'));
    }

    /**
     * Actualizar capacidades y límites del tenant.
     * Ahora todo se guarda en el JSON 'configuracion'.
     */
    public function updateCapabilities(Request $request, Tenant $tenant)
    {
        $request->validate([
            // SOIA-Bot
            'bot_mode' => 'required|in:manual,automatico,deshabilitado',
            'bot_consultas_limite_mes' => 'nullable|integer|min:1',

            // Límites de recursos
            'limite_clientes' => 'nullable|integer|min:1',
            'limite_importadores' => 'nullable|integer|min:1',
            'limite_bodegas' => 'nullable|integer|min:1',
            'limite_aduanas' => 'nullable|integer|min:1',
            'limite_patentes' => 'nullable|integer|min:1',
            'limite_pedimentos_mes' => 'nullable|integer|min:1',
            'limite_documentos_mes' => 'nullable|integer|min:1',

            // Límites de funcionalidades
            'limite_reportes_mes' => 'nullable|integer|min:1',
            'limite_correos_dia' => 'nullable|integer|min:1',
            'limite_whatsapp_mes' => 'nullable|integer|min:1',

            // Features
            'features_enabled' => 'nullable|array',
            'features_enabled.*' => 'string',

            // Reportes habilitados
            'reportes_enabled' => 'nullable|array',
            'reportes_enabled.*' => 'string|in:' . implode(',', array_keys(\App\Models\Tenant::getAllAvailableReports())),
        ]);

        // Obtener configuración existente
        $config = $tenant->configuracion ?? [];

        // Asegurar que existan las estructuras
        if (!isset($config['bot'])) {
            $config['bot'] = [];
        }
        if (!isset($config['limites'])) {
            $config['limites'] = [];
        }
        if (!isset($config['limites']['recursos'])) {
            $config['limites']['recursos'] = [];
        }
        if (!isset($config['limites']['funcionalidades'])) {
            $config['limites']['funcionalidades'] = [];
        }

        // ==========================================
        // 1. Actualizar configuración del BOT
        // ==========================================
        $config['bot']['mode'] = $request->bot_mode;

        // Solo actualizar el límite si se proporcionó un valor
        if ($request->filled('bot_consultas_limite_mes')) {
            $config['bot']['consultas_limite_mes'] = (int) $request->bot_consultas_limite_mes;
        }

        // Preservar contadores existentes
        $config['bot']['consultas_mes'] = $config['bot']['consultas_mes'] ?? 0;
        $config['bot']['consultas_mes_periodo'] = $config['bot']['consultas_mes_periodo'] ?? now()->format('Y-m');

        // ==========================================
        // 2. Actualizar límites de recursos
        // ==========================================
        $recursosMap = [
            'limite_clientes' => 'clientes',
            'limite_importadores' => 'importadores',
            'limite_bodegas' => 'bodegas',
            'limite_aduanas' => 'aduanas',
            'limite_patentes' => 'patentes',
            'limite_pedimentos_mes' => 'pedimentos_mes',
            'limite_documentos_mes' => 'documentos_mes',
        ];

        foreach ($recursosMap as $inputKey => $configKey) {
            if ($request->filled($inputKey)) {
                $config['limites']['recursos'][$configKey] = (int) $request->$inputKey;
            } else {
                // Si no se proporciona valor, establecer como null (sin límite)
                $config['limites']['recursos'][$configKey] = null;
            }
        }

        // ==========================================
        // 3. Actualizar límites de funcionalidades
        // ==========================================
        $funcionalidadesMap = [
            'limite_reportes_mes' => 'reportes_mes',
            'limite_correos_dia' => 'correos_dia',
            'limite_whatsapp_mes' => 'whatsapp_mes',
        ];

        foreach ($funcionalidadesMap as $inputKey => $configKey) {
            if ($request->filled($inputKey)) {
                $config['limites']['funcionalidades'][$configKey] = (int) $request->$inputKey;
            } else {
                $config['limites']['funcionalidades'][$configKey] = null;
            }
        }

        // ==========================================
        // 4. Actualizar features habilitadas
        // ==========================================
        $config['features_enabled'] = $request->features_enabled ?? [];

        // ==========================================
        // 5. Actualizar configuración de reportes
        // ==========================================
        $allReports = array_keys(\App\Models\Tenant::getAllAvailableReports());
        $enabledReports = $request->reportes_enabled ?? [];
        $disabledReports = array_diff($allReports, $enabledReports);

        $config['reportes'] = [
            'enabled' => array_values($enabledReports),
            'disabled' => array_values($disabledReports),
        ];

        // ==========================================
        // 6. Guardar plantilla WhatsApp personalizada
        // ==========================================
        if ($request->has('whatsapp_plantilla_custom_clear')) {
            // Quitar plantilla personalizada
            unset($config['evolution_api']['whatsapp_plantilla_custom']);
        } elseif ($request->filled('whatsapp_plantilla_custom')) {
            // Guardar plantilla personalizada
            if (!isset($config['evolution_api'])) {
                $config['evolution_api'] = [];
            }
            $config['evolution_api']['whatsapp_plantilla_custom'] = $request->whatsapp_plantilla_custom;
        }

        // ==========================================
        // 7. Guardar la configuración actualizada
        // ==========================================
        $tenant->update(['configuracion' => $config]);

        // Log para debugging
        \Log::info('Tenant configuración actualizada', [
            'tenant_id' => $tenant->id,
            'tenant_nombre' => $tenant->nombre_empresa,
            'bot_mode' => $config['bot']['mode'],
            'reportes_enabled' => $config['reportes']['enabled'],
            'configuracion_completa' => json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()->route('admin.tenants.capabilities', $tenant->id)
            ->with('success', 'Capacidades y límites actualizados correctamente. Bot mode: ' . $config['bot']['mode'] . ' | Reportes habilitados: ' . count($config['reportes']['enabled']));
    }

    /**
     * Aplicar configuración por defecto según el plan.
     */
    public function applyPlanDefaults(Tenant $tenant)
    {
        TenantCapabilityService::applyPlanDefaults($tenant, $tenant->plan);
        $tenant->save();

        return redirect()->route('admin.tenants.capabilities', $tenant->id)
            ->with('success', 'Configuración por defecto del plan "' . $tenant->plan . '" aplicada.');
    }

    /**
     * API: Obtener uso actual de un tenant (JSON).
     */
    public function getUsage(Tenant $tenant)
    {
        $capabilityService = new TenantCapabilityService();
        $usageSummary = $capabilityService->getTenantUsageSummary($tenant);

        return response()->json([
            'success' => true,
            'usage' => $usageSummary,
        ]);
    }
}