<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum RecurrenceTypeEnum: string implements HasLabel
{
    case DAILY = 'DAILY';
    case WEEKLY = 'WEEKLY';
    case MONTHLY = 'MONTHLY';
    case YEARLY = 'YEARLY';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DAILY => __('forms.enums.recurrence_type.DAILY'),
            self::WEEKLY => __('forms.enums.recurrence_type.WEEKLY'),
            self::MONTHLY => __('forms.enums.recurrence_type.MONTHLY'),
            self::YEARLY => __('forms.enums.recurrence_type.YEARLY'),
        };
    }
}
