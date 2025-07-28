<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankResource\Pages;
use App\Filament\Resources\BankResource\RelationManagers;
use App\Helpers\Filament\ActionHelper;
use App\Models\Bank;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankResource extends Resource
{
    protected static ?string $model = Bank::class;

    public static function getNavigationGroup(): ?string
    {
        return __('system.labels.settings');
    }

    public static function getModelLabel(): string
    {
        return __('system.labels.bank');
    }

    public static function getNavigationLabel(): string
    {
        return __('system.labels.banks');
    }

    public static function getPluralLabel(): ?string
    {
        return __('system.labels.banks');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->label(__('forms.columns.name'))
                ->maxLength(255),

            TextInput::make('code')
                ->required()
                ->label(__('forms.columns.code'))
                ->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('forms.columns.name'))->searchable(),
                TextColumn::make('code')->label(__('forms.columns.code'))->searchable(),
            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editBank',
                    form: [
                        TextInput::make('name')
                            ->required()
                            ->label(__('forms.columns.name'))
                            ->maxLength(255),
                        TextInput::make('code')
                            ->required()
                            ->label(__('forms.columns.code'))
                            ->numeric(),
                    ],
                    modalHeading: __('forms.actions.edit_bank'),
                    label: __('forms.actions.edit'),
                    fillForm: fn ($record) => [
                        'name' => $record->name,
                        'code' => $record->code,
                    ]
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(null)
            ->recordAction('editBank')
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createBank',
                    form: [
                        TextInput::make('name')
                            ->required()
                            ->label(__('forms.columns.name'))
                            ->maxLength(255),
                        TextInput::make('code')
                            ->required()
                            ->label(__('forms.columns.code'))
                            ->numeric(),
                    ],
                    modalHeading: __('forms.actions.new_bank'),
                    label: __('forms.actions.create'),
                    action: function (array $data, Action $action) {
                        $bank = Bank::where('name', $data['name'])->first();

                        if ($bank) {
                            Notification::make()
                                ->title(__('forms.notifications.bank_exists'))
                                ->body(__('forms.notifications.bank_exists_body', ['name' => $bank->name]))
                                ->danger()
                                ->send();

                            $action->cancel();
                            return;
                        }

                        Bank::create($data);

                        Notification::make()
                            ->title(__('forms.notifications.bank_created'))
                            ->body(__('forms.notifications.bank_created_body'))
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
            'index' => Pages\ListBanks::route('/'),
            'create' => Pages\CreateBank::route('/create'),
            'edit' => Pages\EditBank::route('/{record}/edit'),
        ];
    }
}

