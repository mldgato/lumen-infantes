<?php

use App\Http\Controllers\ReauthController;
use App\Http\Controllers\StudentDataController;
use Illuminate\Support\Facades\Route;

// Redirigimos la raíz (/) directamente a la ruta de login
Route::redirect('/', '/login');

// Rutas públicas — actualización de datos de estudiantes (sin autenticación)
Route::get('/actualizar-datos', \App\Livewire\StudentDataRequest::class)
    ->name('student.data.request');
Route::view('/actualizar-datos/completado', 'student-data.done')
    ->name('student.data.done');
Route::get('/actualizar-datos/{token}', [StudentDataController::class, 'verifyToken'])
    ->name('student.data.verify');

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
require __DIR__.'/settings.php';
