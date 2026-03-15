<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PDF;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\GradeBook;
use App\Models\Student;
use Illuminate\Http\Request;
use ZipArchive;

class GradeBookPdfController extends Controller
{
    public function downloadAll(Request $request)
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

        // Obtener todos los cuadros aprobados de este classroom y unidad
        $gradeBooks = GradeBook::with([
            'assignment.classroom.level',
            'assignment.classroom.grade',
            'assignment.classroom.section',
            'assignment.pensumCourse.course',
            'assignment.professor.user',
            'activities.activityType',
            'activities.scores',
            'academicConfiguration',
            'totals',
        ])
            ->where('status', 'approved')
            ->whereHas(
                'assignment',
                fn($q) =>
                $q->where('classroom_id', $classroom->id)
                    ->where('unit', $request->unit)
            )
            ->get();

        if ($gradeBooks->isEmpty()) {
            return back()->with('error', 'No hay cuadros aprobados para los filtros seleccionados.');
        }

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

        // Crear ZIP en memoria temporal
        $zipPath = sys_get_temp_dir() . '/cuadros_' . uniqid() . '.zip';
        $zip     = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);

        foreach ($gradeBooks as $gradeBook) {
            $pdfContent = $this->generatePdf($gradeBook, $students);
            $courseName = $gradeBook->assignment->pensumCourse->course->course_name;
            $safeName   = preg_replace('/[^A-Za-z0-9_\-]/', '_', $courseName);
            $fileName   = "Cuadro_U{$request->unit}_{$safeName}.pdf";
            $zip->addFromString($fileName, $pdfContent);
        }

        $zip->close();

        $grado   = $classroom->grade->grade_name ?? '';
        $seccion = $classroom->section->section_name ?? '';
        $zipName = "Cuadros_U{$request->unit}_{$grado}_{$seccion}_" . date('dmY') . '.zip';

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    protected function generatePdf(GradeBook $gradeBook, $students): string
    {
        $assignment  = $gradeBook->assignment;
        $classroom   = $assignment->classroom;
        $config      = $gradeBook->academicConfiguration;
        $activities  = $gradeBook->activities;

        $nivel    = $classroom->level->level_name;
        $grado    = $classroom->grade->grade_name;
        $seccion  = $classroom->section->section_name;
        $curso    = $assignment->pensumCourse->course->course_name;
        $unidad   = 'Unidad ' . $assignment->unit;
        $anio     = $classroom->year;
        $profesor = $assignment->professor->user->name;

        $actCount    = $activities->count();
        $hasExtra    = $activities->filter(fn($a) => $a->activityType->is_extra)->count() > 0;
        $numWidth    = 7;
        $sumColWidth = 10;
        $totalCols   = $hasExtra ? 3 : 2;
        $totalSumW   = $totalCols * $sumColWidth;
        $usableWidth = 310;
        $minNameWidth = 55;
        $maxColWidth  = 14;
        $minColWidth  = 6;

        $availableForNameAndActs = $usableWidth - $numWidth - $totalSumW;
        $colWidth  = $maxColWidth;
        $nameWidth = $availableForNameAndActs - ($actCount * 2 * $colWidth);

        if ($nameWidth < $minNameWidth) {
            $nameWidth = $minNameWidth;
            $colWidth  = ($availableForNameAndActs - $nameWidth) / ($actCount * 2);
            $colWidth  = max($minColWidth, round($colWidth, 1));
            $nameWidth = $availableForNameAndActs - ($actCount * 2 * $colWidth);
        }

        $pdf = new PDF('L', 'mm', [215, 330]);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // ENCABEZADO
        $logoPath = env('APP_INSTITUTION_LOGO_IMG', 'vendor/adminlte/dist/img/Escudo.png');
        $pdf->addImage($logoPath, 10, 6, 16);

        $pdf->SetFont('Arial', 'B', 13);
        $pdf->CellUTF8(310, 6, $pdf->dec(env('APP_INSTITUTION_NAME', 'Institución Educativa')), 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->CellUTF8(310, 5, $pdf->dec('Registro de Notas'), 0, 1, 'C');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 9);
        $pdf->CellUTF8(103, 5, $pdf->dec('GRADO: ' . $grado . ' ' . $seccion), 0, 0, 'L');
        $pdf->CellUTF8(103, 5, $pdf->dec('UNIDAD: ' . $unidad), 0, 0, 'C');
        $pdf->CellUTF8(104, 5, $pdf->dec('AÑO: ' . $anio), 0, 1, 'R');

        $pdf->SetFont('Arial', '', 8);
        $pdf->CellUTF8(103, 4, $pdf->dec('NIVEL: ' . $nivel), 0, 0, 'L');
        $pdf->CellUTF8(103, 4, $pdf->dec('CURSO: ' . $curso), 0, 0, 'C');
        $pdf->CellUTF8(104, 4, $pdf->dec('PROFESOR(A): ' . $profesor), 0, 1, 'R');
        $pdf->Ln(2);

        $tipoMejora = match ($config->improvement_type) {
            'full'       => 'Proceso de mejora: 100% del valor de la actividad',
            'percentage' => 'Proceso de mejora: ' . $config->improvement_percentage . '% del valor de la actividad',
            'additive'   => 'Proceso de mejora: Suma sin sobrepasar el valor de la actividad',
            default      => '',
        };
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->CellUTF8(310, 4, $pdf->dec($tipoMejora), 0, 1, 'L');
        $pdf->Ln(1);

        // ENCABEZADO TABLA
        $headerY  = $pdf->GetY();
        $headerH  = 22;
        $startX   = $pdf->GetX();
        $currentX = $startX;

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(217, 217, 217);
        $pdf->SetXY($currentX, $headerY + $headerH - 4);
        $pdf->CellUTF8($numWidth, 4, 'No.', 1, 0, 'C', true);
        $currentX += $numWidth;

        $pdf->SetXY($currentX, $headerY + $headerH - 4);
        $pdf->CellUTF8($nameWidth, 4, $pdf->dec('Estudiante'), 1, 0, 'C', true);
        $currentX += $nameWidth;

        $pdf->SetFont('Arial', 'B', 7);
        foreach ($activities as $activity) {
            $isExtra = $activity->activityType->is_extra;
            $pdf->SetFillColor($isExtra ? 255 : 217, $isExtra ? 243 : 217, $isExtra ? 205 : 217);
            $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, $pdf->dec($activity->name . ' (' . $activity->max_points . ')'));
            $currentX += $colWidth;
            $pdf->SetFillColor(198, 239, 206);
            $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, $pdf->dec('Mejora'));
            $currentX += $colWidth;
        }

        $pdf->SetFillColor(180, 180, 180);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->rotatedHeader($currentX, $headerY, 10, $headerH, 'Normal');
        $currentX += 10;

        if ($hasExtra) {
            $pdf->SetFillColor(255, 235, 156);
            $pdf->rotatedHeader($currentX, $headerY, 10, $headerH, 'Extra');
            $currentX += 10;
        }

        $pdf->SetFillColor(180, 180, 180);
        $pdf->rotatedHeader($currentX, $headerY, 10, $headerH, 'Total');
        $pdf->SetY($headerY + $headerH);

        // FILAS ESTUDIANTES
        $pdf->SetFont('Arial', '', 8);
        $rowH = 4;
        $num  = 1;

        foreach ($students as $student) {
            $total    = $gradeBook->totals->firstWhere('student_id', $student->id);
            $currentX = $startX;

            $pdf->SetFillColor(255, 255, 255);
            if ($num % 2 === 0) $pdf->SetFillColor(245, 245, 245);

            $pdf->SetXY($currentX, $pdf->GetY());
            $pdf->CellUTF8($numWidth, $rowH, $num, 1, 0, 'C', true);
            $currentX += $numWidth;

            $nombreCompleto = $student->user->surname . ' ' . $student->user->second_surname . ', ' . $student->user->first_name . ' ' . $student->user->middle_name;
            $pdf->SetXY($currentX, $pdf->GetY());
            $pdf->CellUTF8($nameWidth, $rowH, $pdf->dec(trim($nombreCompleto)), 1, 0, 'L', true);
            $currentX += $nameWidth;

            foreach ($activities as $activity) {
                $score       = $activity->scores->firstWhere('student_id', $student->id);
                $rawScore    = $score ? (float) $score->score : 0;
                $improvement = $score ? $score->improvement_score : null;
                $isExtra     = $activity->activityType->is_extra;

                $fillNote = $isExtra ? [255, 243, 205] : [255, 255, 255];
                if ($num % 2 === 0) $fillNote = $isExtra ? [255, 235, 156] : [245, 245, 245];

                $pdf->SetFillColor(...$fillNote);
                $pdf->SetXY($currentX, $pdf->GetY());
                $pdf->CellUTF8($colWidth, $rowH, $rawScore > 0 ? number_format($rawScore, 1) : '', 1, 0, 'C', true);
                $currentX += $colWidth;

                $fillMejora = $num % 2 === 0 ? [198, 239, 206] : [255, 255, 255];
                $pdf->SetFillColor(...$fillMejora);
                $pdf->SetXY($currentX, $pdf->GetY());
                $pdf->CellUTF8($colWidth, $rowH, (!is_null($improvement) && $improvement > 0) ? number_format($improvement, 1) : '', 1, 0, 'C', true);
                $currentX += $colWidth;
            }

            $normalPts = $total ? (int) ceil((float) $total->normal_points) : 0;
            $extraPts  = $total ? (int) ceil((float) $total->extra_points)  : 0;
            $totalPts  = $total ? (int) ceil((float) $total->total_points)  : 0;

            $fillTotal = $num % 2 === 0 ? [200, 200, 200] : [230, 230, 230];
            $pdf->SetFillColor(...$fillTotal);

            $pdf->SetXY($currentX, $pdf->GetY());
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->CellUTF8(10, $rowH, number_format($normalPts, 0), 1, 0, 'C', true);
            $currentX += 10;

            if ($hasExtra) {
                $pdf->SetFillColor(255, 235, 156);
                $pdf->SetXY($currentX, $pdf->GetY());
                $pdf->CellUTF8(10, $rowH, number_format($extraPts, 0), 1, 0, 'C', true);
                $currentX += 10;
            }

            $pdf->SetFillColor(...$fillTotal);
            $pdf->SetXY($currentX, $pdf->GetY());
            $pdf->CellUTF8(10, $rowH, number_format($totalPts, 0), 1, 0, 'C', true);

            $pdf->SetFont('Arial', '', 8);
            $pdf->Ln($rowH);
            $num++;
        }

        return $pdf->Output('S');
    }

    public function viewOne(GradeBook $gradeBook)
    {
        if ($gradeBook->status !== 'approved') {
            abort(403, 'El cuadro no está aprobado.');
        }

        $gradeBook->load([
            'assignment.classroom.level',
            'assignment.classroom.grade',
            'assignment.classroom.section',
            'assignment.pensumCourse.course',
            'assignment.professor.user',
            'activities.activityType',
            'activities.scores',
            'academicConfiguration',
            'totals',
        ]);

        $students = Student::whereHas(
            'enrollments',
            fn($q) =>
            $q->where('classroom_id', $gradeBook->assignment->classroom_id)
                ->where('status', 'Activo')
        )
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->select('students.*')
            ->with('user')
            ->get();

        $pdfContent = $this->generatePdf($gradeBook, $students);
        $curso      = $gradeBook->assignment->pensumCourse->course->course_name;
        $safeName   = preg_replace('/[^A-Za-z0-9_\-]/', '_', $curso);
        $name       = "Cuadro_U{$gradeBook->assignment->unit}_{$safeName}.pdf";

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $name . '"');
    }

    public function viewAll(Request $request)
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

        $gradeBooks = GradeBook::with([
            'assignment.classroom.level',
            'assignment.classroom.grade',
            'assignment.classroom.section',
            'assignment.pensumCourse.course',
            'assignment.professor.user',
            'activities.activityType',
            'activities.scores',
            'academicConfiguration',
            'totals',
        ])
            ->where('status', 'approved')
            ->whereHas(
                'assignment',
                fn($q) =>
                $q->where('classroom_id', $classroom->id)
                    ->where('unit', $request->unit)
            )
            ->get();

        if ($gradeBooks->isEmpty()) {
            abort(404, 'No hay cuadros aprobados para los filtros seleccionados.');
        }

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

        // Generar un solo PDF con todos los cuadros uno tras otro
        $pdf = $this->generateAllInOne($gradeBooks, $students);

        $grado   = $classroom->grade->grade_name ?? '';
        $seccion = $classroom->section->section_name ?? '';
        $name    = "Cuadros_U{$request->unit}_{$grado}_{$seccion}_" . date('dmY') . '.pdf';

        return response($pdf, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $name . '"');
    }

    protected function generateAllInOne($gradeBooks, $students): string
    {
        $pdf = new PDF('L', 'mm', [215, 330]);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->AliasNbPages();

        foreach ($gradeBooks as $gradeBook) {
            $pdf->AddPage();

            $assignment = $gradeBook->assignment;
            $classroom  = $assignment->classroom;
            $config     = $gradeBook->academicConfiguration;
            $activities = $gradeBook->activities;

            $nivel    = $classroom->level->level_name;
            $grado    = $classroom->grade->grade_name;
            $seccion  = $classroom->section->section_name;
            $curso    = $assignment->pensumCourse->course->course_name;
            $unidad   = 'Unidad ' . $assignment->unit;
            $anio     = $classroom->year;
            $profesor = $assignment->professor->user->name;

            $actCount    = $activities->count();
            $hasExtra    = $activities->filter(fn($a) => $a->activityType->is_extra)->count() > 0;
            $numWidth    = 7;
            $sumColWidth = 10;
            $totalCols   = $hasExtra ? 3 : 2;
            $totalSumW   = $totalCols * $sumColWidth;
            $usableWidth = 310;
            $minNameWidth = 55;
            $maxColWidth  = 14;
            $minColWidth  = 6;

            $availableForNameAndActs = $usableWidth - $numWidth - $totalSumW;
            $colWidth  = $maxColWidth;
            $nameWidth = $availableForNameAndActs - ($actCount * 2 * $colWidth);

            if ($nameWidth < $minNameWidth) {
                $nameWidth = $minNameWidth;
                $colWidth  = ($availableForNameAndActs - $nameWidth) / ($actCount * 2);
                $colWidth  = max($minColWidth, round($colWidth, 1));
                $nameWidth = $availableForNameAndActs - ($actCount * 2 * $colWidth);
            }

            // ENCABEZADO
            $logoPath = env('APP_INSTITUTION_LOGO_IMG', 'vendor/adminlte/dist/img/Escudo.png');
            $pdf->addImage($logoPath, 10, 6, 16);

            $pdf->SetFont('Arial', 'B', 13);
            $pdf->CellUTF8(310, 6, $pdf->dec(env('APP_INSTITUTION_NAME', 'Institución Educativa')), 0, 1, 'C');

            $pdf->SetFont('Arial', 'B', 11);
            $pdf->CellUTF8(310, 5, $pdf->dec('Registro de Notas'), 0, 1, 'C');
            $pdf->Ln(2);

            $pdf->SetFont('Arial', '', 9);
            $pdf->CellUTF8(103, 5, $pdf->dec('GRADO: ' . $grado . ' ' . $seccion), 0, 0, 'L');
            $pdf->CellUTF8(103, 5, $pdf->dec('UNIDAD: ' . $unidad), 0, 0, 'C');
            $pdf->CellUTF8(104, 5, $pdf->dec('AÑO: ' . $anio), 0, 1, 'R');

            $pdf->SetFont('Arial', '', 8);
            $pdf->CellUTF8(103, 4, $pdf->dec('NIVEL: ' . $nivel), 0, 0, 'L');
            $pdf->CellUTF8(103, 4, $pdf->dec('CURSO: ' . $curso), 0, 0, 'C');
            $pdf->CellUTF8(104, 4, $pdf->dec('PROFESOR(A): ' . $profesor), 0, 1, 'R');
            $pdf->Ln(2);

            $tipoMejora = match ($config->improvement_type) {
                'full'       => 'Proceso de mejora: 100% del valor de la actividad',
                'percentage' => 'Proceso de mejora: ' . $config->improvement_percentage . '% del valor de la actividad',
                'additive'   => 'Proceso de mejora: Suma sin sobrepasar el valor de la actividad',
                default      => '',
            };
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->CellUTF8(310, 4, $pdf->dec($tipoMejora), 0, 1, 'L');
            $pdf->Ln(1);

            // ENCABEZADO TABLA
            $headerY  = $pdf->GetY();
            $headerH  = 22;
            $startX   = $pdf->GetX();
            $currentX = $startX;

            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(217, 217, 217);
            $pdf->SetXY($currentX, $headerY + $headerH - 4);
            $pdf->CellUTF8($numWidth, 4, 'No.', 1, 0, 'C', true);
            $currentX += $numWidth;

            $pdf->SetXY($currentX, $headerY + $headerH - 4);
            $pdf->CellUTF8($nameWidth, 4, $pdf->dec('Estudiante'), 1, 0, 'C', true);
            $currentX += $nameWidth;

            $pdf->SetFont('Arial', 'B', 7);
            foreach ($activities as $activity) {
                $isExtra = $activity->activityType->is_extra;
                $pdf->SetFillColor($isExtra ? 255 : 217, $isExtra ? 243 : 217, $isExtra ? 205 : 217);
                $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, $pdf->dec($activity->name . ' (' . $activity->max_points . ')'));
                $currentX += $colWidth;
                $pdf->SetFillColor(198, 239, 206);
                $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, $pdf->dec('Mejora'));
                $currentX += $colWidth;
            }

            $pdf->SetFillColor(180, 180, 180);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->rotatedHeader($currentX, $headerY, 10, $headerH, 'Normal');
            $currentX += 10;

            if ($hasExtra) {
                $pdf->SetFillColor(255, 235, 156);
                $pdf->rotatedHeader($currentX, $headerY, 10, $headerH, 'Extra');
                $currentX += 10;
            }

            $pdf->SetFillColor(180, 180, 180);
            $pdf->rotatedHeader($currentX, $headerY, 10, $headerH, 'Total');
            $pdf->SetY($headerY + $headerH);

            // FILAS ESTUDIANTES
            $pdf->SetFont('Arial', '', 8);
            $rowH = 4;
            $num  = 1;

            foreach ($students as $student) {
                $total    = $gradeBook->totals->firstWhere('student_id', $student->id);
                $currentX = $startX;

                $pdf->SetFillColor(255, 255, 255);
                if ($num % 2 === 0) $pdf->SetFillColor(245, 245, 245);

                $pdf->SetXY($currentX, $pdf->GetY());
                $pdf->CellUTF8($numWidth, $rowH, $num, 1, 0, 'C', true);
                $currentX += $numWidth;

                $nombreCompleto = $student->user->surname . ' ' . $student->user->second_surname . ', ' . $student->user->first_name . ' ' . $student->user->middle_name;
                $pdf->SetXY($currentX, $pdf->GetY());
                $pdf->CellUTF8($nameWidth, $rowH, $pdf->dec(trim($nombreCompleto)), 1, 0, 'L', true);
                $currentX += $nameWidth;

                foreach ($activities as $activity) {
                    $score       = $activity->scores->firstWhere('student_id', $student->id);
                    $rawScore    = $score ? (float) $score->score : 0;
                    $improvement = $score ? $score->improvement_score : null;
                    $isExtra     = $activity->activityType->is_extra;

                    $fillNote = $isExtra ? [255, 243, 205] : [255, 255, 255];
                    if ($num % 2 === 0) $fillNote = $isExtra ? [255, 235, 156] : [245, 245, 245];

                    $pdf->SetFillColor(...$fillNote);
                    $pdf->SetXY($currentX, $pdf->GetY());
                    $pdf->CellUTF8($colWidth, $rowH, $rawScore > 0 ? number_format($rawScore, 1) : '', 1, 0, 'C', true);
                    $currentX += $colWidth;

                    $fillMejora = $num % 2 === 0 ? [198, 239, 206] : [255, 255, 255];
                    $pdf->SetFillColor(...$fillMejora);
                    $pdf->SetXY($currentX, $pdf->GetY());
                    $pdf->CellUTF8($colWidth, $rowH, (!is_null($improvement) && $improvement > 0) ? number_format($improvement, 1) : '', 1, 0, 'C', true);
                    $currentX += $colWidth;
                }

                $normalPts = $total ? (int) ceil((float) $total->normal_points) : 0;
                $extraPts  = $total ? (int) ceil((float) $total->extra_points)  : 0;
                $totalPts  = $total ? (int) ceil((float) $total->total_points)  : 0;

                $fillTotal = $num % 2 === 0 ? [200, 200, 200] : [230, 230, 230];
                $pdf->SetFillColor(...$fillTotal);

                $pdf->SetXY($currentX, $pdf->GetY());
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->CellUTF8(10, $rowH, number_format($normalPts, 0), 1, 0, 'C', true);
                $currentX += 10;

                if ($hasExtra) {
                    $pdf->SetFillColor(255, 235, 156);
                    $pdf->SetXY($currentX, $pdf->GetY());
                    $pdf->CellUTF8(10, $rowH, number_format($extraPts, 0), 1, 0, 'C', true);
                    $currentX += 10;
                }

                $pdf->SetFillColor(...$fillTotal);
                $pdf->SetXY($currentX, $pdf->GetY());
                $pdf->CellUTF8(10, $rowH, number_format($totalPts, 0), 1, 0, 'C', true);

                $pdf->SetFont('Arial', '', 8);
                $pdf->Ln($rowH);
                $num++;
            }
        }

        return $pdf->Output('S');
    }
}
