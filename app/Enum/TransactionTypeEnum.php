<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum TransactionTypeEnum: string implements HasLabel
{
    case INCOME = 'INCOME';
    case EXPENSE = 'EXPENSE';
    case TRANSFER = 'TRANSFER';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INCOME => __('forms.enums.transaction_type.INCOME'),
            self::EXPENSE => __('forms.enums.transaction_type.EXPENSE'),
            self::TRANSFER => __('forms.enums.transaction_type.TRANSFER'),
        };
    }
}
