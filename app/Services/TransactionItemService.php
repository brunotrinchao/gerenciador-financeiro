<?php

namespace App\Services;

use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TransactionItemService
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
}
