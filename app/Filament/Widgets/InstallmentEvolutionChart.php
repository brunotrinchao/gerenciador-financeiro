<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class InstallmentEvolutionChart extends ChartWidget
{
    protected static ?string $heading = 'Evolução das Parcelas';
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    protected function getViewData(): array
    {
        return [
            'data' => $this->getData(),
        ];
    }

    protected function getData(): array
    {
        $now = now();
        $start = $now->copy()->startOfYear();
        $end = $now->copy()->endOfYear();

        $items = \App\Models\TransactionItem::with('transaction.card')
            ->whereHas('transaction', fn ($q) => $q->whereNotNull('card_id') && $q->where('type', 'EXPENSE'))
            ->whereBetween('due_date', [$start, $end])
            ->get()
            ->groupBy(function ($item) {
                $month = \Carbon\Carbon::parse($item->due_date)->format('Y-m');
                $cardId = $item->transaction->card_id;
                return $month . '|' . $cardId;
            });

        $months = collect(range(0, 11))->map(fn ($i) => $start->copy()->addMonths($i)->format('Y-m'));
        $labels = $months->map(fn ($m) => \Carbon\Carbon::parse($m)->locale('pt_BR')->isoFormat('MMM/YY'))->toArray();

        $cardSums = []; // [card_id => [valores por mês]]
        $cardNames = []; // [card_id => 'Nome']

        // Inicializa estrutura
        foreach ($items as $key => $group) {
            [$month, $cardId] = explode('|', $key);
            $transaction = $group->first()->transaction;

            if (!isset($cardSums[$cardId])) {
                $cardSums[$cardId] = array_fill(0, 12, 0);
                $cardNames[$cardId] = $transaction->card->name . ' ('.$transaction->card->bank->name.')' ?? 'Cartão ' . $cardId;
            }

            $index = $months->search($month);
            if ($index !== false) {
                $cardSums[$cardId][$index] += $group->sum('amount');
            }
        }

        $baseColors = [
            [255, 99, 132],
            [54, 162, 235],
            [255, 206, 86],
            [75, 192, 192],
            [153, 102, 255],
            [255, 159, 64],
            [201, 203, 207],
            [0, 128, 128],
            [255, 105, 180],
            [100, 149, 237],
            [255, 215, 0],
            [0, 206, 209],
            [139, 69, 19],
            [255, 69, 0],
            [46, 139, 87],
            [123, 104, 238],
            [72, 209, 204],
            [240, 230, 140],
            [70, 130, 180],
            [199, 21, 133],
        ];

        $fixedColors = array_map(fn($rgb) => [
            'fill' => "rgba({$rgb[0]}, {$rgb[1]}, {$rgb[2]}, 0.2)",
            'border' => "rgb({$rgb[0]}, {$rgb[1]}, {$rgb[2]})"
        ], $baseColors);

        $datasets = [];
        $index = 0;
        foreach ($cardSums as $cardId => $values) {

            $colors = $fixedColors[$index % count($fixedColors)];

            $datasets[] = [
                'label' => $cardNames[$cardId],
                'data' => $values,
                'backgroundColor' => $colors['fill'],
                'borderColor' => $colors['border'],
                'pointBackgroundColor' => $colors['border'],
                'pointBorderColor' => $colors['border'],
                'borderWidth' => 2,
                'tension' => 0.5,
                'pointStyle' => 'circle',
                'pointRadius' => 6,
                'pointHoverRadius' => 9,
            ];
            $index++;
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }



    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): RawJs
    {

        return RawJs::make(<<<JS
        {
             interaction: {
                  intersect: false,
                  mode: 'index',
            },
            scales: {
                 x: {
                    display: true,
                    title: {
                      display: true
                    }
                 },
                y: {
                    display: true,
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL',
                                minimumFractionDigits: 2,
                            }).format(value / 100);
                        }
                    }
                }
            },
            responsive: true,
            plugins: {
               tooltip: {
                   callbacks: {
                       footer: (tooltipItems) => {
                          let sum = 0;

                          tooltipItems.forEach(function(tooltipItem) {
                            sum += tooltipItem.parsed.y / 100;
                          });
                          let total = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 2 }).format(sum);

                          return 'Total: ' + total;
                        },
                       label: function(context) {
                           let label = context.dataset.label || '';
                           if (label) {
                               label += ': ';
                           }
                           if (context.parsed.y !== null) {
                               label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 2, }).format(context.parsed.y / 100);
                           }
                           return label;
                       }
                   }
               },
           }
        }
    JS);
    }

}
