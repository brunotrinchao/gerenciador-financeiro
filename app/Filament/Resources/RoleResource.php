<?php

namespace App\Filament\Resources;

use App\Enum\RolesEnum;
use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Helpers\TranslateString;
use App\Models\Permission;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoleResource extends Resource
{
    protected static ?string $navigationLabel = 'Perfil de acesso';
    protected static ?string $navigationGroup = 'Configuração';
    protected static ?string $model = Role::class;

//    protected static ?string $navigationIcon = 'eos-admin';

    protected static ?string $pluralModelLabel = 'Perfis de acesso'; // Listagem

    protected static ?string $modelLabel = 'Perfil de acesso'; // Criação/Edição

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole(RolesEnum::ADMIN->name);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                CheckboxList::make('permissions')
                    ->label('Permissões')
                    ->relationship('permissions', 'name')
                    ->options(function () {
                        return Permission::all()
                            ->pluck('name', 'id')
                            ->mapWithKeys(function ($value, $key) {
                                return [$key => TranslateString::formatRolePermission($value)];
                            })
                            ->toArray();
                    })
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Perfil')
                    ->badge()
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view users');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create users');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('edit users');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('delete users');
    }
}
