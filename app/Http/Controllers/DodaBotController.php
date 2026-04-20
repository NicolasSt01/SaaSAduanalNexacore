<?php

namespace App\Http\Controllers;

use App\Services\DodaConsultaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * DodaBotController
 *
 * Controlador independiente para las operaciones del SOIA-Bot.
 * 
 * Este controlador es el punto de entrada API para que un bot externo
 * (job en servidor Windows) pueda disparar la consulta masiva de
 * modulaciones contra el portal PECEM/SOIA del SAT.
 * 
 * Características:
 * - Autenticación por token (sin sesión)
 * - Anti-concurrencia (impide ejecuciones simultáneas)
 * - Rate limiting por IP
 * - Logging dedicado
 * - Multi-tenant transparente
 * 
 * Endpoints:
 * - GET /api/bot/doda/ejecutar?token=xxx  → Ejecutar consulta masiva
 * - GET /api/bot/doda/status?token=xxx    → Verificar estado del bot
 * - GET /api/bot/doda/health              → Health check (sin token)
 */
class DodaBotController extends Controller
{
    protected DodaConsultaService $consultaService;

    public function __construct(DodaConsultaService $consultaService)
    {
        $this->consultaService = $consultaService;
    }

    // ==================== METODOS PARA EL PANEL UI ====================

    /**
     * Muestra el panel de control manual del Bot en la interfaz administrativa
     */
    public function showTestPanel()
    {
        return view('admin.bot-doda');
    }

