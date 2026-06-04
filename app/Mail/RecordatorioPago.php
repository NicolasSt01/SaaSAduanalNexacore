<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecordatorioPago extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public int $dias,
    ) {}

    public function build()
    {
        return $this->subject("Recordatorio de pago — NexaCore Aduanal")
            ->markdown('emails.recordatorio-pago', [
                'tenant' => $this->tenant,
                'dias' => $this->dias,
            ]);
    }
}
