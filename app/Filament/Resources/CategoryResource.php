<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationGroup = 'Financeiro';

//    protected static ?string $navigationIcon = 'heroicon-o-bank';
    protected static ?string $pluralModelLabel = 'Categorias'; // Listagem

    protected static ?string $modelLabel = 'Categoria'; // Criação/Edição

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->label('Nome')
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('edit')
                    ->label('Editar')
                    ->modalWidth(MaxWidth::Medium)
                ->form([
                    Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                ]),
                Tables\Actions\DeleteAction::make('delete')
                ->label('Excluir')
                    ->before(function ($record, $action) {
                        if ($record->transactions()->exists()) {
                            Notification::make()
                                ->color('warning')
                                ->warning()
                                ->title('Ação permitida')
                                ->body("A categoria '{$record->name}' possui transações associadas e não pode ser deletado.")
                                ->send();
                            $action->cancel();
                            return false;
                        }
                        return true;
                    })
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make()
//                        ->before(function ($records,$action) {
//                            foreach ($records as $record) {
//                                if ($record->transactions()->exists()) {
//                                    $action->failureNotificationTitle('Erro ao deletar');
//                                    $action->cancel();
//                                    return;
//                                }
//                            }
//                        }),
//                ]),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
