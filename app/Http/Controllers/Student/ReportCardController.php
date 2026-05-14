<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Admin\ReportCardController as AdminReportCardController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ReportCardController extends Controller
{
    public function print(Request $request): Response
    {
        $request->validate([
            'unit' => 'required|integer|min:1',
        ]);

        $student = Auth::user()->student;

        if (! $student) {
            abort(403, 'No tiene un registro de estudiante asociado.');
        }

        $enrollment = $student->enrollments()
            ->where('status', 'Activo')
            ->whereHas('classroom', fn ($q) => $q->where('year', date('Y')))
            ->first();

        if (! $enrollment) {
            abort(403, 'No tiene una inscripción activa para el año actual.');
        }

        $fakeRequest = Request::create(
            route('admin.reports.report-cards.student'),
            'GET',
            [
                'student_id'   => $student->id,
                'classroom_id' => $enrollment->classroom_id,
                'unit'         => $request->unit,
            ]
        );

        return (new AdminReportCardController())->student($fakeRequest);
    }
}
