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
            'status' => $transaction->method == 'CARD' ? 'DEBIT' : 'PENDING',
        ]);

        $this->updateAmountAndInstallmentCount($transactionItem);
    }

    public function update(Transaction $transaction, bool $addMonth = true): void
    {
        $items = TransactionItem::where('transaction_id', $transaction->id)
            ->get();

        $items->each(function (TransactionItem $item) use ($transaction) {
           $this->updateAmountAndInstallmentCount($item);
        });

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

    public function updateAmountAndInstallmentCount(TransactionItem $transactionItem, bool $addNextMonth = true): void
    {
        $transaction = $transactionItem->transaction;

        $items = TransactionItem::where('transaction_id', $transaction->id)->get();

        $grouped = $items->groupBy(fn ($item) => $item->status === 'PAID' ? 'paid' : 'remaining');

        $paidItems = $grouped->get('paid', collect());
        $remainingItems = $grouped->get('remaining', collect());

        $installmentsCount = $remainingItems->count();
        if ($installmentsCount === 0) {
            return;
        }

        $remainingAmount = $transaction->amount - $paidItems->sum('amount');
        $baseValue = floor($remainingAmount / $installmentsCount * 100) / 100;
        $difference = $remainingAmount - ($baseValue * $installmentsCount);

        $installmentNumber = $paidItems->max('installment_number') ?? 0;

        $startDate = $paidItems->max('due_date')
            ? Carbon::parse($paidItems->max('due_date'))
            : Carbon::parse($transaction->date);

        $cardDueDay = $transaction->method === 'CARD' && $transaction->card?->due_date
            ? (int) $transaction->card->due_date
            : null;

        foreach ($remainingItems->values() as $i => $item) {
            $installmentNumber++;
            $amount = ($i === $installmentsCount - 1) ? $baseValue + $difference : $baseValue;

            $dueDate = (clone $startDate)->addMonthsNoOverflow($addNextMonth ? $i + 1 : $i);
            if ($cardDueDay) {
                $dueDate->day = min($cardDueDay, $dueDate->daysInMonth);
            }

            $item->update([
                'amount' => $amount,
                'installment_number' => $installmentNumber,
                'due_date' => $dueDate,
            ]);
        }
    }
}
