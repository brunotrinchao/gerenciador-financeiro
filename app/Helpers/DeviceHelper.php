<?php

namespace App\Helpers;

use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;

class DeviceHelper
{
    public static function isMobile(): bool
    {
        $agent = request()->header('User-Agent') ?? '';

        return preg_match('/Mobile|Android|Silk\/|Kindle|BlackBerry|Opera Mini|Opera Mobi/i', $agent) === 1;
    }

    public static function getTableColumns (array $columns): array|Split
    {
        if(self::isMobile()){
            $columns = Split::make([
                Stack::make([
                    $columns
                ])
            ]);
        }

        return $columns;
    }
}
