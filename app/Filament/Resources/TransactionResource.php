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

class TransactionResource extends Resource
{
    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?string $model = Transaction::class;

    protected static ?string $pluralModelLabel = 'Transações'; // Listagem

    protected static ?string $modelLabel = 'Transação'; // Criação/Edição

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
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'INCOME' => 'success',
                        'EXPENSE' => 'danger',
                    })
                    ->sortable()
                    ->formatStateUsing(fn (string $state) => $state === 'INCOME' ? 'Receita' : 'Despesa'),
                TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(20),
                TextColumn::make('category.name')
                    ->label('Categoria'),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->sortable()
                    ->currency('BRL'),
                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('recurrence_interval')
                    ->label('Parcelas')
                    ->formatStateUsing(function (string $state) {
                        return $state > 1 ? $state : ' A vista ';
                    })
                    ->alignCenter(),
                TextColumn::make('method')
                    ->label('Método')
                    ->getStateUsing(function ($record) {
                        if ($record->method == 'CARD') {
                            return 'Cartão de crédito';
                        }

                        if ($record->method == 'ACCOUNT') {
                            return 'Débito em conta';
                        }

                        return 'Dinheiro';
                    }),
            ])
            ->filters([
                Filter::make('date')
                    ->label('Período')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Data início')
                            ->default(Carbon::now()->startOfMonth()),
                        DatePicker::make('end_date')
                            ->label('Data fim')
                            ->default(Carbon::now()->endOfMonth()),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['start_date'], fn ($q) => $q->whereDate('date', '>=', $data['start_date']))
                            ->when($data['end_date'], fn ($q) => $q->whereDate('date', '<=', $data['end_date']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['start_date'] ?? null) {
                            $indicators[] = 'De ' . Carbon::parse($data['start_date'])->format('d/m/Y');
                        }

                        if ($data['end_date'] ?? null) {
                            $indicators[] = 'Até ' . Carbon::parse($data['end_date'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),
                Tables\Filters\SelectFilter::make('category_id')
                ->label('Categoria')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('method')
                    ->label('Método')
                    ->options([
                        'CARD' => 'Cartão de crédito',
                        'ACCOUNT' => 'Conta corrente',
                        'CASH' => 'Dinheiro',
                    ])
            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editTransaction',
                    form: [
                        Radio::make('type')
                            ->label('Tipo')
                            ->options([
                                'INCOME' => 'Receita',
                                'EXPENSE' => 'Despesa'
                            ])
                            ->inline()
                            ->required()
                            ->inlineLabel(false),
                        Select::make('category_id')
                            ->required()
                            ->label('Categoria')
                            ->relationship('category', 'name'),
                        Select::make('method')
                            ->label('Método')
                            ->options([
                                'CARD' => 'Cartão de crédito',
                                'ACCOUNT' => 'Conta corrente',
                                'CASH' => 'Dinheiro',
                            ])
                            ->reactive()
                            ->required(),
                        Select::make('card_id')
                            ->label('Cartão de crédito')
                            ->options(function () {
                                return Card::all()->pluck('name', 'id');
                            })
                            ->visible(function ($get) {
                                return $get('method') == 'CARD';
                            })
                            ->required(function ($get) {
                                return $get('method') == 'CARD';
                            }),
                        Select::make('account_id')
                            ->label('Conta')
                            ->options(function () {
                                return Account::with('bank')->get()->mapWithKeys(function ($account) {
                                    return [$account->id => $account->bank->name ?? 'Sem banco'];
                                });
                            })
                            ->visible(function ($get) {
                                return $get('method') == 'ACCOUNT';
                            })
                            ->required(function ($get) {
                                return $get('method') == 'ACCOUNT';
                            }),
                        TextInput::make('amount')
                            ->required()
                            ->label('Valor')
//                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',', precision: 2)
                            ->prefix('R$'),
                        DatePicker::make('date')
                            ->required()
                            ->label('Data'),
                        Textarea::make('description')
                            ->required()
                            ->label('Descrição')
                            ->maxLength(100),
                        Toggle::make('is_recurring')
                            ->label('Parcelado?')
                            ->default(false)
                            ->inline(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Se o toggle for ativado, altere o estado do campo
                                $set('recurrence_interval', $state ? 1 : null); // Ajuste conforme necessário
                            }),
                        TextInput::make('recurrence_interval')
                            ->required(fn ($get) => $get('is_recurring'))
                            ->label('Nº Parcelas')
                            ->hidden(fn ($get) => !$get('is_recurring'))
                            ->numeric()
                            ->minValue(fn ($get) => $get('is_recurring') ? 2 : null),
                        Select::make('recurrence_type')
                            ->label('Frequência')
                            ->options([
                                'DAILY' => 'Diário',
                                'WEEKLY' => 'Semanal',
                                'MONTHLY' => 'Mensal',
                                'YAERLY' => 'Anual'
                            ])
                            ->hidden(fn ($get) => !$get('is_recurring')),
                        Forms\Components\Hidden::make('user_id')->default(auth()->id())
                    ],
                    modalHeading: 'Editar Transação',
                    label: 'Editar',
                    fillForm: fn ($record) => [
                        'type'                => $record->type,
                        'category_id'         => $record->category_id,
                        'method'              => $record->method,
                        'card_id'             => $record->card_id,
                        'account_id'          => $record->account_id,
                        'amount'              => number_format($record->amount / 100, 2, ',', ''), // para o currencyMask
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
                )
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
                            ->label('Tipo')
                            ->options([
                                'INCOME' => 'Receita',
                                'EXPENSE' => 'Despesa'
                            ])
                            ->inline()
                            ->required()
                            ->inlineLabel(false),
                        Select::make('category_id')
                            ->required()
                            ->label('Categoria')
                            ->relationship('category', 'name'),
                        Select::make('method')
                            ->label('Método')
                            ->options([
                                'CARD' => 'Cartão de crédito',
                                'ACCOUNT' => 'Conta corrente',
                                'CASH' => 'Dinheiro',
                            ])
                            ->reactive()
                            ->required(),
                        Select::make('card_id')
                            ->label('Cartão de crédito')
                            ->options(function () {
                                return Card::all()->pluck('name', 'id');
                            })
                            ->visible(function ($get) {
                                return $get('method') == 'CARD';
                            })
                            ->required(function ($get) {
                                return $get('method') == 'CARD';
                            }),
                        Select::make('account_id')
                            ->label('Conta')
                            ->options(function () {
                                return Account::with('bank')->get()->mapWithKeys(function ($account) {
                                    return [$account->id => $account->bank->name ?? 'Sem banco'];
                                });
                            })
                            ->visible(function ($get) {
                                return $get('method') == 'ACCOUNT';
                            })
                            ->required(function ($get) {
                                return $get('method') == 'ACCOUNT';
                            }),
                        TextInput::make('amount')
                            ->required()
                            ->label('Valor')
                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',', precision: 2)
                            ->prefix('R$'),
                        DatePicker::make('date')
                            ->required()
                            ->label('Data'),
                        Textarea::make('description')
                            ->required()
                            ->label('Descrição')
                            ->maxLength(100),
                        Toggle::make('is_recurring')
                            ->label('Parcelado?')
                            ->default(false)
                            ->inline(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Se o toggle for ativado, altere o estado do campo
                                $set('recurrence_interval', $state ? 1 : null); // Ajuste conforme necessário
                            }),
                        TextInput::make('recurrence_interval')
                            ->label('Nº Parcelas')
                            ->hidden(fn ($get) => !$get('is_recurring'))
                            ->numeric(),
                        Select::make('recurrence_type')
                            ->label('Frequência')
                            ->options([
                                'DAILY' => 'Diário',
                                'WEEKLY' => 'Semanal',
                                'MONTHLY' => 'Mensal',
                                'YAERLY' => 'Anual'
                            ])
                            ->hidden(fn ($get) => !$get('is_recurring')),
                        Forms\Components\Hidden::make('user_id')->default(auth()->id())
                    ],
                    modalHeading: 'Nova Transação',
                    label: 'Criar',
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
                            ->title('Transação criada')
                            ->body("{$parcelas} parcela(s) foram geradas com sucesso.")
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