    /**
     * Ejecutar el bot manualmente desde la UI administrativa
     */
    public function runLocal(Request $request): JsonResponse
    {
        $lockKey = 'doda_bot_running';
        if (Cache::has($lockKey)) {
            $lockInfo = Cache::get($lockKey);
            return response()->json([
                'success' => false,
                'error' => 'Bot ya en ejecución',
                'message' => 'Hay una ejecución en curso.',
                'execution_id_actual' => $lockInfo['execution_id'] ?? null,
            ], 429);
        }

        $executionId = uniqid('doda_ui_', true);
        Cache::put($lockKey, [
            'execution_id' => $executionId,
            'started_at' => now()->toIso8601String(),
            'user_id' => auth()->id(),
        ], 600);

        try {
            $this->logBot('info', '🚀 Ejecución manual iniciada desde Panel UI', [
                'user_id' => auth()->id(),
            ]);

            // IMPORTANTE: Marcar como ejecución manual para notificaciones completas
            $this->consultaService->setEjecucionManual(true);

            $resultado = $this->consultaService->ejecutarConsultaMasiva();

            Cache::forget($lockKey);

            return response()->json(array_merge(['success' => true], $resultado), 200);

        } catch (Exception $e) {
            Cache::forget($lockKey);
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener los últimos logs para mostrarlos en la UI.
     */
    public function getLogs(): JsonResponse
    {
        $logPath = storage_path('logs/doda_bot.log');
        if (!file_exists($logPath)) {
            return response()->json(['logs' => 'El archivo de logs (doda_bot.log) aún no existe. Ejecuta el bot al menos una vez.']);
        }

        $lines = file($logPath);
        $tail = array_slice($lines, -150);

        return response()->json(['logs' => implode("", $tail)]);
    }

    /**
     * Estado del bot usado exclusivamente por la UI (sin requerimiento de token auth)
     */
    public function statusUi(): JsonResponse
    {
        $lockKey = 'doda_bot_running';
        $isRunning = Cache::has($lockKey);

        $response = [
            'bot_activo' => $isRunning,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($isRunning) {
            $response['ejecucion_actual'] = Cache::get($lockKey);
        }

        $ultimaConsulta = \DB::table('operacion_historial_doda')
            ->where('source', 'bot')
            ->orderByDesc('created_at')
            ->first();

        if ($ultimaConsulta) {
            $response['ultima_ejecucion'] = [
                'execution_id' => $ultimaConsulta->execution_id,
                'consultado_at' => $ultimaConsulta->consultado_at,
            ];
        }

        $pendientes = \App\Models\Operacion::withoutGlobalScope('tenant')
            ->whereNotNull('num_doda')
            ->where('num_doda', '!=', '')
            ->where(function ($q) {
                $q->whereNull('modulacion')
                    ->orWhere('modulacion', '')
                    ->orWhere('modulacion', '0')
                    ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                    ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado');
            })
            ->count();

        $response['operaciones_pendientes'] = $pendientes;

        return response()->json($response, 200);
    }

    // ==================== ENPOINTS DEL BOT (EXTERNAL CRON) ====================

    /**
     * Ejecutar la consulta masiva de DODAs.
     * 
     * Este endpoint es invocado por un job externo (Windows Task Scheduler)
     * que hace un GET periódico para iniciar el procesamiento.
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * Ejemplo de llamada:
     * GET https://app.nexacore.com/api/bot/doda/ejecutar?token=9d12f90d...
     * 
     * Respuesta exitosa:
     * {
     *   "success": true,
     *   "execution_id": "doda_6605b...",
     *   "estado": "completado",
     *   "total_consultadas": 45,
     *   "total_cambios": 3,
     *   "total_errores": 0,
     *   "duracion_segundos": 12.5
     * }
     */
    public function ejecutar(Request $request): JsonResponse
    {
        // 1. Autenticación por token
        if (!$this->validarToken($request)) {
            $this->logAccesoNoAutorizado($request);
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Token inválido o no proporcionado',
            ], 401);
        }

        // 2. Protección anti-concurrencia
        $lockKey = 'doda_bot_running';
        if (Cache::has($lockKey)) {
            $lockInfo = Cache::get($lockKey);
            return response()->json([
                'success' => false,
                'error' => 'Bot ya en ejecución',
                'message' => 'Hay una ejecución en curso. Intenta de nuevo más tarde.',
                'execution_id_actual' => $lockInfo['execution_id'] ?? null,
                'iniciado_at' => $lockInfo['started_at'] ?? null,
            ], 429);
        }

        // 3. Adquirir lock (expira en 10 minutos como safety net)
        $executionId = uniqid('doda_', true);
        Cache::put($lockKey, [
            'execution_id' => $executionId,
            'started_at' => now()->toIso8601String(),
            'ip' => $request->ip(),
        ], 600);

        try {
            $this->logBot('info', '🚀 Ejecución iniciada por API', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // 4. Ejecutar la consulta masiva
            $resultado = $this->consultaService->ejecutarConsultaMasiva();

            // 5. Liberar lock
            Cache::forget($lockKey);

            return response()->json([
                'success' => true,
                'execution_id' => $resultado['execution_id'],
                'estado' => $resultado['estado'],
                'total_consultadas' => $resultado['total_consultadas'],
                'total_cambios' => $resultado['total_cambios'],
                'total_errores' => $resultado['total_errores'],
                'duracion_segundos' => $resultado['duracion_segundos'],
                'timestamp' => $resultado['timestamp'],
            ], 200);

        } catch (Exception $e) {
            // Liberar lock en caso de error
            Cache::forget($lockKey);

            $this->logBot('critical', '💀 Error crítico en ejecución API', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verificar el estado actual del bot.
     * Útil para que el job externo verifique si hay una ejecución en curso
     * antes de disparar una nueva.
     */
    public function status(Request $request): JsonResponse
    {
        if (!$this->validarToken($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $lockKey = 'doda_bot_running';
        $isRunning = Cache::has($lockKey);

        $response = [
            'bot_activo' => $isRunning,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($isRunning) {
            $lockInfo = Cache::get($lockKey);
            $response['ejecucion_actual'] = $lockInfo;
        }

        // Obtener última ejecución del historial (última entrada en historial_doda)
        $ultimaConsulta = \DB::table('operacion_historial_doda')
            ->where('source', 'bot')
            ->orderByDesc('created_at')
            ->first();

        if ($ultimaConsulta) {
            $response['ultima_ejecucion'] = [
                'execution_id' => $ultimaConsulta->execution_id,
                'consultado_at' => $ultimaConsulta->consultado_at,
            ];
        }

        // Estadísticas rápidas
        $pendientes = \App\Models\Operacion::withoutGlobalScope('tenant')
            ->whereNotNull('num_doda')
            ->where('num_doda', '!=', '')
            ->where(function ($q) {
                $q->whereNull('modulacion')
                    ->orWhere('modulacion', '')
                    ->orWhere('modulacion', '0')
                    ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                    ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado');
            })
            ->count();

        $response['operaciones_pendientes'] = $pendientes;

        return response()->json($response, 200);
    }

    /**
     * Health check simple (no requiere token).
     * Para monitoreo de uptime del servicio.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'NexaCore SOIA-Bot API',
            'version' => '2.0.0',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    /**
     * Actualizar fechas de operaciones rezagadas sin modulacion.
     *
     * Este endpoint se ejecuta diariamente (23:50) via cron job para
     * actualizar la fecha_cruce_estimada de operaciones que no han
     * logrado modulacion y cuya fecha ya pasó.
     *
     * Lógica:
     * - Busca operaciones con fecha_cruce_estimada <= hoy
     * - Que NO tengan modulacion válida
     * - Actualiza fecha_cruce_estimada + 1 día
     *
     * @param Request $request
     * @return JsonResponse
     *
     * Ejemplo de llamada:
     * POST https://app.nexacore.com/api/bot/doda/rollover-dates?token=9d12f90d...
     *
     * Respuesta exitosa:
     * {
     *   "success": true,
     *   "execution_id": "rollover_6605b...",
     *   "fecha_ejecucion": "2026-04-04T23:50:00.000000Z",
     *   "total_actualizadas": 15,
     *   "operaciones_actualizadas": [
     *     {"id": 123, "referencia": "45678", "fecha_anterior": "2026-04-03", "fecha_nueva": "2026-04-04"}
     *   ],
     *   "errores": []
     * }
     */
    public function actualizarFechasRezagadas(Request $request): JsonResponse
    {
        // 1. Autenticación por token
        if (!$this->validarToken($request)) {
            $this->logAccesoNoAutorizado($request);
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Token inválido o no proporcionado',
            ], 401);
        }

        // 2. Protección anti-concurrencia
        $lockKey = 'doda_date_rollover_running';
        if (Cache::has($lockKey)) {
            $lockInfo = Cache::get($lockKey);
            return response()->json([
                'success' => false,
                'error' => 'Rollover ya en ejecución',
                'message' => 'Hay un proceso de actualización de fechas en curso.',
                'execution_id_actual' => $lockInfo['execution_id'] ?? null,
            ], 429);
        }

        // 3. Adquirir lock (expira en 5 minutos como safety net)
        $executionId = uniqid('rollover_', true);
        Cache::put($lockKey, [
            'execution_id' => $executionId,
            'started_at' => now()->toIso8601String(),
            'ip' => $request->ip(),
        ], 300);

        try {
            $this->logBot('info', '🔄 Rollover de fechas iniciado', [
                'execution_id' => $executionId,
                'ip' => $request->ip(),
            ]);

            // 4. Buscar operaciones rezagadas
            $hoy = now()->startOfDay();

            $operacionesRezagadas = \App\Models\Operacion::withoutGlobalScope('tenant')
                ->whereNotNull('num_doda')
                ->where('num_doda', '!=', '')
                ->whereNotNull('fecha_cruce_estimada')
                ->whereDate('fecha_cruce_estimada', '<=', $hoy)
                ->where(function ($q) {
                    $q->whereNull('modulacion')
                        ->orWhere('modulacion', '')
                        ->orWhere('modulacion', '0')
                        ->orWhere('modulacion', 'RECONOCIMIENTO ADUANERO')
                        ->orWhere('modulacion', 'DODA no presentado al Mecanismo de Selección Automatizado');
                })
                ->get();

            $this->logBot('info', '📋 Operaciones rezagadas encontradas', [
                'total' => $operacionesRezagadas->count(),
            ]);

            // 5. Actualizar fechas
            $actualizadas = [];
            $errores = [];

            foreach ($operacionesRezagadas as $operacion) {
                try {
                    $fechaAnterior = $operacion->fecha_cruce_estimada->copy();
                    $fechaNueva = $fechaAnterior->copy()->addDay();

                    $operacion->update([
                        'fecha_cruce_estimada' => $fechaNueva,
                    ]);

                    $actualizadas[] = [
                        'id' => $operacion->id,
                        'referencia' => $operacion->referencia,
                        'num_doda' => $operacion->num_doda,
                        'fecha_anterior' => $fechaAnterior->format('Y-m-d'),
                        'fecha_nueva' => $fechaNueva->format('Y-m-d'),
                    ];

                    $this->logBot('info', "✅ Operación #{$operacion->id} actualizada", [
                        'referencia' => $operacion->referencia,
                        'fecha_anterior' => $fechaAnterior->format('Y-m-d'),
                        'fecha_nueva' => $fechaNueva->format('Y-m-d'),
                    ]);

                } catch (Exception $e) {
                    $errores[] = [
                        'id' => $operacion->id,
                        'referencia' => $operacion->referencia,
                        'error' => $e->getMessage(),
                    ];

                    $this->logBot('error', "❌ Error actualizando operación #{$operacion->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // 6. Liberar lock
            Cache::forget($lockKey);

            $this->logBot('info', '✅ Rollover completado', [
                'total_actualizadas' => count($actualizadas),
                'total_errores' => count($errores),
            ]);

            return response()->json([
                'success' => true,
                'execution_id' => $executionId,
                'fecha_ejecucion' => now()->toIso8601String(),
                'total_actualizadas' => count($actualizadas),
                'operaciones_actualizadas' => $actualizadas,
                'total_errores' => count($errores),
                'errores' => $errores,
            ], 200);

        } catch (Exception $e) {
            // Liberar lock en caso de error
            Cache::forget($lockKey);

            $this->logBot('critical', '💀 Error crítico en rollover de fechas', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validar el token de autenticación del bot
     */
    protected function validarToken(Request $request): bool
    {
        $token = $request->query('token') ?? $request->header('X-Bot-Token');

        if (empty($token)) {
            return false;
        }

        $expectedToken = env('CHECK_TRAFICO_TOKEN');

        if (empty($expectedToken)) {
            $this->logBot('error', 'CHECK_TRAFICO_TOKEN no configurado en .env');
            return false;
        }

        return hash_equals($expectedToken, $token);
    }

    /**
     * Registrar intento de acceso no autorizado
     */
    protected function logAccesoNoAutorizado(Request $request): void
    {
        $this->logBot('warning', '🚫 Intento de acceso no autorizado', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'token_parcial' => substr($request->query('token', ''), 0, 8) . '...',
            'url' => $request->fullUrl(),
        ]);
    }

    /**
     * Helper de logging dedicado
     */
    protected function logBot(string $level, string $message, array $context = []): void
    {
        try {
            Log::channel('doda_bot')->$level("[API] {$message}", $context);
        } catch (Exception $e) {
            Log::$level("[DODA_API] {$message}", $context);
        }
    }
}
