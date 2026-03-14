<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentPdfController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\PensumController;
use App\Http\Controllers\Admin\ClassroomCourseAssignmentController;
use App\Http\Controllers\Admin\AcademicConfigurationController;
use App\Http\Controllers\Admin\GradeBookController;
use App\Http\Controllers\Admin\ReportController;

Route::get('users/index', [UserController::class, 'index'])->middleware('can:admin.users.index')->name('admin.users.index');
Route::get('students/index', [StudentController::class, 'index'])->middleware('can:admin.students.index')->name('admin.students.index');
Route::get('students/{student}/pdf', [StudentPdfController::class, 'generate'])->name('admin.students.pdf');


// Niveles
Route::get('levels', [LevelController::class, 'index'])
    ->name('admin.levels.index')
    ->middleware('can:admin.levels.index');

// Grados
Route::get('grades', [GradeController::class, 'index'])
    ->name('admin.grades.index')
    ->middleware('can:admin.grades.index');

// Secciones
Route::get('sections', [SectionController::class, 'index'])
    ->name('admin.sections.index')
    ->middleware('can:admin.sections.index');

Route::get('/classrooms', [ClassroomController::class, 'index'])
    ->name('admin.classrooms.index')
    ->middleware('can:admin.classrooms.index');

Route::get('/courses', [CourseController::class, 'index'])
    ->name('admin.courses.index')
    ->middleware('can:admin.courses.index');

Route::get('/pensums', [PensumController::class, 'index'])
    ->name('admin.pensums.index')
    ->middleware('can:admin.pensums.index');

Route::get('/classroom-course-assignments', [ClassroomCourseAssignmentController::class, 'index'])
    ->name('admin.classroom-course-assignments.index')
    ->middleware('can:admin.classroom-course-assignments.index');

Route::get('/academic-configurations', [AcademicConfigurationController::class, 'index'])
    ->name('admin.academic-configurations.index')
    ->middleware('can:admin.academic-configurations.index');

Route::get('/grade-books', [GradeBookController::class, 'index'])
    ->name('admin.grade-books.index')
    ->middleware('can:admin.grade-books.index');


Route::get('/reports/sabana-unidad', [ReportController::class, 'sabanaUnidad'])
    ->name('admin.reports.sabana-unidad.index')
    ->middleware('can:admin.reports.sabana-unidad');

Route::get('/reports/sabana-unidad/export', [ReportController::class, 'exportSabanaUnidad'])
    ->name('admin.reports.sabana-unidad.export')
    ->middleware('can:admin.reports.sabana-unidad');

Route::get('/roles', fn() => view('admin.roles.index'))
    ->name('admin.roles.index')
    ->middleware('can:admin.roles.index');

Route::get('/permissions', fn() => view('admin.permissions.index'))
    ->name('admin.permissions.index')
    ->middleware('can:admin.permissions.index');
