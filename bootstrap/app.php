<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsurePasswordIsChanged;
use Illuminate\Session\TokenMismatchException;

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

            Route::middleware(['web', 'auth', 'force.password.change'])
                ->prefix('student')
                ->group(base_path('routes/student.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Agregamos tu alias aquí
        $middleware->alias([
            'force.password.change' => EnsurePasswordIsChanged::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'reauth',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (TokenMismatchException $e, $request) {
            if (! $request->expectsJson()) {
                return redirect()->back()
                    ->withInput()
                    ->with('reauth_required', true);
            }
        });
    })->create();
