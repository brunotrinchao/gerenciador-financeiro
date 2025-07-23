<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Helpers\Filament\ActionHelper;
use App\Helpers\Filament\MaskHelper;
use App\Models\Account;
use App\Models\Card;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\TransactionItemService;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    public static function getNavigationGroup(): ?string
    {
        return __('system.labels.finance');
    }

    public static function getModelLabel(): string
    {
        return __('system.labels.transaction');
    }

    public static function getNavigationLabel(): string
    {
        return __('system.labels.transactions');
    }

    public static function getPluralLabel(): ?string
    {
        return __('system.labels.transactions');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(__('forms.columns.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'INCOME' => 'success',
                        'EXPENSE' => 'danger',
                    })
                    ->sortable()
                    ->formatStateUsing(fn (string $state) => __('forms.enums.transaction_type.' . $state)),
                TextColumn::make('description')
                    ->label(__('forms.columns.description'))
                    ->limit(20)
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('forms.columns.category'))
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label(__('forms.columns.amount'))
                    ->sortable()
                    ->currency('BRL')
                    ->toggleable(),
                TextColumn::make('paid_amount')
                    ->label('Pago')
                    ->getStateUsing(function ($record) {
                        $amount = $record->items()
                            ->where('status', 'PAID')
                            ->sum('amount');

                        $value = (int) preg_replace('/[^0-9,]/', '', $amount);

                        $valuePerInstallment = round(($value / 100), 2);

                        return  $valuePerInstallment;
                    })
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('installment_value')
                    ->label('Valor da Parcela')
                    ->getStateUsing(function ($record) {
                        if ($record->recurrence_interval > 0) {
                            return round($record->amount / $record->recurrence_interval) / 100;
                        }
                        return null;
                    })
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('date')
                    ->label(__('forms.columns.date'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('recurrence_interval')
                    ->label(__('forms.forms.recurrence_interval'))
                    ->getStateUsing(function ($record) {
                        $recurrenceinterval = $record->items()
                            ->where('status', 'PAID')->count();
                        return $record->recurrence_interval > 1 ? $recurrenceinterval . '/' . $record->recurrence_interval :  __('system.texts.at_sight');
                    })
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('method')
                    ->label(__('forms.columns.method'))
                    ->getStateUsing(fn ($record) => __('forms.enums.method.' . strtolower($record->method)))
                    ->toggleable(),
            ])
            ->filters([
                DateRangeFilter::make('items_due_date')
                    ->label(__('forms.filters.period'))
                    ->startDate(Carbon::now()->startOfMonth())
                    ->endDate(Carbon::now()->endOfMonth())
                    ->withIndicator()
                    ->useRangeLabels()
                    ->autoApply()
                    ->modifyQueryUsing(function (Builder $query, ?Carbon $startDate, ?Carbon $endDate, $dateString) {
                        if (!empty($dateString) && $startDate && $endDate) {
                            $start = $startDate->copy()->subDays(3);

                            return $query->whereHas('items', function (Builder $q) use ($start, $endDate) {
                                $q->whereBetween('due_date', [$start, $endDate]);
                            });
                        }

                        return $query;
                    }),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('system.labels.category'))
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('method')
                    ->label(__('forms.forms.method'))
                    ->options([
                        'CARD' => __('forms.enums.method.card'),
                        'ACCOUNT' => __('forms.enums.method.account'),
                        'CASH' => __('forms.enums.method.cash'),
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('forms.forms.status'))
                    ->options([
                        'PENDING' => __('forms.enums.status.pending'),
                        'PAID' => __('forms.enums.status.paid'),
                        'SCHEDULED' => __('forms.enums.status.scheduled'),
                        'DEBIT' => __('forms.enums.status.debit'),
                    ])
                ->modifyQueryUsing(function (Builder $query, array $data) {
                    if ($data['value']) {
                        $query->whereHas('items', function (Builder $q) use ($data) {
                            $q->where('status', $data['value']);
                        });
                    }
                })

            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editTransaction',
                    form: [
                        Radio::make('type')
                            ->label(__('forms.forms.type'))
                            ->options([
                                'INCOME' => __('forms.enums.transaction_type.INCOME'),
                                'EXPENSE' => __('forms.enums.transaction_type.EXPENSE'),
                            ])
                            ->inline()
                            ->required()
                            ->inlineLabel(false),
                        Select::make('category_id')
                            ->required()
                            ->label(__('forms.forms.category'))
                            ->relationship('category', 'name'),
                        Select::make('method')
                            ->label(__('forms.forms.method'))
                            ->options([
                                'CARD' => __('forms.enums.method.card'),
                                'ACCOUNT' => __('forms.enums.method.account'),
                                'CASH' => __('forms.enums.method.cash'),
                            ])
                            ->reactive()
                            ->required()
                            ->disabled(function ($get, $record) {
                                return $record?->items()->where('status', 'PAID')->exists();
                            })
                            ->hint(function ($get, $record) {
                                return $record?->items()->where('status', 'PAID')->exists()
                                    ? 'Este campo está bloqueado porque há parcelas já pagas.'
                                    : null;
                            }),
                        Select::make('card_id')
                            ->label(__('forms.forms.card'))
                            ->options(fn () => Card::all()->pluck('name', 'id'))
                            ->visible(fn ($get) => $get('method') === 'CARD')
                            ->required(fn ($get) => $get('method') === 'CARD')
                            ->disabled(function ($get, $record) {
                                return $get('method') === 'CARD' && $record?->items()->where('status', 'PAID')->exists();
                            })
                            ->hint(function ($get, $record) {
                                return $get('method') === 'CARD' && $record?->items()->where('status', 'PAID')->exists()
                                    ? 'Este campo está bloqueado porque há parcelas já pagas.'
                                    : null;
                            }),
                        Select::make('account_id')
                            ->label(__('forms.forms.account'))
                            ->options(fn () => Account::with('bank')->get()->mapWithKeys(fn ($account) => [$account->id => $account->bank->name ?? 'Sem banco']))
                            ->visible(fn ($get) => $get('method') === 'ACCOUNT')
                            ->required(fn ($get) => $get('method') === 'ACCOUNT'),
                        TextInput::make('amount')
                            ->required()
                            ->label(__('forms.forms.amount'))
                            ->mask(MaskHelper::maskMoney())
                            ->stripCharacters(',')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled(function ($get, $record) {
//                                return $record?->items()->where('status', 'PAID')->exists();
                            })
                            ->hint(function ($get, $record) {
                                $amount = (int) $get('amount');

                                $installments = (int) $get('recurrence_interval');

                                if ($record?->items()->where('status', 'PAID')->exists()) {
                                    return 'Este campo está bloqueado porque há parcelas já pagas.';
                                }

                                if ($amount > 0 && $installments > 0) {
                                    $valuePerInstallment = round(($amount / 100) / $installments, 2);

                                    return 'Valor por parcela: R$ ' . number_format($valuePerInstallment, 2, ',', '.');
                                }

                                return null;
                            }),
                        DatePicker::make('date')
                            ->required()
                            ->label(__('forms.forms.date')),
                        Textarea::make('description')
                            ->required()
                            ->label(__('forms.forms.description'))
                            ->maxLength(100),
                        Toggle::make('is_recurring')
                            ->label(__('forms.forms.is_recurring'))
                            ->default(false)
                            ->inline(false)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('recurrence_interval', $state ? 1 : null))
                            ->disabled(function ($get, $record) {
                                return $get('is_recurring') && $record?->items()->where('status', 'PAID')->exists();
                            })
                            ->hint(function ($get, $record) {
                                return $get('is_recurring') && $record?->items()->where('status', 'PAID')->exists()
                                    ? 'Este campo está bloqueado porque há parcelas já pagas.'
                                    : null;
                            }),
                        TextInput::make('recurrence_interval')
                            ->required(fn ($get) => $get('is_recurring'))
                            ->label(__('forms.forms.recurrence_interval'))
                            ->hidden(fn ($get) => !$get('is_recurring'))
                            ->numeric()
                            ->minValue(fn ($get) => $get('is_recurring') ? 2 : null)
                            ->disabled(function ($get, $record) {
                                return $get('is_recurring') && $record?->items()->where('status', 'PAID')->exists();
                            })
                            ->hint(function ($get, $record) {
                                return $get('is_recurring') && $record?->items()->where('status', 'PAID')->exists()
                                    ? 'Este campo está bloqueado porque há parcelas já pagas.'
                                    : null;
                            }),
                        Select::make('recurrence_type')
                            ->label(__('forms.forms.recurrence_type'))
                            ->options([
                                'DAILY' => __('forms.enums.recurrence_type.DAILY'),
                                'WEEKLY' => __('forms.enums.recurrence_type.WEEKLY'),
                                'MONTHLY' => __('forms.enums.recurrence_type.MONTHLY'),
                                'YEARLY' => __('forms.enums.recurrence_type.YEARLY'),
                            ])
                            ->hidden(fn ($get) => !$get('is_recurring'))
                            ->disabled(function ($get, $record) {
                                return $get('is_recurring') && $record?->items()->where('status', 'PAID')->exists();
                            })
                            ->hint(function ($get, $record) {
                                return $get('is_recurring') && $record?->items()->where('status', 'PAID')->exists()
                                    ? 'Este campo está bloqueado porque há parcelas já pagas.'
                                    : null;
                            }),
                        Forms\Components\Hidden::make('user_id')->default(auth()->id()),
                    ],
                    modalHeading: __('forms.modal_headings.edit_transaction'),
                    label: __('forms.buttons.edit'),
                    fillForm: fn ($record) => [
                        'type'                => $record->type,
                        'category_id'         => $record->category_id,
                        'method'              => $record->method,
                        'card_id'             => $record->card_id,
                        'account_id'          => $record->account_id,
                        'amount'              => $record->amount,
                        'date'                => $record->date,
                        'description'         => $record->description,
                        'is_recurring'        => $record->is_recurring,
                        'recurrence_interval' => $record->recurrence_interval,
                        'recurrence_type'     => $record->recurrence_type,
                        'user_id'             => $record->user_id,
                    ],
                    action: function (array $data, $record) {
                        if(isset($data['amount']) && !empty($data['amount'])) {
                            $data['amount'] = (int) preg_replace('/[^0-9,]/', '', $data['amount']);
                        }

                        $record->update($data);

                        $service = new TransactionItemService();
                        $service->update($record, false);

                        return true;
                    }
                ),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
            ])
