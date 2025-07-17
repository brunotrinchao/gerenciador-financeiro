<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use App\Filament\Resources\PermissionResource\RelationManagers;
use App\Helpers\TranslateString;
use App\Models\Permission;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    public static function getNavigationGroup(): ?string
    {
        return __('system.labels.settings');
    }

    public static function getModelLabel(): string
    {
        return __('system.labels.permission');
    }

    public static function getNavigationLabel(): string
    {
        return __('system.labels.permissions');
    }

    public static function getPluralLabel(): ?string
    {
        return __('system.labels.permissions');
    }
    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Chave (ex: view users)')
                ->required()
                ->unique(Permission::class, 'name', ignoreRecord: true)
                ->maxLength(100),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Permissão')
                    ->formatStateUsing(fn ($state) => TranslateString::formatRolePermission($state)),
                TextColumn::make('created_at')->label('Criada em')->dateTime('d/m/Y H:i'),
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
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
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
