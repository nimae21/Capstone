<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use App\Models\AdminInvitation;
use App\Models\ApprovalRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ShoeType;
use App\Models\Stock;
use App\Models\User;
use App\Observers\AdminInvitationObserver;
use App\Observers\AnalyticsCacheObserver;
use App\Observers\ApprovalRequestObserver;
use App\Observers\CatalogCacheObserver;
use App\Observers\MobileOrderObserver;
use App\Observers\StockObserver;
use App\Observers\UserObserver;
use App\Services\CartSummary;
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
            Limit::perMinute(5)->by(hash('sha256', strtolower(trim((string) $request->email)))),
            Limit::perMinute(30)->by('login-ip:'.$request->ip())]);
        RateLimiter::for('password-reset', fn ($request) => [
            Limit::perMinute(3)->by('password-email:'.hash('sha256', strtolower(trim((string) $request->email))).'|'.$request->ip()),
            Limit::perHour(10)->by('password-ip:'.$request->ip()),
        ]);
        RateLimiter::for('public-search', fn ($request) => Limit::perMinute(30)->by('search-ip:'.$request->ip()));
        RateLimiter::for('address-data', fn ($request) => app()->environment('testing')
            ? Limit::none()
            : Limit::perMinute(120)->by('address-data:'.($request->user()?->getAuthIdentifier() ?? $request->ip())));
        RateLimiter::for('authenticated_api', fn ($request) => app()->environment('testing')
            ? Limit::none()
            : Limit::perMinute(120)->by('api-user:'.$request->user()->getAuthIdentifier()));
        RateLimiter::for('expensive-admin', fn ($request) => Limit::perMinute(10)->by('admin-expensive:'.($request->user()?->id ?? $request->ip())));
        Order::observe(MobileOrderObserver::class);
        ApprovalRequest::observe(ApprovalRequestObserver::class);
        AdminInvitation::observe(AdminInvitationObserver::class);
        User::observe(UserObserver::class);
        Stock::observe(StockObserver::class);
        foreach ([
            Order::class,
            Product::class,
            ProductVariant::class,
            Stock::class,
            User::class,
            Category::class,
            Brand::class,
            ShoeType::class,
        ] as $analyticsModel) {
            $analyticsModel::observe(AnalyticsCacheObserver::class);
        }
        foreach ([
            Order::class,
            OrderItem::class,
            Product::class,
            ProductImage::class,
            ProductVariant::class,
            Stock::class,
        ] as $catalogModel) {
            $catalogModel::observe(CatalogCacheObserver::class);
        }
        Event::listen(Login::class, [LogAuthenticationActivity::class, 'handleLogin']);
        Event::listen(Logout::class, [LogAuthenticationActivity::class, 'handleLogout']);
        Event::listen(Failed::class, [LogAuthenticationActivity::class, 'handleFailed']);
        Event::listen(Registered::class, [LogAuthenticationActivity::class, 'handleRegistered']);
        View::composer('partials.customer-header', function ($view) {
            $user = auth()->user();
            $cartCount = $user && $user->role === 'user'
                ? app(CartSummary::class)->quantityForUser($user->getKey())
                : 0;

            $view->with('cartCount', $cartCount);
        });
    }
}
