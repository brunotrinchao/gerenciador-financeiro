<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandCardResource\Pages;
use App\Filament\Resources\BrandCardResource\RelationManagers;
use App\Models\BrandCard;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class BrandCardResource extends Resource
{
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $model = BrandCard::class;

//    protected static ?string $navigationIcon = 'mdi-card-multiple-outline';
    protected static ?string $pluralModelLabel = 'Bandeiras'; // Listagem

    protected static ?string $modelLabel = 'Bandeira'; // Criação/Edição

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function(string $state, Set $set){
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('slug')
                    ->label('Slug')
                    ->disabled(true)
                    ->visible(false),
                FileUpload::make('brand')
                    ->label('Bandeira')
                    ->disk('public')
                    ->directory('brand_card')
                    ->image()
                    ->imageEditor()
                    ->imageResizeMode('contain')
                    ->imageEditorEmptyFillColor('#ffffff')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('brand')
                    ->label('Bandeira')
                    ->height(25),
                TextColumn::make('name')
                    ->label('Nome'),
                TextColumn::make('slug')
                    ->label('Slug'),
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

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('ADMIN');
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrandCards::route('/'),
            'create' => Pages\CreateBrandCard::route('/create'),
            'edit' => Pages\EditBrandCard::route('/{record}/edit'),
        ];
    }
}
