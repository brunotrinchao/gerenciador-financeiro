<?php

namespace App\Filament\Exports;

use App\Models\TransactionItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TransactionItemExporter extends Exporter
{
    protected static ?string $model = TransactionItem::class;

    public static function getColumns(): array
    {
        return [
             ExportColumn::make('id')
                 ->label('ID'),

            ExportColumn::make('transaction.user.name')
                ->label('Usuário'),

            ExportColumn::make('transaction.category.name')
                ->label('Categoria'),

            ExportColumn::make('transaction.type')
                ->label('Tipo')
                ->formatStateUsing(fn (string $state) => $state === 'INCOME' ? 'Receita' : 'Despesa'),

            ExportColumn::make('installment_number')
                ->label('Parcela'),

            ExportColumn::make('transaction.description')
                ->label('Descrição'),

            ExportColumn::make('amount')
                ->label('Valor'),

            ExportColumn::make('due_date')
                ->label('Vencimento'),

            ExportColumn::make('payment_date')
                ->label('Pagamento'),

            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn (string $state) => match ($state) {
                    'PAID' => 'Pago',
                    'SCHEDULED' => 'Agendado',
                    'DEBIT' => 'Débito automático',
                    'PENDING' => 'Pendente',
                }),

            ExportColumn::make('transaction.method')
                ->label('Forma de pagamento')
                ->formatStateUsing(fn (?string $state) => match ($state) {
                    'CASH' => 'Dinheiro',
                    'ACCOUNT' => 'Conta',
                    'CARD' => 'Cartão',
                    default => 'Não definido',
                }),

            ExportColumn::make('transaction.account.name')
                ->label('Conta')
                ->default('-'),

            ExportColumn::make('transaction.card.name')
                ->label('Cartão')
                ->default('-'),

            ExportColumn::make('transaction.date')
                ->label('Data da transação'),

            ExportColumn::make('transaction.is_recurring')
                ->label('Recorrente')
                ->formatStateUsing(fn (bool $state) => $state ? 'Sim' : 'Não'),

            ExportColumn::make('transaction.recurrence_interval')
                ->label('Intervalo recorrente')
                ->default('-'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your transaction item export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
