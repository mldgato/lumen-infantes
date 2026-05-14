<?php

use App\Http\Controllers\Student\ReportCardController;
use Illuminate\Support\Facades\Route;

Route::get('/grades', fn () => view('student.grades.index'))
    ->name('student.grades.index')
    ->middleware('can:student.grades.view');

Route::get('/attendance', fn () => view('student.attendance.index'))
    ->name('student.attendance.index')
    ->middleware('can:student.attendance.view');

Route::get('/report-card', fn () => view('student.report-card.index'))
    ->name('student.report-card.index')
    ->middleware('can:student.report-card.view');

Route::get('/report-card/print', [ReportCardController::class, 'print'])
    ->name('student.report-card.print')
    ->middleware('can:student.report-card.view');
