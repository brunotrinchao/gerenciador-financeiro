<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Filament\Widgets\TransactionInfoWidget;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;


    protected static ?string $breadcrumb = 'Parcelas';

    protected static ?string $navigationLabel = 'Parcelas';

    protected static ?string $title = 'Parcelas';

    protected function getHeaderActions(): array
    {
        return [
//            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return $this->record->description ?? parent::getTitle();
    }

    #[On('refreshProducts')]
    public function refreshForm(): void
    {
        // Recarrega os dados da transação (ownerRecord)
        $this->fillForm();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransactionInfoWidget::class,
        ];
    }
}
