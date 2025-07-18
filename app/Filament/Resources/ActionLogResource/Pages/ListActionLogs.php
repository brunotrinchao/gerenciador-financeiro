<?php

namespace App\Filament\Resources\ActionLogResource\Pages;

use App\Filament\Resources\ActionLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActionLogs extends ListRecords
{
    protected static string $resource = ActionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
}
