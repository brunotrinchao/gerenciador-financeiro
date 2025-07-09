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

class ActionLogResource extends Resource
{
    protected static ?string $model = ActionLog::class;

    protected static ?string $navigationGroup = 'Administração';

//    protected static ?string $navigationIcon = 'heroicon-o-bank';
    protected static ?string $pluralModelLabel = 'Logs'; // Listagem

    protected static ?string $modelLabel = 'Log'; // Criação/Edição


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')->label('Usuário')->formatStateUsing(function ($state) {
                    return User::where('id', '=', $state)->first()->name;
                }),
                TextColumn::make('action')->label('Ação'),
                TextColumn::make('model_type')->label('Modelo'),
                TextColumn::make('model_id')->label('ID do Modelo'),
                TextColumn::make('created_at')->label('Data')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('viewLog')
                    ->label('Visualizar')
                    ->modalHeading('Detalhes do Log')
                    ->slideOver()
                    ->modalSubmitAction(false)
                    ->form([
                        Grid::make()
                            ->schema([
                            TextInput::make('user_id')->label('Usuário')
                                ->disabled(),
                            TextInput::make('action')->label('Ação')
                                ->disabled(),
                            TextInput::make('model_type')->label('Modelo')
                                ->disabled(),
                            TextInput::make('model_id')->label('ID do Modelo')
                                ->disabled(),
                            TextInput::make('created_at')->label('Data')
                                ->disabled(),
                            Textarea::make('old_values')->label('Valores Antigos')
                                ->disabled()
                                ->columnSpan(2)
                                ->rows(5),
                            Textarea::make('new_values')->label('Valores Novos')
                                ->disabled()
                                ->columnSpan(2)
                                ->rows(5),
                            Textarea::make('description')->label('Descrição')
                                ->disabled()
                                ->columnSpan(2)
                                ->rows(5),
                        ])
                        ->columns(2)
                    ])
                    ->fillForm(function ($record) {
                        $user = User::find($record->user_id);
                        return [
                            'user_id' => $user?->name ?? 'Desconhecido',
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
            ->recordUrl(null)
            ->recordAction('viewLog');
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
            'index' => Pages\ListActionLogs::route('/'),
            'create' => Pages\CreateActionLog::route('/create'),
            'edit' => Pages\EditActionLog::route('/{record}/edit'),
        ];
    }
}
