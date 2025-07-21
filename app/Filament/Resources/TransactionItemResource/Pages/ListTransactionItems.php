<?php

namespace App\Filament\Resources\TransactionItemResource\Pages;

use App\Filament\Resources\TransactionItemResource;
use App\Filament\Widgets\CountWidget;
use App\Filament\Widgets\TransactionItemResourceStats;
use App\Helpers\DeviceHelper;
use App\Models\Card;
use App\Models\TransactionItem;
use Carbon\Carbon;
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
use Illuminate\Support\Facades\DB;

class ListTransactionItems extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = TransactionItemResource::class;


    public function mount(): void
    {
        parent::mount();

        if (request()->has('method')) {
            $methods = (array) request()->query('method');

            // Aplica o filtro manualmente
            $this->tableFilters['method'] = $methods;
        }
    }
    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getTableQuery();

        $query->whereHas('transaction', function ($q) {
            $q->whereIn('method', ['CASH', 'ACCOUNT']);
        });

        return $query;
    }

//    protected function getHeaderWidgets(): array
//    {
//        if (DeviceHelper::isMobile()) {
//            return [];
//        }
//        return [
//            CountWidget::class
//        ];
//    }


    public static function canCreate(): bool
    {
        return false;
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

    protected function isCardMethod(array $methods): bool
    {
        return in_array('CARD', $methods ?? []);
    }

}
