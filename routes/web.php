<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReauthController;

// Redirigimos la raíz (/) directamente a la ruta de login
Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {

    // 1. La ruta de cambio de contraseña va ANTES del middleware
    // (para que pueda acceder a ella y no se quede atrapado)
    Route::get('/forzar-cambio-clave', \App\Livewire\ForcePasswordChange::class)
        ->name('password.force-change');

    // 2. Aplicamos tu nuevo middleware a las rutas que queremos proteger
    Route::middleware(['force.password.change'])->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');

        // Vista contenedora del perfil
        Route::view('profile', 'profile')->name('profile');
    });
});

Route::post('/reauth', [ReauthController::class, 'store'])->name('reauth');

// Las rutas de configuración adicionales de tu Starter Kit
require __DIR__ . '/settings.php';
