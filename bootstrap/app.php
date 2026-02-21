<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
                ->group(base_path('routes/webhooks.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->appendToGroup('web', \App\Http\Middleware\HandleImpersonation::class);

        $middleware->alias([
            'trainer' => \App\Http\Middleware\EnsureIsTrainer::class,
            'vendor' => \App\Http\Middleware\EnsureIsVendor::class,
            'nda' => \App\Http\Middleware\EnsureNdaSigned::class,
            'full-member' => \App\Http\Middleware\EnsureNotAssociate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
