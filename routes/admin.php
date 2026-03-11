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
