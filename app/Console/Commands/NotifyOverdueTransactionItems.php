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
        $items = TransactionItem::with('transaction')
            ->where('status', 'PENDING')
            ->whereDate('due_date', '<', now())
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

            $html = "Valor: R$ " . number_format($item->amount, 2, ',', '.') .
                "\nProduto: " . $transaction->description .
                "\nVencimento: " . Carbon::parse($item->due_date)->format('d/m/Y') .
                "\nMétodo: " . TranslateString::getMethod($item) .
                "\nStatus: " . TranslateString::getStatusLabel($item->status);
            $htmlEmail[] = $html;
            Notification::make()
                ->title('Transação em atraso')
                ->body($html)
                ->icon('heroicon-o-exclamation-circle')
                ->iconColor('danger')
                ->sendToDatabase($recepient);
        }

        $this->info("Foram notificadas {$items->count()} transações em atraso.");

        // Envia email com todas as transações vencidas
        Mail::to($recepient->email)->send(new OverdueTransactionItemsMail($htmlEmail));
    }

}
