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
use App\Http\Controllers\Admin\GradeBookPdfController;
use App\Http\Controllers\Admin\ReportCardController;

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


Route::get('/reports/sabana-general', [ReportController::class, 'sabanaGeneral'])
    ->name('admin.reports.sabana-general.index')
    ->middleware('can:admin.reports.sabana-general');

Route::get('/reports/sabana-general/export', [ReportController::class, 'exportSabanaGeneral'])
    ->name('admin.reports.sabana-general.export')
    ->middleware('can:admin.reports.sabana-general');

Route::get('/reports/sabana-promedio', [ReportController::class, 'sabanaPromedio'])
    ->name('admin.reports.sabana-promedio.index')
    ->middleware('can:admin.reports.sabana-promedio');

Route::get('/reports/sabana-promedio/export', [ReportController::class, 'exportSabanaPromedio'])
    ->name('admin.reports.sabana-promedio.export')
    ->middleware('can:admin.reports.sabana-promedio');

Route::get('/reports/cuadros-classroom', [GradeBookPdfController::class, 'downloadAll'])
    ->name('admin.reports.cuadros-classroom.download')
    ->middleware('can:admin.reports.cuadros-classroom');

Route::get('/reports/cuadros-classroom/index', fn() => view('admin.reports.cuadros-classroom.index'))
    ->name('admin.reports.cuadros-classroom.index')
    ->middleware('can:admin.reports.cuadros-classroom');

Route::get('/reports/cuadros-classroom/{gradeBook}/view', [GradeBookPdfController::class, 'viewOne'])
    ->name('admin.reports.cuadros-classroom.view')
    ->middleware('can:admin.reports.cuadros-classroom');

Route::get('/reports/cuadros-classroom/view-all', [GradeBookPdfController::class, 'viewAll'])
    ->name('admin.reports.cuadros-classroom.view-all')
    ->middleware('can:admin.reports.cuadros-classroom');

Route::get('/reports/student-list', [ReportController::class, 'studentList'])
    ->name('admin.reports.student-list')
    ->middleware('can:admin.reports.student-list');

Route::get('/reports/student-list/index', fn() => view('admin.reports.student-list.index'))
    ->name('admin.reports.student-list.index')
    ->middleware('can:admin.reports.student-list');

Route::get('/reports/student-list-excel', [ReportController::class, 'studentListExcel'])
    ->name('admin.reports.student-list-excel')
    ->middleware('can:admin.reports.student-list-excel');

Route::get('/reports/student-list-excel/index', fn() => view('admin.reports.student-list-excel.index'))
    ->name('admin.reports.student-list-excel.index')
    ->middleware('can:admin.reports.student-list-excel');

Route::get('/grade-change-requests', fn() => view('admin.grade-change-requests.index'))
    ->name('admin.grade-change-requests.index')
    ->middleware('can:admin.grade-change-requests.index');

Route::get('/reports/report-cards', fn() => view('admin.reports.report-cards.index'))
    ->name('admin.reports.report-cards.index')
    ->middleware('can:admin.reports.report-cards');

Route::get('/reports/report-cards/all', [ReportCardController::class, 'all'])
    ->name('admin.reports.report-cards.all')
    ->middleware('can:admin.reports.report-cards');

Route::get('/reports/report-cards/student', [ReportCardController::class, 'student'])
    ->name('admin.reports.report-cards.student')
    ->middleware('can:admin.reports.report-cards');
