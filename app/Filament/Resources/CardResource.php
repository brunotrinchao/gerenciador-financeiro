<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Filament\Resources\CardResource\RelationManagers;
use App\Filament\Resources\TransactionResource\RelationManagers\ItemsRelationManager;
use App\Filament\Widgets\InstallmentEvolutionChart;
use App\Helpers\Filament\ActionHelper;
use App\Helpers\Filament\MaskHelper;
use App\Models\Bank;
use App\Models\Card;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    public static function getNavigationGroup(): ?string
    {
        return  __('system.labels.account_payable_receivable');
    }

    public static function getModelLabel(): string
    {
        return __('system.labels.credit_card');
    }

    public static function getNavigationLabel(): string
    {
        return __('system.labels.credits_card');
    }

    public static function getPluralLabel(): ?string
    {
        return __('system.labels.credits_card');
    }

    public static function form(Form $form): Form
    {
        $moneyMask = RawJs::make('function($input){
            let value = $input.replace(/\\D/g, \'\');
            value = (value / 100).toFixed(2);
            value = value.replace(\'.\', \',\');
            value = value.replace(/\\B(?=(\\d{3})+(?!\\d))/g, \'.\');
            return value;
        }');

        return $form->schema([
//            Select::make('bank_id')
//                ->label(__('forms.columns.bank'))
//                ->prefixIcon('phosphor-bank')
//                ->options(Bank::pluck('name', 'id'))
//                ->disabled(),
//            TextInput::make('name')
//                ->label(__('forms.columns.name'))
//                ->disabled(),
//            TextInput::make('number')
//                ->prefixIcon('heroicon-m-credit-card')
//                ->label(__('forms.columns.number'))
//                ->mask(RawJs::make(<<<'JS'
//                    $input.startsWith('34') || $input.startsWith('37') ? '9999 999999 99999' : '9999 9999 9999 9999'
//                JS))
//                ->disabled(),
//            Select::make('brand_id')
//                ->label(__('forms.columns.brand'))
//                ->searchable()
//                ->relationship('brand', 'name')
//                ->preload()
//                ->disabled(),
//            TextInput::make('due_date')
//                ->label(__('forms.columns.due_date'))
//                ->numeric()
//                ->minValue(1)
//                ->maxValue(31)
//                ->disabled(),
//            TextInput::make('limit')
//                ->label(__('forms.columns.limit'))
//                ->prefix('R$')
//                ->mask($moneyMask)
//                ->default(0)
//                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('brand.brand')
                    ->label(__('forms.columns.brand'))
                    ->height(30)
                    ->stacked(),
                TextColumn::make('name')->label(__('forms.columns.name'))
                    ->searchable(),
                TextColumn::make('number')->label(__('forms.columns.number')),
                TextColumn::make('bank.name')->label(__('forms.columns.bank')),
                TextColumn::make('limit')->label(__('forms.columns.limit'))->currency('BRL'),
                TextColumn::make('due_date')->label(__('forms.columns.due_date'))->alignCenter(),
            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editCard',
                    form: self::getFormSchema(),
                    modalHeading: __('forms.actions.edit_card'),
                    label: __('forms.actions.edit'),
                    fillForm: fn($record) => [
                        'bank_id' => $record->bank_id,
                        'name' => $record->name,
                        'number' => $record->number,
                        'brand_id' => $record->brand_id,
                        'due_date' => $record->due_date,
                        'limit' => $record->limit,
                    ]
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
//            ->recordUrl(null)
            ->recordAction('editCard')
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createCard',
                    form: self::getFormSchema(),
                    modalHeading: __('forms.actions.new_card'),
                    label: __('forms.actions.create'),
                    action: function (array $data, Action $action) {
                        $card = Card::where('name', $data['name'])
                            ->where('bank_id', $data['bank_id'])
                            ->where('brand_id', $data['brand_id'])
                            ->with(['bank', 'brand'])
                            ->first();

                        if ($card) {
                            Notification::make()
                                ->title(__('forms.notifications.card_exists'))
                                ->body(__('forms.notifications.card_exists_msg', [
                                    'name' => $card->name,
                                    'bank' => $card->bank->name,
                                    'brand' => $card->brand->name,
                                ]))
                                ->danger()
                                ->send();
                            $action->cancel();
                            return;
                        }

                        $data['limit'] = (float) str_replace(',', '.', str_replace('.', '', $data['limit']));
                        $data['user_id'] = auth()->id();

                        Card::create($data);

                        Notification::make()
                            ->title(__('forms.notifications.card_created'))
                            ->body(__('forms.notifications.card_created_msg'))
                            ->success()
                            ->send();
                    }
                ),
            ]);
    }

    protected static function getFormSchema(): array
    {
        return [
            Select::make('bank_id')
                ->required()
                ->label(__('forms.columns.bank'))
                ->prefixIcon('phosphor-bank')
                ->options(Bank::pluck('name', 'id')),
            TextInput::make('name')
                ->required()
                ->label(__('forms.columns.name')),
            TextInput::make('number')
                ->required()
                ->prefixIcon('heroicon-m-credit-card')
                ->label(__('forms.columns.number'))
                ->mask(RawJs::make(<<<'JS'
                    $input.startsWith('34') || $input.startsWith('37') ? '9999 999999 99999' : '9999 9999 9999 9999'
                JS)),
            Select::make('brand_id')
                ->required()
                ->label(__('forms.columns.brand'))
                ->searchable()
                ->relationship('brand', 'name')
                ->preload(),
            TextInput::make('due_date')
                ->required()
                ->label(__('forms.columns.due_date'))
                ->numeric()
                ->minValue(1)
                ->maxValue(31),
            TextInput::make('limit')
                ->label(__('forms.columns.limit'))
                ->prefix('R$')
                ->mask(MaskHelper::maskMoney())
                ->stripCharacters(',')
                ->numeric()
                ->default(0),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CardTransactionRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCards::route('/'),
            'create' => Pages\CreateCard::route('/create'),
            'edit' => Pages\EditCard::route('/{record}/edit'),
            'import-transactions' => Pages\ImportCardTransactions::route('/{record}/import-transactions'),
        ];
    }
}

