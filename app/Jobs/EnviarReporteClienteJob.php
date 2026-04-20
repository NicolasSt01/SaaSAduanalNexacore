<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ReporteClienteService;
use App\Mail\ReporteClienteMail;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Cliente;
use App\Services\TenantMailService;
use Illuminate\Support\Facades\Log;

class EnviarReporteClienteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cliente;
    protected $urlReporte;
    protected $desde;
    protected $hasta;

    public function __construct($cliente, $urlReporte, $desde, $hasta)
    {
        // Si recibe el ID, buscar el cliente
        if (is_numeric($cliente)) {
            $this->cliente = Cliente::find($cliente);
        } else {
            $this->cliente = $cliente;
        }

        $this->urlReporte = $urlReporte;
        $this->desde = $desde;
        $this->hasta = $hasta;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Validar que el cliente tenga email
            if (empty($this->cliente->correo_contacto_principal)) {
                Log::warning("Cliente {$this->cliente->nombre_empresa} no tiene email configurado");
                return;
            }

            // Obtener tenant_id del cliente para usar su configuración SMTP
            $tenantId = $this->cliente->tenant_id;

            // Usar configuración SMTP del tenant
            $enviado = TenantMailService::sendForTenant(
                $tenantId,
                $this->cliente->correo_contacto_principal,
                new ReporteClienteMail(
                    $this->cliente,
                    $this->urlReporte,
                    $this->desde,
                    $this->hasta
                )
            );

            if ($enviado) {
                Log::info("Reporte enviado exitosamente a {$this->cliente->correo_contacto_principal}");
            } else {
                Log::error("Error al enviar reporte a {$this->cliente->correo_contacto_principal}");
            }

        } catch (\Exception $e) {
            Log::error("Error enviando reporte a {$this->cliente->correo_contacto_principal}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job falló para cliente {$this->cliente->nombre_empresa}: " . $exception->getMessage());
    }
}
