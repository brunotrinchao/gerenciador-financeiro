<?php

namespace App\Enum;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MethodPaymentEnum: string implements HasLabel, HasDescription, HasIcon
{
    case CARD = 'CARD';
    case ACCOUNT = 'ACCOUNT';
    case CASH = 'CASH';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CARD => __('forms.enums.method.card'),
            self::ACCOUNT => __('forms.enums.method.account'),
            self::CASH => __('forms.enums.method.cash'),
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::CARD => 'Pagamento com cartão de crédito.',
            self::ACCOUNT => 'Pagamento debitado em conta.',
            self::CASH => 'Pagamento realizado com dinheiro.',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::CARD => 'heroicon-m-card',
            self::ACCOUNT => 'heroicon-m-bank',
            self::CASH => 'heroicon-m-cash',
        };
    }
}
