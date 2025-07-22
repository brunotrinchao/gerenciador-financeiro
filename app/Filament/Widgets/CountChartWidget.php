<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\TransactionItemFilterService;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class CountChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    public array $tableColumnSearches = [];

    protected static bool $isLazy = true;

    public function getHeading(): string|Htmlable|null
    {
        return __('forms.widgets.per_month');
    }

//    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 8;

    protected static ?string $maxHeight = '400px';



    protected function getData(): array
    {
        $query = TransactionItem::query();
        $query->selectRaw("DATE_FORMAT(due_date, '%Y-%m') as month, SUM(amount) as total");

        $service =  new TransactionItemFilterService($this->filters);
        $filters = $service->getFilters();

        $service->setFilters('startDate', Carbon::parse($filters['startDate'])->startOfYear()->toDateString());
        $service->setFilters('endDate', Carbon::parse($filters['endDate'])->startOfYear()->toDateString());

        $service->setBulder($query);


        $items = $service->items()
            ->groupBy(DB::raw("DATE_FORMAT(due_date, '%Y-%m')"))
            ->orderBy('month')
            ->get();

        $labels = [];
        $data = [];
        $backgroundColors = [];
        $borderColors = [];
        $currentMonth = Carbon::now()->format('Y-m');

        foreach ($items as $item) {
            $month = $item->month;
            $labels[] = Carbon::createFromFormat('Y-m', $month)->translatedFormat('F');
            $data[] = $item->total / 100;

            // Define cor especial para o mês atual
            $borderColors[] = $month === $currentMonth ? '#60a5fa' : '#cbd5e1';
            $backgroundColors[] = $month === $currentMonth ? 'rgb(96,165,250, 0.5)' : 'rgb(204,204,204,0.5)';
        }

//        $labels = $items->pluck('month')->map(fn ($month) => Carbon::createFromFormat('Y-m', $month)->translatedFormat('F'));
//        $data = $items->pluck('total');

        return [
            'datasets' => [
                [
                    'label' => __('forms.widgets.amount'),
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'tension' => 0.6
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

//    protected static ?array $options = [
//        'plugins' => [
//            'scales' => {
//                'y' => {
//                    'ticks' => {
//                        'callback' => function($value, $index, $ticks) {
//                            return '$' . $value.toLocaleString();
//                        }
//                    }
//                }
//            }
//        ],
//    ];

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
        {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL',
                                minimumFractionDigits: 2,
                            }).format(value);
                        }
                    }
                }
            },
            plugins: {
               tooltip: {
                   callbacks: {
                       label: function(context) {
                           let label = context.dataset.label || '';
                           if (label) {
                               label += ': ';
                           }
                           if (context.parsed.y !== null) {
                               label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 2, }).format(context.parsed.y);
                           }
                           return label;
                       }
                   }
               }
           }
        }
    JS);
    }

}
