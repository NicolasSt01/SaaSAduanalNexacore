<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Documento;
use App\Models\NotificacionSistema;
use App\Services\SistemaNotificacionesService;
use Illuminate\Console\Command;

class VerificarCSFClientes extends Command
{
    protected $signature = 'clientes:verificar-csf';
    protected $description = 'Verifica la vigencia de la Constancia de Situación Fiscal de todos los clientes y genera alertas';

    public function handle(SistemaNotificacionesService $sistemaNotificaciones): int
    {
        $this->info('Iniciando verificación de CSF de clientes...');

        $clientes = Cliente::with(['documentosMaestros' => function ($q) {
            $q->where('tipo_documento', 'rfc');
        }])->get();

        $alertasCreadas = 0;

        foreach ($clientes as $cliente) {
            $csf = $cliente->documentosMaestros->first();

            if (!$csf) {
                // Cliente sin CSF registrada
                $mensaje = "El cliente {$cliente->nombre} no tiene registrada su Constancia de Situación Fiscal (CSF).";
                $existeAlerta = NotificacionSistema::where('tenant_id', $cliente->tenant_id)
                    ->where('tipo', 'csf_faltante')
                    ->where('metadata->cliente_id', $cliente->id)
                    ->whereMonth('created_at', now()->month)
                    ->exists();

                if (!$existeAlerta) {
                    $sistemaNotificaciones->crearNotificacion(
                        $cliente->tenant_id,
                        'csf_faltante',
                        "CSF pendiente: {$cliente->nombre}",
                        $mensaje . " Solicítala al cliente para cumplir con el Art. 36-A.",
                        'error',
                        route('clientes.show', $cliente->id),
                        'Ver Cliente',
                        ['cliente_id' => $cliente->id, 'cliente_nombre' => $cliente->nombre]
                    );
                    $alertasCreadas++;
                    $this->warn("ALERTA: {$mensaje}");
                }
                continue;
            }

            $fechaVencimiento = $csf->fecha_vencimiento;

            if (!$fechaVencimiento) {
                // CSF sin fecha de vencimiento, asignar una basada en created_at
                $fechaVencimiento = $csf->created_at->addMonthNoOverflow()->startOfMonth()->addDays(4);
                $csf->fecha_vencimiento = $fechaVencimiento;
                $csf->save();
            }

            $now = now()->startOfDay();
            $diasRestantes = (int) $now->diffInDays($fechaVencimiento, false);

            if ($diasRestantes < 0) {
                // CSF VENCIDA
                $diasVencida = abs($diasRestantes);
                $mensaje = "La CSF de {$cliente->nombre} venció hace {$diasVencida} día(s). ";
                $mensaje .= "Solicita una CSF actualizada para poder facturarle este mes.";

                $existeAlerta = NotificacionSistema::where('tenant_id', $cliente->tenant_id)
                    ->where('tipo', 'csf_vencida')
                    ->where('metadata->cliente_id', $cliente->id)
                    ->where('metadata->mes', now()->format('Y-m'))
                    ->exists();

                if (!$existeAlerta) {
                    $sistemaNotificaciones->crearNotificacion(
                        $cliente->tenant_id,
                        'csf_vencida',
                        "CSF vencida: {$cliente->nombre}",
                        $mensaje,
                        'error',
                        route('clientes.show', $cliente->id),
                        'Actualizar CSF',
                        ['cliente_id' => $cliente->id, 'cliente_nombre' => $cliente->nombre, 'mes' => now()->format('Y-m'), 'dias_vencida' => $diasVencida]
                    );
                    $alertasCreadas++;
                    $this->warn("ALERTA: CSF vencida para {$cliente->nombre}");
                }
            } elseif ($diasRestantes <= 5) {
                // CSF por vencer en 5 días o menos
                $mensaje = "La CSF de {$cliente->nombre} vence en {$diasRestantes} día(s) (el {$fechaVencimiento->format('d/m/Y')}). ";
                $mensaje .= "Solicita una CSF actualizada al cliente.";

                $existeAlerta = NotificacionSistema::where('tenant_id', $cliente->tenant_id)
                    ->where('tipo', 'csf_por_vencer')
                    ->where('metadata->cliente_id', $cliente->id)
                    ->where('metadata->mes', now()->format('Y-m'))
                    ->exists();

                if (!$existeAlerta) {
                    $sistemaNotificaciones->crearNotificacion(
                        $cliente->tenant_id,
                        'csf_por_vencer',
                        "CSF por vencer: {$cliente->nombre}",
                        $mensaje,
                        'warning',
                        route('clientes.show', $cliente->id),
                        'Actualizar CSF',
                        ['cliente_id' => $cliente->id, 'cliente_nombre' => $cliente->nombre, 'mes' => now()->format('Y-m'), 'dias_restantes' => $diasRestantes]
                    );
                    $alertasCreadas++;
                    $this->warn("AVISO: CSF por vencer para {$cliente->nombre} en {$diasRestantes} días");
                }
            } else {
                $this->line("OK: CSF de {$cliente->nombre} vigente ({$diasRestantes} días restantes)");
            }
        }

        $this->info("Verificación completada. {$alertasCreadas} alerta(s) creada(s).");
        return 0;
    }
}
