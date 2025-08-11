<?php

namespace App\Filament\Resources\CardResource\RelationManagers;

use App\Filament\Resources\TransactionResource;
use App\Helpers\Filament\ActionHelper;
use App\Helpers\Filament\MaskHelper;
use App\Services\TransactionItemService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class CardTransactionItemRelationManager extends RelationManager
{
    protected static string $relationship = 'transactionItems';

    protected static ?string $title = 'Parcelas do Cartão';


    public function table(Table $table): Table
    {
        return $table
            ->pluralModelLabel('itens')
            ->columns([
                TextColumn::make('transaction.description')
                    ->label('Descrição'),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->currency('BRL')
                    ->summarize(Sum::make()
                        ->label('Total')
                        ->currency('BRL')
                    ),
                TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y'),
                TextColumn::make('installment_number')
                    ->label('Parcela')
                    ->getStateUsing(function ($record) {
                        return "{$record->installment_number}/{$record->transaction->recurrence_interval}";
                })
                ->alignCenter(),
                TextColumn::make('status')
                    ->default('Status')
                    ->formatStateUsing(function (string $state) {
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
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editTransactionItem',
                    form: [
                        TextInput::make('description')
                            ->label('Nome')
                        ->disabled(),
                        TextInput::make('amount')
                            ->label('Valor')
                            ->mask(MaskHelper::maskMoney())
                            ->stripCharacters(',')
                            ->numeric()
                            ->required(),
                        DatePicker::make('due_date')
                            ->label('Data de vencimento')
                            ->disabled(function ($get) {
                                return $get('method') == 'CARD';
                            })
                            ->readOnly(function ($get) {
                                return $get('method') == 'CARD';
                            }),
                        DatePicker::make('payment_date')
                            ->label('Data de pagamento')
                            ->required(function ($get) {
                                return $get('status') == 'PAID';
                            }),
                        Select::make('method')
                            ->label('Método')
                            ->options([
                                'CARD' => __('forms.enums.method.card'),
                                'ACCOUNT' => __('forms.enums.method.account'),
                                'CASH' => __('forms.enums.method.cash'),
                            ])
                            ->required(function ($get) {
                                return $get('status') == 'PAID';
                            })
                            ->disabled()
                            ->reactive(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'PENDING' => 'Pendente',
                                'PAID' => 'Pago',
                                'SCHEDULED' => 'Agendado',
                                'DEBIT' => 'Débito automático',
                            ])
                            ->default('PENDING')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($record, $state, callable $set) {
                                if ($state === 'PENDING') {
                                    $set('payment_date', null);
                                }
                                else{
                                    $set('payment_date', $record->payment_date ?? $record->due_date);
                                }
                            }),
                    ],
                    modalHeading: __('forms.modal_headings.edit_transaction_item'),
                    label: __('forms.buttons.edit'),
                    fillForm: function ($record) {
                        return [
                            'description' => $record->transaction->description,
                            'amount' => (int) $record->amount,
                            'due_date' => $record->due_date,
                            'payment_date' =>  $record->payment_date ?? $record->due_date,
                            'method' => $record->transaction->method,
                            'status' => $record->transaction->method == 'CARD' ? 'DEBIT' : $record->status
                        ];
                    },
                    action: function (array $data, $record) {
                        $transaction = $record->transaction;

                        $paidItems = $transaction->items()->where('status', 'PAID')->get();

                        $otherPendingItems = $transaction->items()
                            ->where('id', '!=', $record->id)
                            ->where('status', '!=', 'PAID')
                            ->get();

                        // Novo valor convertido para centavos
                        $newAmount = (int) preg_replace('/[^0-9]/', '', $data['amount']);

                        $somaPagas = $paidItems->sum('amount');
                        $somaPendentes = $otherPendingItems->sum('amount');

                        $valorRestante = $transaction->amount - $somaPagas - $newAmount;

                        $temOutrasPendentes = $otherPendingItems->count() > 0;

                        // Validação principal
                        if (
                            ($temOutrasPendentes && $valorRestante < 0) ||
                            (!$temOutrasPendentes && $valorRestante !== 0)
                        ) {
                            $maxAmount = $transaction->amount - $somaPagas - ($temOutrasPendentes ? 0 : $somaPendentes);

                            Notification::make()
                                ->title('A soma das parcelas excede o valor da transação.')
                                ->danger()
                                ->send();

                            return throw ValidationException::withMessages([
                                'amount' => "O valor da parcela não pode ser maior que o valor restante da transação (R$ " . number_format($maxAmount / 100, 2, ',', '.') . ").",
                            ]);
                        }
                        $data['amount'] = $newAmount;
                        // Atualiza e recalcula
                        $record->update($data);

                        (new TransactionItemService())->recalcAmountTransactionItem($record);
                    },
                    after: function (array $data, $record) {
                        $transactionItemService = new TransactionItemService();
                        $transactionItemService->recalcAmountTransactionItem($record);

                        $this->dispatch('refreshProducts');

                        return true;
                    },
                    visible: false
                )

            ])
            ->bulkActions([
                BulkAction::make('paid_fast')
                    ->label('Marcar como pago')
                    ->icon('heroicon-m-currency-dollar')
                    ->requiresConfirmation()
                    ->color('success')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            if ($record->status !== 'PAID') {
                                $record->update([
                                    'payment_date' => $record->due_date,
                                    'status' => 'PAID',
                                ]);
                            }
                        }

                        Notification::make()
                            ->title('Parcelas marcadas como pagas!')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion()
            ])
            ->recordUrl(null)
            ->recordAction('editTransactionItem')
            ->headerActions([
                Action::make('import')
                    ->label('Importar Transações')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->url(fn ($livewire) => route('filament.admin.resources.cards.import-transactions', ['record' => $livewire->getOwnerRecord()])),
            ]);
    }
}
