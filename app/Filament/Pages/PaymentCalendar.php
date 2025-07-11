<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CalendarWidget;
use Filament\Pages\Page;

class PaymentCalendar extends Page
{

    protected static string $view = 'filament.pages.payment-calendar';
    protected static ?string $navigationIcon = 'heroicon-s-calendar-days';

    protected static ?string $pluralModelLabel = 'Calendário';

    protected static ?string $navigationLabel = 'Calendário';

    public function getHeaderWidgets(): array
    {
        return [
            CalendarWidget::class
        ];
    }
}
