<?php

namespace App\Http\Controllers\Profesor;

use App\Helpers\PDF;
use App\Http\Controllers\Controller;
use App\Models\GradeBook;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class GradeBookPdfController extends Controller
{
    public function generate(GradeBook $gradeBook)
    {
        // Solo cuadros aprobados
        if ($gradeBook->status !== 'approved') {
            abort(403, 'El cuadro no está aprobado.');
        }

        // Solo el profesor dueño del cuadro
        $professor = Auth::user()->professor;
        if ($gradeBook->assignment->professor_id !== $professor->id) {
            abort(403, 'No tienes permiso para ver este cuadro.');
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

        $students = Student::whereHas('enrollments', function ($q) use ($gradeBook) {
            $q->where('classroom_id', $gradeBook->assignment->classroom_id)
                ->where('status', 'Activo');
        })
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->select('students.*')
            ->with('user')
            ->get();

        $assignment  = $gradeBook->assignment;
        $classroom   = $assignment->classroom;
        $config      = $gradeBook->academicConfiguration;
        $activities  = $gradeBook->activities;

        // Datos del encabezado
        $nivel    = $classroom->level->level_name;
        $grado    = $classroom->grade->grade_name;
        $seccion  = $classroom->section->section_name;
        $curso    = $assignment->pensumCourse->course->course_name;
        $unidad   = 'Unidad ' . $assignment->unit;
        $anio     = $classroom->year;
        $profesor = $assignment->professor->user->name;

        // Cálculo dinámico de anchos
        $actCount     = $activities->count();
        $hasExtra     = $activities->filter(fn($a) => $a->activityType->is_extra)->count() > 0;
        $numWidth     = 7;
        $sumColWidth  = 10;
        $totalCols    = $hasExtra ? 3 : 2;
        $totalSumW    = $totalCols * $sumColWidth;
        $usableWidth  = 310;
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

        // Orientación horizontal, tamaño carta oficio (330x215)
        $pdf = new PDF('L', 'mm', [215, 330]);
        $pdf->hideFooter = true;
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // ==========================================
        // ENCABEZADO
        // ==========================================
        // LOGO
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

        // ==========================================
        // ENCABEZADO DE LA TABLA (texto rotado)
        // ==========================================
        $headerY    = $pdf->GetY();
        $headerH    = 12; // alto del bloque de encabezados rotados
        $startX     = $pdf->GetX();
        $currentX   = $startX;

        // Encabezados fijos
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(217, 217, 217);
        $pdf->SetXY($currentX, $headerY + $headerH - 4);
        $pdf->CellUTF8($numWidth, 4, 'No.', 1, 0, 'C', true);
        $currentX += $numWidth;

        $pdf->SetXY($currentX, $headerY + $headerH - 4);
        $pdf->CellUTF8($nameWidth, 4, $pdf->dec('Estudiante'), 1, 0, 'C', true);
        $currentX += $nameWidth;

        // Encabezados de actividades (rotados)
        $pdf->SetFont('Arial', 'B', 7);
        foreach ($activities as $activity) {
            $isExtra = $activity->activityType->is_extra;

            // Nota
            $pdf->SetFillColor($isExtra ? 255 : 217, $isExtra ? 243 : 217, $isExtra ? 205 : 217);
            $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, (string) $activity->ordering);
            $currentX += $colWidth;

            // Mejora
            $pdf->SetFillColor(198, 239, 206);
            $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, 'Mejora');
            $currentX += $colWidth;
        }

        // Totales
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

        // ==========================================
        // FILAS DE ESTUDIANTES
        // ==========================================
        $pdf->SetFont('Arial', '', 8);
        $rowH = 4;
        $num  = 1;

        foreach ($students as $student) {
            $total      = $gradeBook->totals->firstWhere('student_id', $student->id);
            $currentX   = $startX;

            $pdf->SetFillColor(255, 255, 255);
            if ($num % 2 === 0) {
                $pdf->SetFillColor(245, 245, 245);
            }

            $pdf->SetXY($currentX, $pdf->GetY());
            $pdf->CellUTF8($numWidth, $rowH, $num, 1, 0, 'C', true);
            $currentX += $numWidth;

            $nombreCompleto = $student->user->surname . ' ' . $student->user->second_surname . ', ' . $student->user->first_name . ' ' . $student->user->middle_name;
            $pdf->SetXY($currentX, $pdf->GetY());
            $pdf->CellUTF8($nameWidth, $rowH, $pdf->dec(trim($nombreCompleto)), 1, 0, 'L', true);
            $currentX += $nameWidth;

            $normalCalc = 0;
            $extraCalc  = 0;

            foreach ($activities as $activity) {
                $score       = $activity->scores->firstWhere('student_id', $student->id);
                $rawScore    = $score ? (float) $score->score : 0;
                $improvement = $score ? $score->improvement_score : null;
                $isExtra     = $activity->activityType->is_extra;

                $eff = $config->effectiveScore($rawScore, $improvement, (float) $activity->max_points);
                if ($isExtra) {
                    $extraCalc += $eff;
                } else {
                    $normalCalc += $eff;
                }

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

            $normalPts = (int) ceil($normalCalc);
            $extraPts  = (int) ceil($extraCalc);
            $totalPts  = (int) ceil($normalCalc + $extraCalc);

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

        // LEYENDA DE ACTIVIDADES
        $pdf->SetAutoPageBreak(false);
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->SetX($startX);
        $pdf->CellUTF8($usableWidth, 4, $pdf->dec('Leyenda de actividades:'), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 7);

        $actArray   = $activities->values();
        $totalActs  = $actArray->count();
        $legendCols = 4;
        $legendColW = (int) ($usableWidth / $legendCols);
        $legendIdx  = 0;

        while ($legendIdx < $totalActs) {
            $pdf->SetX($startX);
            for ($lc = 0; $lc < $legendCols; $lc++) {
                if ($legendIdx < $totalActs) {
                    $act     = $actArray[$legendIdx];
                    $isExtra = $act->activityType->is_extra;
                    $maxPts  = number_format((float) $act->max_points, 0);
                    $marker  = $isExtra ? ' [Extra]' : '';
                    $text    = "{$act->ordering}. {$act->name} ({$maxPts} pts){$marker}";
                    $pdf->CellUTF8($legendColW, 4, $pdf->dec($text), 0, 0, 'L');
                    $legendIdx++;
                } else {
                    $pdf->CellUTF8($legendColW, 4, '', 0, 0, 'L');
                }
            }
            $pdf->Ln(4);
        }
        $pdf->SetAutoPageBreak(true, 14);

        $name = 'Cuadro_' . date('dmY_His') . '.pdf';
        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $name . '"');
    }
}
