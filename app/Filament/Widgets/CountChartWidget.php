<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\TransactionItemService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\DB;

class CountChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;

    protected static ?string $heading = 'Por mês';

//    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 8;

    protected static ?string $maxHeight = '400px';



    protected function getData(): array
    {

        $status = $this->filters['status'] ?? null;

        $query = TransactionItem::query();
        $query->selectRaw("DATE_FORMAT(due_date, '%Y-%m') as month, SUM(amount) as total");

        $filter = $this->filters;

        $filter['startDate'] = Carbon::parse($filter['startDate'])->startOfYear()->toDateString();
        $filter['endDate'] = Carbon::parse($filter['endDate'])->endOfMonth()->toDateString();
        $filter['status'] = $status;

        $service =  new TransactionItemService($filter, $query);

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
            $data[] = $item->total;

            // Define cor especial para o mês atual
            $borderColors[] = $month === $currentMonth ? '#60a5fa' : '#cbd5e1';
            $backgroundColors[] = $month === $currentMonth ? 'rgb(96,165,250, 0.5)' : 'rgb(204,204,204,0.5)';
        }

//        $labels = $items->pluck('month')->map(fn ($month) => Carbon::createFromFormat('Y-m', $month)->translatedFormat('F'));
//        $data = $items->pluck('total');

        return [
            'datasets' => [
                [
                    'label' => 'Valor',
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
}
