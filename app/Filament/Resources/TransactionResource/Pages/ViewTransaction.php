<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Enum\TransactionTypeEnum;
use App\Filament\Resources\TransactionResource;
use App\Helpers\Filament\ActionHelper;
use App\Helpers\Filament\MaskHelper;
use App\Models\Account;
use App\Models\Card;
use App\Services\TransactionService;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;


    public function getTitle(): string
    {
        return $this->record->description ?? parent::getTitle();
    }

    protected function getListeners(): array
    {
        return [
            'refreshInfolist' => '$refresh',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionHelper::makeSlideOverView(
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
                    Hidden::make('user_id')->default(auth()->id()),
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
            )
        ];
    }
}
