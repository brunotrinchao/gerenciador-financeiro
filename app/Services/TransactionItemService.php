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

        $this->updateProcess($items, false);

    }

    public function recalcAmountTransactionItem(TransactionItem $transactionItem)
    {
        $transaction = $transactionItem->transaction;

        $items = TransactionItem::where('transaction_id', $transaction->id)->get();

        // Agrupa os itens pagos e restantes manualmente
        $paidItems = $items->filter(fn($item) => $item->status === 'PAID');
        $remainingItems = $items->filter(fn($item) => $item->status !== 'PAID');

        $remainingAmount = $transaction->amount - $paidItems->sum('amount');
        $installmentsCount = $remainingItems->count();

        if ($installmentsCount === 0) {
            return;
        }

        $baseValue = intdiv($remainingAmount, $installmentsCount);
        $difference = $remainingAmount - ($baseValue * $installmentsCount);

        $i = 0;
        foreach ($remainingItems as $item) {
            $amount = $i === $installmentsCount - 1 ? $baseValue + $difference : $baseValue;

            $item->update([
                'amount' => $amount
            ]);
            $i++;
        }
    }

    public function updateAmountAndInstallmentCount(TransactionItem $transactionItem): void
    {
        $transaction = $transactionItem->transaction;

        $items = TransactionItem::where('transaction_id', $transaction->id)->get();

        $this->updateProcess($items);
    }

    /**
     * @param $items
     * @param mixed $transaction
     * @param bool $addNextMonth
     * @return void
     */
    public function updateProcess(\Illuminate\Database\Eloquent\Collection $items, bool $addNextMonth =  true): void
    {
        $grouped = $items->groupBy(fn($item) => $item->status === 'PAID' ? 'paid' : 'remaining');

        $paidItems = $grouped->get('paid', collect());
        $remainingItems = $grouped->get('remaining', collect());

        $installmentsCount = $remainingItems->count();
        if ($installmentsCount === 0) {
            return;
        }

        $transaction = $remainingItems->first()->transaction;

        $remainingAmount = $transaction->amount;

        if($paidItems->sum('amount') > 0){
            $remainingAmount = $remainingAmount - $paidItems->sum('amount');
        }


        $baseValue = intdiv($remainingAmount, $installmentsCount);
        $difference = $remainingAmount - ($baseValue * $installmentsCount);

        $installmentNumber = $paidItems->max('installment_number') ?? 0;

        $startDate = $paidItems->max('due_date')
            ? Carbon::parse($paidItems->max('due_date'))
            : Carbon::parse($transaction->date);

        $cardDueDay = $transaction->method === 'CARD' && $transaction->card?->due_date
            ? (int)$transaction->card->due_date
            : null;


        foreach ($remainingItems->values() as $i => $item) {
            $installmentNumber++;
            $amount = $i === $installmentsCount - 1 ? $baseValue + $difference : $baseValue;



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
