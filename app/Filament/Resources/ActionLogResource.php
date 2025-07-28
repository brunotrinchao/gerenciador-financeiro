<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActionLogResource\Pages;
use App\Filament\Resources\ActionLogResource\RelationManagers;
use App\Models\ActionLog;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class ActionLogResource extends Resource
{
    protected static ?string $model = ActionLog::class;

    public static function getNavigationGroup(): ?string
    {
        return __('system.labels.settings');
    }

    public static function getModelLabel(): string
    {
        return __('system.labels.log');
    }

    public static function getNavigationLabel(): string
    {
        return __('system.labels.logs');
    }

    public static function getPluralLabel(): ?string
    {
        return __('system.labels.logs');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')
                    ->label(__('forms.columns.user'))
                    ->formatStateUsing(fn($state) => User::find($state)?->name ?? __('forms.misc.unknown'))
                    ->searchable(),
                TextColumn::make('action')->label(__('forms.columns.action')),
                TextColumn::make('model_type')->label(__('forms.columns.model')),
                TextColumn::make('model_id')->label(__('forms.columns.model_id')),
                TextColumn::make('created_at')->label(__('forms.columns.date'))->dateTime(),
            ])
            ->actions([
                Action::make('viewLog')
                    ->label(__('forms.actions.view'))
                    ->modalHeading(__('forms.actions.log_details'))
                    ->slideOver()
                    ->modalSubmitAction(false)
                    ->form([
                        Grid::make()->columns(2)->schema([
                            TextInput::make('user_id')->label(__('forms.columns.user'))->disabled(),
                            TextInput::make('action')->label(__('forms.columns.action'))->disabled(),
                            TextInput::make('model_type')->label(__('forms.columns.model'))->disabled(),
                            TextInput::make('model_id')->label(__('forms.columns.model_id'))->disabled(),
                            TextInput::make('created_at')->label(__('forms.columns.date'))->disabled(),

                            Textarea::make('old_values')
                                ->label(__('forms.columns.old_values'))
                                ->disabled()
                                ->columnSpan(2)
                                ->rows(5),

                            Textarea::make('new_values')
                                ->label(__('forms.columns.new_values'))
                                ->disabled()
                                ->columnSpan(2)
                                ->rows(5),

                            Textarea::make('description')
                                ->label(__('forms.columns.description'))
                                ->disabled()
                                ->columnSpan(2)
                                ->rows(5),
                        ])
                    ])
                    ->fillForm(function ($record) {
                        $user = User::find($record->user_id);
                        return [
                            'user_id' => $user?->name ?? __('forms.misc.unknown'),
                            'action' => $record->action,
                            'model_type' => $record->model_type,
                            'model_id' => $record->model_id,
                            'old_values' => json_encode($record->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                            'new_values' => json_encode($record->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                            'description' => $record->description,
                            'created_at' => $record->created_at->format('d/m/Y H:i:s'),
                        ];
                    }),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                ->label(__('forms.columns.created_at'))
            ])
            ->recordUrl(null)
            ->recordAction('viewLog');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('ADMIN');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActionLogs::route('/'),
            'create' => Pages\CreateActionLog::route('/create'),
            'edit' => Pages\EditActionLog::route('/{record}/edit'),
        ];
    }
}

