<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpcomingTransactionItemNotification extends Notification
{
    use Queueable;

    protected int $count;
    /**
     * Create a new notification instance.
     */
    public function __construct(int $count)
    {
        $this->count = $count;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Transações a vencer',
            'body' => "Você tem {$this->count} transações que vencem em até 3 dias.",
            'url' => route('filament.admin.resources.transaction-items.index'), // ajuste a rota se necessário
        ];
    }

    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => 'Transações a vencer',
            'message' => "Você tem {$this->count} transações que vencem nos próximos 3 dias.",
        ]);
    }
}
