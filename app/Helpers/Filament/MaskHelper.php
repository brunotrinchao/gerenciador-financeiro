<?php

namespace App\Helpers\Filament;
use Filament\Support\RawJs;

class MaskHelper
{

    public static function maskMoney(): RawJs
    {
        return RawJs::make(<<<'JS'
        function ($input) {
            let isNegative = $input.includes('-');
            let value = $input.replace(/[^0-9]/g, '');
            if (value.length === 0) return '';

            value = (parseFloat(value) / 100).toFixed(2);
            value = value.replace('.', ',');
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            return (isNegative ? '-' : '') + value;
        }
    JS);
    }

    public static function covertIntToReal(int $value, bool $prefix = true): string{
        return 'R$ ' . number_format($value / 100, 2, ',', '.');
    }
}
