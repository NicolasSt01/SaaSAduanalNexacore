<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistroExitosoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $tenant;
    public $verificationUrl;
    public $passwordTemporal;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $tenant, $verificationUrl, $passwordTemporal)
    {
        $this->user = $user;
        $this->tenant = $tenant;
        $this->verificationUrl = $verificationUrl;
        $this->passwordTemporal = $passwordTemporal;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Bienvenido a NexaCore Aduanal! Tu cuenta ha sido creada',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.registro-exitoso',
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
