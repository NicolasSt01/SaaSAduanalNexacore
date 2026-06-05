<?php

namespace App\Mail;

use App\Models\Suscripcion;
use App\Models\ConfiguracionFacturacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PagoRechazadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Suscripcion $suscripcion,
        public ConfiguracionFacturacion $config,
        public string $motivo,
    ) {}

    public function build()
    {
        return $this->subject("Pago No Aprobado — NexaCore Aduanal")
            ->view('emails.pago-rechazado');
    }
}
