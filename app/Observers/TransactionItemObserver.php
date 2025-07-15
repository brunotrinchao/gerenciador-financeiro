<?php

namespace App\Observers;

use App\Models\TransactionItem;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class TransactionItemObserver
{
    /**
     * Handle the TransactionItem "created" event.
     */
    public function created(TransactionItem $transactionItem): void
    {
        $recepient = Auth::user() ?? \App\Models\User::where('email', env('EMAIL_USER_ADMIN'))->first();
        if(!$recepient){
            return;
        }

        $transaction = $transactionItem->transaction;

        Notification::make()
            ->title('Nova transação adicionada')
            ->body("Valor: R$ " . number_format($transactionItem->amount, 2, ',', '.') .
                "\nProduto: " .  $transaction->description.
                "\nVencimento: " .  Carbon::parse($transactionItem->due_date)->format('d/m/Y') .
                "\nMétodo: " . $this->getMethod($transactionItem) .
                "\nStatus: " . $this->getStatusLabel($transactionItem->status))
            ->icon('heroicon-o-plus-circle')
            ->iconColor('success')
            ->sendToDatabase($recepient);
    }

    /**
     * Handle the TransactionItem "updated" event.
     */
    public function updated(TransactionItem $transactionItem): void
    {
        $recepient = Auth::user() ?? \App\Models\User::where('email', env('EMAIL_USER_ADMIN'))->first();
        if(!$recepient){
            return;
        }

        $transaction = $transactionItem->transaction;

        Notification::make()
            ->title('Transação atualizada ('.$this->getStatusLabel($transactionItem->status).')')
            ->body("Valor: R$ " . number_format($transactionItem->amount, 2, ',', '.') .
                "<br>Produto: " .  $transaction->description .
                "<br>Vencimento: " .  Carbon::parse($transactionItem->duw_date)->format('d/m/Y') .
                "<br>Método: " . $this->getMethod($transactionItem) .
                "<br>Status: " . $this->getStatusLabel($transactionItem->status))
            ->icon('heroicon-o-pencil-square')
            ->iconColor('warning')
            ->sendToDatabase($recepient);
    }

    /**
     * Handle the TransactionItem "deleted" event.
     */
    public function deleted(TransactionItem $transactionItem): void
    {
        $recepient = Auth::user() ?? \App\Models\User::where('email', env('EMAIL_USER_ADMIN'))->first();
        if(!$recepient){
            return;
        }

        $transaction = $transactionItem->transaction;

        Notification::make()
            ->title('Transação removida')
            ->body(
                'Transação de R$ ' . number_format($transactionItem->amount, 2, ',', '.') .
                ' com vencimento em ' . Carbon::parse($transactionItem->payment_date)->format('d/m/Y') .
                ($transaction ? "\nProduto: " . $transaction->description : '')
            )
            ->icon('heroicon-o-trash')
            ->iconColor('danger')
            ->sendToDatabase($recepient);
    }

    /**
     * Handle the TransactionItem "restored" event.
     */
    public function restored(TransactionItem $transactionItem): void
    {
        //
    }

    /**
     * Handle the TransactionItem "force deleted" event.
     */
    public function forceDeleted(TransactionItem $transactionItem): void
    {
        //
    }


    /**
     * Retorna o nome do método de pagamento.
     */
    protected function getMethod(TransactionItem $item): string
    {
        return match ($item->transaction->method) {
            'CARD' => 'Cartão de crédito',
            'ACCOUNT' => 'Conta',
            'CASH' => 'Dinheiro',
            default => 'Indefinido',
        };
    }

    /**
     * Retorna o rótulo do status da parcela.
     */
    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            'PAID' => 'Pago',
            'SCHEDULED' => 'Agendado',
            'DEBIT' => 'Débito automático',
            'PENDING' => 'Pendente',
            default => 'Desconhecido'
        };
    }
}
