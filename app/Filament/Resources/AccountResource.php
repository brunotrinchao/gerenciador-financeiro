<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers;
use App\Helpers\DeviceHelper;
use App\Helpers\Filament\ActionHelper;
use App\Helpers\Filament\MaskHelper;
use App\Helpers\TranslateString;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    public static function getNavigationGroup(): ?string
    {
        return __('system.labels.finance');
    }

    public static function getModelLabel(): string
    {
        return __('system.labels.account_bank');
    }

    public static function getNavigationLabel(): string
    {
        return __('system.labels.accounts_banks');
    }

    public static function getPluralLabel(): ?string
    {
        return __('system.labels.accounts_banks');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('type')
                ->required()
                ->label(__('forms.forms.type'))
                ->options([
                    1 => __('forms.forms.account_checking'), // nova chave de tradução sugerida
                    2 => __('forms.forms.account_savings'),
                ]),
            Select::make('bank_id')
                ->label(__('forms.forms.bank'))
                ->relationship('bank', 'name'),
            TextInput::make('balance')
                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                ->prefix('R$')
                ->label(__('forms.forms.balance')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bank.name')
                    ->label(__('forms.columns.bank')),
                TextColumn::make('type')
                    ->label(__('forms.columns.type'))
                    ->formatStateUsing(fn ($state) => TranslateString::getAccountType((int) $state)),
                TextInput::make('balance')
                    ->mask(MaskHelper::maskMoney())
                    ->stripCharacters(',')
                    ->numeric()
                    ->prefix('R$')
                    ->label(__('forms.columns.balance'))
                    ->color(fn (string $state) => $state < 0 ? 'danger' : 'success'),
            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editAccount',
                    form: [
                        Select::make('type')
                            ->required()
                            ->label(__('forms.forms.type'))
                            ->options([
                                1 => __('forms.forms.account_checking'),
                                2 => __('forms.forms.account_savings'),
                            ]),
                        Select::make('bank_id')
                            ->required()
                            ->label(__('forms.forms.bank'))
                            ->relationship('bank', 'name'),
                        TextInput::make('balance')
                            ->mask(MaskHelper::maskMoney())
                            ->stripCharacters(',')
                            ->numeric()
                            ->prefix('R$')
                            ->label(__('forms.forms.balance')),
                    ],
                    modalHeading: __('forms.forms.edit_bank_account'),
                    label: __('forms.forms.edit'),
                    fillForm: fn ($record) => [
                        'name'     => $record->name,
                        'bank_id'  => $record->bank_id,
                        'type'     => $record->type,
                        'balance'  => $record->balance,
                    ]
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(null)
            ->recordAction('editAccount')
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createAccount',
                    form: [
                        Select::make('type')
                            ->required()
                            ->label(__('forms.forms.type'))
                            ->options([
                                1 => __('forms.forms.account_checking'),
                                2 => __('forms.forms.account_savings'),
                            ]),
                        Select::make('bank_id')
                            ->required()
                            ->label(__('forms.forms.bank'))
                            ->relationship('bank', 'name'),
                        TextInput::make('balance')
                            ->mask(MaskHelper::maskMoney())
                            ->stripCharacters(',')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0)
                            ->label(__('forms.forms.balance')),
                    ],
                    modalHeading: __('forms.forms.new_bank_account'),
                    label: __('forms.forms.create'),
                    action: function (array $data, Action $action) {
                        $data['user_id'] = auth()->id();
                        $data['balance'] = (float) str_replace(['.', ','], ['', '.'], $data['balance']);

                        Account::create($data);

                        Notification::make()
                            ->title(__('forms.notifications.bank_account_created'))
                            ->body(__('forms.notifications.bank_account_success'))
                            ->success()
                            ->send();
                    }
                ),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }

}

