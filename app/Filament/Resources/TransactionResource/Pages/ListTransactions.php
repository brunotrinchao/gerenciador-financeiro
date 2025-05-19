<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make(),
            'Receita' => Tab::make()->modifyQueryUsing(function (Builder $query) {
                $query->where('type', 'INCOME');
            }),
            'Despesa' => Tab::make()->modifyQueryUsing(function (Builder $query) {
                $query->where('type', 'EXPENSE');
            })
        ];
    }
}
