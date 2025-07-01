<?php

namespace App\Filament\Resources\BrandCardResource\Pages;

use App\Filament\Resources\BrandCardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBrandCard extends EditRecord
{
    protected static string $resource = BrandCardResource::class;

    protected function getRedirectUrl(): string
    {
        return parent::getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
