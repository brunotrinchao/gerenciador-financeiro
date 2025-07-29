<?php

namespace App\Filament\Widgets;

use App\Helpers\Filament\MaskHelper;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\TransactionItemFilterService;
use Carbon\Carbon;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Illuminate\Support\Collection;

class CountWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    use InteractsWithPageTable;

    public array $tableColumnSearches = [];

    protected static bool $isLazy = true;
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = [
        'default' => 12
    ];


//    protected function getColumns(): int
//    {
//        $count = count($this->getCachedStats());
//        return 6;
//    }


    protected function getStats(): array
    {
        $filters = $this->filters;

        $methods = request()->query('method');

        $filters['method'] = $methods;

        if(!$filters){
            $filters = $this->getFilters();
        }

        $service = new TransactionItemFilterService($filters);
        $items = $service->items()
                ->get()
                ->groupBy(fn ($item) => $item?->transaction?->type);

        $expenses = $items->get('EXPENSE', collect());
        $incomes = $items->get('INCOME', collect());

        $filters = $service->getFilters();
        $startDate = $filters['startDate'] ?? null;
        $endDate = $filters['endDate'] ?? null;

        $groupedByStatus = $expenses->groupBy('status');

        $stats = [];
        // Pendente
        $pendingItems = $groupedByStatus['PENDING'] ?? collect();
        $pendingTotal = $pendingItems->sum('amount');
        $pendingTrend = $this->calculateMonthlyTrend($pendingItems, $startDate, $endDate);

        $stats[] = Stat::make(__('forms.widgets.pending'), MaskHelper::covertIntToReal($pendingTotal))
            ->description(__('forms.widgets.total_pending'))
            ->descriptionIcon('heroicon-o-banknotes')
            ->chart($pendingTrend)
            ->color('gray');

        // Pago
        $paidItems = $groupedByStatus['PAID'] ?? collect();
        $paidTotal = $paidItems->sum('amount');
        $paidTrend = $this->calculateMonthlyTrend($paidItems, $startDate, $endDate);

        $stats[] = Stat::make(__('forms.widgets.paid'), MaskHelper::covertIntToReal($paidTotal))
            ->description(__('forms.widgets.total_paid'))
            ->descriptionIcon('heroicon-o-banknotes')
            ->chart($paidTrend)
            ->color('success');

        // Agendado + Débito
        $scheduledItems = $groupedByStatus['SCHEDULED'] ?? collect();
        $debitItems = $groupedByStatus['DEBIT'] ?? collect();
        $mergedItems = $scheduledItems->merge($debitItems);
        $mergedTotal = $mergedItems->sum('amount');
        $mergedTrend = $this->calculateMonthlyTrend($mergedItems, $startDate, $endDate);

        $stats[] = Stat::make(__('forms.widgets.schedule_debit'), MaskHelper::covertIntToReal($mergedTotal))
            ->description(__('forms.widgets.total_schedule_debit'))
            ->descriptionIcon('heroicon-o-banknotes')
            ->chart($mergedTrend)
            ->color('info');

        // Total geral
        $totalGeral = $expenses->sum('amount');
        $totalTrend = $this->calculateMonthlyTrend($expenses, $startDate, $endDate);
//        dd($totalTrend);
        $stats[] = Stat::make(__('forms.widgets.grand_total'), MaskHelper::covertIntToReal($totalGeral))
            ->description(__('forms.widgets.sum_all_transactions_period'))
            ->descriptionIcon('heroicon-o-banknotes')
            ->chart($totalTrend)
            ->color('danger');

        // Receita
        $income = $incomes->sum('amount');
        $incomeTrend = $this->calculateMonthlyTrend($incomes, $startDate, $endDate);
//        dd($totalTrend);
        $stats[] = Stat::make(__('forms.widgets.all_income'), MaskHelper::covertIntToReal($income))
            ->description(__('forms.widgets.sum_all_income_period'))
            ->descriptionIcon('heroicon-o-banknotes')
            ->chart($incomeTrend)
            ->color('purple');


        $calc = $income - $totalGeral;
        $expenseTrend = $this->calculateMonthlyTrend($expenses, $startDate, $endDate);
        $incomeTrend = $this->calculateMonthlyTrend($incomes, $startDate, $endDate);

        $calcTrend = collect($incomeTrend)->zip($expenseTrend)->map(fn($pair) => $pair[0] - $pair[1])->values()->toArray();

        $stats[] = Stat::make(__('forms.widgets.balane_income_expense'), MaskHelper::covertIntToReal($calc))
            ->description(__('forms.widgets.balance_all_income_expense_period'))
            ->descriptionIcon('heroicon-o-banknotes')
            ->chart($calcTrend)
            ->color($calc >= 0 ? 'success' : 'danger');

        return $stats;
    }

    private function calculateMonthlyTrend(Collection $items, ?string $startDate, ?string $endDate): array
    {
        $trend = $items
            ->groupBy(fn ($item) => Carbon::parse($item->payment_date)->format('Y-m'))
            ->map(fn ($items) => $items->sum('amount'));

        $months = collect();
        $start = Carbon::parse($startDate ?? now()->subMonths(6))->startOfMonth();
        $end = Carbon::parse($endDate ?? now())->endOfMonth();
        $cursor = $start->copy();

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m');
            $months->put($key, $trend[$key] ?? 0);
            $cursor->addMonth();
        }

        return $months->values()->toArray();
    }

    private function getFilters(): array
    {
        $status = match ($this->activeTab) {
            'Pendente' => 'PENDING',
            'Pago' => 'PAID',
            'Agendado/Débito' => 'SCHEDULED/DEBIT',
            default => ''
        };

        return [
            'status' => $status,
            'startDate' => $this->tableFilters['startDate'] ?? null,
            'endDate' => $this->tableFilters['endDate'] ?? null,
        ];
    }

}
