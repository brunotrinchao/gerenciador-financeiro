<?php

namespace App\Filament\Resources\TransactionResource\RelationManagers;

use App\Enum\RolesEnum;
use App\Helpers\Filament\ActionHelper;
use App\Helpers\Filament\MaskHelper;
use App\Models\Account;
use App\Models\Card;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\TransactionItemFilterService;
use App\Services\TransactionItemService;
use Carbon\Carbon;
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
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle(Model $ownerRecord,  string $pageClass): string
    {
        return 'Parcelas';
    }

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
            ->recordClasses(fn (Model $record) => $record->status == 'PAID' ? 'bg-red-500' : 'bg-red-500')
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

                        // Atualiza e recalcula
                        $record->update([
                            'amount' => $newAmount,
                            'due_date' => $data['due_date'],
                            'payment_date' => $data['payment_date'],
                            'status' => $data['status'],
                        ]);

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
                BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('paid_fast')
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

                            $this->dispatch('refreshInfolist');
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('delete')
                        ->label('Excluir parcelas')
                        ->icon('heroicon-m-trash')
                        ->requiresConfirmation()
                        ->color('danger')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->status === 'PAID') {
                                    continue; // Ignora parcelas já pagas
                                }

                                $transaction = $record->transaction;

                                // Verifica se é a última parcela não paga
                                $unpaidItems = TransactionItem::where('transaction_id', $transaction->id)
                                    ->where('status', '!=', 'PAID')
                                    ->get();

                                if ($unpaidItems->count() === 1 && $unpaidItems->first()->id === $record->id) {
                                    continue; // Ignora exclusão da última parcela não paga
                                }

                                $record->delete();

                                // Atualiza a transação
                                $transaction->update([
                                    'is_recurring' => TransactionItem::where('transaction_id', $transaction->id)->count() > 1 ? true : false,
                                    'recurrence_interval' => TransactionItem::where('transaction_id', $transaction->id)->count(),
                                ]);

                                $transactionItemService = new TransactionItemService();
                                $transactionItemService->updateAmountAndInstallmentCount($record);
                            }

                            Notification::make()
                                ->title('Parcelas excluídas com sucesso!')
                                ->success()
                                ->send();


                            $this->dispatch('refreshInfolist');
                        })
                        ->deselectRecordsAfterCompletion(),
                ])
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => $record->status !== 'PAID',
            )
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

                        $transaction->update([
                            'is_recurring' => true,
                            'recurrence_interval' => $transaction->items()->count(),
                        ]);


                        $this->dispatch('refreshInfolist');
                        $this->dispatch('refreshProducts'); // Atualiza formulário
                    }),
            ]);
    }


}
