<?php

namespace App\Jobs;

use App\Models\AddonContratado;
use App\Models\NotificacionSistema;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerificarAddonsVencidos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $addons = AddonContratado::with(['tenant', 'addon'])
            ->where('estado', 'activo')
            ->whereNotNull('fecha_fin')
            ->get();

        foreach ($addons as $ac) {
            $dias = $ac->diasRestantes();
            if ($dias === null) continue;

            if (in_array($dias, [7, 3, 1])) {
                $existeHoy = NotificacionSistema::where('tenant_id', $ac->tenant_id)
                    ->where('tipo', 'addon_por_vencer')
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();

                if (!$existeHoy) {
                    NotificacionSistema::create([
                        'tenant_id' => $ac->tenant_id,
                        'tipo' => 'addon_por_vencer',
                        'titulo' => 'Add-on por Vencer',
                        'mensaje' => "Tu add-on '{$ac->addon->nombre}' vence en {$dias} día(s). Contacta a soporte para renovar.",
                        'nivel' => $dias <= 3 ? 'warning' : 'info',
                    ]);
                }
            }

            if ($ac->fecha_fin && now()->startOfDay()->gt($ac->fecha_fin)) {
                $ac->vencer();
                \Log::info("Add-on vencido: '{$ac->addon->nombre}' de {$ac->tenant->nombre_empresa}");
            }
        }
    }
}
