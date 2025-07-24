<?php

namespace App\Filament\Widgets;

use App\Helpers\Filament\MaskHelper;
use App\Models\Transaction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Widgets\Widget;

class TransactionInfoWidget extends Widget
{
    public Transaction $record;

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

    public function mount(Transaction $record): void
    {
        $this->record = $record;
        $this->record->with(['category', 'items']);
    }

    public function getInfolist(): Infolist
    {
        return Infolist::make()
            ->record($this->record)
            ->schema([
                TextEntry::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => __('forms.enums.transaction_type.' . $state)),
                TextEntry::make('category.name')
                    ->label('Categoria'),
                TextEntry::make('method')
                    ->label('Método')
                    ->formatStateUsing(fn ($state) => __('forms.enums.method.' . strtolower($state))),
                TextEntry::make('description')
                    ->label('Descrição'),
                TextEntry::make('amount')
                    ->label('Valor')->currency('BRL'),
                TextEntry::make('date')
                    ->label('Data')->since()
                    ->dateTime(),
                TextEntry::make('is_recurring')
                    ->label('Parcelado?')
                    ->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não'),
                TextEntry::make('recurrence_interval')
                    ->label('Parcelas pagas')
                    ->formatStateUsing(function () {
                        $paid = $this->record->items()->where('status', 'PAID')->count();
                        $total = $this->record->items()->count();
                        return "{$paid} de {$total}";
                    })
            ])
            ->columns(6);
    }
}
