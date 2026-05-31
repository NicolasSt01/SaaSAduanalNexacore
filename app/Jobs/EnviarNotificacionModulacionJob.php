<?php

namespace App\Jobs;

use Exception;
use App\Models\Cliente;
use App\Models\Operacion;
use App\Models\Tenant;
use App\Mail\EstatusModulacionMail;
use App\Services\TenantMailService;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;

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

    public int $tries = 3;

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

            $operacion = Operacion::withoutGlobalScope('tenant')
                ->with(['expediente', 'aduana'])
                ->find($this->operacionId);

            // Determinar plantilla de correo del tenant
            $config = $tenant->configuracion ?? [];
            $templateName = $config['plantilla_correo_modulacion'] ?? null;
            $usaTemplatePersonalizado = $templateName
                && view()->exists("emails.modulacion.{$templateName}");

            if ($usaTemplatePersonalizado) {
                $templateView = "emails.modulacion.{$templateName}";
            } else {
                $templateView = 'emails.estatus_modulacion';
            }

            Log::channel('doda_bot')->info("{$logPrefix} Procesando envío de correo", [
                'cliente' => $cliente->nombre,
                'tenant' => $tenantNombre,
                'estatus' => $this->estatusTexto,
                'destinatarios' => count($this->destinatarios),
                'bcc' => count($this->bcc),
                'template' => $templateView,
                'usa_personalizado' => $usaTemplatePersonalizado,
            ]);

            // Caso 1: Hay destinatarios (directorio o fallback de cliente)
            if (!empty($this->destinatarios)) {
                foreach ($this->destinatarios as $destinatario) {
                    $email = $destinatario['email'] ?? null;
                    if (empty($email)) continue;

                    $nombreContacto = $destinatario['nombre'] ?? $cliente->nombre;

                    $mailable = $this->buildMailable(
                        $cliente,
                        $tenant,
                        $operacion,
                        $nombreContacto,
                        $templateView,
                        $usaTemplatePersonalizado
                    );

                    $enviado = TenantMailService::sendForTenant(
                        $this->tenantId,
                        $email,
                        $mailable
                    );

                    Log::channel('doda_bot')->info(
                        $enviado
                            ? "{$logPrefix} ✅ Correo enviado a {$email} ({$nombreContacto})"
                            : "{$logPrefix} ❌ Fallo envío a {$email}",
                        ['template' => $templateView]
                    );
                }

                // BCC: correos internos del tenant (sin personalizar nombre)
                if (!empty($this->bcc)) {
                    foreach ($this->bcc as $bccEmail) {
                        if (empty($bccEmail)) continue;

                        $mailableBcc = $this->buildMailable(
                            $cliente,
                            $tenant,
                            $operacion,
                            $cliente->nombre,
                            $templateView,
                            $usaTemplatePersonalizado
                        );

                        TenantMailService::sendForTenant(
                            $this->tenantId,
                            $bccEmail,
                            $mailableBcc
                        );
                    }
                }
            }
            // Caso 2: Solo correos internos del tenant
            elseif (!empty($this->bcc)) {
                foreach ($this->bcc as $bccEmail) {
                    if (empty($bccEmail)) continue;

                    $mailable = $this->buildMailable(
                        $cliente,
                        $tenant,
                        $operacion,
                        $cliente->nombre,
                        $templateView,
                        $usaTemplatePersonalizado
                    );

                    TenantMailService::sendForTenant(
                        $this->tenantId,
                        $bccEmail,
                        $mailable
                    );
                }

                Log::channel('doda_bot')->info("{$logPrefix} ✅ Correo enviado solo a internos", [
                    'bcc_count' => count($this->bcc),
                ]);
            }
            // Caso 3: Sin destinatarios
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
            throw $e;
        }
    }

    /**
     * Construir el Mailable con los datos apropiados según el template.
     */
    protected function buildMailable(
        Cliente $cliente,
        Tenant $tenant,
        ?Operacion $operacion,
        string $nombreContacto,
        string $templateView,
        bool $usaPersonalizado
    ): EstatusModulacionMail {
        if ($usaPersonalizado) {
            $viewData = [
                'tenant_empresa' => $tenant->nombre_empresa ?? 'Agencia Aduanal',
                'estatus' => $this->estatusTexto,
                'contacto_nombre' => $nombreContacto,
                'contacto_cliente' => $cliente->nombre,
                'operacion' => $this->buildOperacionData($operacion),
            ];
        } else {
            $viewData = [
                'cliente' => $cliente,
                'datosTramite' => $this->datosTramite,
                'estatus' => $this->estatusTexto,
            ];
        }

        return new EstatusModulacionMail($templateView, $viewData);
    }

    /**
     * Construir el objeto $operacion que esperan las plantillas configurables.
     */
    protected function buildOperacionData(?Operacion $operacion): object
    {
        return (object) [
            'factura' => $this->datosTramite['factura'] ?? null,
            'nombre_producto' => $this->datosTramite['nombre_producto'] ?? null,
            'expediente' => (object) [
                'numero_pedimento' => $operacion->expediente->numero_pedimento ?? null,
            ],
            'aduana' => (object) [
                'nombre' => $operacion->aduana->nombre ?? null,
            ],
            'fecha_pago' => $operacion->fecha_modulacion
                ? $operacion->fecha_modulacion->format('Y-m-d H:i')
                : date('Y-m-d H:i'),
        ];
    }

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
