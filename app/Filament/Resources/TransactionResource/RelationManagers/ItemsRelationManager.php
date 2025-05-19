<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use App\Models\Account;
use App\Models\Card;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('amount')
                    ->label('Valor')
                    ->currencyMask(thousandSeparator: '.',decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->required(),
                DatePicker::make('due_date')
                    ->label('Data de vencimento')
                    ->required(),
                DatePicker::make('payment_date')
                    ->label('Data de pagamento')
                    ->required(function ($get) {
                        return $get('status') == 'PAID';
                    }),
                Select::make('payment_method')
                    ->label('Método')
                    ->options([
                        'CARD' => 'Cartão de crédito',
                        'ACCOUNT' => 'Conta corrente',
                        'CASH' => 'Dinheiro',
                    ])
                    ->reactive()
                    ->required(function ($get) {
                        return $get('status') == 'PAID';
                    }),
                Select::make('card_id')
                    ->label('Cartão de crédito')
                    ->options(function () {
                        return Card::all()->pluck('name', 'id');
                    })
                    ->visible(function ($get) {
                        return $get('payment_method') == 'CARD';
                    })
                    ->required(function ($get) {
                        return $get('payment_method') == 'CARD';
                    }),
                Select::make('account_id')
                    ->label('Conta')
                    ->options(function () {
                        return Account::with('bank')->get()->mapWithKeys(function ($account) {
                            return [$account->id => $account->bank->name ?? 'Sem banco'];
                        });
                    })
                    ->visible(function ($get) {
                        return $get('payment_method') == 'ACCOUNT';
                    })
                    ->required(function ($get) {
                        return $get('payment_method') == 'ACCOUNT';
                    }),
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
                    ->reactive(),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaction_id')
            ->columns([
                TextColumn::make('installment_number')
                ->label('Nº'),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->sortable()
                    ->currency('BRL'),
                TextColumn::make('due_date')
                    ->label('Data de vencimento')
                    ->date('d/m/Y'),
                TextColumn::make('payment_method')
                    ->label('Método')
                    ->formatStateUsing(function (string $state) {
                        return match ($state) {
                            'ACCOUNT' => 'Conta',
                            'CARD' => 'Cartão de crédito',
                            'CASH' => 'Dinheiro',
                            default => ''
                        };
                    }),
                TextColumn::make('source')
                    ->label('Fonte pagamento')
                    ->getStateUsing(function ($record) {
                        if ($record->card_id && $record->card) {
                            return $record->card->name;
                        }

                        if ($record->account_id && $record->account?->bank) {
                            return $record->account->bank->name;
                        }
                        return null;
                    }),
                TextColumn::make('payment_date')
                    ->label('Data de pagamento')
                    ->date('d/m/Y'),
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
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data, string $model, RelationManager $livewire) {
                        $transactionId = $livewire->ownerRecord->id;

                        $data['transaction_id'] = $transactionId;

                        $data['installment_number'] = (TransactionItem::where('transaction_id', $transactionId)->max('installment_number') ?? 0) + 1;

                        return $data;
                    })
                    ->after(function (array $data, string $model, RelationManager $livewire) {
                        $transactionId = $livewire->ownerRecord->id;

                        $totalAmount = TransactionItem::where('transaction_id', $transactionId)->sum('amount');

                        $livewire->ownerRecord->update([
                            'amount' => $totalAmount,
                            'recurrence_interval' => TransactionItem::where('transaction_id', $transactionId)->count()
                        ]);
                        $livewire->dispatch('refreshProducts');
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->modalHeading(fn ($record) => "Editar parela Nº $record->installment_number")
                ->after(function (TransactionItem $record, RelationManager $livewire) {
                    $transactionId = $livewire->ownerRecord->id;

                    $totalAmount = TransactionItem::where('transaction_id', $transactionId)->sum('amount');

                    $livewire->ownerRecord->update([
                        'amount' => $totalAmount
                    ]);
                    $livewire->dispatch('refreshProducts');
                }),
                Tables\Actions\DeleteAction::make()
                    ->after(function (TransactionItem $record, RelationManager $livewire) {
                        $transaction = $livewire->ownerRecord;

                        $totalAmount = TransactionItem::where('transaction_id', $transaction->id)->sum('amount');
                        $installmentCount = TransactionItem::where('transaction_id', $transaction->id)->count();

                        $transaction->update([
                            'amount' => $totalAmount,
                            'recurrence_interval' => $installmentCount,
                        ]);

                        $livewire->dispatch('refreshProducts'); // Gatilho para EditTransaction
                    }),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
            ]);
    }


}
