<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Enum\TransactionTypeEnum;
use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
use Illuminate\Database\Eloquent\Builder;

class ListTransactions extends ListRecords
{
    use HasToggleableTable;

    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make(),
            'Receita' => Tab::make()->modifyQueryUsing(function (Builder $query) {
                $query->where('type', TransactionTypeEnum::INCOME->name);
            }),
            'Despesa' => Tab::make()->modifyQueryUsing(function (Builder $query) {
                $query->where('type', TransactionTypeEnum::EXPENSE->name);
            }),
            'Transferência' => Tab::make()->modifyQueryUsing(function (Builder $query) {
                $query->where('type', TransactionTypeEnum::TRANSFER->name);
            })
        ];
    }
}
