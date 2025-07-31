<?php

namespace App\Filament\Resources;

use App\Enum\RolesEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Helpers\ColumnFormatter;
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
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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

                        $randomPassword = Str::random(10);
                        $data['password'] = Hash::make($randomPassword);

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


                        Mail::to($user->email)->send(new WelcomeToSystemCreateUser($user, $randomPassword));
                    })
                    ->requiresConfirmation(),
            ]);
    }

    public static function getListTableColumns(): array
    {
        return [
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
        ];
    }
    public static function getGridTableColumns(): array
    {
        return [
            Split::make([
                // Parte esquerda: avatar
                ImageColumn::make('avatar_url')
                    ->label(__('forms.forms.avatar'))
                    ->disk('public')
                    ->circular()
                    ->extraAttributes(['class' => 'w-12 h-12']), // opcional para controlar tamanho
                // Parte direita: nome + informações abaixo
                Stack::make([
                    TextColumn::make('name')
                        ->weight(FontWeight::Bold)
                        ->searchable()
                        ->formatStateUsing(ColumnFormatter::labelValue(__('forms.forms.name'))),
                    TextColumn::make('email')
                        ->icon('heroicon-m-envelope')
                        ->fontFamily(FontFamily::Mono)
                        ->iconColor('primary')
                        ->searchable()
                        ->formatStateUsing(ColumnFormatter::labelValue(__('forms.forms.email'))),
                    TextColumn::make('email_verified_at')
                        ->label('Email Verified')
                        ->dateTime('d/m/Y H:i'),
                    TextColumn::make('roles.name')
                        ->label(__('forms.forms.role'))
                        ->formatStateUsing(function (Model $record) {
                            $role = $record->roles->first();
                            return $role ? RolesEnum::getLabel($role->name) : '-';
                        }),
                ]),
            ]),
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

