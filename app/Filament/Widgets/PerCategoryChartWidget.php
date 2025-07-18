<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use App\Services\TransactionItemFilterService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Illuminate\Contracts\Support\Htmlable;

class PerCategoryChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;

    public function getHeading(): string|Htmlable|null
    {
        return __('forms.widgets.per_category');
    }

    protected int | string | array $columnSpan = [
        'default' => 8,
        'md' => 4
    ];



//    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '400px';

    public function getColumns(): int | string | array
    {
        return 4;
    }

    protected function getData(): array
    {
        $status = $this->filters['status'] ?? null;

        $service =  new TransactionItemFilterService($this->filters);

        $query = $service->items()
            ->with('transaction.category')
            ->get();

        $grouped = $query->groupBy(fn ($item) => $item->transaction?->category?->name ?? 'Sem categoria');
        $labels = [];
        $data = [];

        foreach ($grouped as $categoryName => $items) {
            $labels[] = $categoryName;
            $data[] = $items->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => __('forms.widgets.per_category'),
                    'data'  => $data,
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)'
                    ],
                ]
            ],
            'labels' => $labels
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
