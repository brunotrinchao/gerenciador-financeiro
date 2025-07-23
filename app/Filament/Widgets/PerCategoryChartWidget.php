<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use App\Services\TransactionItemFilterService;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Illuminate\Contracts\Support\Htmlable;

class PerCategoryChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;
    public array $tableColumnSearches = [];

    protected static bool $isLazy = true;

    protected static ?int $sort = 5;

    public function getHeading(): string|Htmlable|null
    {
        return __('forms.widgets.per_category');
    }

    protected int | string | array $columnSpan = [
        'default' => 12,
        'lg' => 4
    ];

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
            ->whereHas('transaction', fn ($q) => $q->where('type', 'EXPENSE'))
            ->with('transaction.category')
            ->get();

        $grouped = $query->groupBy(fn ($item) => $item->transaction?->category?->name ?? 'Sem categoria');
        $labels = [];
        $data = [];

        foreach ($grouped as $categoryName => $items) {
            $labels[] = $categoryName;
            $data[] = $items->sum('amount');
        }

        $fixedColors = [
            'rgb(255, 99, 132)',
            'rgb(54, 162, 235)',
            'rgb(255, 206, 86)',
            'rgb(75, 192, 192)',
            'rgb(153, 102, 255)',
            'rgb(255, 159, 64)',
            'rgb(201, 203, 207)',
            'rgb(0, 128, 128)',
            'rgb(255, 105, 180)',
            'rgb(100, 149, 237)',
            'rgb(255, 215, 0)',
            'rgb(0, 206, 209)',
            'rgb(139, 69, 19)',
            'rgb(255, 69, 0)',
            'rgb(46, 139, 87)',
            'rgb(123, 104, 238)',
            'rgb(72, 209, 204)',
            'rgb(240, 230, 140)',
            'rgb(70, 130, 180)',
            'rgb(199, 21, 133)',
        ];

        return [
            'datasets' => [
                [
                    'label' => __('forms.widgets.per_category'),
                    'data'  => $data,
                    'backgroundColor' => $fixedColors,
                    'hoverOffset' => 4
                ]
            ],
            'labels' => $labels
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
    {
        scales: {
            y: { grid: { display: false }, ticks: { display: false } },
            x: { grid: { display: false }, ticks: { display: false } }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = context.parsed.y ?? context.parsed.x ?? context.parsed;
                        if (value === null || value === undefined) {
                            return label;
                        }
                        const valueFormatted = new Intl.NumberFormat('pt-BR', {
                            style: 'currency',
                            currency: 'BRL',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(value / 100);

                        const data = context.dataset.data;
                        const total = data.reduce((acc, val) => acc + val, 0);
                        const percent = ((value / total) * 100).toFixed(2);

                        return `${label}: ${valueFormatted} (${percent}%)`;
                    }
                }
            }
        }
    }
    JS);
    }


}
