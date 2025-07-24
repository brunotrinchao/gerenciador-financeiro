<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeToSystemCreateUser extends Mailable
{
    use Queueable, SerializesModels;

    private User $user;

    public function __construct($user)
    {
        $this->user = $user;
    }


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bem vindo ao ' . config('app.name'),
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-to-system-create-user',
            with: [
                'user' => $this->user
            ],
        );
    }


    public function attachments(): array
    {
        return [];
    }
}
