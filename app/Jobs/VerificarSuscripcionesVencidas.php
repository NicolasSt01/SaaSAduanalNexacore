<?php

namespace App\Jobs;

use App\Models\Suscripcion;
use App\Models\NotificacionSistema;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerificarSuscripcionesVencidas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $suscripciones = Suscripcion::with(['tenant', 'plan'])
            ->where('estado', 'activa')
            ->whereNotNull('fecha_fin')
            ->get();

        foreach ($suscripciones as $sus) {
            $dias = $sus->diasRestantes();
            if ($dias === null) continue;

            if (in_array($dias, [7, 3, 1])) {
                $existeHoy = NotificacionSistema::where('tenant_id', $sus->tenant_id)
                    ->where('tipo', 'suscripcion_por_vencer')
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();

                if (!$existeHoy) {
                    NotificacionSistema::create([
                        'tenant_id' => $sus->tenant_id,
                        'tipo' => 'suscripcion_por_vencer',
                        'titulo' => 'Suscripción por Vencer',
                        'mensaje' => "Tu suscripción al plan '{$sus->plan->nombre}' vence en {$dias} día(s). Contacta a soporte para renovar.",
                        'nivel' => $dias <= 3 ? 'warning' : 'info',
                    ]);
                }
            }

            if ($sus->fecha_fin && now()->startOfDay()->gt($sus->fecha_fin)) {
                $sus->vencer();
                $sus->tenant->suspend();
                \Log::info("Suscripción vencida y tenant suspendido: {$sus->tenant->nombre_empresa}");
            }
        }
    }
}
