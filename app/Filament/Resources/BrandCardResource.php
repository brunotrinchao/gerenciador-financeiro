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
        return $form->schema([]); // vazio, pois usa SlideOver
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('brand')
                    ->label(__('forms.columns.brand'))
                    ->height(25),

                TextColumn::make('name')
                    ->label(__('forms.columns.name'))
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('Slug'),
            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editBrand',
                    form: [
                        TextInput::make('name')
                            ->required()
                            ->label(__('forms.columns.name'))
                            ->live(onBlur: true),
//                            ->afterStateUpdated(fn(?string $state, Set $set) => $set('slug', Str::slug($state ?? ''))),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->visible(false),

                        FileUpload::make('brand')
                            ->label(__('forms.columns.brand'))
                            ->disk('public')
                            ->directory('brand_card')
                            ->image()
                            ->imageEditor(),
                    ],
                    modalHeading: __('forms.actions.edit_flag'),
                    label: __('forms.actions.edit'),
                    fillForm: fn($record) => [
                        'name'  => $record->name,
                        'slug'  => $record->slug,
                        'brand' => $record->brand,
                    ]
                ),

                Tables\Actions\DeleteAction::make('delete')
                    ->label(__('forms.actions.delete'))
                    ->before(function ($record, $action) {
                        $totalCards = $record->cards()->count();
                        if ($totalCards > 0) {
                            Notification::make()
                                ->color('warning')
                                ->warning()
                                ->title(__('forms.notifications.delete_blocked_title'))
                                ->body(__('forms.notifications.delete_blocked_body', [
                                    'name' => $record->name,
                                    'count' => $totalCards,
                                ]))
                                ->send();
                            $action->cancel();
                            return false;
                        }
                        return true;
                    }),
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
                            ->required()
                            ->label(__('forms.columns.name'))
                            ->live(onBlur: true)
                            ->unique(BrandCard::class, 'name'),
//                            ->afterStateUpdated(fn(?string $state, Set $set) => $set('slug', Str::slug($state ?? ''))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->visible(false),

                        FileUpload::make('brand')
                            ->label(__('forms.columns.brand'))
                            ->disk('public')
                            ->directory('brand_card')
                            ->image()
                            ->imageEditor(),
                    ],
                    modalHeading: __('forms.actions.new_flag'),
                    label: __('forms.actions.create'),
                    action: function (array $data, Action $action) {
                        if (BrandCard::where('name', $data['name'])->exists()) {
                            Notification::make()
                                ->title(__('forms.notifications.flag_exists'))
                                ->body(__('forms.notifications.flag_exists_body', ['name' => $data['name']]))
                                ->danger()
                                ->send();

                            $action->cancel();
                            return;
                        }

                        $data['slug'] = Str::slug($data['name']);

                        BrandCard::create($data);

                        Notification::make()
                            ->title(__('forms.notifications.flag_created'))
                            ->body(__('forms.notifications.flag_created_body'))
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
            'index' => Pages\ListBrandCards::route('/'),
            'create' => Pages\CreateBrandCard::route('/create'),
            'edit' => Pages\EditBrandCard::route('/{record}/edit'),
        ];
    }
}

