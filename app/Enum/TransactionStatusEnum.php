<?php

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TransactionStatusEnum: string implements HasLabel, HasIcon, HasColor
{
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case SCHEDULED = 'SCHEDULED';
    case DEBIT = 'DEBIT';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => __('forms.enums.status.pending'),
            self::PAID => __('forms.enums.status.paid'),
            self::SCHEDULED => __('forms.enums.status.scheduled'),
            self::DEBIT => __('forms.enums.status.debit'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-m-pencil',
            self::PAID => 'heroicon-m-eye',
            self::SCHEDULED => 'heroicon-m-check',
            self::DEBIT => 'heroicon-m-x-mark',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PAID => 'success',
            self::SCHEDULED => 'warning',
            self::DEBIT => 'info',
            self::PENDING => 'gray',
        };
    }
}
