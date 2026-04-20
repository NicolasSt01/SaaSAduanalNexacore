<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EstatusModulacionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public  $cliente;
    public  $datosTramite;
    public  $estatus;



    public function __construct($cliente, $datosTramite, $estatus)
    {
        $this->cliente = $cliente;          // Objeto cliente
        $this->datosTramite = $datosTramite; // Arreglo con factura, carta porte, no. económico, no. alpha
        $this->estatus = $estatus;          // Texto estatus: "Desaduanamiento Libre" o "Reconocimiento Aduanero"
    }
    
    public function build()
    {
        return $this->subject('Actualización de Trámite - Crosspoint')
                    ->view('emails.estatus_modulacion');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Estatus Modulacion Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.estatus_modulacion',
        );
    }
    

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
