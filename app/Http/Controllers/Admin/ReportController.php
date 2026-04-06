<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SabanaUnidadExport;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function sabanaUnidad()
    {
        return view('admin.reports.sabana-unidad.index');
    }

    public function exportSabanaUnidad(Request $request)
    {
        $request->validate([
            'year'    => 'required',
            'level'   => 'required|exists:levels,id',
            'grade'   => 'required|exists:grades,id',
            'section' => 'required|exists:sections,id',
            'unit'    => 'required|integer|min:1',
        ]);

        $classroom = Classroom::where('year', $request->year)
            ->where('level_id', $request->level)
            ->where('grade_id', $request->grade)
            ->where('section_id', $request->section)
            ->firstOrFail();

        $filename = 'Sabana_U' . $request->unit . '_' . date('dmY_His') . '.xlsx';

        return Excel::download(
            new SabanaUnidadExport($classroom->id, (int) $request->unit),
            $filename
        );
    }

    public function sabanaGeneral()
    {
        return view('admin.reports.sabana-general.index');
    }

    public function exportSabanaGeneral(Request $request)
    {
        $request->validate([
            'year'    => 'required',
            'level'   => 'required|exists:levels,id',
            'grade'   => 'required|exists:grades,id',
            'section' => 'required|exists:sections,id',
        ]);

        $classroom = Classroom::where('year', $request->year)
            ->where('level_id', $request->level)
            ->where('grade_id', $request->grade)
            ->where('section_id', $request->section)
            ->firstOrFail();

        $filename = 'SabanaGeneral_' . date('dmY_His') . '.xlsx';

        return Excel::download(
            new \App\Exports\SabanaGeneralExport($classroom->id),
            $filename
        );
    }

    public function sabanaPromedio()
    {
        return view('admin.reports.sabana-promedio.index');
    }

    public function exportSabanaPromedio(Request $request)
    {
        $request->validate([
            'year'    => 'required',
            'level'   => 'required|exists:levels,id',
            'grade'   => 'required|exists:grades,id',
            'section' => 'required|exists:sections,id',
        ]);

        $classroom = Classroom::where('year', $request->year)
            ->where('level_id', $request->level)
            ->where('grade_id', $request->grade)
            ->where('section_id', $request->section)
            ->firstOrFail();

        $filename = 'SabanaPromedio_' . date('dmY_His') . '.xlsx';

        return Excel::download(
            new \App\Exports\SabanaPromedioExport($classroom->id),
            $filename
        );
    }

    public function studentList(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $classroom = Classroom::with(['level', 'grade', 'section'])->findOrFail($request->classroom_id);

        // Consulta estandarizada: Solo Activos, orden jerárquico por 4 campos y Eager Loading del usuario
        $students = Student::whereHas(
            'enrollments',
            fn($q) =>
            $q->where('classroom_id', $classroom->id)->where('status', 'Activo')
        )
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->select('students.*')
            ->with('user')
            ->get();

        $levelName   = $classroom->level->level_name;
        $gradeName   = $classroom->grade->grade_name;
        $sectionName = $classroom->section->section_name;
        $year        = $classroom->year;

        $pdf = new \App\Helpers\PDF('P', 'mm', 'Letter');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $logoPath = env('APP_INSTITUTION_LOGO_IMG', 'vendor/adminlte/dist/img/AdminLTELogo.png');
        $pdf->addImage($logoPath, 15, 12, 18);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->CellUTF8(180, 7, $pdf->dec(env('APP_INSTITUTION_NAME', 'Institución Educativa')), 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->CellUTF8(180, 6, $pdf->dec('Listado de Estudiantes'), 0, 1, 'C');
        $pdf->Ln(3);

        $pdf->SetFont('Arial', '', 9);
        $pdf->CellUTF8(90, 5, $pdf->dec('NIVEL: ' . $levelName), 0, 0, 'L');
        $pdf->CellUTF8(90, 5, $pdf->dec('AÑO: ' . $year), 0, 1, 'R');
        $pdf->CellUTF8(180, 5, $pdf->dec('GRADO: ' . $gradeName . ' ' . $sectionName), 0, 1, 'L');
        $pdf->Ln(4);

        $numWidth         = 10;
        $nameWidth        = 100;
        $observationWidth = 70;
        $rowHeight        = 7;

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(47, 117, 182);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->CellUTF8($numWidth, $rowHeight, 'No.', 1, 0, 'C', true);
        $pdf->CellUTF8($nameWidth, $rowHeight, $pdf->dec('Estudiante (Apellidos, Nombres)'), 1, 0, 'C', true);
        $pdf->CellUTF8($observationWidth, $rowHeight, $pdf->dec('Observación'), 1, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 9);

        $count = 1;
        foreach ($students as $student) {
            $fillColor = $count % 2 === 0 ? [240, 240, 240] : [255, 255, 255];
            $pdf->SetFillColor(...$fillColor);

            $pdf->CellUTF8($numWidth, $rowHeight, $count, 1, 0, 'C', true);

            // REEMPLAZO: Uso de accessor full_full_name del modelo User
            $pdf->CellUTF8($nameWidth, $rowHeight, $pdf->dec($student->user->full_full_name), 1, 0, 'L', true);

            $pdf->CellUTF8($observationWidth, $rowHeight, '', 1, 1, 'L', true);
            $count++;
        }

        $safeGrade   = preg_replace('/[^A-Za-z0-9_\-]/', '_', $gradeName);
        $safeSection = preg_replace('/[^A-Za-z0-9_\-]/', '_', $sectionName);
        $fileName    = "StudentList_{$safeGrade}_{$safeSection}_" . date('dmY_His') . '.pdf';

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }

    public function studentListExcel(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $classroom = Classroom::with(['grade', 'section'])->findOrFail($request->classroom_id);

        $grade   = preg_replace('/[^A-Za-z0-9_\-]/', '_', $classroom->grade->grade_name);
        $section = preg_replace('/[^A-Za-z0-9_\-]/', '_', $classroom->section->section_name);
        $fileName = "StudentList_{$grade}_{$section}_" . date('dmY_His') . '.xlsx';

        return Excel::download(
            new \App\Exports\StudentListExport($classroom->id),
            $fileName
        );
    }

    public function missingActivitiesExport(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'unit'         => 'required|integer|min:1',
        ]);

        $export = new \App\Exports\MissingActivitiesAdminExport(
            classroomId: (int) $request->classroom_id,
            unit: (int) $request->unit,
        );

        return \Maatwebsite\Excel\Facades\Excel::download($export, 'actividades_faltantes_aula_' . date('dmY') . '.xlsx');
    }

    public function professorCoursesExcel(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
        ]);

        $year     = $request->integer('year');
        $fileName = "Profesores_Cursos_{$year}_" . date('dmY_His') . '.xlsx';

        return Excel::download(
            new \App\Exports\ProfessorCoursesExport($year),
            $fileName
        );
    }
}
