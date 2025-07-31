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
    private string $password;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
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
                'user' => $this->user,
                'password' => $this->password,
            ],
        );
    }


    public function attachments(): array
    {
        return [];
    }
}
