<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsurePasswordIsChanged;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Agregamos 'force.password.change' a este grupo
            Route::middleware(['web', 'auth', 'force.password.change'])
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));

            // Agregamos 'force.password.change' a este grupo también
            Route::middleware(['web', 'auth', 'force.password.change'])
                ->prefix('profesor')
                ->group(base_path('routes/profesor.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Agregamos tu alias aquí
        $middleware->alias([
            'force.password.change' => EnsurePasswordIsChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
