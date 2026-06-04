<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\NotificacionSistema;
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
        $tenants = Tenant::where('estado', 'activo')
            ->whereNotNull('fecha_vencimiento')
            ->get();

        foreach ($tenants as $tenant) {
            $diasRestantes = (int) now()->startOfDay()->diffInDays($tenant->fecha_vencimiento, false);

            // Recordatorios por email a 7, 3 y 1 día
            if (in_array($diasRestantes, [7, 3, 1]) && $tenant->correo_admin) {
                try {
                    Mail::to($tenant->correo_admin)->send(new RecordatorioPago($tenant, $diasRestantes));
                } catch (\Exception $e) {
                    \Log::error("Error enviando recordatorio a {$tenant->nombre_empresa}", ['error' => $e->getMessage()]);
                }
            }

            // Notificaciones in-app al admin del tenant
            if (in_array($diasRestantes, [7, 3, 1, 0])) {
                $tipo = $diasRestantes <= 0 ? 'licencia_vencida' : 'licencia_por_vencer';
                $titulo = $diasRestantes <= 0 ? 'Licencia Vencida' : 'Licencia por Vencer';
                $mensaje = $diasRestantes <= 0
                    ? "Tu licencia ha vencido. Contacta a soporte para renovar."
                    : "Tu licencia vence en {$diasRestantes} día(s). Contacta a soporte para renovar.";

                $existeHoy = NotificacionSistema::where('tenant_id', $tenant->id)
                    ->where('tipo', $tipo)
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();

                if (!$existeHoy) {
                    NotificacionSistema::create([
                        'tenant_id' => $tenant->id,
                        'tipo' => $tipo,
                        'titulo' => $titulo,
                        'mensaje' => $mensaje,
                        'nivel' => $diasRestantes <= 0 ? 'error' : ($diasRestantes <= 3 ? 'warning' : 'info'),
                        'accion_url' => route('admin.config'),
                    ]);
                }
            }

            // Suspensión automática si venció + periodo de gracia agotado
            if ($tenant->estaVencido()) {
                $tenant->suspend();
                \Log::info("Tenant {$tenant->nombre_empresa} suspendido por licencia vencida");
            }
        }
    }
}
