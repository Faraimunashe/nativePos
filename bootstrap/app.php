<?php

use App\Http\Middleware\ApiTokenMiddleware;
use App\Http\Middleware\EnvConfigMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\HandleTerminalState;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
        $middleware->alias([
            'api.auth' => ApiTokenMiddleware::class,
            'configs' => EnvConfigMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
