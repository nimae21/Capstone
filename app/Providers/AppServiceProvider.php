<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use App\Models\Cart;
use App\Models\Order;
use App\Observers\MobileOrderObserver;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;

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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('registration', function ($request) {
            $error = fn () => throw ValidationException::withMessages(['email' => 'Too many attempts. Please wait a few minutes before trying again.']);

            return [Limit::perMinute(3)->by('register-ip:'.$request->ip())->response($error),
                Limit::perHour(10)->by('register-hour:'.$request->ip())->response($error),
                Limit::perMinutes(10, 3)->by('register-email:'.hash('sha256', strtolower(trim((string) $request->email))))->response($error)];
        });
        RateLimiter::for('api-login', fn ($request) => [
            Limit::perMinute(5)->by(hash('sha256', strtolower(trim((string) $request->email)).'|'.$request->ip())),
            Limit::perMinute(30)->by('login-ip:'.$request->ip())]);
        RateLimiter::for('password-reset', fn ($request) => [
            Limit::perMinute(3)->by('password-email:'.hash('sha256', strtolower(trim((string) $request->email))).'|'.$request->ip()),
            Limit::perHour(10)->by('password-ip:'.$request->ip()),
        ]);
        RateLimiter::for('public-search', fn ($request) => Limit::perMinute(30)->by('search-ip:'.$request->ip()));
        RateLimiter::for('authenticated_api', fn ($request) => app()->environment('testing')
            ? Limit::none()
            : Limit::perMinute(120)->by('api-user:'.$request->user()->getAuthIdentifier()));
        RateLimiter::for('expensive-admin', fn ($request) => Limit::perMinute(10)->by('admin-expensive:'.($request->user()?->id ?? $request->ip())));
        Order::observe(MobileOrderObserver::class);
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
