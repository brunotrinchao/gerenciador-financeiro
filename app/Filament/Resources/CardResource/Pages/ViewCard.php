<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use App\Filament\Resources\TransactionResource;
use App\Helpers\Filament\ActionHelper;
use App\Helpers\Filament\MaskHelper;
use App\Models\Bank;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\RawJs;

class ViewCard extends ViewRecord
{
    protected static string $resource = CardResource::class;
    public function getTitle(): string
    {
        return $this->record->name ?? parent::getTitle();
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionHelper::makeSlideOverView(
                name: 'editCard',
                form: [
                    Select::make('bank_id')
                        ->required()
                        ->label(__('forms.columns.bank'))
                        ->prefixIcon('phosphor-bank')
                        ->options(Bank::pluck('name', 'id')),
                    TextInput::make('name')
                        ->required()
                        ->label(__('forms.columns.name')),
                    TextInput::make('number')
                        ->required()
                        ->prefixIcon('heroicon-m-credit-card')
                        ->label(__('forms.columns.number'))
                        ->mask(RawJs::make(<<<'JS'
                    $input.startsWith('34') || $input.startsWith('37') ? '9999 999999 99999' : '9999 9999 9999 9999'
                JS)),
                    Select::make('brand_id')
                        ->required()
                        ->label(__('forms.columns.brand'))
                        ->searchable()
                        ->relationship('brand', 'name')
                        ->preload(),
                    TextInput::make('due_date')
                        ->required()
                        ->label(__('forms.columns.due_date'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(31),
                    TextInput::make('limit')
                        ->label(__('forms.columns.limit'))
                        ->prefix('R$')
                        ->mask(MaskHelper::maskMoney())
                        ->stripCharacters(',')
                        ->numeric()
                        ->default(0)
                        ->required(),
                ],
                modalHeading: __('forms.actions.edit_card'),
                label: __('forms.actions.edit'),
                fillForm: fn($record) => [
                    'bank_id' => $record->bank_id,
                    'name' => $record->name,
                    'number' => $record->number,
                    'brand_id' => $record->brand_id,
                    'due_date' => $record->due_date,
                    'limit' => $record->limit,
                ]
            ),
        ];
    }
}
