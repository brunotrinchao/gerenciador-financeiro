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
                    ->label(__('system.labels.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'INCOME' => 'success',
                        'EXPENSE' => 'danger',
                    })
                    ->sortable()
                    ->formatStateUsing(fn (string $state) => __('system.enums.transaction_type.' . $state)),
                TextColumn::make('description')
                    ->label(__('system.labels.description'))
                    ->limit(20),
                TextColumn::make('category.name')
                    ->label(__('system.labels.category')),
                TextColumn::make('amount')
                    ->label(__('system.labels.amount'))
                    ->sortable()
                    ->currency('BRL'),
                TextColumn::make('date')
                    ->label(__('system.labels.date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('recurrence_interval')
                    ->label(__('system.labels.recurrence_interval'))
                    ->formatStateUsing(fn (string $state) => $state > 1 ? $state : __('system.texts.at_sight'))
                    ->alignCenter(),
                TextColumn::make('method')
                    ->label(__('system.labels.method'))
                    ->getStateUsing(fn ($record) => __('system.enums.method.' . $record->method)),
            ])
            ->filters([
                DateRangeFilter::make('date')
                    ->label(__('system.labels.period'))
                    ->startDate(Carbon::now()->startOfMonth())
                    ->endDate(Carbon::now()->endOfMonth())
                    ->withIndicator()
                    ->useRangeLabels()
                    ->autoApply(),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('system.labels.category'))
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('method')
                    ->label(__('system.labels.method'))
                    ->options([
                        'CARD' => __('system.enums.method.CARD'),
                        'ACCOUNT' => __('system.enums.method.ACCOUNT'),
                        'CASH' => __('system.enums.method.CASH'),
                    ]),
            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editTransaction',
                    form: [
                        Radio::make('type')
                            ->label(__('system.labels.type'))
                            ->options([
                                'INCOME' => __('system.enums.transaction_type.INCOME'),
                                'EXPENSE' => __('system.enums.transaction_type.EXPENSE'),
                            ])
                            ->inline()
                            ->required()
                            ->inlineLabel(false),
                        Select::make('category_id')
                            ->required()
                            ->label(__('system.labels.category'))
                            ->relationship('category', 'name'),
                        Select::make('method')
                            ->label(__('system.labels.method'))
                            ->options([
                                'CARD' => __('system.enums.method.CARD'),
                                'ACCOUNT' => __('system.enums.method.ACCOUNT'),
                                'CASH' => __('system.enums.method.CASH'),
                            ])
                            ->reactive()
                            ->required(),
                        Select::make('card_id')
                            ->label(__('system.labels.card'))
                            ->options(fn () => Card::all()->pluck('name', 'id'))
                            ->visible(fn ($get) => $get('method') === 'CARD')
                            ->required(fn ($get) => $get('method') === 'CARD'),
                        Select::make('account_id')
                            ->label(__('system.labels.account'))
                            ->options(fn () => Account::with('bank')->get()->mapWithKeys(fn ($account) => [$account->id => $account->bank->name ?? 'Sem banco']))
                            ->visible(fn ($get) => $get('method') === 'ACCOUNT')
                            ->required(fn ($get) => $get('method') === 'ACCOUNT'),
                        TextInput::make('amount')
                            ->required()
                            ->label(__('system.labels.amount'))
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->prefix('R$'),
                        DatePicker::make('date')
                            ->required()
                            ->label(__('system.labels.date')),
                        Textarea::make('description')
                            ->required()
                            ->label(__('system.labels.description'))
                            ->maxLength(100),
                        Toggle::make('is_recurring')
                            ->label(__('system.labels.is_recurring'))
                            ->default(false)
                            ->inline(false)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('recurrence_interval', $state ? 1 : null)),
                        TextInput::make('recurrence_interval')
                            ->required(fn ($get) => $get('is_recurring'))
                            ->label(__('system.labels.recurrence_interval'))
                            ->hidden(fn ($get) => !$get('is_recurring'))
                            ->numeric()
                            ->minValue(fn ($get) => $get('is_recurring') ? 2 : null),
                        Select::make('recurrence_type')
                            ->label(__('system.labels.recurrence_type'))
                            ->options([
                                'DAILY' => __('system.enums.recurrence_type.DAILY'),
                                'WEEKLY' => __('system.enums.recurrence_type.WEEKLY'),
                                'MONTHLY' => __('system.enums.recurrence_type.MONTHLY'),
                                'YEARLY' => __('system.enums.recurrence_type.YEARLY'),
                            ])
                            ->hidden(fn ($get) => !$get('is_recurring')),
                        Forms\Components\Hidden::make('user_id')->default(auth()->id()),
                    ],
                    modalHeading: __('system.modal_headings.edit_transaction'),
                    label: __('system.buttons.edit'),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(null)
            ->recordAction('editTransaction')
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createTransaction',
                    form: [
                        Radio::make('type')
                            ->label(__('system.labels.type'))
                            ->options([
                                'INCOME' => __('system.enums.transaction_type.INCOME'),
                                'EXPENSE' => __('system.enums.transaction_type.EXPENSE'),
                            ])
                            ->inline()
                            ->required()
                            ->inlineLabel(false),
                        Select::make('category_id')
                            ->required()
                            ->label(__('system.labels.category'))
                            ->relationship('category', 'name'),
                        Select::make('method')
                            ->label(__('system.labels.method'))
                            ->options([
                                'CARD' => __('system.enums.method.CARD'),
                                'ACCOUNT' => __('system.enums.method.ACCOUNT'),
                                'CASH' => __('system.enums.method.CASH'),
                            ])
                            ->reactive()
                            ->required(),
                        Select::make('card_id')
                            ->label(__('system.labels.card'))
                            ->options(fn () => Card::all()->pluck('name', 'id'))
                            ->visible(fn ($get) => $get('method') === 'CARD')
                            ->required(fn ($get) => $get('method') === 'CARD'),
                        Select::make('account_id')
                            ->label(__('system.labels.account'))
                            ->options(fn () => Account::with('bank')->get()->mapWithKeys(fn ($account) => [$account->id => $account->bank->name ?? 'Sem banco']))
                            ->visible(fn ($get) => $get('method') === 'ACCOUNT')
                            ->required(fn ($get) => $get('method') === 'ACCOUNT'),
                        TextInput::make('amount')
                            ->required()
                            ->label(__('system.labels.amount'))
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->prefix('R$'),
                        DatePicker::make('date')
                            ->required()
                            ->label(__('system.labels.date')),
                        Textarea::make('description')
                            ->required()
                            ->label(__('system.labels.description'))
                            ->maxLength(100),
                        Toggle::make('is_recurring')
                            ->label(__('system.labels.is_recurring'))
                            ->default(false)
                            ->inline(false)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('recurrence_interval', $state ? 1 : null)),
                        TextInput::make('recurrence_interval')
                            ->label(__('system.labels.recurrence_interval'))
                            ->hidden(fn ($get) => !$get('is_recurring'))
                            ->minValue(fn ($get) => $get('is_recurring') ? 2 : null)
                            ->numeric(),
                        Select::make('recurrence_type')
                            ->label(__('system.labels.recurrence_type'))
                            ->options([
                                'DAILY' => __('system.enums.recurrence_type.DAILY'),
                                'WEEKLY' => __('system.enums.recurrence_type.WEEKLY'),
                                'MONTHLY' => __('system.enums.recurrence_type.MONTHLY'),
                                'YEARLY' => __('system.enums.recurrence_type.YEARLY'),
                            ])
                            ->hidden(fn ($get) => !$get('is_recurring')),
                        Forms\Components\Hidden::make('user_id')->default(auth()->id()),
                    ],
                    modalHeading: __('system.modal_headings.create_transaction'),
                    label: __('system.buttons.create'),
                    action: function (array $data, Action $action) {
                        $transaction = Transaction::create($data);

                        $parcelas = !empty($data['is_recurring']) ? (int) ($data['recurrence_interval'] ?? 1) : 1;

                        $amount = (float) str_replace(['.', ','], ['', '.'], $data['amount']);
                        $baseValue = floor($amount / $parcelas * 100) / 100; // força 2 casas
                        $remaining = $amount - ($baseValue * $parcelas);

                        $date = Carbon::parse($data['date']);

                        for ($i = 0; $i < $parcelas; $i++) {
                            $parcela = $i + 1;
                            $currentAmount = $parcela == $parcelas ? $baseValue + $remaining : $baseValue;
                            $paymentDate = (clone $date)->addMonths($i);

                            TransactionItem::create([
                                'transaction_id' => $transaction->id,
                                'due_date' => $paymentDate,
                                'amount' => $currentAmount,
                                'installment_number' => $parcela,
                                'status' => 'PENDING',
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

