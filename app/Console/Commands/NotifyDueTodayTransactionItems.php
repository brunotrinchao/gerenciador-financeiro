<?php

namespace App\Console\Commands;

use App\Helpers\TranslateString;
use App\Mail\DueTodayTransactionItemsMail;
use App\Models\TransactionItem;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class NotifyDueTodayTransactionItems extends Command
{
    protected $signature = 'transactions:notify-due-today';
    protected $description = 'Notifica o usuário sobre transações que vencem hoje com status PENDING';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $items = TransactionItem::with('transaction')
            ->where('status', 'PENDING')
            ->whereDate('due_date', now()->toDateString())
            ->get();

        if ($items->isEmpty()) {
            $this->info('Nenhuma transação com vencimento hoje.');
            return;
        }

        $recipient = \App\Models\User::where('email', env('EMAIL_USER_ADMIN'))->first();

        if (!$recipient) {
            $this->error('Usuário destinatário não encontrado.');
            return;
        }

        $htmlEmail = [];

        foreach ($items as $item) {
            $transaction = $item->transaction;

            $html = "Valor: R$ " . number_format($item->amount, 2, ',', '.') .
                "<br>Produto: " . $transaction->description .
                "<br>Vencimento: " . Carbon::parse($item->due_date)->format('d/m/Y') .
                "<br>Método: " . TranslateString::getMethod($item) .
                "<br>Status: " . TranslateString::getStatusLabel($item->status);

            $htmlEmail[] = $html;

            Notification::make()
                ->title('Transação vence hoje')
                ->body($html)
                ->icon('heroicon-o-clock')
                ->iconColor('warning')
                ->sendToDatabase($recipient);
        }

        $ccRecipients = \App\Models\User::pluck('email')->toArray();
        $ccRecipients = array_diff($ccRecipients, [$recipient->email]);

        Mail::to($recipient->email)->cc($ccRecipients)->send(new DueTodayTransactionItemsMail($htmlEmail));

        $this->info("Foram notificadas {$items->count()} transações com vencimento hoje.");

    }
}
