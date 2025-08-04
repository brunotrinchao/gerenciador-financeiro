<?php

namespace App\Filament\Resources;

use App\Enum\RolesEnum;
use App\Filament\Resources\FamilyResource\Pages;
use App\Filament\Resources\FamilyResource\RelationManagers;
use App\Helpers\ColumnFormatter;
use App\Helpers\Filament\ActionHelper;
use App\Models\Family;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FamilyResource extends Resource
{
    protected static ?string $model = Family::class;

    protected static ?int $navigationSort = 60;

    public static function getNavigationGroup(): ?string
    {
        return __('system.labels.settings');
    }

    public static function getModelLabel(): string
    {
        return 'Famílias';
    }

    public static function getNavigationLabel(): string
    {
        return 'Famílias';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Famílias';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        $livewire = $table->getLivewire();

        return $table
            ->columns(
                $livewire->isGridLayout()
                    ? static::getGridTableColumns()
                    : static::getListTableColumns()
            )
            ->contentGrid(
                fn () => $livewire->isListLayout()
                    ? null
                    : [
                        'md' => 2,
                        'lg' => 3,
                        'xl' => 4,
                    ]
            )
            ->filters([
                TrashedFilter::make()
                    ->visible(fn () => auth()->user()?->hasRole(RolesEnum::SUPER->name)),
                Filter::make('filter')
                    ->label(__('forms.columns.filter'))
                    ->form([
                        Select::make('status')
                            ->label(__('forms.columns.status'))
                            ->options([
                                'CLIENT' => 'Cliente',
                                'DEMO' => 'Demostração',
                                'TEST' => 'Teste',
                            ])
                    ])
            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editFamily',
                    form: [
                        Select::make('type')
                            ->label(__('forms.forms.type'))
                            ->options([
                                'CLIENT' => 'Cliente',
                                'DEMO' => 'Demostração',
                                'TEST' => 'Teste',
                            ])
                            ->reactive()
                            ->required(),
                        TextInput::make('name')
                            ->label(__('forms.forms.name'))
                            ->required(),
                    ],
                    modalHeading: __('forms.modal_headings.edit_transaction'),
                    label: __('forms.buttons.edit'),
                    fillForm: fn ($record) => [
                        'name'                => $record->name,
                        'type'                => $record->type,
                        'status'                => $record->status,
                    ]
                )
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
            ])
            ->checkIfRecordIsSelectableUsing(
                function (Family $record) {
                    return !$record?->trashed();
                }
            )
            ->recordUrl(null)
            ->recordAction('editFamily')
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createFamily',
                    form: [
                        Select::make('type')
                            ->label(__('forms.forms.type'))
                            ->options([
                                'CLIENT' => 'Cliente',
                                'DEMO' => 'Demostração',
                                'TEST' => 'Teste',
                            ])
                            ->reactive()
                            ->required(),
                        TextInput::make('name')
                            ->label(__('forms.forms.name'))
                            ->required(),
                    ],
                    modalHeading: __('forms.actions.new_category'),
                    label: __('forms.actions.create'),
                    action: function (array $data) {

                        Family::create($data);

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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilies::route('/'),
            'create' => Pages\CreateFamily::route('/create'),
            'edit' => Pages\EditFamily::route('/{record}/edit'),
        ];
    }

    public static function getListTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label(__('forms.forms.name'))
                ->searchable(),
            TextColumn::make('type')
                ->label(__('forms.columns.type'))
                ->badge()
                ->sortable()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'CLIENT' => 'Cliente',
                    'DEMO' => 'Demostração',
                    'TEST' => 'Teste',
                }),
            TextColumn::make('status')
                ->label(__('forms.columns.status'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'ATI' => 'success',
                    'CAN' => 'danger',
                    'INA' => 'gray',
                })
                ->sortable()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'ATI' => 'Ativo',
                    'CAN' => 'Cancelado',
                    'INA' => 'Inativo',
                }),
        ];
    }

    public static function getGridTableColumns(): array
    {
        return [
            Split::make([
                TextColumn::make('name')
                    ->formatStateUsing(ColumnFormatter::labelValue(__('forms.forms.name')))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('forms.columns.type'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'CLIENT' => 'Cliente',
                        'DEMO' => 'Demostração',
                        'TEST' => 'Teste',
                    }),
                TextColumn::make('status')
                    ->label(__('forms.columns.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ATI' => 'success',
                        'CAN' => 'danger',
                        'INA' => 'gray',
                    })
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ATI' => 'Ativo',
                        'CAN' => 'Cancelado',
                        'INA' => 'Inativo',
                    }),
            ])
        ];

    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole(RolesEnum::SUPER->name);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole(RolesEnum::SUPER->name);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasRole(RolesEnum::SUPER->name);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole(RolesEnum::SUPER->name);
    }
}
