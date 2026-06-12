<?php

use App\Http\Controllers\Admin\AcademicConfigurationController;
use App\Http\Controllers\Admin\ActivityTypeController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\ClassroomCourseAssignmentController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\EnrollmentPeriodController;
use App\Http\Controllers\Admin\GradeBookController;
use App\Http\Controllers\Admin\GradeBookPdfController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\PensumController;
use App\Http\Controllers\Admin\ReportCardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\StudentActivityDetailPdfController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentPdfController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Profesor\AttendancePdfController;
use Illuminate\Support\Facades\Route;

Route::get('users/index', [UserController::class, 'index'])->middleware('can:admin.users.index')->name('admin.users.index');
Route::get('professors', fn () => view('admin.professors.index'))->name('admin.professors.index')->middleware('can:admin.professors.index');
Route::get('guardians', fn () => view('admin.guardians.index'))->name('admin.guardians.index')->middleware('can:admin.guardians.index');
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

Route::get('/activity-types', [ActivityTypeController::class, 'index'])
    ->name('admin.activity-types.index')
    ->middleware('can:admin.activity-types.index');

Route::get('/grade-books', [GradeBookController::class, 'index'])
    ->name('admin.grade-books.index')
    ->middleware('can:admin.grade-books.index');

Route::get('/reports/sabana-unidad', [ReportController::class, 'sabanaUnidad'])
    ->name('admin.reports.sabana-unidad.index')
    ->middleware('can:admin.reports.sabana-unidad');

Route::get('/reports/sabana-unidad/export', [ReportController::class, 'exportSabanaUnidad'])
    ->name('admin.reports.sabana-unidad.export')
    ->middleware('can:admin.reports.sabana-unidad');

Route::get('/roles', fn () => view('admin.roles.index'))
    ->name('admin.roles.index')
    ->middleware('can:admin.roles.index');

Route::get('/permissions', fn () => view('admin.permissions.index'))
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

Route::get('/reports/cuadros-classroom/index', fn () => view('admin.reports.cuadros-classroom.index'))
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

Route::get('/reports/student-list/index', fn () => view('admin.reports.student-list.index'))
    ->name('admin.reports.student-list.index')
    ->middleware('can:admin.reports.student-list');

Route::get('/reports/student-list-excel', [ReportController::class, 'studentListExcel'])
    ->name('admin.reports.student-list-excel')
    ->middleware('can:admin.reports.student-list-excel');

Route::get('/reports/student-list-excel/index', fn () => view('admin.reports.student-list-excel.index'))
    ->name('admin.reports.student-list-excel.index')
    ->middleware('can:admin.reports.student-list-excel');

Route::get('/grade-change-requests', fn () => view('admin.grade-change-requests.index'))
    ->name('admin.grade-change-requests.index')
    ->middleware('can:admin.grade-change-requests.index');

Route::get('/reports/report-cards', fn () => view('admin.reports.report-cards.index'))
    ->name('admin.reports.report-cards.index')
    ->middleware('can:admin.reports.report-cards');

Route::get('/reports/report-cards/all', [ReportCardController::class, 'all'])
    ->name('admin.reports.report-cards.all')
    ->middleware('can:admin.reports.report-cards');

Route::get('/reports/report-cards/student', [ReportCardController::class, 'student'])
    ->name('admin.reports.report-cards.student')
    ->middleware('can:admin.reports.report-cards');

Route::get('/reports/missing-activities', fn () => view('admin.reports.missing-activities.index'))
    ->name('admin.reports.missing-activities.index')
    ->middleware('can:admin.reports.missing-activities');

Route::get('/reports/missing-activities/export', [ReportController::class, 'missingActivitiesExport'])
    ->name('admin.reports.missing-activities.export')
    ->middleware('can:admin.reports.missing-activities');

Route::get('/reports/activity-summary', fn () => view('admin.reports.activity-summary.index'))
    ->name('admin.reports.activity-summary.index')
    ->middleware('can:admin.reports.activity-summary');

Route::get('/reports/activity-summary/export', [ReportController::class, 'activitySummaryExport'])
    ->name('admin.reports.activity-summary.export')
    ->middleware('can:admin.reports.activity-summary');

Route::get('/reports/student-activity-detail', fn () => view('admin.reports.student-activity-detail.index'))
    ->name('admin.reports.student-activity-detail.index')
    ->middleware('can:admin.reports.student-activity-detail');

Route::get('/reports/student-activity-detail/pdf/student', [StudentActivityDetailPdfController::class, 'student'])
    ->name('admin.reports.student-activity-detail.pdf.student')
    ->middleware('can:admin.reports.student-activity-detail');

Route::get('/reports/student-activity-detail/pdf/classroom', [StudentActivityDetailPdfController::class, 'classroom'])
    ->name('admin.reports.student-activity-detail.pdf.classroom')
    ->middleware('can:admin.reports.student-activity-detail');

