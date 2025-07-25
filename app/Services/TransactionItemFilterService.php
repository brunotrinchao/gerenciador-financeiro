<?php

namespace App\Services;

use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TransactionItemFilterService
{
    protected Builder $builder;
    protected array $filters;

    public function __construct(array $filters, ?Builder $builder = null)
    {
        $this->filters = $filters;
        $this->resolveDateFilter();
        $this->builder = TransactionItem::query();

        if($builder){
            $this->builder = $builder;
        }
    }

    public function items(): Builder
    {
        $start = Carbon::parse($this->filters['startDate'] ?? Carbon::now()->startOfMonth());
        $end = Carbon::parse($this->filters['endDate'] ?? Carbon::now()->endOfMonth());

        $status = $this->filters['status'] ?? null;
        $methods = $this->filters['method'] ?? null;

        return $this->builder
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
            ->with(['transaction'])
            ->whereHas('transaction', function ($q) {
                return $q->where('type', 'EXPENSE')
                    ->where('method', '!=', 'CARD');
            })
            ->where('status', '<>', 'PAID')
            ->orderBy('due_date');
    }

    public function setBuilder(Builder $builder): void
    {
        $this->builder = $builder;
    }

    protected function resolveDateFilter(): void
    {
        if (empty($this->filters['periodFilter'])) {
            throw new \InvalidArgumentException('Filtro de período não informado.');
        }

        $dates = explode(' - ', $this->filters['periodFilter']);

        if (count($dates) !== 2) {
            throw new \InvalidArgumentException('Filtro de período inválido. Use o formato "dd/mm/yyyy - dd/mm/yyyy".');
        }

        try {
            $this->filters['startDate'] = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
            $this->filters['endDate'] = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Data inválida no filtro de período.');
        }
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(string $index, mixed $value): void
    {
        $this->filters[$index] = $value;
    }

}

