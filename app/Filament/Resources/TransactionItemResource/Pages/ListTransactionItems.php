<?php

namespace App\Filament\Resources\TransactionItemResource\Pages;

use App\Filament\Resources\TransactionItemResource;
use App\Filament\Widgets\CountWidget;
use App\Filament\Widgets\TransactionItemResourceStats;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;

class ListTransactionItems extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = TransactionItemResource::class;


    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        return parent::getTableQuery()
            ->orderBy('due_date', 'ASC');              // Depois por data
    }

    protected function getHeaderWidgets(): array
    {
        if ($this->isMobile()) {
            return [];
        }
        return [
            CountWidget::class
        ];
    }


    public static function canCreate(): bool
    {
        return false;
    }

    protected function isMobile(): bool
    {
        $agent = request()->header('User-Agent') ?? '';
        return preg_match('/Mobile|Android|Silk\/|Kindle|BlackBerry|Opera Mini|Opera Mobi/i', $agent);
    }

    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make(),
            'Pendente' => Tab::make()
                ->modifyQueryUsing(function (Builder $query) {
                    $query->where('status', '=', 'PENDING');
//                $query->whereHas('transaction', function ($q){
//                    $q->where('type', 'INCOME');
//                });
            }),
            'Agendado/Débito' => Tab::make()
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereIn('status', ['SCHEDULED','DEBIT']);
            }),
            'Pago' => Tab::make()
                ->modifyQueryUsing(function (Builder $query) {
                    $query->where('status', '=', 'PAID');
                }),
        ];
    }
}
