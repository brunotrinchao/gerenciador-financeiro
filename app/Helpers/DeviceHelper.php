<?php

namespace App\Helpers;

use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;

class DeviceHelper
{
    public static function isMobile(): bool
    {
        $agent = request()->header('User-Agent') ?? '';

        return preg_match('/Mobile|Android|Silk\/|Kindle|BlackBerry|Opera Mini|Opera Mobi/i', $agent) === 1;
    }

    public static function getTableColumns(array $columns): array
    {
        if(DeviceHelper::isMobile()) {
            $columnsWithLabelsForMobile = array_map(function ($column) {
                if ($column instanceof TextColumn) {
                    $label = $column->getLabel();

                    $column->formatStateUsing(fn($state) => "<span class='md:hidden text-stone-400 font-bold'>{$label}: </span>$state")
                        ->html();
                }

                return $column;
            }, $columns);

            return [
                Split::make($columnsWithLabelsForMobile)->from('md'),
            ];
        }

        return $columns;
    }
}
