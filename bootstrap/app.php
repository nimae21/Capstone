<?php

use App\Http\Middleware\ActiveAccount;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\IsUser;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SuperAdmin;
use App\Support\PdoMysqlCompat;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

if (PHP_VERSION_ID < 80500 && extension_loaded('pdo_mysql') && ! class_exists('Pdo\\Mysql')) {
    class_alias(PdoMysqlCompat::class, 'Pdo\\Mysql');
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->trustHosts(
            at: fn () => app()->environment('production')
                ? array_values(array_filter(array_map(
                    fn (string $host): string => '^'.preg_quote(trim($host), '/').'$',
                    explode(',', (string) (env('TRUSTED_HOSTS') ?: parse_url(config('app.url'), PHP_URL_HOST)))
                )))
                : ['^.*$'],
            subdomains: false,
        );

        $middleware->api(prepend: [
            HandleCors::class,
        ]);

        $middleware->web(append: [ActiveAccount::class, SecurityHeaders::class]);
        $middleware->alias([
            'active' => ActiveAccount::class,
            'super_admin' => SuperAdmin::class,
            'admin' => AdminMiddleware::class,
            'isUser' => IsUser::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/paymongo',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->expectsJson() || $request->is('api/*'));
    })->create();
