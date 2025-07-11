<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Saade\FilamentFullCalendar\Actions\ViewAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;

class CalendarWidget extends FullCalendarWidget
{

    public Model | string | null $model = TransactionItem::class;

    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->hidden(),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->disabled()
            ->hidden(),
            Actions\EditAction::make()
                ->mountUsing(
                    function (TransactionItem $record, Form $form, array $arguments) {
                        $form->fill([
                            'color' => $this->colorStatus($record->status),
                            'title' => $record->transaction->description ?? '-',
                            'due_date' => $arguments['event']['start'] ?? $record->due_date,
                            'payment_date' => $record->payment_date,
                            'status' => $record->status,
                            'amount' => $record->amount
                        ]);
                    }
                ),
            Actions\DeleteAction::make(),
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return TransactionItem::query()
            ->with('transaction')
            ->whereBetween('due_date', [$fetchInfo['start'], $fetchInfo['end']])
            ->get()
            ->map(
                fn (TransactionItem $item) => [
                    'id' => $item->id,
                    'color' => $this->colorStatus($item->status),
                    'title' => $item->transaction->description ?? '-',
                    'start' => $item->due_date,
                    'end' => $item->due_date,
                    'shouldOpenUrlInNewTab' => true
                ]
            )
            ->all();
    }

    public function getFormSchema(): array
    {
        return [
            Grid::make()
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
                        ->label('Data de pagemento')
                        ->reactive()
                        ->required(fn ($get) => $get('status') !== 'PENDING'),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'PENDING' => 'Pendente',
                            'PAID' => 'Pago',
                            'SCHEDULED' => 'Agendado',
                            'DEBIT' => 'Débito automático',
                        ])
                        ->default('PENDING')
                        ->required(fn ($get) => filled($get('payment_date')))
                        ->rules([
                            fn ($get) => filled($get('payment_date')) && $get('status') === 'PENDING'
                                ? 'not_in:PENDING'
                                : null,
                        ])
                        ->reactive()
                ])
        ];
    }

    protected function viewAction(): \Filament\Actions\Action
    {
        return ViewAction::make()
            ->modalFooterActions(fn (ViewAction $viewAction) =>[
                EditAction::make(),
                DeleteAction::make()
            ]);
    }

    public function eventDidMount(): string
    {
        return <<<JS
        function({ event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }){
            el.setAttribute("x-tooltip", "tooltip");
            el.setAttribute("x-data", "{ tooltip: '"+event.title+"' }");
        }
    JS;
    }

    private function colorStatus(string $status): string
    {
        return match ($status){
            'PAID' => 'green',
            'SCHEDULED' => 'blue',
            'DEBIT' => 'blue',
            default => 'gray',
        };

    }
}
