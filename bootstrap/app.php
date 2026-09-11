<?php

use App\Http\Middleware\ActiveAccount;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\IsUser;
use App\Http\Middleware\SuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->api(prepend: [
            HandleCors::class,
        ]);

        $middleware->web(append: [ActiveAccount::class]);
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
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*'));
    })->create();
