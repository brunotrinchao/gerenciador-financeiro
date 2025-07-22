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
            ->whereHas('transaction', fn ($q) => $q->whereNotNull('card_id'))
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
                $cardNames[$cardId] = $transaction->card->name ?? 'Cartão ' . $cardId;
            }

            $index = $months->search($month);
            if ($index !== false) {
                $cardSums[$cardId][$index] += $group->sum('amount');
            }
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

        $datasets = [];
        foreach ($cardSums as $cardId => $values) {
            $datasets[] = [
                'label' => $cardNames[$cardId],
                'data' => $values,
                'backgroundColor' => $fixedColors,
                'borderColor' => $fixedColors,
                'tension' => 0.6
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }



    protected function getType(): string
    {
        return 'bar';
    }

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
                            }).format(value / 100);
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
                               label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 2, }).format(context.parsed.y / 100);
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
