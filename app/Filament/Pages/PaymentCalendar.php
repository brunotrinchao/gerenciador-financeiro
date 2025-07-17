<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CalendarWidget;
use Filament\Pages\Page;

class PaymentCalendar extends Page
{

    protected static string $view = 'filament.pages.payment-calendar';
    protected static ?string $navigationIcon = 'heroicon-s-calendar-days';

    public static function getNavigationLabel(): string
    {
        return __('system.labels.payment_calendar');
    }

//    public static function getPluralLabel(): string
//    {
//        return __('labels.payment_calendar');
//    }
    public function getHeaderWidgets(): array
    {
        return [
            CalendarWidget::class
        ];
    }
}
