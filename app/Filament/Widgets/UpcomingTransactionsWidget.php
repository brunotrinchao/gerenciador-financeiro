<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use App\Services\TransactionItemService;
use Carbon\Carbon;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class UpcomingTransactionsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;
//    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 8;

    protected function getTableQuery(): Builder|Relation|null
    {
        $query =  new TransactionItemService($this->filters);
        return $query->upcomingTransaction();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('transaction.description')
                ->label('Descrição')
                ->limit(20),

            Tables\Columns\TextColumn::make('due_date')
                ->label('Vencimento')
                ->date('d/m/Y'),

            Tables\Columns\TextColumn::make('amount')
                ->label('Valor')
                ->money('BRL'),

            Tables\Columns\TextColumn::make('toOverdue')
                ->label('Dias para vencer')
                ->getStateUsing(function ($record) {
                    $paymentDate = Carbon::parse($record->payment_date);
                    $today = Carbon::now();
                    $diff = intval($today->diffInDays($paymentDate));

                    if ($diff === 0) {
                        return 'Vence hoje';
                    } elseif ($diff > 0) {
                        return 'Vence em ' . $diff . ' dia' . ($diff > 1 ? 's' : '');
                    } else {
                        return 'Venceu há ' . abs($diff) . ' dia' . (abs($diff) > 1 ? 's' : '');
                    }
                })
                ->color(function ($record) {
                    $paymentDate = Carbon::parse($record->payment_date)->startOfDay();
                    $today = Carbon::now()->startOfDay();
                    $diff = intval($today->diffInDays($paymentDate));

                    if ($diff === 0) {
                        return 'warning'; // Hoje
                    } elseif ($diff > 0) {
                        return 'success'; // Futuro
                    } else {
                        return 'danger';  // Passado
                    }
                }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')->formatStateUsing(function (string $state) {
                        return match ($state) {
                            'PAID' => 'Pago',
                            'SCHEDULED' => 'Agendado',
                            'DEBIT' => 'Débito automático',
                            'PENDING' => 'Pendente',
                        };
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PAID' => 'success',
                        'SCHEDULED' => 'warning',
                        'DEBIT' => 'info',
                        'PENDING' => 'gray',
                    })
        ];
    }

    protected function getTableHeading(): string
    {
        return 'Contas a pagar';
    }
}
