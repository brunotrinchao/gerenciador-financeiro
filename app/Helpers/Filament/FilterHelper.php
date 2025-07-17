<?php

namespace App\Helpers\Filament;
class FilterHelper
{
    public static function formatFilter(array $filter): array
    {
        return [
            'startDate' => $filter['event_period']['start'],
            'endDate' => $filter['event_period']['end'],
        ];
    }
}
