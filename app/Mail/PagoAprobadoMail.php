<?php

namespace App\Mail;

use App\Models\Suscripcion;
use App\Models\ConfiguracionFacturacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PagoAprobadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Suscripcion $suscripcion,
        public ConfiguracionFacturacion $config,
    ) {}

    public function build()
    {
        return $this->subject("Pago Aprobado — {$this->suscripcion->plan->nombre} | NexaCore Aduanal")
            ->view('emails.pago-aprobado');
    }
}
