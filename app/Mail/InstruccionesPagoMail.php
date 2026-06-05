<?php

namespace App\Mail;

use App\Models\Suscripcion;
use App\Models\ConfiguracionFacturacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstruccionesPagoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Suscripcion $suscripcion,
        public ConfiguracionFacturacion $config,
    ) {}

    public function build()
    {
        return $this->subject("Instrucciones de Pago — {$this->suscripcion->plan->nombre} | NexaCore Aduanal")
            ->view('emails.instrucciones-pago');
    }
}