//            ->recordUrl(null)
            ->recordAction('editTransaction')
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createTransaction',
                    form: [
                        Grid::make(3)->schema(
                            [
                                Radio::make('type')
                                    ->label(__('forms.forms.type'))
                                    ->options([
                                        'INCOME' => __('forms.enums.transaction_type.INCOME'),
                                        'EXPENSE' => __('forms.enums.transaction_type.EXPENSE'),
                                    ])
                                    ->inline()
                                    ->required()
                                    ->inlineLabel(false),
                                Select::make('category_id')
                                    ->required()
                                    ->label(__('forms.forms.category'))
                                    ->relationship('category', 'name'),
                                Select::make('method')
                                    ->label(__('forms.forms.method'))
                                    ->options([
                                        'CARD' => __('forms.enums.method.card'),
                                        'ACCOUNT' => __('forms.enums.method.account'),
                                        'CASH' => __('forms.enums.method.cash'),
                                    ])
                                    ->reactive()
                                    ->required(),
                            ]
                        ),
                        Grid::make(3)->schema([

                            Select::make('card_id')
                                ->label(__('forms.forms.card'))
                                ->options(fn () => Card::all()->pluck('name', 'id'))
                                ->visible(fn ($get) => $get('method') === 'CARD')
                                ->required(fn ($get) => $get('method') === 'CARD'),
                            Select::make('account_id')
                                ->label(__('forms.forms.account'))
                                ->options(fn () => Account::with('bank')->get()->mapWithKeys(fn ($account) => [$account->id => $account->bank->name ?? 'Sem banco']))
                                ->visible(fn ($get) => $get('method') === 'ACCOUNT')
                                ->required(fn ($get) => $get('method') === 'ACCOUNT'),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('description')
                                ->required()
                                ->label(__('forms.forms.description')),
                            TextInput::make('amount')
                                ->required()
                                ->label(__('forms.forms.amount'))
                                ->mask(MaskHelper::maskMoney())
                                ->stripCharacters(',')
                                ->numeric()
                                ->prefix('R$')
                                ->reactive()
                                ->hint(function ($get) {
                                    $installments = (int) $get('recurrence_interval');

                                    $amount = (float) str_replace(['.', ','], ['', '.'], $get('amount'));

                                    if ($amount && $installments > 0) {
                                        $value = round($amount / $installments, 2); // preserva 2 casas decimais corretamente
                                        return 'Valor por parcela: R$ ' . number_format($value, 2, ',', '.');
                                    }

                                    return null;
                                }),
                            DatePicker::make('date')
                                ->required()
                                ->label(__('forms.forms.date')),
                            Toggle::make('is_recurring')
                                ->label(__('forms.forms.is_recurring'))
                                ->default(false)
                                ->inline(false)
                                ->reactive()
                                ->afterStateUpdated(fn ($state, callable $set) => $set('recurrence_interval', $state ? 1 : null)),
                            TextInput::make('recurrence_interval')
                                ->label(__('forms.forms.recurrence_interval'))
                                ->hidden(fn ($get) => !$get('is_recurring'))
                                ->minValue(fn ($get) => $get('is_recurring') ? 2 : null)
                                ->numeric()
                                ->reactive(),
                            Select::make('recurrence_type')
                                ->label(__('forms.forms.recurrence_type'))
                                ->options([
                                    'DAILY' => __('forms.enums.recurrence_type.DAILY'),
                                    'WEEKLY' => __('forms.enums.recurrence_type.WEEKLY'),
                                    'MONTHLY' => __('forms.enums.recurrence_type.MONTHLY'),
                                    'YEARLY' => __('forms.enums.recurrence_type.YEARLY'),
                                ])
                                ->hidden(fn ($get) => !$get('is_recurring')),
                            TextInput::make('paid_interval')
                                ->label('Parcelas já pagas')
                                ->visible(fn ($get) => $get('is_recurring') && (int) $get('recurrence_interval') >= 1)
                                ->minValue(fn ($get) => $get('is_recurring') && (int) $get('recurrence_interval') >= 1 ? 0 : null)
                                ->numeric()
                                ->default(0)
                                ->reactive(),
                            Forms\Components\Hidden::make('user_id')->default(auth()->id()),
                          ])
                    ],
                    modalHeading: __('forms.modal_headings.create_transaction'),
                    label: __('forms.buttons.create'),
                    action: function (array $data, Action $action) {

                        $data['amount'] = preg_replace('/[^0-9,]/', '', $data['amount']);

                        $transaction = Transaction::create($data);

                        $installmentsCount = !empty($data['is_recurring']) ? (int) ($data['recurrence_interval'] ?? 1) : 1;

                        $amount = (int) str_replace(['.', ','], ['', '.'], $data['amount']);


                        $baseValue = intdiv($amount, $installmentsCount);
                        $remaining = $amount - ($baseValue * $installmentsCount);

                        $date = Carbon::parse($data['date']);

                        $cardDueDay = null;
                        if ($data['method'] === 'CARD' && !empty($data['card_id'])) {
                            $card = \App\Models\Card::find($data['card_id']);
                            if ($card && $card->due_date) {
                                $cardDueDay = (int) $card->due_date;
                            }
                        }

                        for ($i = 0; $i < $installmentsCount; $i++) {
                            $currentAmount = $i === $installmentsCount - 1 ? $baseValue + $remaining : $baseValue;
                            $paymentDate = (clone $date)->addMonths($i);

                            if ($cardDueDay) {
                                $paymentDate->day = min($cardDueDay, $paymentDate->daysInMonth);
                            }
                            $paidInterval = $data['paid_interval'] ?? 0;
                            $paidIntervalItem = $paidInterval > 0 && $i + 1 <= $paidInterval;
                            $status = $paidIntervalItem ? 'PAID' : (in_array($data['method'], ['CARD', 'ACCOUNT']) ? 'DEBIT' : 'PENDING');
                            TransactionItem::create([
                                'transaction_id' => $transaction->id,
                                'due_date' => $paymentDate,
                                'payment_date' => $paidIntervalItem ? $paymentDate : null,
                                'amount' => $currentAmount,
                                'installment_number' => $i + 1,
                                'status' =>  $status,
                            ]);
                        }

                        Notification::make()
                            ->title(__('forms.notifications.transaction_created_title'))
                            ->body(trans_choice(__('forms.notifications.transaction_created_body'), $installmentsCount, ['count' => $installmentsCount]))
                            ->success()
                            ->send();
                    }
                ),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'account.bank',
                'card',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}

