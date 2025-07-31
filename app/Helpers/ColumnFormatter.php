<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\HtmlString;

class ColumnFormatter
{
    public static function labelValue(string $label): \Closure
    {
        return fn ($state) => new HtmlString('<b>'.$label . ':</b> ' . $state);
    }

    public static function money(string $label): \Closure
    {
        return fn ($state) => new HtmlString('<b>'.$label . ':</b> R$ ' . number_format($state / 100, 2, ',', '.'));
    }

    public static function enum(string $label, string $prefix): \Closure
    {
        return fn ($state) => new HtmlString('<b>'.$label . ':</b> ' . __($prefix . $state));
    }

    public static function date(string $label): \Closure
    {
        return fn ($state) => new HtmlString('<b>'.$label . ':</b> ' .  Carbon::parse($state)->format('d/m/Y'));
    }
}
