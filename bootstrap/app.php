<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // TRD §6.1: the Paystack webhook route is exempted from CSRF by exact
        // path only, never by a wildcard. Added in Phase 8, alongside the
        // signature verification that replaces CSRF as its protection.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
