<?php

namespace App\Filament\Widgets;

use App\Models\Card;
use App\Models\TransactionItem;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Support\Colors\Color;
use Saade\FilamentFullCalendar\Actions\ViewAction;
use Saade\FilamentFullCalendar\Widgets\Concerns\InteractsWithEvents;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;

class CalendarWidget extends FullCalendarWidget
{
    use InteractsWithEvents;

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
                )
                ->disabled(function (TransactionItem $record) {}),
            Actions\DeleteAction::make(),
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $transactionEvents = TransactionItem::query()
            ->whereHas('transaction', function ($q) {
                $q->where('method', '!=', 'CARD');
            })
            ->whereBetween('due_date', [$fetchInfo['start'], $fetchInfo['end']])
            ->get()
            ->map(
                fn (TransactionItem $item) => [
                    'id' => $item->id,
                    'color' => $this->colorStatus($item->status),
                    'title' => $item->transaction->description ?? '-',
                    'start' => $item->due_date,
                    'end' => $item->due_date,
                    'due_date' => $item->due_date,
                    'amount' => $item->amount,
                    'shouldOpenUrlInNewTab' => true
                ]
            )
            ->all();

        $cardDueEvents = Card::query()
            ->with(['items', 'items.transaction.account.bank'])
            ->whereNotNull('due_date')
            ->get()
            ->flatMap(function ($card) use ($fetchInfo) {
                $events = [];
                $start = \Carbon\Carbon::parse($fetchInfo['start'])->copy()->startOfMonth();
                $end = \Carbon\Carbon::parse($fetchInfo['end'])->copy()->endOfMonth();

                $current = $start->copy();

                while ($current->lte($end)) {
                    $dueDay = (int) $card->due_date;
                    $dueDate = $current->copy()->day(min($dueDay, $current->daysInMonth));

                    // Itens do mês do vencimento
                    $itemsOfMonth = $card->transactions
                        ->flatMap->items
                        ->filter(fn ($item) => \Carbon\Carbon::parse($item->due_date)->isSameMonth($dueDate) &&
                            \Carbon\Carbon::parse($item->due_date)->between($start, $end));

                    $monthlyAmount = $itemsOfMonth->sum('amount');

                    $allPaid = $itemsOfMonth->isNotEmpty() && $itemsOfMonth->every(fn ($item) => $item->status === 'PAID');
                    $color = $allPaid ? 'PAID' : 'DEBIT';

                    if ($dueDate->between($start, $end)) {
                        $events[] = [
                            'id' => 'card-due-' . $card->id . '-' . $dueDate->format('Y-m-d'),
                            'title' => $card->name,
                            'start' => $dueDate->toDateString(),
                            'end' => $dueDate->toDateString(),
                            'color' => $this->colorStatus($color),
                            'amount' => $monthlyAmount,
                            'shouldOpenUrlInNewTab' => false,
                        ];
                    }

                    $current->addMonth();
                }

                return $events;
            })
            ->all();

        return array_merge($transactionEvents, $cardDueEvents);
    }

    public function getFormSchema(): array
    {
        return [
            Grid::make()
                ->schema([
                    TextInput::make('amount')
                        ->label(__('forms.widgets.amount'))
                        ->currencyMask(thousandSeparator: '.',decimalSeparator: ',', precision: 2)
                        ->prefix('R$')
                        ->required(),
                    DatePicker::make('due_date')
                        ->label(__('forms.widgets.due_date'))
                        ->required(),
                    DatePicker::make('payment_date')
                        ->label(__('forms.widgets.payment_date'))
                        ->reactive()
                        ->required(fn ($get) => $get('status') !== 'PENDING'),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'PAID' => __('forms.widgets.paid') ,
                            'SCHEDULED' => __('forms.widgets.scheduled') ,
                            'DEBIT' => __('forms.widgets.debit') ,
                            'PENDING' => __('forms.widgets.pending'),
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
            ->modalHeading(fn (TransactionItem $record) => $record->transaction->description ?? '-')
            ->modal(true)
            ->modalFooterActions(fn (ViewAction $viewAction) =>[
                EditAction::make()
                    ->modalHeading(fn (TransactionItem $record) => $record->transaction->description ?? '-')
                    ->icon('heroicon-m-pencil')
                    ->modal(true)
                    ->form([
                    Grid::make()
                        ->schema([
                            TextInput::make('amount')
                                ->label(__('forms.widgets.amount'))
                                ->currencyMask(thousandSeparator: '.',decimalSeparator: ',', precision: 2)
                                ->prefix('R$')
                                ->required(),
                            DatePicker::make('due_date')
                                ->label(__('forms.widgets.due_date'))
                                ->required(),
                            DatePicker::make('payment_date')
                                ->label(__('forms.widgets.payment_date'))
                                ->reactive()
                                ->required(fn ($get) => $get('status') !== 'PENDING'),
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'PAID' => __('forms.widgets.paid') ,
                                    'SCHEDULED' => __('forms.widgets.scheduled') ,
                                    'DEBIT' => __('forms.widgets.debit') ,
                                    'PENDING' => __('forms.widgets.pending'),
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
                ])
                    ->after(function () {
                        $this->refreshRecords();
                    }),
                DeleteAction::make()
                    ->modalHeading(fn (TransactionItem $record) => $record->transaction->description ?? '-')
                    ->icon('heroicon-m-trash')
                    ->modal(true)
                    ->after(function () {
                        $this->refreshRecords();
                    })
            ]);
    }

    public function eventDidMount(): string
    {
        return <<<JS
        function({ event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }){
    const amountFormatted = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2
        }).format(event.extendedProps.amount / 100);
            el.setAttribute("x-tooltip", "tooltip");
            el.setAttribute("x-data", "{ " +
             "tooltip: '"+event.title+" : "+amountFormatted+"' }");
        }
    JS;
    }

    private function colorStatus(string $status): string
    {
        return match ($status){
            'PAID' => 'green',
            'SCHEDULED' => 'warning',
            'DEBIT' => 'blue',
            default => 'gray',
        };

    }
}
