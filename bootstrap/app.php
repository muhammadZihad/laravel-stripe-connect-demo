<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'company' => \App\Http\Middleware\EnsureCompany::class,
            'agent' => \App\Http\Middleware\EnsureAgent::class,
        ]);

        // Exclude Stripe webhooks from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
            'stripe/webhook/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // $exceptions->render(function (Throwable $e, Request $request) {
        //     dd($e);
        // });
    })->create();
