<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers;
use App\Helpers\DeviceHelper;
use App\Helpers\Filament\ActionHelper;
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
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?string $model = Account::class;

//    protected static ?string $navigationIcon = 'heroicon-o-bank';
    protected static ?string $pluralModelLabel = 'Contas bancárias'; // Listagem

    protected static ?string $modelLabel = 'Conta bancária'; // Criação/Edição

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Select::make('type')
                    ->required()
                    ->label('Tipo')
                    ->options([
                        1 => 'Conta Corrente',
                        2 => 'Poupança'
                    ]),
                Select::make('bank_id')
                    ->label('Banco')
                    ->relationship('bank', 'name'),
                \Filament\Forms\Components\TextInput::make('balance')
                    ->currencyMask(thousandSeparator: '.',decimalSeparator: ',', precision: 2)
                    ->prefix('R$')

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(DeviceHelper::getTableColumns([
                TextColumn::make('bank.name')
                    ->label('Banco'),
                TextColumn::make('type')
                    ->label('Tipo'),
                TextColumn::make('balance')
                    ->label('Saldo')
                    ->currency('BRL')
            ]))
            ->filters([
                //
            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editAccount',
                    form: [
                        Select::make('type')
                            ->required()
                            ->label('Tipo')
                            ->options([
                                1 => 'Conta Corrente',
                                2 => 'Poupança'
                            ]),
                        Select::make('bank_id')
                            ->required()
                            ->label('Banco')
                            ->relationship('bank', 'name'),
                        \Filament\Forms\Components\TextInput::make('balance')
                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',', precision: 2)
                            ->prefix('R$')

                    ],
                    modalHeading: 'Editar conta bancária',
                    label: 'Editar',
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
                            ->label('Tipo')
                            ->options([
                                1 => 'Conta Corrente',
                                2 => 'Poupança'
                            ]),
                        Select::make('bank_id')
                            ->required()
                            ->label('Banco')
                            ->relationship('bank', 'name'),
                        \Filament\Forms\Components\TextInput::make('balance')
                            ->currencyMask(thousandSeparator: '.',decimalSeparator: ',', precision: 2)
                            ->prefix('R$')
                        ->default('0,0')

                    ],
                    modalHeading: 'Nova conta bancária',
                    label: 'Criar',
                    action: function (array $data, Action $action) {
                        $data['user_id'] = auth()->id();
                        Account::create($data);

                        Notification::make()
                            ->title('Conta bancária criada')
                            ->body('A nova conta bancária foi cadastrada com sucesso.')
                            ->success()
                            ->send();
                    }
                ),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
