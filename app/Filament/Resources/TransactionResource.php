<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Helpers\Filament\ActionHelper;
use App\Models\Account;
use App\Models\Card;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
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
                    ->limit(20),
                TextColumn::make('category.name')
                    ->label(__('forms.columns.category')),
                TextColumn::make('amount')
                    ->label(__('forms.columns.amount'))
                    ->sortable()
                    ->currency('BRL'),
                TextColumn::make('date')
                    ->label(__('forms.columns.date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('recurrence_interval')
                    ->label(__('forms.forms.recurrence_interval'))
                    ->formatStateUsing(fn (string $state) => $state > 1 ? $state : __('forms.texts.at_sight'))
                    ->alignCenter(),
                TextColumn::make('method')
                    ->label(__('forms.columns.method'))
                    ->getStateUsing(fn ($record) => __('forms.enums.method.' . strtolower($record->method))),
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
                    ->label(__('system.labels.method'))
                    ->options([
                        'CARD' => __('forms.enums.method.card'),
                        'ACCOUNT' => __('forms.enums.method.account'),
                        'CASH' => __('forms.enums.method.cash'),
                    ]),
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
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->prefix('R$')
                            ->disabled(function ($get, $record) {
                                return $record?->items()->where('status', 'PAID')->exists();
                            })
                            ->hint(function ($get, $record) {
                                return $record?->items()->where('status', 'PAID')->exists()
                                    ? 'Este campo está bloqueado porque há parcelas já pagas.'
                                    : null;
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
                        $data['amount'] = (float) str_replace(['.', ','], ['', '.'], $data['amount']);
                        return $record->update($data);
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
                        TextInput::make('amount')
                            ->required()
                            ->label(__('forms.forms.amount'))
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->prefix('R$')
                            ->reactive()
                            ->hint(function ($get) {
                                $amount = str_replace(['.', ','], ['', '.'], $get('amount'));
                                $installments = (int) $get('recurrence_interval');

                                if ($amount && $installments > 0) {
                                    $value = floatval($amount) / $installments;
                                    return 'Valor por parcela: R$ ' . number_format($value, 2, ',', '.');
                                }

                                return null;
                            }),
                        Toggle::make('is_recurring')
                            ->label(__('forms.forms.is_recurring'))
                            ->default(false)
                            ->inline(false)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('recurrence_interval', $state ? 1 : null)),
                        DatePicker::make('date')
                            ->required()
                            ->label(__('forms.forms.date')),
                        Textarea::make('description')
                            ->required()
                            ->label(__('forms.forms.description'))
                            ->maxLength(100),
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
                        Forms\Components\Hidden::make('user_id')->default(auth()->id()),
                    ],
                    modalHeading: __('forms.modal_headings.create_transaction'),
                    label: __('forms.buttons.create'),
                    action: function (array $data, Action $action) {
                        $transaction = Transaction::create($data);

                        $parcelas = !empty($data['is_recurring']) ? (int) ($data['recurrence_interval'] ?? 1) : 1;

                        $amount = (float) str_replace(['.', ','], ['', '.'], $data['amount']);
                        $baseValue = floor($amount / $parcelas * 100) / 100; // força 2 casas
                        $remaining = $amount - ($baseValue * $parcelas);

                        $date = Carbon::parse($data['date']);

                        $cardDueDay = null;
                        if ($data['method'] === 'CARD' && !empty($data['card_id'])) {
                            $card = \App\Models\Card::find($data['card_id']);
                            if ($card && $card->due_date) {
                                $cardDueDay = (int) $card->due_date;
                            }
                        }

                        for ($i = 0; $i < $parcelas; $i++) {
                            $parcela = $i + 1;
                            $currentAmount = $parcela == $parcelas ? $baseValue + $remaining : $baseValue;
                            $paymentDate = (clone $date)->addMonths($i);

                            if ($cardDueDay) {
                                $paymentDate->day = min($cardDueDay, $paymentDate->daysInMonth);
                            }

                            TransactionItem::create([
                                'transaction_id' => $transaction->id,
                                'due_date' => $paymentDate,
                                'amount' => $currentAmount,
                                'installment_number' => $parcela,
                                'status' => in_array($data['method'], ['CARD', 'ACCOUNT']) ? 'DEBIT' : 'PENDING' ,
                            ]);
                        }

                        Notification::make()
                            ->title(__('system.notifications.transaction_created_title'))
                            ->body(trans_choice(__('system.notifications.transaction_created_body'), $parcelas, ['count' => $parcelas]))
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

