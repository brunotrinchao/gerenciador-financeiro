<?php

namespace App\Console\Commands;

use App\Helpers\TranslateString;
use App\Mail\DueTodayTransactionItemsMail;
use App\Mail\OverdueTransactionItemsMail;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class NotifyOverdueTransactionItems extends Command
{
    protected $signature = 'transactions:notify-overdue';
    protected $description = 'Notifica o usuário sobre transações em atraso com status PENDING';

    public function handle(): void
    {
        $items = TransactionItem::with('transaction')->where('status', 'PENDING')->whereDate('due_date', '<', now())
            ->get();

        if ($items->isEmpty()) {
            $this->info('Nenhuma transação em atraso encontrada.');
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

            $amount = number_format($item->amount, 2, ',', '.');
            $description = $transaction->description;
            $dueDate = \Illuminate\Support\Carbon::parse($item->due_date)->format('d/m/Y');
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
                ->title('Transação em atraso')
                ->body($html)
                ->icon('heroicon-o-exclamation-circle')
                ->iconColor('danger')
                ->sendToDatabase($recipient);
        }

        $ccRecipients = \App\Models\User::where('email', '!=', $recipient->email)->pluck('email')->toArray();

        Mail::to($recipient->email)->cc($ccRecipients)->send(new DueTodayTransactionItemsMail($htmlEmail));

        $this->info("Foram notificadas {$items->count()} transações em atraso.");

    }
}
