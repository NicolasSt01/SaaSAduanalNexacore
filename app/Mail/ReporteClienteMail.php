<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReporteClienteMail extends Mailable
{
    use Queueable, SerializesModels;
    

    public function __construct($cliente, $urlReporte, $desde, $hasta)
    {
        $this->cliente = $cliente;
        $this->urlReporte = $urlReporte;
        $this->desde = $desde;
        $this->hasta = $hasta;
    }

    public function build()
    {
        $tenant = \App\Models\Tenant::find($this->cliente->tenant_id);

        return $this->subject('📊 Tu Reporte de Operaciones - ' . \Carbon\Carbon::parse($this->desde)->format('M Y'))
            ->view('emails.reporte-cliente')
            ->with([
                'cliente' => $this->cliente,
                'urlReporte' => $this->urlReporte,
                'desde' => $this->desde,
                'hasta' => $this->hasta,
                'tenant' => $tenant
            ]);
    }










    /**
     * Create a new message instance.
     */
    /*public function __construct(public array $data)
    {
        //
    }*/
    
    /*public function build()
    {
        return $this->subject('Reporte Semanal de Operaciones')
            ->view('emails.reporte-cliente')
            ->with($this->data);
    }*/

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reporte Cliente Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
