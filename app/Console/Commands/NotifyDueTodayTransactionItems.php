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
            ->whereHas('transaction', function ($query) {
                $query->where('type', 'EXPENSE');
            })
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

            $amount = number_format($item->amount / 100, 2, ',', '.');
            $description = $transaction->description;
            $dueDate = Carbon::parse($item->due_date)->format('d/m/Y');
            $method = TranslateString::getMethod($item);
            $status = TranslateString::getStatusLabel($item->status);
            $installment = $item->installment_number;
            $recurrence_interval = $transaction->recurrence_interval;

            $html = "Valor: R$ {$amount}" .
                "<br>Produto: {$description}" .
                "<br>Vencimento: {$dueDate}" .
                "<br>Método: {$method}" .
                "<br>Status: {$status}" .
                "<br>Parcela: {$installment}/{$recurrence_interval}";

            $htmlEmail[] = [
                'amount' => $amount,
                'description' => $description,
                'due_date' => $dueDate,
                'method' => $method,
                'installment' => "{$installment}/{$recurrence_interval}",
                'status' => $status,
            ];

            Notification::make()
                ->title('Transação vence hoje')
                ->body($html)
                ->icon('heroicon-o-clock')
                ->iconColor('warning')
                ->sendToDatabase($recipient);
        }

        $ccRecipients = \App\Models\User::where('email', '!=', $recipient->email)->pluck('email')->toArray();

        Mail::to($recipient->email)->cc($ccRecipients)->send(new DueTodayTransactionItemsMail($htmlEmail));

        $this->info("Foram notificadas {$items->count()} transações com vencimento hoje.");

    }
}
