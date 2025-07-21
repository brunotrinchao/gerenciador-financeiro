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
        $start = Carbon::parse($this->filters['startDate'] ?? Carbon::now()->startOfMonth());
        $end = Carbon::parse($this->filters['endDate'] ?? Carbon::now()->endOfMonth());

        $status = $this->filters['status'] ?? null;
        $methods = $this->filters['method'] ?? null;

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
            )
            ->when($methods, fn ($q) => $q->whereIn('method', is_array($methods) ? $methods : [$methods]));
    }

    public function all(): Collection
    {
        return $this->items()->get();
    }

    public function upcomingTransaction(): Builder
    {
        return $this->items()
            ->with(['transaction', 'card', 'account.bank'])
            ->where('status', '<>', 'PAID')
            ->orderBy('due_date')
            ->limit(10);
    }
}

