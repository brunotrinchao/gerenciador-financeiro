<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandCardResource\Pages;
use App\Filament\Resources\BrandCardResource\RelationManagers;
use App\Helpers\Filament\ActionHelper;
use App\Models\BrandCard;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class BrandCardResource extends Resource
{
    protected static ?string $model = BrandCard::class;

    public static function getNavigationGroup(): ?string
    {
        return __('system.labels.finance');
    }

    public static function getModelLabel(): string
    {
        return __('system.labels.flag');
    }

    public static function getNavigationLabel(): string
    {
        return __('system.labels.flags');
    }

    public static function getPluralLabel(): ?string
    {
        return __('system.labels.flags');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                ActionHelper::makeSlideOver(
                    name: 'editBrand',
                    form: [
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
                    ],
                    modalHeading: 'Editar bandeira',
                    label: 'Editar',
                    fillForm: fn ($record) => [
                        'name'  => $record->name,
                        'slug'  => $record->slug,
                        'brand' => $record->brand,
                    ]
                ),
                Tables\Actions\DeleteAction::make('delete')
                    ->label('Excluir')
                    ->before(function ($record, $action) {
                        $totalCards = $record->cards()->count();
                        if ($totalCards > 0) {
                            Notification::make()
                                ->color('warning')
                                ->warning()
                                ->title('Ação permitida')
                                ->body("A bandeira '{$record->name}' possui {$totalCards} cartões associados e não pode ser deletada.")
                                ->send();
                            $action->cancel();
                            return false;
                        }
                        return true;
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(null)
            ->recordAction('editBrand')
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createBrand',
                    form: [
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
                    ],
                    modalHeading: 'Nova bandeira',
                    label: 'Criar',
                    action: function (array $data, Action $action) {
                        if (BrandCard::where('name', $data['name'])->exists()) {
                            Notification::make()
                                ->title('Bandeira já existe')
                                ->body("Já existe uma bandeira '{$data['name']}' cadastrada.")
                                ->danger()
                                ->send();

                            $action->cancel();
                            return;
                        }

                        BrandCard::create($data);

                        Notification::make()
                            ->title('Bandeira criada')
                            ->body('A nova bandeira foi cadastrada com sucesso.')
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
            'index' => Pages\ListBrandCards::route('/'),
            'create' => Pages\CreateBrandCard::route('/create'),
            'edit' => Pages\EditBrandCard::route('/{record}/edit'),
        ];
    }
}
