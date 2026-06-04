<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $password,
        public Tenant $tenant,
    ) {}

    public function build()
    {
        return $this->subject('Bienvenido a NexaCore Aduanal')
            ->markdown('emails.welcome', [
                'user' => $this->user,
                'password' => $this->password,
                'tenant' => $this->tenant,
            ]);
    }
}
