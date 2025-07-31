<?php

namespace App\Providers\Filament;

use App\Enum\RolesEnum;
use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Filament\Resources\TransactionItemResource;
use App\Filament\Widgets\CountChartWidget;
use App\Filament\Widgets\CountWidget;
use App\Filament\Widgets\PerCardChartWidget;
use App\Filament\Widgets\PerCategoryChartWidget;
use App\Filament\Widgets\UpcomingTransactionsWidget;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Hydrat\TableLayoutToggle\TableLayoutTogglePlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->profile(isSimple: false)
            ->login()
            ->passwordReset()
//            ->emailVerification()
//            ->brandLogo(asset('images/logo.svg'))
//            ->brandName('Gerenciador financeiro')
            ->defaultThemeMode(ThemeMode::Light)
            ->font('Exo')
            ->colors([
                'primary' => Color::Sky,
                'danger' => Color::Red,
                'gray' => Color::Zinc,
                'info' => Color::Blue,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'stone' => Color::Stone,
                'purple' => Color::Purple,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn() => auth()->user()->name)
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle')
            ])
//            ->navigationItems($this->NavehationItem())
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(__('system.labels.finance'))
                    ->icon('heroicon-o-banknotes'),
                NavigationGroup::make()
                    ->label(__('system.labels.settings'))
                    ->icon('heroicon-o-lock-closed')
            ])
            ->topNavigation()
            ->databaseNotifications()
            ->maxContentWidth(MaxWidth::Full)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                TableLayoutTogglePlugin::make(),
                \Hasnayeen\Themes\ThemesPlugin::make(),
                EnvironmentIndicatorPlugin::make()
                    ->visible(fn () => auth()->user()?->hasRole(RolesEnum::ADMIN->name))
                    ->color(fn () => match (app()->environment()) {
                        'production' => Color::Lime,
                        'staging' => Color::Orange,
                        default => Color::Blue,
                    })
                    ->showBorder(false),
                FilamentEditProfilePlugin::make()
                    ->slug('profile')
                    ->setTitle('Perfil')
                    ->setNavigationLabel('Perfil')
                    ->setNavigationGroup('Group Profile')
                    ->setIcon('heroicon-o-user')
                    ->setSort(10)
//                    ->canAccess(fn () => auth()->user()->id === auth()->id())
                    ->shouldRegisterNavigation(false)
                    ->shouldShowEmailForm()
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldShowSanctumTokens()
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars',
                        rules: 'mimes:jpeg,png|max:1024'
                    ),
                FilamentFullCalendarPlugin::make()
                ->selectable()
                ->editable()
                ->config([
//                    'dayMaxEvents' => 3
                ])
            ]);
    }

    private function NavehationItem(){
        return [
            NavigationItem::make('Contas a Pagar - Cartão')
                ->label('Contas a Pagar - Cartão de Crédito')
                ->url('/transaction-items?method[0]=CARD')
                ->icon('heroicon-o-credit-card')
                ->group(__('system.labels.account_payable_receivable')),

            NavigationItem::make('Contas a Pagar - Conta/Dinheiro')
                ->label('Contas a Pagar - Conta / Dinheiro')
                ->url('/transaction-items?method[0]=ACCOUNT&method[1]=CASH')
                ->icon('heroicon-o-banknotes')
                ->group(__('system.labels.account_payable_receivable')),
        ];
    }
}
