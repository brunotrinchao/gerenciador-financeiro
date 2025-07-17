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
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class TransactionItemResource extends Resource
{
    protected static ?string $model = TransactionItem::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return __('system.labels.account_payable_receivable');
    }

    public static function getNavigationLabel(): string
    {
        return __('system.labels.account_payable_receivable');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction.description')
                    ->label(__('forms.columns.description'))
                    ->searchable(),
                TextColumn::make('transaction.category.name')
                    ->label(__('forms.columns.category'))
                    ->searchable(),
                TextColumn::make('transaction.method')
                    ->label(__('forms.columns.method'))
                    ->searchable()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'CASH' => __('forms.enums.method.cash'),
                        'ACCOUNT' => __('forms.enums.method.account'),
                        'CARD' => __('forms.enums.method.card'),
                    }),
                TextColumn::make('installment_number')
                    ->label(__('forms.columns.installments'))
                    ->formatStateUsing(fn ($state, $record) => (
                    !$record->transaction || $record->transaction->recurrence_interval == 1
                        ? __('forms.enums.installments.cash')
                        : $state
                    )),
                TextColumn::make('amount')
                    ->label(__('forms.columns.amount'))
                    ->sortable()
                    ->currency('BRL'),
                TextColumn::make('due_date')
                    ->label(__('forms.columns.due_date'))
                    ->sortable()
                    ->date('d/m/Y'),
                TextColumn::make('payment_date')
                    ->label(__('forms.columns.payment_date'))
                    ->sortable()
                    ->date('d/m/Y'),
                TextColumn::make('status')
                    ->label(__('forms.columns.status'))
                    ->sortable()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'PAID' => __('forms.enums.status.paid'),
                        'SCHEDULED' => __('forms.enums.status.scheduled'),
                        'DEBIT' => __('forms.enums.status.debit'),
                        'PENDING' => __('forms.enums.status.pending'),
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'PAID' => 'success',
                        'SCHEDULED' => 'warning',
                        'DEBIT' => 'info',
                        'PENDING' => 'gray',
                    }),
            ])
            ->striped()
            ->filters([
                DateRangeFilter::make('due_date')
                    ->label(__('forms.filters.period'))
                    ->startDate(Carbon::now()->startOfMonth())
                    ->endDate(Carbon::now()->endOfMonth())
                    ->withIndicator()
                    ->useRangeLabels()
                    ->autoApply(),
            ])
            ->actions([
                Action::make('editTransactionItem')
                    ->form([
                        TextInput::make('amount')
                            ->label(__('forms.columns.amount'))
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->prefix('R$')
                            ->required(),
                        DatePicker::make('due_date')
                            ->label(__('forms.columns.due_date'))
                            ->required(),
                        DatePicker::make('payment_date')
                            ->label(__('forms.columns.payment_date'))
                            ->reactive()
                            ->required(fn ($get) => $get('status') !== 'PENDING'),
                        Select::make('status')
                            ->label(__('forms.columns.status'))
                            ->options([
                                'PENDING' => __('forms.enums.status.pending'),
                                'PAID' => __('forms.enums.status.paid'),
                                'SCHEDULED' => __('forms.enums.status.scheduled'),
                                'DEBIT' => __('forms.enums.status.debit'),
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
                    ->modalHeading(__('forms.actions.edit'))
                    ->modalButton(__('forms.actions.save_changes'))
                    ->label(__('forms.actions.edit'))
                    ->icon('heroicon-m-pencil')
                    ->fillForm(fn ($record) => [
                        'amount' => $record->amount,
                        'due_date' => $record->due_date,
                        'payment_date' => $record->payment_date,
                        'status' => $record->status,
                    ])
                    ->action(fn (array $data, $record) => $record->update($data))
                    ->slideOver(true),

                Action::make('log')
                    ->label(__('forms.actions.log'))
                    ->icon('heroicon-m-document-text')
                    ->modalHeading(__('forms.actions.log_history'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('forms.actions.close'))
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
                    ->label(__('forms.actions.export'))
                    ->color(Color::Blue)
                    ->icon('zondicon-download')
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
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

