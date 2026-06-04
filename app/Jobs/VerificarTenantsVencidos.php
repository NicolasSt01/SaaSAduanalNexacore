<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Mail\RecordatorioPago;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class VerificarTenantsVencidos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $tenants = Tenant::where('estado', 'activo')->where('saldo_pendiente', '>', 0)->get();

        foreach ($tenants as $tenant) {
            $dias = $tenant->diasHastaVencimiento();

            if ($dias === null) continue;

            // Recordatorios
            if (in_array($dias, [7, 3, 1]) && $tenant->correo_admin) {
                try {
                    Mail::to($tenant->correo_admin)->send(new RecordatorioPago($tenant, $dias));
                } catch (\Exception $e) {
                    \Log::error("Error enviando recordatorio a {$tenant->nombre_empresa}", ['error' => $e->getMessage()]);
                }
            }

            // Corte automático
            if ($tenant->estaVencido()) {
                $tenant->suspend();
                \Log::info("Tenant {$tenant->nombre_empresa} suspendido por impago (vencido + gracia agotada)");
            }
        }
    }
}
