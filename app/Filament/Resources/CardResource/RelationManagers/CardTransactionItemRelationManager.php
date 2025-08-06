<?php

namespace App\Filament\Resources\CardResource\RelationManagers;

use App\Filament\Resources\TransactionResource;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CardTransactionItemRelationManager extends RelationManager
{
    protected static string $relationship = 'transactionItems';

    protected static ?string $title = 'Parcelas do Cartão';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction.description')
                    ->label('Descrição'),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->currency('BRL')
                    ->summarize(Sum::make()->label('Total')->currency('BRL')),
                TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y'),
                TextColumn::make('installment_number')
                    ->label('Parcela')
                    ->getStateUsing(function ($record) {
                        return "{$record->installment_number}/{$record->transaction->recurrence_interval}";
                })
                ->alignCenter(),
            ])
            ->filters([
                Filter::make('fatura')
                    ->label('Filtrar por fatura (mês/ano)')
                    ->form([
                        Select::make('year')
                            ->label('Ano')
                            ->options(
                                fn () => array_combine(
                                    range(date('Y') - 5, 2030),
                                    range(date('Y') - 5, 2030)
                                )
                            )
                            ->default(date('Y')),

                        Select::make('month')
                            ->label('Mês')
                            ->options([
                                1 => 'Janeiro',
                                2 => 'Fevereiro',
                                3 => 'Março',
                                4 => 'Abril',
                                5 => 'Maio',
                                6 => 'Junho',
                                7 => 'Julho',
                                8 => 'Agosto',
                                9 => 'Setembro',
                                10 => 'Outubro',
                                11 => 'Novembro',
                                12 => 'Dezembro',
                            ])
                            ->default((int) date('m')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $year = $data['year'] ?? date('Y');
                        $month = $data['month'] ?? date('m');

                        $start = Carbon::create($year, $month, 1)->startOfMonth();
                        $end = Carbon::create($year, $month, 1)->endOfMonth();

                        return $query->whereBetween('due_date', [$start, $end]);
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['month'] && ! $data['year']) return null;
                        return 'Fatura de ' . ucfirst(Carbon::create($data['year'], $data['month'], 1)->locale('pt_BR')->isoFormat('MMMM')) . ' de ' . $data['year'];
                    }),
            ])
            ->recordUrl(
                fn ($record) => TransactionResource::getUrl('edit', ['record' => $record->transaction_id])
            );
    }
}
