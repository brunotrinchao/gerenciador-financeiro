<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use App\Helpers\Filament\ActionHelper;
use App\Helpers\Filament\MaskHelper;
use App\Models\Account;
use App\Models\Card;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\TransactionItemFilterService;
use App\Services\TransactionItemService;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static function getModelLabel(): ?string
    {
        return 'OK';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('amount')
                    ->mask(MaskHelper::maskMoney())
                    ->stripCharacters(',')
                    ->numeric()
                    ->prefix('R$')
                    ->label('Valor')
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
                ->label('Parcela'),
                TextColumn::make('amount')
                    ->label('Valor')
                    ->sortable()
                    ->currency('BRL'),
                TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y'),
                TextColumn::make('transaction.method')
                    ->label('Método')
                    ->formatStateUsing(function (string $state) {
                        return match ($state) {
                            'CARD' => __('forms.enums.method.card'),
                            'ACCOUNT' => __('forms.enums.method.account'),
                            'CASH' => __('forms.enums.method.cash'),
                            default => ''
                        };
                    }),
                TextColumn::make('source')
                    ->label('Fonte pagamento')
                    ->getStateUsing(function ($record) {
                        if ($record->transaction->card_id && $record->transaction->card) {
                            return $record->transaction->card->name;
                        }

                        if ($record->transaction->account_id && $record->transaction->account?->bank) {
                            return $record->transaction->account->bank->name;
                        }
                        return null;
                    }),
                TextColumn::make('payment_date')
                    ->label('Pagamento')
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
//            ->striped()
            ->recordClasses(fn (Model $record) => $record->status == 'PAID' ? 'bg-red-500' : 'bg-red-500')
            ->filters([
                //
            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editTransactionItem',
                    form: [
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
//                            ->disabled(function ($get) {
//                                return in_array($get('method'),['ACCOUNT', 'CARD']);
//                            })
                            ->required()
                            ->reactive(),
                    ],
                    modalHeading: __('forms.modal_headings.edit_transaction_item'),
                    label: __('forms.buttons.edit'),
                    fillForm: function ($record) {
                        return [
                            'amount' => (int) $record->amount,
                            'due_date' => $record->due_date,
                            'payment_date' => $record->payment_date,
                            'method' => $record->transaction->method,
                            'status' => $record->transaction->method == 'CARD' ? 'DEBIT' : $record->status
                        ];
                    },
                    after: function (array $data, $record) {
                        $transactionItemService = new TransactionItemService();
                        $transactionItemService->recalcAmountTransactionItem($record);

                        $this->dispatch('refreshProducts');

                        return true;
                    }
                )
                ->visible(fn ($record) => $record->status !== 'PAID'),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Deletar parcela')
                    ->modalDescription(fn ($record) => 'Você tem certeza que gostaria de excluir a parcela Nº ' . $record->installment_number)
                    ->before(function (TransactionItem $record, RelationManager $livewire, DeleteAction $action) {
                        $transaction = $livewire->ownerRecord;

                        if ($record->status === 'PAID') {
                            Notification::make()
                                ->title('Ação não permitida')
                                ->body('Não é possível excluir uma parcela que já foi paga.')
                                ->danger()
                                ->send();

                            $action->halt();
                        }

                        // Verifica se é a última parcela ainda não paga
                        $unpaidItems = TransactionItem::where('transaction_id', $transaction->id)
                            ->where('status', '!=', 'PAID')
                            ->get();

                        if ($unpaidItems->count() === 1 && $unpaidItems->first()->id === $record->id) {
                            Notification::make()
                                ->title('Ação não permitida')
                                ->body('Não é possível excluir a última parcela não paga da transação.<br/>Edite com o valor R$ 0,0.')
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    })
                    ->after(function (TransactionItem $record, RelationManager $livewire) {
                        $transaction = $livewire->ownerRecord;

                        // Recalcula apenas com as parcelas que não foram pagas
                        $remainingItems = TransactionItem::where('transaction_id', $transaction->id)
                            ->get();

                        $installmentCount = $remainingItems->count();

                        $transaction->update([
                            'recurrence_interval' => $installmentCount,
                        ]);

                        $transactionItemService = new TransactionItemService();
                        $transactionItemService->updateAmountAndInstallmentCount($record);

                        $livewire->dispatch('refreshProducts'); // Atualiza formulário
                    })
                    ->visible(fn ($record) => $record->status !== 'PAID'),
            ])
            ->bulkActions([
            ])
            ->recordUrl(null)
            ->recordAction('editTransactionItem')
            ->headerActions([
                Tables\Actions\Action::make('create_installemnt')
                    ->label('Parcela')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function () {
                        $transactionItemService = new TransactionItemService();

                        /* @var Transaction $transaction */
                        $transaction = $this->ownerRecord;
                        $transactionItemService->create($transaction);

                        $this->dispatch('refreshProducts'); // Atualiza formulário
                    }),
            ]);
    }


}
