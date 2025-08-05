<?php

namespace App\Console\Commands;

use App\Helpers\TranslateString;
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

        $recepient = Auth::user() ?? \App\Models\User::where('email', env('EMAIL_USER_ADMIN'))->first();

        if (!$recepient) {
            $this->error('Usuário destinatário não encontrado.');
            return;
        }

        $htmlEmail = [];
        foreach ($items as $item) {
            $transaction = $item->transaction;

            $html = "Valor: R$ " . number_format($item->amount / 100, 2, ',', '.') .
                "<br>Produto: " . $transaction->description .
                "<br>Vencimento: " . Carbon::parse($item->due_date)->format('d/m/Y') .
                "<br>Método: " . TranslateString::getMethod($item) .
                "<br>Status: " . TranslateString::getStatusLabel($item->status);
            $htmlEmail[] = $html;
            Notification::make()
                ->title('Transação em atraso')
                ->body($html)
                ->icon('heroicon-o-exclamation-circle')
                ->iconColor('danger')
                ->sendToDatabase($recepient);
        }

        $this->info("Foram notificadas {$items->count()} transações em atraso.");

        $ccRecipients = \App\Models\User::pluck('email')->toArray();
        $ccRecipients = array_diff($ccRecipients, [$recepient->email]);


        // Envia email com todas as transações vencidas
        Mail::to($recepient->email)->cc($ccRecipients)->send(new OverdueTransactionItemsMail($htmlEmail));
    }
}
