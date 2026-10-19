<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        $middleware->trustProxies(at: ['127.0.0.1', '::1', '172.16.0.0/12']);

        $middleware->alias([
            'set.locale' => SetLocale::class,
            'admin.auth' => EnsureAdmin::class,
        ]);

        $middleware->web(prepend: [
            SetLocale::class,
        ]);

        $middleware->replaceInGroup(
            'web',
            EncryptCookies::class,
            App\Http\Middleware\EncryptCookies::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