Route::get('/reports/student-activity-detail/pdf/student-compact', [StudentActivityDetailPdfController::class, 'studentCompact'])
    ->name('admin.reports.student-activity-detail.pdf.student-compact')
    ->middleware('can:admin.reports.student-activity-detail');

Route::get('/reports/student-activity-detail/pdf/classroom-compact', [StudentActivityDetailPdfController::class, 'classroomCompact'])
    ->name('admin.reports.student-activity-detail.pdf.classroom-compact')
    ->middleware('can:admin.reports.student-activity-detail');

Route::get('/reports/student-activity-detail/pdf/classroom-compact-carta', [StudentActivityDetailPdfController::class, 'classroomCompactCarta'])
    ->name('admin.reports.student-activity-detail.pdf.classroom-compact-carta')
    ->middleware('can:admin.reports.student-activity-detail');

Route::get('/students/enrollments', fn () => view('admin.students.registrations'))
    ->name('admin.students.enrollments.index')
    ->middleware('can:admin.students.enrollments.index');

Route::get('/enrollment-periods', [EnrollmentPeriodController::class, 'index'])
    ->name('admin.enrollment-periods.index')
    ->middleware('can:admin.enrollment-periods.index');

Route::get('/audit', fn () => view('admin.audit.index'))
    ->name('admin.audit.index')
    ->middleware('can:admin.audit.index');

Route::get('/audit/export', [ReportController::class, 'auditExport'])
    ->name('admin.audit.export')
    ->middleware('can:admin.audit.index');

Route::post('user/loginUser', [UserController::class, 'loginUser'])
    ->name('admin.user.loginUser');

Route::get('/reports/professor-courses/index', fn () => view('admin.reports.professor-courses.index'))
    ->name('admin.reports.professor-courses.index')
    ->middleware('can:admin.reports.professor-courses');

Route::get('/reports/professor-courses/download', [ReportController::class, 'professorCoursesExcel'])
    ->name('admin.reports.professor-courses.download')
    ->middleware('can:admin.reports.professor-courses');

Route::get('/reports/attendance/', fn () => view('admin.reports.attendance.index'))
    ->name('admin.reports.attendance.index')
    ->middleware('can:admin.reports.attendance');

Route::get('/reports/attendance/pdf', [AttendancePdfController::class, 'adminGenerate'])
    ->name('admin.reports.attendance.pdf')
    ->middleware('can:admin.reports.attendance');

Route::get('/reports/grade-progress/index', fn () => view('admin.reports.grade-progress.index'))
    ->name('admin.reports.grade-progress.index')
    ->middleware('can:admin.reports.grade-progress');

Route::get('/reports/students-at-risk', fn () => view('admin.reports.students-at-risk.index'))
    ->name('admin.reports.students-at-risk.index')
    ->middleware('can:admin.reports.students-at-risk');

Route::get('/reports/grade-progress-comparison', fn () => view('admin.reports.grade-progress-comparison.index'))
    ->name('admin.reports.grade-progress-comparison.index')
    ->middleware('can:admin.reports.grade-progress-comparison');

Route::get('/reports/student-history', fn () => view('admin.reports.student-history.index'))
    ->name('admin.reports.student-history.index')
    ->middleware('can:admin.reports.student-history');

Route::get('/reports/professor-workload', fn () => view('admin.reports.professor-workload.index'))
    ->name('admin.reports.professor-workload.index')
    ->middleware('can:admin.reports.professor-workload');

// Configuraciones del sistema
Route::get('/settings', fn () => view('admin.settings'))
    ->name('admin.settings.index')
    ->middleware('can:admin.settings.index');

// Solicitudes de admisión
Route::get('/students/admissions', fn () => view('admin.students.admissions'))
    ->name('admin.admissions.index')
    ->middleware('can:admin.admissions.index');

Route::get('/students/admissions/billing', fn () => view('admin.students.admission-billing'))
    ->name('admin.admissions.billing.index')
    ->middleware('can:admin.admissions.billing');

Route::get('/students/admissions/psychometric', fn () => view('admin.students.admission-psychometric'))
    ->name('admin.admissions.psychometric.index')
    ->middleware('can:admin.admissions.psychometric');

Route::get('/students/admissions/academic', fn () => view('admin.students.admission-academic'))
    ->name('admin.admissions.academic.index')
    ->middleware('can:admin.admissions.academic');

Route::get('/admissions/courses', fn () => view('admin.admission-courses'))
    ->name('admin.admission-courses.index')
    ->middleware('can:admin.admission-courses.index');

// Actualización de notas
Route::get('/grade-books/score-update', fn () => view('admin.grade-books.score-update'))
    ->name('admin.grade-books.score-update.index')
    ->middleware('can:admin.grade-books.score-update.index');
