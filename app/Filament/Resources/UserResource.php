<?php

namespace App\Filament\Resources;

use App\Enum\RolesEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Helpers\Filament\ActionHelper;
use App\Helpers\TranslateString;
use App\Mail\OverdueTransactionItemsMail;
use App\Mail\WelcomeToSystemCreateUser;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Auth\VerifyEmail;
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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationGroup(): ?string
    {
        return __('system.labels.settings');
    }

    public static function getModelLabel(): string
    {
        return __('system.labels.user');
    }

    public static function getNavigationLabel(): string
    {
        return __('system.labels.users');
    }

    public static function getPluralLabel(): ?string
    {
        return __('system.labels.users');
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
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label(__('forms.forms.avatar'))
                    ->disk('public')
                    ->circular()
                    ->stacked(),
                TextColumn::make('name')
                    ->label(__('forms.forms.name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('forms.forms.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->dateTime('d m Y H:i'),
                TextColumn::make('roles.name')
                    ->label(__('forms.forms.role'))
                    ->formatStateUsing(function (Model $record) {
                        $role = $record->roles->first();
                        return $role ? RolesEnum::getLabel($role->name) : '-';
                    }),
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
                            ->label(__('forms.forms.name')),
                        TextInput::make('email')
                            ->label(__('forms.forms.email')),
                        Select::make('roles')
                            ->label(__('forms.forms.role'))
                            ->options(
                                collect(RolesEnum::cases())
                                    ->filter(function ($role) {
                                        // Oculta ADMIN se o usuário logado não for ADMIN
                                        return $role !== RolesEnum::ADMIN->value || auth()->user()->hasRole(RolesEnum::ADMIN->value);
                                    })
                                    ->mapWithKeys(fn ($role) => [$role->name => $role->value])
                                    ->toArray()
                            )
                            ->required(),
                        FileUpload::make('avatar_url')
                            ->label(__('forms.forms.avatar'))
                            ->disk('public')
                            ->directory('avatars')
                            ->image()
                            ->imageEditor()
                    ],
                    modalHeading: __('forms.modal_headings.edit_user'),
                    fillForm: function ($record) {

                        return [
                            'name' => $record->name,
                            'email' => $record->email,
                            'avatar_url' => $record->avatar_url,
                            'roles' => auth()->user()->getRoleNames()[0],
                        ];
                    }
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
                        TextInput::make('name')->label(__('forms.forms.name'))->required(),
                        TextInput::make('email')->label(__('forms.forms.email'))->email()->required(),
                        FileUpload::make('avatar_url')
                            ->label(__('forms.forms.avatar'))
                            ->disk('public')
                            ->directory('avatars')
                            ->image()
                            ->imageEditor(),
                        TextInput::make('password')
                            ->label(__('forms.forms.password'))
                            ->password()
                            ->required()
                            ->minLength(6)
                            ->same('password_confirmation')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                        TextInput::make('password_confirmation')
                            ->label(__('forms.forms.password_confirmation'))
                            ->password()
                            ->required()
                            ->dehydrated(false),
                        Select::make('roles')
                            ->label(__('forms.forms.role'))
                            ->options(
                                collect(RolesEnum::cases())
                                    ->filter(fn ($role) => $role !== RolesEnum::ADMIN)
                                    ->mapWithKeys(fn ($role) => [$role->name => $role->value])
                                    ->toArray()
                            )
                            ->required()
                    ],
                    modalHeading: __('system.modal_headings.create_user'),
                    label: __('forms.buttons.create'),
                    action: function (array $data) {
                        if (User::where('email', $data['email'])->exists()) {
                            Notification::make()
                                ->title(__('forms.notifications.user_create_error_title'))
                                ->body(__('forms.notifications.user_create_error_body_email_exists'))
                                ->danger()
                                ->send();
                            return;
                        }

                        $user = User::create($data);
                        if (isset($data['roles'])) {
                            $role = \Spatie\Permission\Models\Role::where('name', $data['roles'])->first();
                            if ($role) {
                                $user->assignRole($role);
                            }
                        }
                        Notification::make()
                            ->title(__('forms.notifications.user_created_title'))
                            ->body(__('forms.notifications.user_created_body'))
                            ->success()
                            ->send();

                        Mail::to($user->email)->send(new WelcomeToSystemCreateUser($user));
//                        $notification = new VerifyEmail();
//                        $notification->url = filament()->getVerifyEmailUrl($user);
//                        $user->notify($notification);
                    })
                    ->requiresConfirmation(),
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

