<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankResource\Pages;
use App\Filament\Resources\BankResource\RelationManagers;
use App\Helpers\Filament\ActionHelper;
use App\Models\Bank;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankResource extends Resource
{
    protected static ?string $navigationGroup = 'Configuração';
    protected static ?string $model = Bank::class;

//    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralModelLabel = 'Bancos'; // Listagem

    protected static ?string $modelLabel = 'Banco'; // Criação/Edição

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->label('Nome')
                    ->maxLength(255),
                TextInput::make('code')
                    ->required()
                    ->label('Código')
                    ->numeric()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome'),
                TextColumn::make('code')
                    ->label('Código')
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editBank',
                    form: [
                        TextInput::make('name')
                        ->required()
                        ->label('Nome')
                        ->maxLength(255),
                        TextInput::make('code')
                            ->required()
                            ->label('Código')
                            ->numeric()
                    ],
                    modalHeading: 'Editar cartão',
                    label: 'Editar',
                    fillForm: fn ($record) => [
                        'name'      => $record->name,
                        'code'    => $record->code,
                    ]
                )
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(null)
            ->recordAction('editBank')
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createBank',
                    form: [
                        TextInput::make('name')
                            ->required()
                            ->label('Nome')
                            ->maxLength(255),
                        TextInput::make('code')
                            ->required()
                            ->label('Código')
                            ->numeric()
                    ],
                    modalHeading: 'Novo banco',
                    label: 'Criar',
                    action: function (array $data, Action $action) {
                        $bank= Bank::where('name', $data['name'])->first();
                        if ($bank->count() > 0) {
                            Notification::make()
                                ->title('Banco já existe')
                                ->body("Já existe uma banco '{$bank->name}' cadastrado.")
                                ->danger()
                                ->send();

                            $action->cancel();
                            return;
                        }

                        Bank::create($data);

                        Notification::make()
                            ->title('Banco criada')
                            ->body('A nova banco foi cadastrada com sucesso.')
                            ->success()
                            ->send();
                    }
                ),
            ]);;
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
            'index' => Pages\ListBanks::route('/'),
            'create' => Pages\CreateBank::route('/create'),
            'edit' => Pages\EditBank::route('/{record}/edit'),
        ];
    }
}
