<?php

namespace App\Filament\Resources\ActionLogResource\Pages;

use App\Filament\Resources\ActionLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActionLog extends EditRecord
{
    protected static string $resource = ActionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\DeleteAction::make(),
        ];
    }
}
