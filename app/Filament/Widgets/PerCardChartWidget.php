<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use App\Services\TransactionItemFilterService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Contracts\Support\Htmlable;

class PerCardChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;

    public function getHeading(): string|Htmlable|null
    {
        return __('forms.widgets.per_card');
    }

    protected int | string | array $columnSpan = [
        'default' => 8,
        'md' => 4
    ];

    protected static ?string $maxHeight = '400px';

    protected function getData(): array
    {
        $status = $this->filters['status'] ?? null;

        $service =  new TransactionItemFilterService($this->filters);

        $query = $service->items()
            ->whereHas('transaction', function ($query) {
                $query->whereNotNull('card_id');
            })
            ->get();

        $grouped = $query->groupBy(fn ($item) => $item->card?->name ?? __('forms.widgets.others'));

        $labels = [];
        $data = [];

        foreach ($grouped as $categoryName => $items) {
            $labels[] = $categoryName;
            $data[] = $items->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => __('forms.widgets.per_card'),
                    'data'  => $data,
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)'
                    ],
                    'hoverOffset' =>  4
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
