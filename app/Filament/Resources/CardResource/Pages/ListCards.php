<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use App\Filament\Widgets\InstallmentEvolutionChart;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;


class ListCards extends ListRecords
{
    use HasToggleableTable;
    protected static string $resource = CardResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            InstallmentEvolutionChart::class,
        ];
    }
}
