<?php

use Illuminate\Support\Facades\Route;

// Redirigimos la raíz (/) directamente a la ruta de login
Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Vista contenedora del perfil
    Route::view('profile', 'profile')->name('profile');
});

// Las rutas de configuración adicionales de tu Starter Kit
require __DIR__ . '/settings.php';
