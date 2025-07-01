<?php

namespace App\Providers;

use App\Models\TransactionItem;
use App\Notifications\UpcomingTransactionItemNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class FilamentNotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (!app()->runningInConsole() && Auth::check() && Request::is('filament*')) {
            $user = Auth::user();

            $items = TransactionItem::query()
                ->whereDate('payment_date', '<=', now()->addDays(3)->toDateString())
                ->whereDate('payment_date', '>=', now()->toDateString())
                ->where('status', '!=', 'PAID')
                ->get();

            if ($items->isNotEmpty()) {
                if (!$user->unreadNotifications()
                    ->where('type', UpcomingTransactionItemNotification::class)
                    ->exists()) {
                    $user->notify(new UpcomingTransactionItemNotification($items->count()));
                }
            }
        }
    }
}
