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
                ->label(__('forms.widgets.description'))
                ->limit(20),

            Tables\Columns\TextColumn::make('due_date')
                ->label(__('forms.widgets.due_date'))
                ->date('d/m/Y'),

            Tables\Columns\TextColumn::make('amount')
                ->label(__('forms.widgets.amount'))
                ->money('BRL'),

            Tables\Columns\TextColumn::make('toOverdue')
                ->label(__('forms.widgets.to_overdue'))
                ->getStateUsing(function ($record) {
                    $dueDate = Carbon::parse($record->due_date);
                    $today = Carbon::now();
                    $diff = intval($today->diffInDays($dueDate));

                    if ($diff === 0) {
                        return __('forms.widgets.expires_today');
                    } elseif ($diff > 0) {
                        return __('forms.widgets.expires_on') .' ' . $diff . ' '.__('forms.widgets.day')  . ($diff > 1 ? 's' : '');
                    } else {
                        return __('forms.widgets.expires_ago') . ' ' . abs($diff) . ' '.__('forms.widgets.day')  . (abs($diff) > 1 ? 's' : '');
                    }
                })
                ->color(function ($record) {
                    $dueDate = Carbon::parse($record->due_date)->startOfDay();
                    $today = Carbon::now()->startOfDay();
                    $diff = intval($today->diffInDays($dueDate));

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
                            'PAID' => __('forms.widgets.paid') ,
                            'SCHEDULED' => __('forms.widgets.scheduled') ,
                            'DEBIT' => __('forms.widgets.debit') ,
                            'PENDING' => __('forms.widgets.pending'),
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
        return __('forms.widgets.accounts_payable');
    }
}
