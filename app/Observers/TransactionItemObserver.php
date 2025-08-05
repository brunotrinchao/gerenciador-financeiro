<?php

namespace App\Observers;

use App\Helpers\TranslateString;
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

        if($transaction) {

            Notification::make()
                ->title('Nova transação adicionada')
                ->body("<b>Valor:<b/> R$ " . number_format($transactionItem->amount / 100, 2, ',', '.') .
                    "<br><b>Produto:<b/> " . $transaction->description .
                    "<br><b>Vencimento:<b/> " . Carbon::parse($transactionItem->due_date)->format('d/m/Y') .
                    "<br><b>Parcelas:<b/> " . $transactionItem->installment_number .
                    "<br><b>Método:<b/> " . TranslateString::getMethod($transactionItem) .
                    "<br><b>Status:<b/> " . TranslateString::getStatusLabel($transactionItem->status))
                ->icon('heroicon-o-plus-circle')
                ->iconColor('success')
                ->sendToDatabase($recepient);
        }
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
            ->title('Transação atualizada ('.TranslateString::getStatusLabel($transactionItem->status).')')
            ->body("<b>Valor:<b/> R$ " . number_format($transactionItem->amount / 100, 2, ',', '.') .
                "<br><b>Produto:<b/> " .  $transaction->description .
                "<br><b>Vencimento:<b/> " .  Carbon::parse($transactionItem->due_date)->format('d/m/Y') .
                "<br><b>Parcelas:<b/> " . $transactionItem->installment_number .
                "<br><b>Método:<b/> " . TranslateString::getMethod($transactionItem) .
                "<br><b>Status:<b/> " . TranslateString::getStatusLabel($transactionItem->status))
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
                'Transação de <b>R$ ' . number_format($transactionItem->amount / 100, 2, ',', '.') .
                '<b/> com vencimento em <b>' . Carbon::parse($transactionItem->payment_date)->format('d/m/Y') . '<b>.' .
                ($transaction ? "<br><b>Produto:<b/> " . $transaction->description : '')
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
}
