<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EstatusModulacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $templateData;
    protected string $templateView;

    public function __construct(string $templateView, array $templateData)
    {
        $this->templateView = $templateView;
        $this->templateData = $templateData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Actualización de Trámite - Crosspoint',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->templateView,
            with: $this->templateData,
        );
    }
}
