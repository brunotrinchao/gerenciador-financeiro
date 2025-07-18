<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TransactionItemService
{

    public function __construct()
    {
    }

    public function create(Transaction $transaction)
    {
        $remainingItems = TransactionItem::where('transaction_id', $transaction->id)
            ->get();

        $lastPaidDueDate = Carbon::parse($remainingItems->max('due_date'));
        $installmentsCount = $remainingItems->max('installment_number');

        $dueDate = (clone $lastPaidDueDate)->addMonthsNoOverflow(1);

        $transactionItem = TransactionItem::create([
            'transaction_id' => $transaction->id,
            'due_date' => $dueDate,
            'amount' => 0,
            'installment_number' => $installmentsCount + 1,
            'status' => 'PENDING',
        ]);

        $this->updateAmountAndInstallmentCount($transactionItem);
    }

    public function recalcAmountTransactionItem(TransactionItem $transactionItem)
    {
        $transaction = $transactionItem->transaction;

        $remainingItems = TransactionItem::where('transaction_id', $transaction->id)
            ->where('status', '!=', 'PAID')
            ->whereKeyNot($transactionItem->id)
            ->get();

        $amount = $transaction->amount - $transactionItem->amount;
        $installmentsCount = $remainingItems->count();

        if ($installmentsCount === 0) {
            return;
        }

        $baseValue = floor($amount / $installmentsCount * 100) / 100; // força 2 casas
        $remaining = $amount - ($baseValue * $installmentsCount);

        foreach ($remainingItems as $i => $item) {
            $currentAmount = ($i === $installmentsCount - 1) ? $baseValue + $remaining : $baseValue;

            $item->update([
                'amount' => $currentAmount
            ]);
        }
    }

    public function updateAmountAndInstallmentCount(TransactionItem $transactionItem): void
    {
        $transaction = $transactionItem->transaction;

        $paidItems = TransactionItem::where('transaction_id', $transaction->id)
            ->where('status', '=', 'PAID')
            ->get();
        $remainingItems = TransactionItem::where('transaction_id', $transaction->id)
            ->where('status', '!=', 'PAID')
            ->get();

        $amount = $transaction->amount - $paidItems->sum('amount');
        $installmentsCount = $remainingItems->count();

        if ($installmentsCount === 0) {
            return;
        }

        $baseValue = floor($amount / $installmentsCount * 100) / 100; // força 2 casas
        $remaining = $amount - ($baseValue * $installmentsCount);

        $installmentNumber = $paidItems->max('installment_number') ?? 0;

        $lastPaidDueDate = $paidItems->max('due_date');
        $startDate = $lastPaidDueDate ? Carbon::parse($lastPaidDueDate) : Carbon::parse($transaction->date);

        $cardDueDay = null;
        if ($transaction->method === 'CARD' && $transaction->card_id) {
            $cardDueDay = $transaction->card->due_date; // Ex: 23
        }
        foreach ($remainingItems as $i => $item) {
            $installmentNumber++;
            $currentAmount = ($i === $installmentsCount - 1) ? $baseValue + $remaining : $baseValue;

            $dueDate = (clone $startDate)->addMonthsNoOverflow($i + 1);

            if ($cardDueDay) {
                $dueDate->day = min((int) $cardDueDay, $dueDate->daysInMonth);
            }

            $item->update([
                'amount' => $currentAmount,
                'installment_number' => $installmentNumber,
                'due_date' => $dueDate,
            ]);
        }
    }
}
