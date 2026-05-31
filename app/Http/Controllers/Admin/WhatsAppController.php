<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EvolutionApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppController
 *
 * Panel de configuración WhatsApp por tenant.
 * 
 * Permite al administrador de cada agencia conectar su número de WhatsApp
 * escaneando un código QR generado por Evolution API.
 * 
 * La habilitación de WhatsApp por tenant la controla el superadmin desde
 * el panel de capabilities (/nexacore-admin/tenants/{id}/capabilities).
 */
class WhatsAppController extends Controller
{
    protected EvolutionApiService $evolution;

    public function __construct(EvolutionApiService $evolution)
    {
        $this->middleware(function ($request, $next) {
            $allowedRoles = ['admin', 'super_admin'];
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            $user = auth()->user();
            if (!in_array($user->role, $allowedRoles)) {
                $route = config("dashboards.role_routes.{$user->role}", 'home');
                return redirect()->route($route)
                    ->with('error', 'No tienes permiso para acceder a esta sección.');
            }
            return $next($request);
        });

        $this->evolution = $evolution;
    }

    /**
     * Mostrar panel de configuración WhatsApp.
     */
    public function index()
    {
        $tenant = Auth::user()->tenant;
        $config = $tenant->configuracion ?? [];
        $evolutionConfig = $config['evolution_api'] ?? [];

        // Verificar estado real en Evolution API si hay instancia configurada
        $instance = $evolutionConfig['instance'] ?? null;
        if ($instance && $this->evolution->isConfigured()) {
            $stateResult = $this->evolution->getConnectionState($instance);
            $stateBody = $stateResult['response'] ?? $stateResult;
            
            // Evolution API v2.3.7 devuelve: {instance: {instanceName, state, ...}}
            $instanceData = $stateBody['instance'] ?? $stateBody;
            $realState = $instanceData['state'] ?? $instanceData['status'] ?? $instanceData['connectionStatus'] ?? '';

            if ($realState === 'open' && !($evolutionConfig['connected'] ?? false)) {
                $config['evolution_api']['connected'] = true;
                $config['evolution_api']['connected_at'] = now()->toDateTimeString();
                $tenant->update(['configuracion' => $config]);
                $evolutionConfig = $config['evolution_api'];
            } elseif ($realState !== 'open' && ($evolutionConfig['connected'] ?? false)) {
                $config['evolution_api']['connected'] = false;
                $tenant->update(['configuracion' => $config]);
                $evolutionConfig = $config['evolution_api'];
            }
        }

        $estado = $this->determinarEstado($evolutionConfig);

        return view('admin.config.whatsapp', compact('evolutionConfig', 'estado'));
    }

