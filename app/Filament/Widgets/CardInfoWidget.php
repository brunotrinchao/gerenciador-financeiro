<?php

namespace App\Filament\Widgets;

use App\Helpers\Filament\MaskHelper;
use App\Models\Card;
use App\Models\Transaction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Support\RawJs;
use Filament\Widgets\Widget;

class CardInfoWidget extends Widget
{
    public Card $record;

    protected static string $view = 'filament.widgets.transaction-info-widget';
    protected function getViewData(): array
    {
        return [
            'record' => $this->record,
        ];
    }
    protected int | string | array $columnSpan = [
        'default' => 12,
    ];

    public function mount(Card $record): void
    {
        $this->record = $record;
        $this->record->with(['brand', 'account']);
    }

    public function getInfolist(): Infolist
    {
        $moneyMask = RawJs::make('function($input){
            let value = $input.replace(/\\D/g, \'\');
            value = (value / 100).toFixed(2);
            value = value.replace(\'.\', \',\');
            value = value.replace(/\\B(?=(\\d{3})+(?!\\d))/g, \'.\');
            return value;
        }');
        return Infolist::make()
            ->record($this->record)
            ->schema([
                ImageEntry::make('brand.brand')
                    ->label(__('forms.columns.brand'))
                    ->height(30),
                TextEntry::make('bank.name')
                    ->label('Banco'),
                TextEntry::make('name')
                    ->label(__('forms.columns.name')),
                TextEntry::make('number')
                    ->label(__('forms.columns.number')),
                TextEntry::make('due_date')
                    ->label(__('forms.columns.due_date')),
                TextEntry::make('limit')
                    ->label(__('forms.columns.limit'))
                    ->currency('BRL'),


//                TextEntry::make('category.name')
//                    ->label('Categoria'),
//                TextEntry::make('method')
//                    ->label('Método')
//                    ->formatStateUsing(fn ($state) => __('forms.enums.method.' . strtolower($state))),
//                TextEntry::make('description')
//                    ->label('Descrição'),
//                TextEntry::make('amount')
//                    ->label('Valor')->currency('BRL'),
//                TextEntry::make('date')
//                    ->label('Data')->since()
//                    ->dateTime(),
//                TextEntry::make('is_recurring')
//                    ->label('Parcelado?')
//                    ->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não'),
//                TextEntry::make('recurrence_interval')
//                    ->label('Parcelas pagas')
//                    ->formatStateUsing(function () {
//                        $paid = $this->record->items()->where('status', 'PAID')->count();
//                        $total = $this->record->items()->count();
//                        return "{$paid} de {$total}";
//                    })
            ])
            ->columns(6);
    }
}
