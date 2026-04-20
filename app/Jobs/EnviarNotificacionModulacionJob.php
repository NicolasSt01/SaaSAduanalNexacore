<?php

namespace App\Jobs;

use Exception;
use App\Models\Cliente;
use App\Models\Tenant;
use App\Mail\EstatusModulacionMail;
use App\Services\TenantMailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;

/**
 * EnviarNotificacionModulacionJob
 *
 * Job multi-tenant para envío de notificaciones de modulación.
 *
 * A diferencia del legacy EnviarCorreoModulacionJob:
 * - No tiene correos hardcodeados
 * - Usa el Directorio y la config del tenant para routing
 * - Recibe los destinatarios calculados por NotificacionModulacionService
 * - Soporta diferentes reglas por tenant/cliente
 * - Usa la configuración SMTP específica del tenant
 */
class EnviarNotificacionModulacionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $operacionId;
    public int $tenantId;
    public int $clienteId;
    public array $datosTramite;
    public string $estatusTexto;
    public array $destinatarios;
    public array $bcc;
    public string $executionId;

    /**
     * Intentos máximos del job
     */
    public int $tries = 3;

    /**
     * Tiempo de espera entre reintentos (backoff en segundos)
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function __construct(
        int $operacionId,
        int $tenantId,
        int $clienteId,
        array $datosTramite,
        string $estatusTexto,
        array $destinatarios,
        array $bcc,
        string $executionId
    ) {
        $this->operacionId = $operacionId;
        $this->tenantId = $tenantId;
        $this->clienteId = $clienteId;
        $this->datosTramite = $datosTramite;
        $this->estatusTexto = $estatusTexto;
        $this->destinatarios = $destinatarios;
        $this->bcc = $bcc;
        $this->executionId = $executionId;
    }

    public function handle(): void
    {
        $logPrefix = "[JOB:{$this->executionId}][OP:{$this->operacionId}][T:{$this->tenantId}]";

        try {
            $cliente = Cliente::withoutGlobalScope('tenant')->find($this->clienteId);

            if (!$cliente) {
                Log::channel('doda_bot')->error("{$logPrefix} Cliente no encontrado", [
                    'cliente_id' => $this->clienteId,
                ]);
                return;
            }

            $tenant = Tenant::find($this->tenantId);
            $tenantNombre = $tenant->nombre_empresa ?? 'Desconocido';

            Log::channel('doda_bot')->info("{$logPrefix} Procesando envío de correo", [
                'cliente' => $cliente->nombre,
                'tenant' => $tenantNombre,
                'estatus' => $this->estatusTexto,
                'destinatarios' => count($this->destinatarios),
                'bcc' => count($this->bcc),
            ]);

            // Caso 1: Hay destinatarios del Directorio del cliente
            if (!empty($this->destinatarios)) {
                $emails = array_column($this->destinatarios, 'email');
                $emailPrincipal = array_shift($emails);

                // Usar configuración SMTP del tenant
                $enviado = TenantMailService::sendForTenant(
                    $this->tenantId,
                    $emailPrincipal,
                    new EstatusModulacionMail(
                        $cliente,
                        $this->datosTramite,
                        $this->estatusTexto
                    )
                );

                // Agregar CC y BCC si hay más destinatarios
                if ($enviado) {
                    if (!empty($emails)) {
                        foreach ($emails as $ccEmail) {
                            TenantMailService::sendForTenant(
                                $this->tenantId,
                                $ccEmail,
                                new EstatusModulacionMail(
                                    $cliente,
                                    $this->datosTramite,
                                    $this->estatusTexto
                                )
                            );
                        }
                    }

                    if (!empty($this->bcc)) {
                        foreach ($this->bcc as $bccEmail) {
                            TenantMailService::sendForTenant(
                                $this->tenantId,
                                $bccEmail,
                                new EstatusModulacionMail(
                                    $cliente,
                                    $this->datosTramite,
                                    $this->estatusTexto
                                )
                            );
                        }
                    }
                }

                Log::channel('doda_bot')->info("{$logPrefix} ✅ Correo enviado a cliente + internos", [
                    'to' => $emailPrincipal,
                    'cc' => $emails,
                    'bcc_count' => count($this->bcc),
                ]);

            }
            // Caso 2: Solo correos internos del tenant (sin destinatarios del cliente)
            elseif (!empty($this->bcc)) {
                $bccEmails = $this->bcc;
                $principal = array_shift($bccEmails);

                $enviado = TenantMailService::sendForTenant(
                    $this->tenantId,
                    $principal,
                    new EstatusModulacionMail(
                        $cliente,
                        $this->datosTramite,
                        $this->estatusTexto
                    )
                );

                if ($enviado && !empty($bccEmails)) {
                    foreach ($bccEmails as $bccEmail) {
                        TenantMailService::sendForTenant(
                            $this->tenantId,
                            $bccEmail,
                            new EstatusModulacionMail(
                                $cliente,
                                $this->datosTramite,
                                $this->estatusTexto
                            )
                        );
                    }
                }

                Log::channel('doda_bot')->info("{$logPrefix} ✅ Correo enviado solo a internos", [
                    'to' => $principal,
                    'bcc_count' => count($bccEmails),
                ]);

            }
            // Caso 3: Sin destinatarios en absoluto
            else {
                Log::channel('doda_bot')->warning("{$logPrefix} ⚠️ Sin destinatarios, correo no enviado", [
                    'cliente' => $cliente->nombre,
                ]);
            }

        } catch (Exception $e) {
            Log::channel('doda_bot')->error("{$logPrefix} ✗ Error enviando correo", [
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Re-throw para que el queue system pueda reintentar
            throw $e;
        }
    }

    /**
     * Handle a job failure after all retries
     */
    public function failed(?\Throwable $exception): void
    {
        Log::channel('doda_bot')->critical("[JOB:FAILED][OP:{$this->operacionId}] Job falló definitivamente", [
            'execution_id' => $this->executionId,
            'tenant_id' => $this->tenantId,
            'cliente_id' => $this->clienteId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
