<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'onboarding' => \App\Http\Middleware\EnsureOnboardingCompleted::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'verified' => \App\Http\Middleware\EnsureProviderVerified::class,
            'corporate' => \App\Http\Middleware\EnsureCorporateAccess::class,
            'mobile.auth' => \App\Http\Middleware\AuthenticateMobileToken::class,
        ]);

        // Allow logout even when CSRF/session token is stale to avoid 419 on sign-out.
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
