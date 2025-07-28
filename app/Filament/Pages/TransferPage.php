<?php

namespace App\Filament\Pages;

use App\Enum\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Card;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Transfer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransferPage extends Page
{
//    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.transfer-page';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Transferência')->schema([
                Grid::make(2)->schema([
                    Select::make('origin_method')
                        ->label('Origem')
                        ->options([
                            'ACCOUNT' => 'Conta',
                            'CARD' => 'Cartão',
                            'CASH' => 'Dinheiro',
                        ])
                        ->required()
                        ->reactive(),

                    Select::make('origin_id')
                        ->label('Selecionar origem')
                        ->options(function ($get) {
                            return match ($get('origin_method')) {
                                'ACCOUNT' => Account::pluck('name', 'id'),
                                'CARD' => Card::pluck('name', 'id'),
                                default => [],
                            };
                        })
                        ->required(),

                    Select::make('target_method')
                        ->label('Destino')
                        ->options([
                            'ACCOUNT' => 'Conta',
                            'CARD' => 'Cartão',
                            'CASH' => 'Dinheiro',
                        ])
                        ->required()
                        ->reactive(),

                    Select::make('target_id')
                        ->label('Selecionar destino')
                        ->options(function ($get) {
                            return match ($get('target_method')) {
                                'ACCOUNT' => Account::pluck('name', 'id'),
                                'CARD' => Card::pluck('name', 'id'),
                                default => [],
                            };
                        })
                        ->required(fn($get) => $get('target_method') !== 'CASH'),

                    TextInput::make('amount')
                        ->label('Valor')
                        ->required(),

                    DatePicker::make('date')
                        ->label('Data')
                        ->default(now())
                        ->required(),

                    TextInput::make('description')
                        ->label('Descrição (opcional)'),
                ])
            ])
        ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data) {
            $amount = (int) str_replace(['.', ','], ['', '.'], $data['amount']);
            $date = Carbon::parse($data['date']);

            $origin = Transaction::create([
                'type' => TransactionTypeEnum::TRANSFER,
                'method' => $data['origin_method'],
                'account_id' => $data['origin_method'] === 'ACCOUNT' ? $data['origin_id'] : null,
                'card_id' => $data['origin_method'] === 'CARD' ? $data['origin_id'] : null,
                'amount' => $amount,
                'date' => $date,
                'description' => 'Transferência de: ' . ucfirst($data['origin_method']),
            ]);

            TransactionItem::create([
                'transaction_id' => $origin->id,
                'due_date' => $date,
                'payment_date' => $date,
                'amount' => $amount,
                'installment_number' => 1,
                'status' => 'PAID',
            ]);

            $target = Transaction::create([
                'type' => TransactionTypeEnum::TRANSFER,
                'method' => $data['target_method'],
                'account_id' => $data['target_method'] === 'ACCOUNT' ? $data['target_id'] : null,
                'card_id' => $data['target_method'] === 'CARD' ? $data['target_id'] : null,
                'amount' => $amount,
                'date' => $date,
                'description' => 'Transferência para: ' . ucfirst($data['target_method']),
            ]);

            TransactionItem::create([
                'transaction_id' => $target->id,
                'due_date' => $date,
                'payment_date' => $date,
                'amount' => $amount,
                'installment_number' => 1,
                'status' => 'PAID',
            ]);

            Transfer::create([
                'source_transaction_id' => $origin->id,
                'target_transaction_id' => $target->id,
            ]);
        });

        Notification::make()
            ->title('Transferência realizada')
            ->success()
            ->send();

        $this->form->fill(); // limpa o form
    }
}
