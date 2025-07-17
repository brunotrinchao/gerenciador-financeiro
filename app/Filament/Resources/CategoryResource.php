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

    public static function getNavigationGroup(): ?string
    {
        return __('system.labels.finance');
    }

    public static function getModelLabel(): string
    {
        return __('system.labels.category');
    }

    public static function getNavigationLabel(): string
    {
        return __('system.labels.categories');
    }

    public static function getPluralLabel(): ?string
    {
        return __('system.labels.categories');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('forms.columns.name'))
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('forms.columns.name')),
            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editCategory',
                    form: [
                        Forms\Components\TextInput::make('name')
                            ->label(__('forms.columns.name'))
                            ->required(),
                    ],
                    modalHeading: __('forms.actions.edit_category'),
                    label: __('forms.actions.edit'),
                    fillForm: fn($record) => ['name' => $record->name]
                ),
                Tables\Actions\DeleteAction::make('delete')
                    ->label(__('forms.actions.delete'))
                    ->before(function ($record, $action) {
                        $totalTransactions = $record->transactions()->count();
                        if ($totalTransactions > 0) {
                            Notification::make()
                                ->color('warning')
                                ->warning()
                                ->title(__('forms.notifications.delete_not_allowed'))
                                ->body(__('forms.notifications.category_has_transactions', [
                                    'name' => $record->name,
                                    'total' => $totalTransactions
                                ]))
                                ->send();

                            $action->cancel();
                            return false;
                        }
                        return true;
                    }),
            ])
            ->recordUrl(null)
            ->recordAction('editCategory')
            ->bulkActions([])
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createCategory',
                    form: [
                        Forms\Components\TextInput::make('name')
                            ->label(__('forms.columns.name'))
                            ->required(),
                    ],
                    modalHeading: __('forms.actions.new_category'),
                    label: __('forms.actions.create'),
                    action: function (array $data, Action $action) {
                        if (Category::where('name', $data['name'])->exists()) {
                            Notification::make()
                                ->title(__('forms.notifications.category_exists'))
                                ->body(__('forms.notifications.category_exists_msg', ['name' => $data['name']]))
                                ->danger()
                                ->send();

                            $action->cancel();
                            return;
                        }

                        Category::create($data);

                        Notification::make()
                            ->title(__('forms.notifications.category_created'))
                            ->body(__('forms.notifications.category_created_msg'))
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}

