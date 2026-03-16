<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Profesor\GradeBookController;
use App\Http\Controllers\Profesor\GradeBookPdfController;
use App\Http\Controllers\Profesor\ReportController;

Route::get('/grade-books', [GradeBookController::class, 'index'])
    ->name('profesor.grade-books.index')
    ->middleware('can:profesor.grade-books.index');

Route::get('/grade-books/{gradeBook}/pdf', [GradeBookPdfController::class, 'generate'])
    ->name('profesor.grade-books.pdf')
    ->middleware('can:profesor.grade-books.index');

Route::get('/reports/sabana-promedio', [ReportController::class, 'sabanaPromedio'])
    ->name('profesor.reports.sabana-promedio')
    ->middleware('can:profesor.reports.sabana-promedio');

Route::get('/reports/sabana-promedio/index', fn() => view('profesor.reports.sabana-promedio.index'))
    ->name('profesor.reports.sabana-promedio.index')
    ->middleware('can:profesor.reports.sabana-promedio');

Route::get('/reports/cuadro-vacio', [ReportController::class, 'cuadroVacio'])
    ->name('profesor.reports.cuadro-vacio')
    ->middleware('can:profesor.reports.cuadro-vacio');

Route::get('/reports/cuadro-vacio/index', fn() => view('profesor.reports.cuadro-vacio.index'))
    ->name('profesor.reports.cuadro-vacio.index')
    ->middleware('can:profesor.reports.cuadro-vacio');

Route::get('/reports/cuadros-unidad', [ReportController::class, 'cuadrosUnidad'])
    ->name('profesor.reports.cuadros-unidad.download')
    ->middleware('can:profesor.reports.cuadros-unidad');

Route::get('/reports/cuadros-unidad/view-all', [ReportController::class, 'cuadrosUnidadViewAll'])
    ->name('profesor.reports.cuadros-unidad.view-all')
    ->middleware('can:profesor.reports.cuadros-unidad');

Route::get('/reports/cuadros-unidad/{gradeBook}/view', [ReportController::class, 'cuadrosUnidadViewOne'])
    ->name('profesor.reports.cuadros-unidad.view-one')
    ->middleware('can:profesor.reports.cuadros-unidad');

Route::get('/reports/cuadros-unidad/index', fn() => view('profesor.reports.cuadros-unidad.index'))
    ->name('profesor.reports.cuadros-unidad.index')
    ->middleware('can:profesor.reports.cuadros-unidad');

Route::get('/reports/student-list', [ReportController::class, 'studentList'])
    ->name('profesor.reports.student-list')
    ->middleware('can:profesor.reports.student-list');

Route::get('/reports/student-list/index', fn() => view('profesor.reports.student-list.index'))
    ->name('profesor.reports.student-list.index')
    ->middleware('can:profesor.reports.student-list');


Route::get('/reports/student-list-excel', [ReportController::class, 'studentListExcel'])
    ->name('profesor.reports.student-list-excel')
    ->middleware('can:profesor.reports.student-list-excel');

Route::get('/reports/student-list-excel/index', fn() => view('profesor.reports.student-list-excel.index'))
    ->name('profesor.reports.student-list-excel.index')
    ->middleware('can:profesor.reports.student-list-excel');

Route::get('/grade-change-requests', fn() => view('profesor.grade-change-requests.index'))
    ->name('profesor.grade-change-requests.index')
    ->middleware('can:profesor.grade-change-requests.create');
