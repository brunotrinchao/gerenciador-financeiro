<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
use Illuminate\Database\Eloquent\Builder;

class ListCategories extends ListRecords
{
    use HasToggleableTable;

    protected static string $resource = CategoryResource::class;

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
