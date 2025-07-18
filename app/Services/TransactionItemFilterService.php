<?php

namespace App\Services;

use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TransactionItemFilterService
{
    protected Builder $bulder;
    protected array $filters;

    public function __construct(array $filters, ?Builder $builder = null)
    {
        $this->filters = $filters;
        $this->bulder = TransactionItem::query();

        if($builder){
            $this->bulder = $builder;
        }
    }

    public function items(): Builder
    {
        $start = $this->filters['startDate'] ? Carbon::parse($this->filters['startDate']) : Carbon::now()->startOfMonth();
        $end = $this->filters['endDate'] ? Carbon::parse($this->filters['endDate']) : Carbon::now()->endOfMonth();
        $status = $this->filters['status'] ?? null;

        return $this->bulder
            ->with([
                'transaction',
                'card'
            ])
            ->when($status, function ($q) use ($status) {
                if($status == 'SCHEDULED/DEBIT'){
                    $q->whereIn('status', explode('/', $status));
                }
                $q->where('status', '=', $status);
            })
            ->when($start, fn ($q) => $q->whereDate('due_date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('due_date', '<=', $end))
            ->when($this->filters['category'] ?? null, fn ($q) =>
            $q->whereHas('transaction', fn ($q) => $q->where('category_id', $this->filters['category']))
            );
    }

    public function all(): Collection
    {
        return $this->items()->get();
    }

    public function upcomingTransaction(): Builder
    {
        return $this->items()
            ->with(['transaction', 'card', 'account.bank'])
            ->where('status', '=', 'PENDING')
            ->orderBy('due_date')
            ->limit(10);
    }

    public function updateAmountAndInstallmentCount(TransactionItem $transactionItem): void
    {
        $transaction = $transactionItem->transaction();

        $paidItems = TransactionItem::where('transaction_id', $transaction->id)
            ->where('status', '=', 'PAID')
            ->get();
        $remainingItems = TransactionItem::where('transaction_id', $transaction->id)
            ->where('status', '!=', 'PAID')
            ->get();

        $totalPaid = $paidItems->sum('amount');

        $amount = $transaction->amount - $totalPaid;
        $installmentsCount = $remainingItems->count();

        $baseValue = floor($amount / $installmentsCount * 100) / 100; // força 2 casas
        $remaining = $amount - ($baseValue * $installmentsCount);


        $installmentCount = $paidItems->count();
        $lastPaidInstallmentNumber = $paidItems->max('installment_number') ?? 0;
        $startDate = Carbon::parse($transaction->date);

        $cardDueDay = null;
        if ($transaction->method === 'CARD' && $transaction->card_id) {
            $cardDueDay = $transaction->card->due_date; // Ex: 23
        }

        foreach ($remainingItems as $i => $item) {
            $installmentNumber = $lastPaidInstallmentNumber + $i + 1;
            $currentAmount = ($installmentCount === $installmentsCount) ? $baseValue + $remaining : $baseValue;

            $dueDate = (clone $startDate)->addMonths($installmentNumber);

            if ($cardDueDay) {
                $dueDate->day = min($cardDueDay, $dueDate->daysInMonth);
            }

            $item->update([
                'amount' => $currentAmount,
                'installment_number' => $installmentNumber,
                'due_date' => $dueDate,
            ]);
        }
    }
}
