<?php

namespace App\Providers;

use App\Models\Cart;
use Illuminate\Support\Facades\View;
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
        View::composer(['layouts.pages', 'layouts.app'], function ($view) {
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
