<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Card;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function create(array $data): void
    {
        DB::transaction(function () use ($data) {
            $data['amount'] = preg_replace('/[^0-9,]/', '', $data['amount']);

            $transaction = Transaction::create($data);

            if (!$transaction) {
                return;
            }

            $this->updateBalancesBasedOnPaidInstallments($transaction, $data);
            $this->createInstallments($transaction, $data);
            $this->notifyTransactionCreated($transaction);
        });
        DB::commit();
    }


    public function update(Transaction $record, array $data): void
    {
        if(isset($data['amount']) && !empty($data['amount'])) {
            $data['amount'] = (int) preg_replace('/[^0-9,]/', '', $data['amount']);
        }

        $record->update($data);

        $service = new TransactionItemService();
        $service->update($record, false);
    }

    protected function updateBalancesBasedOnPaidInstallments(Transaction $transaction, array $data): void
    {
        $amountInCents = (int) str_replace(['.', ','], ['', '.'], $transaction->amount);
        $installmentsCount = !empty($data['is_recurring']) ? (int) ($transaction->recurrence_interval ?? 1) : 1;

        $baseValue = intdiv($amountInCents, $installmentsCount);
        $remaining = $amountInCents - ($baseValue * $installmentsCount);

        $paidInterval = (int) ($data['paid_interval'] ?? 0);
        $paidValue = 0;

        for ($i = 0; $i < $paidInterval; $i++) {
            $currentCents = $i === $installmentsCount - 1 ? $baseValue + $remaining : $baseValue;
            $paidValue += $currentCents;
        }

        if (!in_array($transaction->method, ['CARD', 'ACCOUNT']) || $paidValue <= 0) {
            return;
        }

        $multiplier = $transaction->type === 'EXPENSE' ? -1 : 1;
        $adjustedValue = $multiplier * $paidValue;

        if ($transaction->method === 'CARD' && $transaction->card_id) {
            $card = Card::find($transaction->card->id);
            $card->balance = (int) $transaction->card->balance + $adjustedValue;
            $card->save();
            $transaction->account->refresh();
        }

        if ($transaction->method === 'ACCOUNT' && $transaction->account_id) {
            $account = Account::find($transaction->account->id);
            $account->balance = (int) $transaction->account->balance + $adjustedValue;
            $account->save();
            $transaction->account->refresh();
        }
    }

    protected function createInstallments(Transaction $transaction, array $data): void
    {
        $amountInCents = (int) str_replace(['.', ','], ['', '.'], $transaction->amount);
        $installmentsCount = !empty($data['is_recurring']) ? (int) ($transaction->recurrence_interval ?? 1) : 1;

        $baseValue = intdiv($amountInCents, $installmentsCount);
        $remaining = $amountInCents - ($baseValue * $installmentsCount);

        $date = Carbon::parse($data['date']);
        $cardDueDay = ($transaction->method === 'CARD' && $transaction->card?->due_date)
            ? (int) $transaction->card->due_date
            : null;

        $paidInterval = (int) ($data['paid_interval'] ?? 0);

        for ($i = 0; $i < $installmentsCount; $i++) {
            $currentCents = $i === $installmentsCount - 1 ? $baseValue + $remaining : $baseValue;
            $paymentDate = (clone $date)->addMonths($i);

            if ($cardDueDay) {
                $paymentDate->day = min($cardDueDay, $paymentDate->daysInMonth);
            }

            $isPaid = $paidInterval > 0 && $i + 1 <= $paidInterval;
            $status = $isPaid
                ? 'PAID'
                : (in_array($transaction->method, ['CARD', 'ACCOUNT']) ? 'DEBIT' : 'PENDING');

            TransactionItem::create([
                'transaction_id'     => $transaction->id,
                'due_date'           => $paymentDate,
                'payment_date'       => $isPaid ? $paymentDate : null,
                'amount'             => $currentCents,
                'installment_number' => $i + 1,
                'status'             => $status,
            ]);
        }
    }

    protected function notifyTransactionCreated(Transaction $transaction): void
    {
        Notification::make()
            ->title(__('forms.notifications.transaction_created_title'))
            ->body(trans_choice(
                __('forms.notifications.transaction_created_body'),
                $transaction->recurrence_interval,
                ['count' => $transaction->recurrence_interval]
            ))
            ->success()
            ->send();
    }

}
