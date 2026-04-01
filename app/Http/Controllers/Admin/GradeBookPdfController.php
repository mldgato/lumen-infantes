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

        $actCount       = $activities->count();
        $hasExtra       = $activities->filter(fn($a) => $a->activityType->is_extra)->count() > 0;
        $hasImprovement = $config->improvement_type !== 'none';

        $numWidth     = 7;
        $sumColWidth  = 10;
        $totalCols    = $hasExtra ? 3 : 2;
        $totalSumW    = $totalCols * $sumColWidth;
        $usableWidth  = 310;
        $minNameWidth = 55;
        $maxColWidth  = 14;
        $minColWidth  = 6;

        // Si no hay mejora, cada actividad usa 1 sola columna en lugar de 2
        $colsPerAct = $hasImprovement ? 2 : 1;

        $availableForNameAndActs = $usableWidth - $numWidth - $totalSumW;
        $colWidth  = $maxColWidth;
        $nameWidth = $availableForNameAndActs - ($actCount * $colsPerAct * $colWidth);

        if ($nameWidth < $minNameWidth) {
            $nameWidth = $minNameWidth;
            $colWidth  = max($minColWidth, round(($availableForNameAndActs - $nameWidth) / ($actCount * $colsPerAct), 1));
            $nameWidth = $availableForNameAndActs - ($actCount * $colsPerAct * $colWidth);
        }

        $pdf = new PDF('L', 'mm', [215, 330]);
        $pdf->hideFooter = true;
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->AliasNbPages();
        $pdf->AddPage();

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
        $pdf->Ln(1);

        $headerY  = $pdf->GetY();
        $headerH  = 12;
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

            $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, (string) $activity->ordering);
            $currentX += $colWidth;

            // Ocultamos Mejora dinámicamente
            if ($hasImprovement) {
                $pdf->SetFillColor(198, 239, 206);
                $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, 'Mejora');
                $currentX += $colWidth;
            }
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

        $pdf->SetFont('Arial', '', 8);
        $rowH = 4;
        $num  = 1;

        foreach ($students as $student) {
            $currentX = $startX;
            $pdf->SetFillColor($num % 2 === 0 ? 245 : 255);
            $pdf->SetXY($currentX, $pdf->GetY());
            $pdf->CellUTF8($numWidth, $rowH, $num, 1, 0, 'C', true);
            $currentX += $numWidth;

            $pdf->SetXY($currentX, $pdf->GetY());
            $pdf->CellUTF8($nameWidth, $rowH, $pdf->dec($student->user->full_full_name), 1, 0, 'L', true);
            $currentX += $nameWidth;

            // Cálculo en tiempo real
            $normalCalc = 0;
            $extraCalc = 0;

            foreach ($activities as $activity) {
                $score = $activity->scores->firstWhere('student_id', $student->id);
                $rawScore = $score ? (float) $score->score : null;
                $improvement = $score ? $score->improvement_score : null;

                if (!is_null($rawScore)) {
                    $eff = $config->effectiveScore($rawScore, $improvement, (float) $activity->max_points);
                    $activity->activityType->is_extra ? $extraCalc += $eff : $normalCalc += $eff;
                }

                $pdf->SetFillColor(...($activity->activityType->is_extra ? ($num % 2 === 0 ? [255, 235, 156] : [255, 243, 205]) : ($num % 2 === 0 ? [245, 245, 245] : [255, 255, 255])));

                $displayRaw = !is_null($rawScore) ? ($rawScore == 0 ? '0' : number_format($rawScore, 1)) : '';
                $pdf->CellUTF8($colWidth, $rowH, $displayRaw, 1, 0, 'C', true);

                if ($hasImprovement) {
                    $pdf->SetFillColor(...($num % 2 === 0 ? [198, 239, 206] : [255, 255, 255]));
                    $pdf->CellUTF8($colWidth, $rowH, ($improvement > 0) ? number_format($improvement, 1) : '', 1, 0, 'C', true);
                }
            }

            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor($num % 2 === 0 ? 200 : 230);
            $pdf->CellUTF8(10, $rowH, number_format($normalCalc, 2), 1, 0, 'C', true);

            if ($hasExtra) {
                $pdf->SetFillColor(255, 235, 156);
                $pdf->CellUTF8(10, $rowH, number_format($extraCalc, 2), 1, 0, 'C', true);
            }

            $pdf->SetFillColor($num % 2 === 0 ? 200 : 230);
            $pdf->CellUTF8(10, $rowH, number_format($normalCalc + $extraCalc, 2), 1, 0, 'C', true);

            $pdf->SetFont('Arial', '', 8);
            $pdf->Ln($rowH);
            $num++;
        }

        $pdf->SetAutoPageBreak(false);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetX($startX);
        $pdf->CellUTF8($usableWidth, 4, $pdf->dec('Leyenda de actividades:'), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 7);
        $actArray = $activities->values();
        $legendCols = 4;
        $legendColW = (int) ($usableWidth / $legendCols);
        for ($i = 0; $i < $actArray->count(); $i += $legendCols) {
            $pdf->SetX($startX);
            for ($j = 0; $j < $legendCols; $j++) {
                if (isset($actArray[$i + $j])) {
                    $act = $actArray[$i + $j];
                    $txt = "{$act->ordering}. {$act->name} (" . number_format($act->max_points, 0) . " pts)" . ($act->activityType->is_extra ? ' [Extra]' : '');
                    $pdf->CellUTF8($legendColW, 4, $pdf->dec($txt), 0, 0, 'L');
                }
            }
            $pdf->Ln(4);
        }
        $pdf->SetAutoPageBreak(true, 14);

        return $pdf->Output('S');
    }

    protected function generateAllInOne($gradeBooks, $students): string
    {
        $pdf = new PDF('L', 'mm', [215, 330]);
        $pdf->hideFooter = true;
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

            $actCount       = $activities->count();
            $hasExtra       = $activities->filter(fn($a) => $a->activityType->is_extra)->count() > 0;
            $hasImprovement = $config->improvement_type !== 'none';

            $numWidth     = 7;
            $sumColWidth  = 10;
            $totalCols    = $hasExtra ? 3 : 2;
            $totalSumW    = $totalCols * $sumColWidth;
            $usableWidth  = 310;
            $minNameWidth = 55;
            $maxColWidth  = 14;
            $minColWidth  = 6;

            $colsPerAct = $hasImprovement ? 2 : 1;

            $availableForNameAndActs = $usableWidth - $numWidth - $totalSumW;
            $colWidth  = $maxColWidth;
            $nameWidth = $availableForNameAndActs - ($actCount * $colsPerAct * $colWidth);

            if ($nameWidth < $minNameWidth) {
                $nameWidth = $minNameWidth;
                $colWidth  = max($minColWidth, round(($availableForNameAndActs - $nameWidth) / ($actCount * $colsPerAct), 1));
                $nameWidth = $availableForNameAndActs - ($actCount * $colsPerAct * $colWidth);
            }

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

            $headerY  = $pdf->GetY();
            $headerH  = 12;
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

                $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, (string) $activity->ordering);
                $currentX += $colWidth;

                if ($hasImprovement) {
                    $pdf->SetFillColor(198, 239, 206);
                    $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, 'Mejora');
                    $currentX += $colWidth;
                }
            }
            $pdf->SetFillColor(180, 180, 180);
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

            $pdf->SetFont('Arial', '', 8);
            $rowH = 4;
            $num = 1;
            foreach ($students as $student) {
                $currentX = $startX;
                $pdf->SetFillColor($num % 2 === 0 ? 245 : 255);
                $pdf->SetXY($currentX, $pdf->GetY());
                $pdf->CellUTF8($numWidth, $rowH, $num, 1, 0, 'C', true);
                $currentX += $numWidth;

                $pdf->SetXY($currentX, $pdf->GetY());
                $pdf->CellUTF8($nameWidth, $rowH, $pdf->dec($student->user->full_full_name), 1, 0, 'L', true);
                $currentX += $nameWidth;

                $normalCalc = 0;
                $extraCalc  = 0;

                foreach ($activities as $activity) {
                    $score = $activity->scores->firstWhere('student_id', $student->id);
                    $rawScore = $score ? (float) $score->score : null;
                    $improvement = $score ? $score->improvement_score : null;

                    if (!is_null($rawScore)) {
                        $eff = $config->effectiveScore($rawScore, $improvement, (float) $activity->max_points);
                        $activity->activityType->is_extra ? $extraCalc += $eff : $normalCalc += $eff;
                    }

                    $pdf->SetFillColor(...($activity->activityType->is_extra ? ($num % 2 === 0 ? [255, 235, 156] : [255, 243, 205]) : ($num % 2 === 0 ? [245, 245, 245] : [255, 255, 255])));

                    $displayRaw = !is_null($rawScore) ? ($rawScore == 0 ? '0' : number_format($rawScore, 1)) : '';
                    $pdf->CellUTF8($colWidth, $rowH, $displayRaw, 1, 0, 'C', true);

                    if ($hasImprovement) {
                        $pdf->SetFillColor(...($num % 2 === 0 ? [198, 239, 206] : [255, 255, 255]));
                        $pdf->CellUTF8($colWidth, $rowH, ($improvement > 0) ? number_format($improvement, 1) : '', 1, 0, 'C', true);
                    }
                }

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetFillColor($num % 2 === 0 ? 200 : 230);
                $pdf->CellUTF8(10, $rowH, number_format($normalCalc, 2), 1, 0, 'C', true);

                if ($hasExtra) {
                    $pdf->SetFillColor(255, 235, 156);
                    $pdf->CellUTF8(10, $rowH, number_format($extraCalc, 2), 1, 0, 'C', true);
                }

                $pdf->SetFillColor($num % 2 === 0 ? 200 : 230);
                $pdf->CellUTF8(10, $rowH, number_format($normalCalc + $extraCalc, 2), 1, 0, 'C', true);

                $pdf->SetFont('Arial', '', 8);
                $pdf->Ln($rowH);
                $num++;
            }

            $pdf->SetAutoPageBreak(false);
            $pdf->Ln(1);
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetX($startX);
            $pdf->CellUTF8($usableWidth, 4, $pdf->dec('Leyenda de actividades:'), 0, 1, 'L');
            $pdf->SetFont('Arial', '', 7);
            $actArray = $activities->values();
            $legendCols = 4;
            $legendColW = (int) ($usableWidth / $legendCols);
            for ($i = 0; $i < $actArray->count(); $i += $legendCols) {
                $pdf->SetX($startX);
                for ($j = 0; $j < $legendCols; $j++) {
                    if (isset($actArray[$i + $j])) {
                        $act = $actArray[$i + $j];
                        $txt = "{$act->ordering}. {$act->name} (" . number_format($act->max_points, 0) . " pts)" . ($act->activityType->is_extra ? ' [Extra]' : '');
                        $pdf->CellUTF8($legendColW, 4, $pdf->dec($txt), 0, 0, 'L');
                    }
                }
                $pdf->Ln(4);
            }
            $pdf->SetAutoPageBreak(true, 14);
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
}
