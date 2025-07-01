<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use App\Services\TransactionItemService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class PerCardChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;

    protected static ?string $heading = 'Por cartão';

    protected int | string | array $columnSpan = [
        'default' => 8,
        'md' => 4
    ];

    protected static ?string $maxHeight = '400px';

//    protected static ?int $sort = 2;

//    public function getColumns(): int | string | array
//    {
//        return [
//            'sm' => 12,
//            'md' => 12,
//            'xl' => 12,
//        ];
//    }

    protected function getData(): array
    {
        $status = $this->filters['status'] ?? null;

        $service =  new TransactionItemService($this->filters);

        $query = $service->items()
            ->whereHas('transaction', function ($query) {
                $query->whereNotNull('card_id');
            })
            ->get();

        $grouped = $query->groupBy(fn ($item) => $item->card?->name ?? 'Outro');

        $labels = [];
        $data = [];

        foreach ($grouped as $categoryName => $items) {
            $labels[] = $categoryName;
            $data[] = $items->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Por cartão',
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
