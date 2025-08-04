<?php

namespace App\Filament\Resources\FamilyResource\Pages;

use App\Filament\Resources\FamilyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;

class ListFamilies extends ListRecords
{
    use HasToggleableTable;
    protected static string $resource = FamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
