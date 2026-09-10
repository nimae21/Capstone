<?php

namespace App\Providers;

use App\Models\Cart;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\{Login, Logout, Failed, Registered};
use App\Listeners\LogAuthenticationActivity;

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
        \App\Models\Order::observe(\App\Observers\MobileOrderObserver::class);
        Event::listen(Login::class, [LogAuthenticationActivity::class, 'handleLogin']);
    Event::listen(Logout::class, [LogAuthenticationActivity::class, 'handleLogout']);
    Event::listen(Failed::class, [LogAuthenticationActivity::class, 'handleFailed']);
    Event::listen(Registered::class, [LogAuthenticationActivity::class, 'handleRegistered']);
        View::composer('partials.customer-header', function ($view) {
            $cartCount = 0;

            if (auth()->check()) {
                $cart = Cart::where('user_id', auth()->id())
                    ->where('status', 0)
                    ->first();

                $cartCount = $cart ? $cart->items->sum('quantity') : 0;
            }

            $view->with('cartCount', $cartCount);
        });
    }
    
}
