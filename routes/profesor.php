<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Profesor\GradeBookController;
use App\Http\Controllers\Profesor\GradeBookPdfController;

Route::get('/grade-books', [GradeBookController::class, 'index'])
    ->name('profesor.grade-books.index')
    ->middleware('can:profesor.grade-books.index');

Route::get('/grade-books/{gradeBook}/pdf', [GradeBookPdfController::class, 'generate'])
    ->name('profesor.grade-books.pdf')
    ->middleware('can:profesor.grade-books.index');
