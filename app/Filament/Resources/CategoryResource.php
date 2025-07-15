<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Helpers\Filament\ActionHelper;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
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
use function Livewire\before;

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
                ActionHelper::makeSlideOver(
                    name: 'editCategory',
                    form: [
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                    ],
                    modalHeading: 'Editar categoria',
                    label: 'Editar',
                    fillForm: fn ($record) => [
                        'name' => $record->name
                    ]
                ),
                Tables\Actions\DeleteAction::make('delete')
                ->label('Excluir')
                    ->before(function ($record, $action) {
                        $totalTransactions = $record->transactions()->count();
                        if ($totalTransactions > 0) {
                            Notification::make()
                                ->color('warning')
                                ->warning()
                                ->title('Ação permitida')
                                ->body("A categoria '{$record->name}' possui {$totalTransactions} transações associadas e não pode ser deletada.")
                                ->send();
                            $action->cancel();
                            return false;
                        }
                        return true;
                    })
            ])
            ->recordUrl(null)
            ->recordAction('editCategory')
            ->bulkActions([
            ])
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createCategory',
                    form: [
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                    ],
                    modalHeading: 'Nova categoria',
                    label: 'Criar',
                    action: function (array $data, Action $action) {
                        if (Category::where('name', $data['name'])->exists()) {
                            Notification::make()
                                ->title('Categoria já existe')
                                ->body("Já existe uma  categoria '{$data['name']}' cadastrada.")
                                ->danger()
                                ->send();

                            $action->cancel();
                            return;
                        }

                        Category::create($data);

                        Notification::make()
                            ->title('Categoria criada')
                            ->body('A nova categoria foi cadastrada com sucesso.')
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
