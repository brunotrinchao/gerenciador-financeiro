<?php

namespace App\Filament\Resources\BrandCardResource\Pages;

use App\Filament\Resources\BrandCardResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBrandCard extends CreateRecord
{
    protected static string $resource = BrandCardResource::class;


    protected function getRedirectUrl(): string
    {
        return parent::getResource()::getUrl('index');
    }
}
