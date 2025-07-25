<?php

namespace App\Providers;

use App\Models\TransactionItem;
use App\Notifications\UpcomingTransactionItemNotification;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if(env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['pt_BR','en'])
                ->displayLocale('pt_BR')
                ->labels([
                    'pt_BR' => 'Português (BR)',
                ])
                ->flags([
                    'pt_BR' => asset('images/flags/br.png'),
                    'en' => asset('images/flags/us.png'),
                ])
                ->flagsOnly();
        });
    }
}
