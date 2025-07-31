<?php

namespace App\Filament\Resources;

use App\Enum\TransactionTypeEnum;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Helpers\ColumnFormatter;
use App\Helpers\Filament\ActionHelper;
use App\Helpers\Filament\MaskHelper;
use App\Models\Account;
use App\Models\Card;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\TransactionItemService;
use App\Services\TransactionService;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
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
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Route;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Filament\Tables\Filters\TrashedFilter;

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
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        $livewire = $table->getLivewire();

        return $table
            ->columns(
                $livewire->isGridLayout()
                ? static::getGridTableColumns()
                : static::getListTableColumns()
            )
            ->contentGrid(
                fn () => $livewire->isListLayout()
                    ? null
                    : [
                        'md' => 2,
                        'lg' => 3,
                        'xl' => 4,
                    ]
            )
            ->filters([

                TrashedFilter::make()
                    ->visible(fn () => auth()->user()?->hasRole('ADMIN')),
                Filter::make('filter')
                    ->label(__('forms.columns.filter'))
                    ->form([
                        DateRangePicker::make('items_due_date')
                            ->label(__('forms.filters.period'))
                            ->startDate(Carbon::now()->startOfMonth())
                            ->endDate(Carbon::now()->endOfMonth())
                            ->minYear(2020)
                            ->maxYear(Carbon::now()->addYear(5)->year)
                            ->showDropdowns(),
                        Select::make('category_id')
                            ->label(__('system.labels.category'))
                            ->relationship('category', 'name'),
                        Select::make('method')
                            ->label(__('forms.forms.method'))
                            ->options([
                                'CARD' => __('forms.enums.method.card'),
                                'ACCOUNT' => __('forms.enums.method.account'),
                                'CASH' => __('forms.enums.method.cash'),
                            ]),
                        Select::make('status')
                            ->label(__('forms.forms.status'))
                            ->options([
                                'PENDING' => __('forms.enums.status.pending'),
                                'PAID' => __('forms.enums.status.paid'),
                                'SCHEDULED' => __('forms.enums.status.scheduled'),
                                'DEBIT' => __('forms.enums.status.debit'),
                            ])
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (!empty($data['items_due_date'])) {
                            $indicators[] = Indicator::make('Período: ' . $data['items_due_date'])
                                ->removeField('items_due_date');
                        }

                        if (!empty($data['category_id'])) {
                            $category = Category::find($data['category_id']);
                            $indicators[] = Indicator::make('Categoria: ' . $category->name)
                                ->removeField('category_id');
                        }

                        if (!empty($data['method'])) {
                            $indicators[] = Indicator::make(__('forms.forms.method') . ': ' . __('forms.enums.method.' . strtolower($data['method'])))
                                ->removeField('method');
                        }

                        if (!empty($data['status'])) {
                            $indicators[] = Indicator::make(__('forms.forms.status') . ': ' . __('forms.enums.status.' . strtolower($data['status'])))
                                ->removeField('status');
                        }

                        return $indicators;
                    })
                    ->modifyQueryUsing(function (Builder $query, array $data) {
                        $dates = $data['items_due_date'] ?? null;
                        $status = $data['status'] ?? null;
                        $method = $data['method'] ?? null;
                        $category = $data['category_id'] ?? null;

                        $query->when($method, fn ($q) => $q->where('method', $method));
                        $query->when($category, fn ($q) => $q->where('category_id', $category));

                        if ($dates) {
                            [$start, $end] = explode(' - ', $dates);
                            $startDate = Carbon::createFromFormat('d/m/Y', trim($start))->startOfDay();
                            $endDate = Carbon::createFromFormat('d/m/Y', trim($end))->endOfDay();

                            $query->whereHas('items', function (Builder $q) use ($startDate, $endDate, $status) {
                                $q->whereBetween('due_date', [$startDate, $endDate]);

                                if ($status) {
                                    $q->where('status', $status);
                                }
                            });
                        }
                        return $query;
                    })

            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editTransaction',
                    form: [
                        Grid::make(3)->schema([
                            Select::make('type')
                                ->label(__('forms.forms.type'))
                                ->options([
                                    TransactionTypeEnum::INCOME->name => __('forms.enums.transaction_type.INCOME'),
                                    TransactionTypeEnum::EXPENSE->name => __('forms.enums.transaction_type.EXPENSE'),
                                    TransactionTypeEnum::TRANSFER->name => __('forms.enums.transaction_type.TRANSFER'),
                                ])
                                ->reactive()
                                ->required()
                                ->disabled(function ($get, $record) {
                                    return $record->type === TransactionTypeEnum::TRANSFER->name;
                                }),
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
                                    $items = $record?->items()->where('status', 'PAID');
                                    $s = $items->count() > 1 ? 's' : null;
                                    return $items->exists()
                                        ? "{$items->count()} parcela{$s} já paga{$s}."
                                        : null;
                                }),
                        ]),
                        Select::make('card_id')
                            ->label(__('forms.forms.card'))
                            ->options(fn () => Card::all()->pluck('name', 'id'))
                            ->visible(fn ($get) => $get('method') === 'CARD')
                            ->required(fn ($get) => $get('method') === 'CARD')
                            ->disabled(function ($get, $record) {
                                return $get('method') === 'CARD' && $record?->items()->where('status', 'PAID')->exists();
                            })
                            ->hint(function ($get, $record) {
                                $items = $record?->items()->where('status', 'PAID');
                                $s = $items->count() > 1 ? 's' : null;
                                return $items->exists()
                                    ? "{$items->count()} parcela{$s} já paga{$s}."
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
                                return $record?->items()->where('status', 'PAID')->exists();
                            })
                            ->hint(function ($get, $record) {
                                $amount = (int) $get('amount');

                                $installments = (int) $get('recurrence_interval');
                                $items = $record?->items()->where('status', 'PAID');
                                $s = $items->count() > 1 ? 's' : null;
                                if ($items->exists()) {
                                    return "{$items->count()} parcela{$s} já paga{$s}.";
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
                                return $get('is_recurring') && $record?->items()->where('status', 'PAID')->exists() || $record->type === TransactionTypeEnum::TRANSFER->name;
                            })
                            ->hint(function ($get, $record) {
                                $items = $record?->items()->where('status', 'PAID');
                                $s = $items->count() > 1 ? 's' : null;
                                return $items->exists()
                                    ? "{$items->count()} parcela{$s} já paga{$s}."
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
                                $items = $record?->items()->where('status', 'PAID');
                                $s = $items->count() > 1 ? 's' : null;
                                return $items->exists()
                                    ? "{$items->count()} parcela{$s} já paga{$s}."
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
                                $items = $record?->items()->where('status', 'PAID');
                                $s = $items->count() > 1 ? 's' : null;
                                return $items->exists()
                                    ? "{$items->count()} parcela{$s} já paga{$s}."
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
                        app(TransactionService::class)->update($record, $data);
                    }
                ),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
            ])
            ->checkIfRecordIsSelectableUsing(
                function (Transaction $record) {
                    return !$record?->trashed();
                }
            )
            ->recordUrl(fn (Transaction $record) => $record->type !== TransactionTypeEnum::TRANSFER->name ? route('filament.admin.resources.transactions.edit', $record) : null)
            ->recordAction('editTransaction')
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createTransaction',
                    form: [
                        Grid::make(3)->schema(
                            [
                                Select::make('type')
                                    ->label(__('forms.forms.type'))
                                    ->options([
                                        TransactionTypeEnum::INCOME->name => __('forms.enums.transaction_type.INCOME'),
                                        TransactionTypeEnum::EXPENSE->name => __('forms.enums.transaction_type.EXPENSE'),
                                        TransactionTypeEnum::TRANSFER->name => __('forms.enums.transaction_type.TRANSFER'),
                                    ])
                                    ->reactive()
                                    ->required(),
                                Select::make('method')
                                    ->label(__('forms.forms.method'))
                                    ->options(function (callable $get) {
                                        $type = $get('type');
                                        $accountName = $type !== TransactionTypeEnum::TRANSFER->name ? __('forms.enums.method.account') : __('forms.enums.method.beteewn_account');
                                        $options = [
                                            'ACCOUNT' => $accountName,
                                            'CASH' => __('forms.enums.method.cash'),
                                        ];

                                        if (!in_array($type,[TransactionTypeEnum::TRANSFER->name, TransactionTypeEnum::INCOME->name])) {
                                            $options['CARD'] = __('forms.enums.method.card');
                                        }

                                        return $options;
                                    })
                                    ->reactive()
                                    ->required()
                                    ->disabled(fn ($get) => $get('type') === null || $get('type') === ''),
                                Select::make('category_id')
                                    ->required()
                                    ->label(__('forms.forms.category'))
                                    ->relationship('category', 'name')
                                    ->disabled(fn ($get) => $get('type') === null || $get('type') === '' || $get('method') === null || $get('method') === ''),
                            ]
                        ),
                        Grid::make(3)->schema([
                            // TRANSFERÊNCIA (origem - apenas se ACCOUNT)
                            Select::make('origin_account_id')
                                ->label(__('forms.forms.origin_account'))
                                ->options(fn () => Account::with('bank')->get()
                                    ->mapWithKeys(fn ($account) => [$account->id => $account->bank->name ?? 'Sem banco']))
                                ->visible(fn ($get) =>
                                    $get('type') === TransactionTypeEnum::TRANSFER->name &&
                                    $get('method') === 'ACCOUNT'
                                )
                                ->required(fn ($get) =>
                                    $get('type') === TransactionTypeEnum::TRANSFER->name &&
                                    $get('method') === 'ACCOUNT'
                                ),

                            // TRANSFERÊNCIA (destino - sempre se ACCOUNT ou CASH)
                            Select::make('target_account_id')
                                ->label(__('forms.forms.target_account'))
                                ->options(fn () => Account::with('bank')->get()
                                    ->mapWithKeys(fn ($account) => [$account->id => $account->bank->name ?? 'Sem banco']))
                                ->visible(fn ($get) =>
                                    $get('type') === TransactionTypeEnum::TRANSFER->name &&
                                    in_array($get('method'), ['ACCOUNT', 'CASH'])
                                )
                                ->required(fn ($get) =>
                                    $get('type') === TransactionTypeEnum::TRANSFER->name &&
                                    in_array($get('method'), ['ACCOUNT', 'CASH'])
                                ),

                            // NÃO TRANSFER: Cartão
                            Select::make('card_id')
                                ->label(__('forms.forms.card'))
                                ->options(fn () => Card::all()->pluck('name', 'id'))
                                ->visible(fn ($get) =>
                                    $get('type') !== TransactionTypeEnum::TRANSFER->name &&
                                    $get('method') === 'CARD'
                                )
                                ->required(fn ($get) =>
                                    $get('type') !== TransactionTypeEnum::TRANSFER->name &&
                                    $get('method') === 'CARD'
                                ),

                            // NÃO TRANSFER: Conta
                            Select::make('account_id')
                                ->label(__('forms.forms.account'))
                                ->options(fn () => Account::with('bank')->get()
                                    ->mapWithKeys(fn ($account) => [$account->id => $account->bank->name ?? 'Sem banco']))
                                ->visible(fn ($get) =>
                                    $get('type') !== TransactionTypeEnum::TRANSFER->name &&
                                    $get('method') === 'ACCOUNT'
                                )
                                ->required(fn ($get) =>
                                    $get('type') !== TransactionTypeEnum::TRANSFER->name &&
                                    $get('method') === 'ACCOUNT'
                                ),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('description')
                                ->required()
                                ->label(__('forms.forms.description'))
                                ->required()
                                ->disabled(function ($get) {
                                    return $get('type') === null ||  $get('type') === '' ||
                                            $get('method') === null ||  $get('method') === '';
                                }),
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
                                })
                                ->disabled(function ($get) {
                                    return $get('type') === null ||  $get('type') === '' ||
                                        $get('method') === null ||  $get('method') === '';
                                }),
                            DatePicker::make('date')
                                ->required()
                                ->label(__('forms.forms.date'))
                                ->default(now())
                                ->disabled(function ($get) {
                                    return $get('type') === null ||  $get('type') === '' ||
                                        $get('method') === null ||  $get('method') === '';
                                }),
                            Toggle::make('is_recurring')
                                ->label(__('forms.forms.is_recurring'))
                                ->default(false)
                                ->inline(false)
                                ->reactive()
                                ->afterStateUpdated(fn ($state, callable $set) => $set('recurrence_interval', $state ? 1 : null))
                                ->visible(fn ($get) =>
                                    $get('type') !== TransactionTypeEnum::TRANSFER->name
                                )
                                ->disabled(fn ($get) => $get('type') === null || $get('type') === ''),
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
                    action: function (array $data) {
                        app(TransactionService::class)->create($data);
                    }
                ),
            ]);
    }

    public static function getListTableColumns(): array
    {
        return [
            TextColumn::make('type')
                ->label(__('forms.columns.type'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'INCOME' => 'success',
                    'EXPENSE' => 'danger',
                    'TRANSFER' => 'purple',
                })
                ->sortable()
                ->formatStateUsing(fn (string $state) => __('forms.enums.transaction_type.' . $state))
                ->icon(fn (string $state): string => match ($state) {
                    'INCOME' => 'heroicon-o-arrow-long-up',
                    'EXPENSE' => 'heroicon-o-arrow-long-down',
                    'TRANSFER' => 'heroicon-o-arrows-right-left',
                }),
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
                    $interval = $record->items()
                        ->where('status', '<>', 'PAID');

                    $sum = $interval->sum('amount');
                    if ($record->recurrence_interval > 0) {
                        $count = $interval->count() > 0 ? $interval->count() : 1;
                        if($count == 1){
                            return $record->amount / 100;
                        }
                        return round($sum/ $count) / 100;
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
            TextColumn::make('deleted_at')
                ->label('Deletado em')
                ->date('d/m/Y')
                ->toggleable(isToggledHiddenByDefault: true)
        ];
    }

    public static function getGridTableColumns(): array
    {
        return [
            Stack::make([
                TextColumn::make('type')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'INCOME' => 'heroicon-o-arrow-long-up',
                        'EXPENSE' => 'heroicon-o-arrow-long-down',
                        'TRANSFER' => 'heroicon-o-arrows-right-left',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'INCOME' => 'success',
                        'EXPENSE' => 'danger',
                        'TRANSFER' => 'purple',
                    })
                    ->formatStateUsing(fn (string $state) => __('forms.enums.transaction_type.' . $state)),

                TextColumn::make('amount')
                    ->currency('BRL')
                    ->formatStateUsing(ColumnFormatter::money(__('forms.columns.amount'))),

                TextColumn::make('paid_amount')
                    ->getStateUsing(function ($record) {
                        $amount = $record->items()->where('status', 'PAID')->sum('amount');
                        $value = (int) preg_replace('/[^0-9,]/', '', $amount);
                        return round($value / 100, 2);
                    })
                    ->money('BRL')
                    ->formatStateUsing(ColumnFormatter::money(__('forms.columns.paid_amount'))),

                TextColumn::make('installment_value')
                    ->getStateUsing(function ($record) {
                        $items = $record->items()->where('status', '<>', 'PAID');
                        $sum = $items->sum('amount');
                        $count = $items->count() ?: 1;
                        return $record->recurrence_interval > 0
                            ? round($sum / $count, 2)
                            : null;
                    })
                    ->money('BRL')
                    ->formatStateUsing(ColumnFormatter::money(__('forms.columns.installment_value'))),

                TextColumn::make('category.name')
                    ->formatStateUsing(ColumnFormatter::labelValue(__('forms.columns.category'))),

                TextColumn::make('recurrence_interval')
                    ->getStateUsing(function ($record) {
                        $paid = $record->items()->where('status', 'PAID')->count();
                        return $record->recurrence_interval > 1
                            ? "$paid/{$record->recurrence_interval}"
                            : __('system.texts.at_sight');
                    })
                    ->formatStateUsing(ColumnFormatter::labelValue(__('forms.forms.recurrence_interval'))),

                TextColumn::make('description')
                    ->limit(30)
                    ->searchable()
                    ->formatStateUsing(ColumnFormatter::labelValue(__('forms.columns.description'))),

                TextColumn::make('method')
                    ->getStateUsing(fn ($record) => __('forms.enums.method.' . strtolower($record->method)))
                    ->formatStateUsing(ColumnFormatter::labelValue(__('forms.columns.method'))),

                TextColumn::make('date')
                    ->date('d/m/Y')
                    ->formatStateUsing(ColumnFormatter::date(__('forms.columns.date'))),

                TextColumn::make('deleted_at')
                    ->date('d/m/Y')
                    ->formatStateUsing(ColumnFormatter::date('Deletado em')),
            ]),
        ];

    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $query->with([
                'account.bank',
                'card',
            ]);

        return $query;
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

