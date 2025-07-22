<?php

namespace App\Filament\Resources\CardResource\RelationManagers;

use App\Filament\Resources\TransactionResource;
use App\Filament\Resources\TransactionResource\RelationManagers\ItemsRelationManager;
use App\Helpers\Filament\ActionHelper;
use App\Helpers\Filament\MaskHelper;
use App\Models\Account;
use App\Models\Card;
use App\Services\TransactionItemService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CardTransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'Transações';

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public function isReadOnly(): bool
    {
        return true;
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')->label('Descrição'),
                TextColumn::make('amount')->label('Valor')->currency('BRL'),
                TextColumn::make('recurrence_interval')->label('Parcelas'),
                TextColumn::make('paid_installments')
                    ->label('Parcelas pagas')
                    ->getStateUsing(fn ($record) => $record->items->where('status', 'PAID')->count()),
                TextColumn::make('pendent_installments')
                    ->label('Parcelas restante')
                    ->getStateUsing(fn ($record) => $record->items->where('status', '<>', 'PAID')->count()),
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
                        Hidden::make('user_id')->default(auth()->id()),
                    ],
                    modalHeading: __('forms.actions.edit_card'),
                    label: __('forms.actions.edit'),
                    fillForm: fn($record) => [
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
                        $data['amount'] = preg_replace('/[^0-9,]/', '', $data['amount']);

                        $record->update($data);

                        $service = new TransactionItemService();
                        $service->update($record);

                        return true;
                    }
                ),
            ])
            ->filters([
                Filter::make('due_month_year')
                    ->label('Mês e Ano')
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
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        $year = $data['year'] ?? date('Y');
                        $month = $data['month'] ?? date('m');

                        $start = Carbon::create($year, $month, 1)->startOfMonth();
                        $end = Carbon::create($year, $month, 1)->endOfMonth();

                        $query->whereHas('items', function (Builder $q) use ($start, $end) {
                            $q->whereBetween('transaction_items.due_date', [$start, $end]);
                        });
                        return $query;
                    }),
                ])
            ->recordUrl(
                fn ($record) => TransactionResource::getUrl('edit', ['record' => $record])
            );
    }
}