    /**
     * Crear instancia y obtener QR para conectar WhatsApp.
     */
    public function conectar(Request $request): JsonResponse
    {
        $tenant = Auth::user()->tenant;

        if (!$this->evolution->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Evolution API no está configurada.',
            ], 500);
        }

        $instanceName = 'tenant_' . $tenant->id;

        // DEBUG: Verificar conectividad basica y auth
        $healthResult = $this->evolution->ping();
        $healthBody = $healthResult['response'] ?? $healthResult;
        $healthStatus = $healthResult['_status'] ?? 0;

        if ($healthStatus !== 200) {
            $healthError = $healthResult['error'] ?? 'sin respuesta';
            return response()->json([
                'success' => false,
                'message' => "Sin conexion a Evolution API (HTTP {$healthStatus}): {$healthError}. Health keys: " . json_encode(array_slice(array_keys($healthResult), 0, 8)),
            ], 500);
        }

        // Verificar si ya esta conectada
        $stateCheck = $this->evolution->getConnectionState($instanceName);
        $stateBody = $stateCheck['response'] ?? $stateCheck;
        $instanceData = $stateBody['instance'] ?? $stateBody;
        if ($stateCheck['_status'] === 200 && ($instanceData['state'] ?? $instanceData['status'] ?? '') === 'open') {
            $this->guardarEstado($tenant, $instanceName, true);
            return response()->json([
                'success' => true,
                'paired' => true,
                'message' => 'WhatsApp ya está conectado.',
            ]);
        }

        // Crear instancia (o verificar si ya existe)
        $createResult = $this->evolution->createInstance($instanceName);
        $createBody = $createResult['response'] ?? $createResult;
        $createStatus = $createResult['_status'] ?? 0;

        $instanceData = $createBody['instance'] ?? $createBody;
        $paired = ($instanceData['state'] ?? $instanceData['status'] ?? '') === 'open';
        $apiError = $createResult['error'] ?? $createBody['error'] ?? $createBody['message'] ?? null;

        // Detectar si la instancia ya existe (400 o 403 con "already"/"exist")
        $instanceExists = in_array($createStatus, [400, 403]) && (
            stripos($apiError ?? '', 'exist') !== false ||
            stripos($apiError ?? '', 'already') !== false ||
            stripos(json_encode($createBody), 'exist') !== false ||
            stripos(json_encode($createBody), 'already') !== false
        );

        // Buscar QR en respuesta de create
        $qr = $this->extraerQr($createBody);

        // Si la instancia ya existe, intentar obtener QR directamente sin borrarla
        if ($instanceExists && !$qr) {
            Log::info("[WhatsApp] Instancia ya existe, intentando reconectar", ['instance' => $instanceName]);
            
            // Intentar 1: POST /instance/connect con instanceName para generar nuevo QR
            $connectResult = $this->evolution->connectInstance($instanceName);
            $connectBody = $connectResult['response'] ?? $connectResult;
            $qr = $this->extraerQr($connectBody);
            
            Log::info("[WhatsApp] connectInstance response", [
                'status' => $connectResult['_status'],
                'keys' => array_keys($connectBody),
            ]);
            
            // Intentar 2: GET /instance/qrcode/{instance}
            if (!$qr) {
                usleep(500000);
                $qrResult = $this->evolution->getQrCode($instanceName);
                $qrBody = $qrResult['response'] ?? $qrResult;
                $qr = $this->extraerQr($qrBody);
            }
            
            // Intentar 3: Borrar y recrear (ultimo recurso)
            if (!$qr) {
                Log::info("[WhatsApp] Reconexion directa fallo, borrando y recreando", ['instance' => $instanceName]);
                $this->evolution->deleteInstance($instanceName);
                usleep(1000000); // 1s
                $createResult = $this->evolution->createInstance($instanceName);
                $createBody = $createResult['response'] ?? $createResult;
                $createStatus = $createResult['_status'] ?? 0;
                $apiError = $createResult['error'] ?? $createBody['error'] ?? $createBody['message'] ?? null;
                $qr = $this->extraerQr($createBody);
                
                if (!$qr && $createStatus >= 200 && $createStatus < 300) {
                    usleep(500000);
                    $qrResult = $this->evolution->getQrCode($instanceName);
                    $qrBody = $qrResult['response'] ?? $qrResult;
                    $qr = $this->extraerQr($qrBody);
                    if (!$qr) {
                        $apiError = $qrResult['error'] ?? $qrBody['error'] ?? $apiError;
                    }
                }
            }
            
            if ($qr) {
                $apiError = null;
            }
        }

        // Si no hay QR y no hay error, intentar GET /instance/connect/{instance}
        if (!$qr && !$apiError && $createStatus >= 200 && $createStatus < 300) {
            // Pequeño delay para que la instancia se inicialice
            usleep(500000); // 500ms
            
            $connectResult = $this->evolution->connectInstance($instanceName);
            $connectBody = $connectResult['response'] ?? $connectResult;
            $qr = $this->extraerQr($connectBody);
            
            // Debug: si connect no dio QR, ver qué devolvió
            if (!$qr) {
                Log::info("[WhatsApp] connectInstance response", [
                    'status' => $connectResult['_status'],
                    'keys' => array_keys($connectBody),
                    'body' => $connectBody,
                ]);
                
                // Intentar GET /instance/qrcode/{instance} como ultimo fallback
                $qrResult = $this->evolution->getQrCode($instanceName);
                $qrBody = $qrResult['response'] ?? $qrResult;
                $qr = $this->extraerQr($qrBody);
                
                if (!$qr) {
                    Log::info("[WhatsApp] getQrCode response", [
                        'status' => $qrResult['_status'],
                        'keys' => array_keys($qrBody),
                        'body' => $qrBody,
                    ]);
                    
                    $apiError = $connectResult['error'] ?? $connectBody['error']
                               ?? $qrResult['error'] ?? $qrBody['error'] ?? $apiError;
                }
            }
        }

        $this->guardarEstado($tenant, $instanceName, $paired);

        return response()->json([
            'success' => true,
            'instance' => $instanceName,
            'qr' => $qr,
            'paired' => $paired,
            'create_status' => $createStatus,
            'create_body_keys' => array_keys($createBody),
            'create_body_message' => $createBody['message'] ?? null,
            'create_body_full' => $createBody,
            'message' => $paired
                ? 'WhatsApp ya está conectado.'
                : ($qr ? 'Escanea el código QR.'
                       : 'Error HTTP ' . $createStatus . ': ' . ($apiError ?: 'sin QR. createKeys=' . json_encode(array_slice(array_keys($createBody), 0, 12)))),
            'debug' => [
                'reconnected' => $instanceExists ?? null,
                'api_error' => $apiError,
            ],
        ]);
    }

    /**
     * Verificar estado de conexión actual.
     */
    public function estado(): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $config = $tenant->configuracion ?? [];
        $evolutionConfig = $config['evolution_api'] ?? [];
        $instance = $evolutionConfig['instance'] ?? null;

        if (!$instance || !$this->evolution->isConfigured()) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'No hay instancia configurada.',
            ]);
        }

        $result = $this->evolution->getConnectionState($instance);
        $stateBody = $result['response'] ?? $result;
        
        // Evolution API v2.3.7: {instance: {instanceName, state, ...}}
        $instanceData = $stateBody['instance'] ?? $stateBody;
        $realState = $instanceData['state'] ?? $instanceData['status'] ?? $instanceData['connectionStatus'] ?? '';
        $connected = $realState === 'open';

        // Actualizar estado en config si cambió
        if ($connected !== ($evolutionConfig['connected'] ?? false)) {
            $config['evolution_api']['connected'] = $connected;
            if ($connected) {
                $config['evolution_api']['connected_at'] = now()->toDateTimeString();
            }
            $tenant->update(['configuracion' => $config]);
        }

        return response()->json([
            'success' => true,
            'connected' => $connected,
            'state' => $realState ?: 'unknown',
            'instance' => $instance,
        ]);
    }

    /**
     * Desconectar (logout de WhatsApp) de la instancia del tenant.
     */
    public function desconectar(): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $config = $tenant->configuracion ?? [];
        $instance = $config['evolution_api']['instance'] ?? null;

        if (!$instance) {
            return response()->json([
                'success' => false,
                'message' => 'No hay instancia para desconectar.',
            ], 400);
        }

        $result = $this->evolution->logout($instance);

        $config['evolution_api']['connected'] = false;
        $config['evolution_api']['connected_at'] = null;
        $tenant->update(['configuracion' => $config]);

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp desconectado. Puedes volver a conectar escaneando un nuevo QR.',
        ]);
    }

    /**
     * Obtener grupos de WhatsApp sincronizados de la instancia del tenant.
     */
    public function grupos(): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $config = $tenant->configuracion ?? [];
        $instance = $config['evolution_api']['instance'] ?? null;

        if (!$instance || !$this->evolution->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay instancia configurada.',
            ], 400);
        }

        // Los grupos vienen en la respuesta de fetchContacts (remoteJid con @g.us)
        $result = $this->evolution->fetchContacts($instance);
        
        $grupos = [];
        foreach ($result as $key => $item) {
            if ($key === '_status' || $key === '_error') continue;
            if (!is_array($item)) continue;
            
            $id = $item['remoteJid'] ?? $item['id'] ?? '';
            
            // Solo grupos (@g.us)
            if (str_ends_with($id, '@g.us')) {
                $grupos[] = [
                    'id' => $id,
                    'nombre' => $item['pushName'] ?? ($item['name'] ?? 'Grupo sin nombre'),
                    'participants' => [],
                    'participant_count' => 0,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'grupos' => $grupos,
        ]);
    }

    /**
     * Debug: listar todas las instancias en Evolution API.
     */
    public function debugInstancias(): JsonResponse
    {
        $listResult = $this->evolution->listInstances();
        $healthResult = $this->evolution->ping();

        return response()->json([
            'health' => [
                'status' => $healthResult['_status'] ?? 0,
                'body' => $healthResult['response'] ?? $healthResult,
            ],
            'instances' => [
                'status' => $listResult['_status'] ?? 0,
                'body' => $listResult['response'] ?? $listResult,
            ],
            'config' => [
                'base_url' => env('EVOLUTION_API_BASE_URL'),
                'api_key_set' => !empty(env('EVOLUTION_API_KEY')),
            ],
        ]);
    }

    /**
     * Guardar plantilla de mensaje WhatsApp seleccionada por el tenant.
     */
    public function guardarPlantilla(Request $request): JsonResponse
    {
        $request->validate([
            'whatsapp_plantilla' => 'required|string|in:breve,detallado,corporativo',
        ]);

        $tenant = Auth::user()->tenant;
        $config = $tenant->configuracion ?? [];
        $config['evolution_api']['whatsapp_plantilla'] = $request->whatsapp_plantilla;
        $tenant->update(['configuracion' => $config]);

        return response()->json([
            'success' => true,
            'plantilla' => $request->whatsapp_plantilla,
        ]);
    }

    /**
     * Debug: ver respuesta cruda de contactos y grupos.
     */
    public function debugContactos(): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $config = $tenant->configuracion ?? [];
        $instance = $config['evolution_api']['instance'] ?? null;

        if (!$instance) {
            return response()->json(['error' => 'No instance']);
        }

        $contactsResult = $this->evolution->fetchContacts($instance);
        $chatsResult = $this->evolution->fetchChats($instance);
        $groupsResult = $this->evolution->fetchGroups($instance, false);

        return response()->json([
            'contacts' => [
                'status' => $contactsResult['_status'],
                'keys' => array_keys($contactsResult),
                'raw' => $contactsResult,
            ],
            'chats' => [
                'status' => $chatsResult['_status'],
                'keys' => array_keys($chatsResult),
                'raw' => $chatsResult,
            ],
            'groups' => [
                'status' => $groupsResult['_status'],
                'keys' => array_keys($groupsResult),
                'raw' => $groupsResult,
            ],
        ]);
    }

    /**
     * Obtener contactos de WhatsApp de la instancia del tenant.
     */
    public function contactos(): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $config = $tenant->configuracion ?? [];
        $instance = $config['evolution_api']['instance'] ?? null;

        if (!$instance || !$this->evolution->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay instancia configurada.',
            ], 400);
        }

        $result = $this->evolution->fetchContacts($instance);
        
        // Evolution API v2.3.7 devuelve contactos con indices numericos en el root
        // {_status: 200, 0: {...}, 1: {...}, ...}
        $contactos = [];
        foreach ($result as $key => $item) {
            if ($key === '_status' || $key === '_error') continue;
            if (!is_array($item)) continue;
            
            $id = $item['remoteJid'] ?? $item['id'] ?? '';
            
            // Solo contactos individuales (@s.whatsapp.net, no @lid ni @g.us)
            if (str_ends_with($id, '@s.whatsapp.net')) {
                $contactos[] = [
                    'id' => $id,
                    'nombre' => $item['pushName'] ?? ($item['name'] ?? 'Sin nombre'),
                    'telefono' => explode('@', $id)[0],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'contactos' => $contactos,
            'total' => count($contactos),
        ]);
    }

    /**
     * Guardar estado de conexion en la config del tenant.
     */
    protected function guardarEstado($tenant, string $instance, bool $connected): void
    {
        $config = $tenant->configuracion ?? [];
        $config['evolution_api'] = [
            'instance' => $instance,
            'connected' => $connected,
            'connected_at' => $connected ? now()->toDateTimeString() : ($config['evolution_api']['connected_at'] ?? null),
        ];
        $tenant->update(['configuracion' => $config]);
    }

    /**
     * Determinar el estado del panel (0=no config, 1=esperando QR, 2=conectado).
     */
    protected function determinarEstado(array $evolutionConfig): int
    {
        $instance = $evolutionConfig['instance'] ?? null;
        $connected = $evolutionConfig['connected'] ?? false;

        if (!$instance) {
            return 0;
        }
        if ($connected) {
            return 2;
        }
        return 1;
    }

    /**
     * Extraer QR code de una respuesta de Evolution API (v2.3.7).
     * El QR puede venir como string base64 o como objeto {base64: "..."}.
     */
    protected function extraerQr(array $body): ?string
    {
        $qrcode = $body['qrcode'] ?? $body['qr'] ?? null;
        if (is_array($qrcode)) {
            return $qrcode['base64'] ?? $qrcode['qr'] ?? $qrcode['image'] ?? null;
        }
        if (is_string($qrcode) && !empty($qrcode)) {
            return $qrcode;
        }
        if (isset($body['instance'])) {
            return $this->extraerQr($body['instance']);
        }
        return null;
    }

    // ==========================================
    // GESTIÓN DE NOTIFICACIONES PENDIENTES
    // ==========================================

    /**
     * Listar notificaciones pendientes (correos y whatsapp).
     */
    public function pendientes(): JsonResponse
    {
        $tenant = Auth::user()->tenant;

        $correos = $tenant->getPendingNotifications('correo');
        $whatsapp = $tenant->getPendingNotifications('whatsapp');

        $infoCorreos = [
            'limite' => $tenant->getLimiteFuncionalidad('correos_dia'),
            'uso' => $tenant->getCorreosUsadosHoy(),
        ];

        $infoWhatsapp = [
            'limite' => $tenant->getLimiteFuncionalidad('whatsapp_mes'),
            'uso' => $tenant->getWhatsappUsadosMes(),
        ];

        return response()->json([
            'success' => true,
            'pendientes_correos' => array_map(fn($p) => $this->formatearPendiente($p), $correos),
            'pendientes_whatsapp' => array_map(fn($p) => $this->formatearPendiente($p), $whatsapp),
            'total_correos' => count($correos),
            'total_whatsapp' => count($whatsapp),
            'info_correos' => $infoCorreos,
            'info_whatsapp' => $infoWhatsapp,
        ]);
    }

    /**
     * Reenviar una notificación pendiente.
     */
    public function reenviarPendiente(Request $request): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $id = $request->input('id');

        $pendientes = $tenant->getPendingNotifications();
        $pendiente = collect($pendientes)->firstWhere('id', $id);

        if (!$pendiente) {
            return response()->json(['success' => false, 'message' => 'Pendiente no encontrado.'], 404);
        }

        $data = $pendiente['data'];

        try {
            if ($pendiente['type'] === 'correo') {
                $this->reenviarCorreo($tenant, $data);
            } elseif ($pendiente['type'] === 'whatsapp') {
                if (!$tenant->canSendWhatsapp()) {
                    $limite = $tenant->getLimiteFuncionalidad('whatsapp_mes');
                    $uso = $tenant->getWhatsappUsadosMes();

                    return response()->json([
                        'success' => false,
                        'limit_exceeded' => true,
                        'message' => "Has alcanzado tu límite de {$limite} mensajes de WhatsApp este mes ({$uso}/{$limite}). Contacta a contacto@nexacore.com.mx para ampliar tu límite.",
                        'limite' => $limite,
                        'uso' => $uso,
                    ], 403);
                }

                $this->reenviarWhatsapp($tenant, $data);
            }

            $tenant->removePendingNotification($id);

            return response()->json([
                'success' => true,
                'message' => 'Notificación reenviada exitosamente.',
            ]);
        } catch (\Exception $e) {
            Log::error("[WhatsApp] Error al reenviar pendiente {$id}", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al reenviar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Descartar una notificación pendiente.
     */
    public function descartarPendiente(Request $request): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $id = $request->input('id');

        $tenant->removePendingNotification($id);

        return response()->json([
            'success' => true,
            'message' => 'Notificación descartada.',
        ]);
    }

    /**
     * Descartar todas las pendientes de un tipo.
     */
    public function descartarTodasPendientes(Request $request): JsonResponse
    {
        $tenant = Auth::user()->tenant;
        $type = $request->input('type');

        if (!in_array($type, ['correo', 'whatsapp'])) {
            return response()->json(['success' => false, 'message' => 'Tipo inválido.'], 400);
        }

        $tenant->clearPendingNotifications($type);

        return response()->json([
            'success' => true,
            'message' => "Todas las notificaciones de {$type} fueron descartadas.",
        ]);
    }

    /**
     * Formatear un pendiente para la UI.
     */
    protected function formatearPendiente(array $p): array
    {
        $data = $p['data'];
        return [
            'id' => $p['id'],
            'type' => $p['type'],
            'created_at' => $p['created_at'],
            'cliente' => $data['cliente_nombre'] ?? ($data['destinatarios'][0]['nombre'] ?? 'N/A'),
            'modulacion' => $data['modulacion'] ?? ($data['estatus'] ?? 'N/A'),
            'destinatarios' => is_array($data['destinatarios'] ?? null) ? count($data['destinatarios']) : 1,
        ];
    }

    /**
     * Reenviar correo desde datos de pendiente.
     */
    protected function reenviarCorreo($tenant, array $data): void
    {
        dispatch_sync(new \App\Jobs\EnviarNotificacionModulacionJob(
            $data['operacion_id'],
            $data['tenant_id'],
            $data['cliente_id'],
            $data['datosTramite'],
            $data['estatus'],
            $data['destinatarios'],
            $data['bcc'] ?? [],
            $data['executionId'] ?? uniqid('retry_', true)
        ));

        $tenant->incrementarConsumoCorreos();
    }

    /**
     * Reenviar WhatsApp desde datos de pendiente.
     */
    protected function reenviarWhatsapp($tenant, array $data): void
    {
        $operacion = \App\Models\Operacion::find($data['operacion_id']);
        if ($operacion) {
            $destinatarios = array_map(function ($d) {
                return [
                    'nombre' => $d['nombre'],
                    'whatsapp' => $d['numero'],
                    'canal' => 'whatsapp',
                ];
            }, $data['destinatarios'] ?? []);

            app(\App\Services\NotificacionWhatsAppService::class)->notificar(
                $operacion,
                $data['modulacion'] ?? 'ACTUALIZACION',
                $destinatarios,
                true
            );
        }
    }
}
