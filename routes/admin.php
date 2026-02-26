<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentPdfController;

Route::get('users/index', [UserController::class, 'index'])->middleware('can:admin.users.index')->name('admin.users.index');
Route::get('students/index', [StudentController::class, 'index'])->middleware('can:admin.students.index')->name('admin.students.index');
Route::get('students/{student}/pdf', [StudentPdfController::class, 'generate'])->name('admin.students.pdf');
