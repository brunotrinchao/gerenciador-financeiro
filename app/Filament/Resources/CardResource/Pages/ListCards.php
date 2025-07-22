<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use App\Filament\Widgets\InstallmentEvolutionChart;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;


class ListCards extends ListRecords
{
    protected static string $resource = CardResource::class;



    protected function getHeaderWidgets(): array
    {
        return [
            InstallmentEvolutionChart::class,
        ];
    }
}
