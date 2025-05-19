<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
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
//        $moneyMask = RawJs::make('
//            function($input){
//
//                let value = $input.replace(/\\D/g, \'\');
//                value = (value / 100).toFixed(2);
//                value = value.replace(\'.\', \',\');
//                value = value.replace(/\\B(?=(\\d{3})+(?!\\d))/g, \'.\');
//
//                return value;
//        }');

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
            ->columns([
                TextColumn::make('bank.name')
                    ->label('Banco'),
                TextColumn::make('type')
                    ->label('Tipo'),
                TextColumn::make('balance')
                    ->label('Saldo')
                    ->currency('BRL'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
