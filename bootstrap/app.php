<?php

use App\Http\Middleware\EnsureUserRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        /*
        |--------------------------------------------------------------------------
        | Role Middleware
        |--------------------------------------------------------------------------
        */

        $middleware->alias([
            'role' => EnsureUserRole::class,
        ]);


        /*
        |--------------------------------------------------------------------------
        | OTP Authentication Routes
        |--------------------------------------------------------------------------
        |
        | OTP itself is the authentication proof for these endpoints.
        | Keep the rest of web routes protected by normal CSRF middleware.
        |
        */

        $middleware->validateCsrfTokens(except: [
            'login/verify',
            'login/verify/resend',

            'register/verify',
            'register/verify/resend',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
