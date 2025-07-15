<?php

namespace App\Console\Commands;

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

        foreach ($items as $item) {
            $transaction = $item->transaction;

            Notification::make()
                ->title('Transação em atraso')
                ->body("Valor: R$ " . number_format($item->amount, 2, ',', '.') .
                    "\nProduto: " . $transaction->description .
                    "\nVencimento: " . Carbon::parse($item->due_date)->format('d/m/Y') .
                    "\nMétodo: " . $this->getMethod($item) .
                    "\nStatus: " . $this->getStatusLabel($item->status))
                ->icon('heroicon-o-exclamation-circle')
                ->iconColor('danger')
                ->sendToDatabase($recepient);
        }

        $this->info("Foram notificadas {$items->count()} transações em atraso.");

        // Envia email com todas as transações vencidas
        Mail::to($recepient->email)->send(new OverdueTransactionItemsMail($items));
    }

    private function getMethod(TransactionItem $item): string
    {
        return $item->payment_method ?? 'Indefinido';
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'PENDING' => 'Pendente',
            'PAID' => 'Pago',
            'CANCELLED' => 'Cancelado',
            default => ucfirst(strtolower($status)),
        };
    }
}
