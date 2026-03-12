<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Profesor\GradeBookController;

Route::get('/grade-books', [GradeBookController::class, 'index'])
    ->name('profesor.grade-books.index')
    ->middleware('can:profesor.grade-books.index');
