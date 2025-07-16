<?php

namespace App\Filament\Resources;

use App\Enum\RolesEnum;
use App\Filament\Exports\TransactionItemExporter;
use App\Filament\Resources\TransactionItemResource\Pages;
use App\Filament\Resources\TransactionItemResource\RelationManagers;
use App\Models\Account;
use App\Models\ActionLog;
use App\Models\Card;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFilters;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\CanPoll;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionItemResource extends Resource
{

    protected static ?string $model = TransactionItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $pluralModelLabel = 'Contas a pagar/receber';

    protected static ?string $modelLabel = 'Conta a pagar/receber';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                    TextColumn::make('transaction.description')
                        ->label('Descrição')
                        ->searchable(),
                    TextColumn::make('transaction.category.name')
                        ->label('Categoria')
                        ->searchable(),
                    TextColumn::make('transaction.method')
                        ->label('Método')
                        ->searchable()
                        ->formatStateUsing(function (string $state) {
                            return match ($state) {
                                'CASH' => 'Dinheiro',
                                'ACCOUNT' => 'Débito',
                                'CARD' => 'Cartão de crédito',
                            };
                        }),
                    TextColumn::make('installment_number')
                        ->label('Parcelas')
                        ->formatStateUsing(function ($state, $record) {
                            if (!$record->transaction || $record->transaction->recurrence_interval == 1) {
                                return 'À vista';
                            }
                            return $state;
                        })
                        ->alignLeft(),
                    TextColumn::make('amount')
                        ->label('Valor')
                        ->sortable()
                        ->currency('BRL'),
                    TextColumn::make('due_date')
                        ->label('Vencimento')
                        ->sortable()
                        ->date('d/m/Y'),
                    TextColumn::make('payment_date')
                        ->label('Data de pagamento')
                        ->sortable()
                        ->date('d/m/Y'),
                    TextColumn::make('status')
                        ->label('Status')
                        ->sortable()
                        ->formatStateUsing(function (string $state) {
                            return match ($state) {
                                'PAID' => 'Pago',
                                'SCHEDULED' => 'Agendado',
                                'DEBIT' => 'Débito automático',
                                'PENDING' => 'Pendente',
                            };
                        })
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'PAID' => 'success',
                            'SCHEDULED' => 'warning',
                            'DEBIT' => 'info',
                            'PENDING' => 'gray',
                        })
            ])
            ->striped()
            ->filters([
                Filter::make('payment_date_range')
                    ->label('Período')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Data início')
                            ->default(Carbon::now()->startOfMonth()),
                        DatePicker::make('end_date')
                            ->label('Data fim')
                            ->default(Carbon::now()->endOfMonth()),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['start_date'], fn ($q) => $q->whereDate('due_date', '>=', $data['start_date']))
                            ->when($data['end_date'], fn ($q) => $q->whereDate('due_date', '<=', $data['end_date']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['start_date'] ?? null) {
                            $indicators[] = 'De ' . Carbon::parse($data['start_date'])->format('d/m/Y');
                        }

                        if ($data['end_date'] ?? null) {
                            $indicators[] = 'Até ' . Carbon::parse($data['end_date'])->format('d/m/Y');
                        }

                        return $indicators;
                })
            ])
            ->actions([
                Action::make('editTransactionItem')
                    ->form([
                        TextInput::make('amount')
                            ->label('Valor')
                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',', precision: 2)
                            ->prefix('R$')
                            ->required(),
                        DatePicker::make('due_date')
                            ->label('Data de vencimento')
                            ->required(),
                        DatePicker::make('payment_date')
                            ->label('Data de pagemento')
                            ->reactive()
                            ->required(fn ($get) => $get('status') !== 'PENDING'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'PENDING' => 'Pendente',
                                'PAID' => 'Pago',
                                'SCHEDULED' => 'Agendado',
                                'DEBIT' => 'Débito automático',
                            ])
                            ->default('PENDING')
                            ->required(fn ($get) => filled($get('payment_date')))
                            ->rules([
                                fn ($get) => filled($get('payment_date')) && $get('status') === 'PENDING'
                                    ? 'not_in:PENDING'
                                    : null,
                            ])
                            ->reactive()
                    ])
                    ->modalHeading('Editar')
                    ->modalButton('Salvar alterações')
                    ->label('Editar')
                    ->icon('heroicon-m-pencil')
                    ->fillForm(fn ($record) => [
                        'amount' => $record->amount,
                        'due_date' => $record->due_date,
                        'payment_date' => $record->payment_date,
                        'status' => $record->status,
                    ])
                    ->action(function (array $data, $record) {
                        $record->update($data);
                    })
                    ->slideOver(true),
                Action::make('log')
                    ->label('Log')
                    ->icon('heroicon-m-document-text')
                    ->modalHeading('Histórico de Ações')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(fn ($record) => view('filament.actions.transaction-log', [
                        'logs' => ActionLog::where('model_type', \App\Models\TransactionItem::class)
                            ->where('model_id', $record->id)
                            ->latest()
                            ->get(),
                    ]))
                    ->visible(auth()->check() && auth()->user()->hasRole(RolesEnum::ADMIN->name))
                ->slideOver()
            ])
            ->recordUrl(null)
            ->recordAction('editTransactionItem')
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(TransactionItemExporter::class)
                    ->label('Exportar')
                    ->color(Color::Blue)
                ->icon('zondicon-download')
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactionItems::route('/'),
            'create' => Pages\CreateTransactionItem::route('/create'),
            'edit' => Pages\EditTransactionItem::route('/{record}/edit'),
        ];
    }
}
