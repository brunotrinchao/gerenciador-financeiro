<?php

namespace App\Filament\Resources\BankResource\Pages;

use App\Filament\Resources\BankResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBanks extends ListRecords
{
    protected static string $resource = BankResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make(),
            'Padrão' => Tab::make()->modifyQueryUsing(function (Builder $query) {
                $query->whereNull('family_id');
            }),
            'Personalizadas' => Tab::make()->modifyQueryUsing(function (Builder $query) {
                $query->where('family_id', auth()->user()->family_id);
            })
        ];
    }
}
