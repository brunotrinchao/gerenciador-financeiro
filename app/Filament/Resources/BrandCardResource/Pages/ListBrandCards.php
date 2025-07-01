<?php

namespace App\Filament\Resources\BrandCardResource\Pages;

use App\Filament\Resources\BrandCardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBrandCards extends ListRecords
{
    protected static string $resource = BrandCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
