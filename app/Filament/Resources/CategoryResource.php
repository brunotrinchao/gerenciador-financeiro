<?php

namespace App\Filament\Resources;

use App\Enum\RolesEnum;
use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Helpers\ColumnFormatter;
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
use Filament\Tables\Columns\Layout\Stack;
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
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        $livewire = $table->getLivewire();

        return $table
            ->columns($livewire->isGridLayout()
                ? static::getGridTableColumns()
                : static::getListTableColumns())
            ->contentGrid(
                fn () => $livewire->isListLayout()
                    ? null
                    : [
                        'md' => 2,
                        'lg' => 3,
                        'xl' => 4,
                    ]
            )
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
                    fillForm: fn($record) => ['name' => $record->name],
                    visible: fn ($record) => $record->family_id === (int) auth()->user()->family_id || auth()->user()->hasRole(RolesEnum::SUPER->name)
                ),
                Tables\Actions\DeleteAction::make('delete')
                    ->label(__('forms.actions.delete'))
                    ->visible(function ($record) {
                        return $record?->family_id === (int) auth()->user()?->family_id || auth()->user()->hasRole(RolesEnum::SUPER->name);

                    })
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
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createCategory',
                    form: [
                        Forms\Components\TextInput::make('name')
                            ->label(__('forms.columns.name'))
                            ->unique(Category::class, 'name')
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
                    },
                    visible: true,

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

    public static function getGridTableColumns(): array
    {
        return [
            Stack::make([
                TextColumn::make('name')
                    ->formatStateUsing(ColumnFormatter::labelValue(__('forms.columns.name')))
                    ->searchable(),
                ])
        ];
    }

    public static function getListTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('forms.columns.name'))
                ->searchable(),
        ];
    }
}

