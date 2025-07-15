<?php

namespace App\Filament\Resources;

use App\Enum\RolesEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Helpers\Filament\ActionHelper;
use App\Helpers\TranslateString;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $navigationGroup = 'Configuração';

    protected static ?string $model = User::class;

//    protected static ?string $navigationIcon = 'bi-people-fill';

    protected static ?string $pluralModelLabel = 'Usuários'; // Listagem

    protected static ?string $modelLabel = 'Usuário'; // Criação/Edição

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
                ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->disk('public')
                    ->circular()
                    ->stacked(),
                TextColumn::make('name')
                ->label('Nome'),
                TextColumn::make('email')
                    ->label('E-mail'),
                TextColumn::make('roles.name')
                    ->label('Perfil')
                    ->formatStateUsing(function (Model $record) {
                        $role = $record->roles->first();
                        return $role ? RolesEnum::getLabel($role->name) : '-';
                    })

            ])
            ->filters([
                //
            ])
            ->actions([
                Impersonate::make(),
                ActionHelper::makeSlideOver(
                    name: 'editUser',
                    form: [
                        TextInput::make('name')
                            ->label('Nome'),
                        TextInput::make('email')
                            ->label('E-mail'),
                        Select::make('roles')
                            ->label('Perfil')
                            ->relationship('roles', 'name', fn ($query) => $query->where('name', '!=', RolesEnum::ADMIN->value))
                            ->preload()
                            ->multiple(false)
                            ->required(),
                        FileUpload::make('avatar_url')
                            ->label('Avatar')
                            ->disk('public')
                            ->directory('avatars')
                            ->image()
                            ->imageEditor()
                    ],
                    modalHeading: 'Editar usuário',
                    fillForm: fn ($record) => [
                        'name' => $record->name,
                        'email' => $record->email,
                        'avatar_url' => $record->avatar_url
                    ]
                ),
            ])
            ->recordUrl(null)
            ->recordAction('editUser')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createUser',
                    form: [
                        TextInput::make('name')->label('Nome')->required(),
                        TextInput::make('email')->label('E-mail')->email()->required(),
                        FileUpload::make('avatar_url')
                            ->label('Avatar')
                            ->disk('public')
                            ->directory('avatars')
                            ->image()
                            ->imageEditor(),
                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->required()
                            ->minLength(6),
                        TextInput::make('password_confirmation')
                            ->label('Confirmar Senha')
                            ->password()
                            ->required()
                            ->same('password'),
                        Select::make('roles')
                            ->label('Perfil')
                            ->relationship('roles', 'name', fn ($query) => $query->where('name', '!=', RolesEnum::ADMIN->name))
                            ->preload()
                            ->getOptionLabelUsing(fn ($value): ?string => RolesEnum::getLabel($value))
                            ->multiple(false)
                            ->required()
                    ],
                    modalHeading: 'Novo usuário',
                    label: 'Criar',
                    action: function (array $data) {
                        $data['password'] = bcrypt($data['password']);
                        unset($data['password_confirmation']);

                        \App\Models\User::create($data);

                        Notification::make()
                            ->title('Usuário criado')
                            ->body('A nooa usuário foi cadastrado com sucesso.')
                            ->success()
                            ->send();
                    }),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('roles');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
