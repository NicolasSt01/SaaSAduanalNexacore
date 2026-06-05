<?php

namespace App\Mail;

use App\Models\AddonContratado;
use App\Models\ConfiguracionFacturacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstruccionesPagoAddonMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AddonContratado $contratado,
        public ConfiguracionFacturacion $config,
    ) {}

    public function build()
    {
        return $this->subject("Instrucciones de Pago — Add-on {$this->contratado->addon->nombre} | NexaCore Aduanal")
            ->view('emails.instrucciones-pago-addon');
    }
}
