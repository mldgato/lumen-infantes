<?php

namespace App\Http\Controllers\Profesor;

use App\Helpers\PDF;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBookTotal;
use App\Models\Pensum;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GradeBook;

class ReportController extends Controller
{
    public function sabanaPromedio(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $professor = Auth::user()->professor;
        $classroom = Classroom::with(['level', 'grade', 'section'])->findOrFail($request->classroom_id);

        $pensum = Pensum::where('grade_id', $classroom->grade_id)
            ->where('year', $classroom->year)
            ->first();

        if (! $pensum) {
            abort(404, 'No existe un pénsum para este grado y año.');
        }

        // Solo los cursos que imparte este profesor en este classroom
        $assignments = ClassroomCourseAssignment::with([
            'pensumCourse.course',
            'gradeBook',
        ])
            ->where('classroom_id', $classroom->id)
            ->where('professor_id', $professor->id)
            ->orderBy('pensum_course_id')
            ->orderBy('unit')
            ->get();

        // Agrupar por pensum_course_id => [unit => assignment]
        $courseColumns = [];
        foreach ($assignments as $assignment) {
            $pcId = $assignment->pensum_course_id;
            if (! isset($courseColumns[$pcId])) {
                $courseColumns[$pcId] = [
                    'name'        => $assignment->pensumCourse->course->course_name,
                    'assignments' => collect(),
                ];
            }
            $courseColumns[$pcId]['assignments']->push($assignment);
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

        // ==========================================
        // CALCULAR ANCHOS DINÁMICOS
        // ==========================================
        // Columnas: No(8) + Nombre(55) + por cada curso: unidades(8c/u) + Prom(12)
        $courseCount = count($courseColumns);
        $unitCounts  = array_map(fn($c) => $c['assignments']->count(), $courseColumns);
        $totalUnitCols = array_sum($unitCounts);
        $totalPromCols = $courseCount;

        // Usable width en landscape 330mm con márgenes 10mm c/lado
        $usableWidth = 310;
        $numWidth    = 8;
        $nameWidth   = 55;
        $promWidth   = 12;
        $available   = $usableWidth - $numWidth - $nameWidth - ($totalPromCols * $promWidth);
        $unitWidth   = $totalUnitCols > 0 ? max(7, round($available / $totalUnitCols, 1)) : 8;

        $pdf = new PDF('L', 'mm', [215, 330]);
        $pdf->hideFooter = true;
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // ==========================================
        // ENCABEZADO
        // ==========================================
        $logoPath = env('APP_INSTITUTION_LOGO_IMG', 'vendor/adminlte/dist/img/Escudo.png');
        $pdf->addImage($logoPath, 10, 6, 16);

        $pdf->SetFont('Arial', 'B', 13);
        $pdf->CellUTF8(310, 6, $pdf->dec(env('APP_INSTITUTION_NAME', 'Institución Educativa')), 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->CellUTF8(310, 5, $pdf->dec('Sábana de Calificaciones'), 0, 1, 'C');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 9);
        $pdf->CellUTF8(103, 5, $pdf->dec('NIVEL: ' . $classroom->level->level_name), 0, 0, 'L');
        $pdf->CellUTF8(103, 5, $pdf->dec('GRADO: ' . $classroom->grade->grade_name . ' ' . $classroom->section->section_name), 0, 0, 'C');
        $pdf->CellUTF8(104, 5, $pdf->dec('AÑO: ' . $classroom->year), 0, 1, 'R');
        $pdf->Ln(3);

        // ==========================================
        // FILA 1 ENCABEZADO: NOMBRES DE CURSOS
        // ==========================================
        $headerH1   = 8;
        $headerH2   = 6;
        $headerY    = $pdf->GetY();
        $startX     = $pdf->GetX();
        $currentX   = $startX;
        $romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI'];

        // Celda No. + Estudiante (abarca ambas filas de header)
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(47, 117, 182);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY($currentX, $headerY);
        $pdf->CellUTF8($numWidth, $headerH1 + $headerH2, 'No.', 1, 0, 'C', true);
        $currentX += $numWidth;

        $pdf->SetXY($currentX, $headerY);
        $pdf->CellUTF8($nameWidth, $headerH1 + $headerH2, $pdf->dec('Estudiante'), 1, 0, 'C', true);
        $currentX += $nameWidth;

        // Nombres de cursos (fila superior)
        foreach ($courseColumns as $pcId => $data) {
            $unitCount  = $data['assignments']->count();
            $courseWidth = ($unitCount * $unitWidth) + $promWidth;

            $pdf->SetFillColor(47, 117, 182);
            $pdf->SetXY($currentX, $headerY);
            $pdf->CellUTF8($courseWidth, $headerH1, $pdf->dec($data['name']), 1, 0, 'C', true);
            $currentX += $courseWidth;
        }

        // Fila inferior: unidades + Prom por curso
        $currentX = $startX + $numWidth + $nameWidth;
        $pdf->SetFont('Arial', 'B', 7);

        foreach ($courseColumns as $pcId => $data) {
            foreach ($data['assignments'] as $aIdx => $assignment) {
                $label = $romanNumerals[$assignment->unit - 1] ?? $assignment->unit;
                $pdf->SetFillColor(47, 117, 182);
                $pdf->SetXY($currentX, $headerY + $headerH1);
                $pdf->CellUTF8($unitWidth, $headerH2, $label, 1, 0, 'C', true);
                $currentX += $unitWidth;
            }
            // Prom
            $pdf->SetFillColor(55, 86, 35);
            $pdf->SetXY($currentX, $headerY + $headerH1);
            $pdf->CellUTF8($promWidth, $headerH2, 'Prom', 1, 0, 'C', true);
            $currentX += $promWidth;
        }

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetY($headerY + $headerH1 + $headerH2);

        // ==========================================
        // FILAS DE ESTUDIANTES
        // ==========================================
        $pdf->SetFont('Arial', '', 7);
        $rowH = 4;
        $num  = 1;

        foreach ($students as $student) {
            $currentX = $startX;
            $fillBg   = $num % 2 === 0 ? [245, 245, 245] : [255, 255, 255];

            $pdf->SetFillColor(...$fillBg);
            $pdf->SetXY($currentX, $pdf->GetY());
            $pdf->CellUTF8($numWidth, $rowH, $num, 1, 0, 'C', true);
            $currentX += $numWidth;

            $nombre = trim(
                $student->user->surname . ' ' .
                    $student->user->second_surname . ', ' .
                    $student->user->first_name . ' ' .
                    $student->user->middle_name
            );
            $pdf->SetFillColor(...$fillBg);
            $pdf->SetXY($currentX, $pdf->GetY());
            $pdf->CellUTF8($nameWidth, $rowH, $pdf->dec($nombre), 1, 0, 'L', true);
            $currentX += $nameWidth;

            foreach ($courseColumns as $pcId => $data) {
                $weightedSum = 0;
                $totalPct    = 0;

                foreach ($data['assignments'] as $assignment) {
                    $pdf->SetFillColor(...$fillBg);
                    $value = '';

                    if ($assignment->gradeBook && $assignment->gradeBook->status === 'approved') {
                        $total = GradeBookTotal::where('grade_book_id', $assignment->gradeBook->id)
                            ->where('student_id', $student->id)
                            ->first();

                        if ($total) {
                            $value    = (int) ceil((float) $total->total_points);
                            $pct      = $pensum->getUnitPercentage($assignment->unit);
                            $weightedSum += $value * $pct / 100;
                            $totalPct    += $pct;
                        }
                    }

                    $pdf->SetXY($currentX, $pdf->GetY());
                    $pdf->CellUTF8($unitWidth, $rowH, $value !== '' ? (string) $value : '', 1, 0, 'C', true);
                    $currentX += $unitWidth;
                }

                // Columna Prom del curso (ponderado)
                $promValue = $totalPct > 0 ? (int) round($weightedSum * 100 / $totalPct) : '';
                $fillProm  = $num % 2 === 0 ? [198, 239, 206] : [214, 228, 188];

                $pdf->SetFillColor(...$fillProm);
                $pdf->SetFont('Arial', 'B', 7);
                $pdf->SetXY($currentX, $pdf->GetY());

                if ($promValue !== '' && $promValue < 60) {
                    $pdf->SetTextColor(156, 0, 6);
                }
                $pdf->CellUTF8($promWidth, $rowH, $promValue !== '' ? (string) $promValue : '', 1, 0, 'C', true);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('Arial', '', 7);
                $currentX += $promWidth;
            }

            $pdf->Ln($rowH);
            $num++;
        }

        $grado   = $classroom->grade->grade_name;
        $seccion = $classroom->section->section_name;
        $name    = 'Sabana_' . preg_replace('/\s+/', '_', $grado) . "_{$seccion}_" . date('dmY_His') . '.pdf';

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $name . '"');
    }

    public function cuadroVacio(Request $request)
    {
        $request->validate([
            'classroom_id'     => 'required|exists:classrooms,id',
            'pensum_course_id' => 'required|exists:pensum_courses,id',
        ]);

        $professor = Auth::user()->professor;
        $classroom = Classroom::with(['level', 'grade', 'section'])->findOrFail($request->classroom_id);

        // Verificar que el profesor está asignado a ese curso en ese classroom
        $hasAssignment = ClassroomCourseAssignment::where('classroom_id', $classroom->id)
            ->where('pensum_course_id', $request->pensum_course_id)
            ->where('professor_id', $professor->id)
            ->exists();

        if (! $hasAssignment) {
            abort(403, 'No tienes permiso para ver este cuadro.');
        }

        // Obtener la primera asignación para datos del curso
        $assignment = ClassroomCourseAssignment::with([
            'pensumCourse.course',
            'gradeBook.activities.activityType',
        ])
            ->where('classroom_id', $classroom->id)
            ->where('pensum_course_id', $request->pensum_course_id)
            ->where('professor_id', $professor->id)
            ->first();

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

        $curso    = $assignment->pensumCourse->course->course_name;
        $nivel    = $classroom->level->level_name;
        $grado    = $classroom->grade->grade_name;
        $seccion  = $classroom->section->section_name;
        $anio     = $classroom->year;
        $profesor = $professor->user->name;

        // Obtener actividades del grade book si existe (cualquier estado)
        $activities = collect();
        if ($assignment->gradeBook) {
            $activities = $assignment->gradeBook->activities;
        }

        // Calcular anchos
        $actCount    = $activities->count();
        $hasExtra    = $actCount > 0 && $activities->filter(fn($a) => $a->activityType->is_extra)->count() > 0;
        $numWidth    = 7;
        $sumColWidth = 10;
        $totalCols   = $hasExtra ? 3 : 2;
        $totalSumW   = $totalCols * $sumColWidth;
        $usableWidth = 310;
        $minNameWidth = 55;
        $maxColWidth  = 14;
        $minColWidth  = 6;

        if ($actCount > 0) {
            $availableForNameAndActs = $usableWidth - $numWidth - $totalSumW;
            $colWidth  = $maxColWidth;
            $nameWidth = $availableForNameAndActs - ($actCount * 2 * $colWidth);

            if ($nameWidth < $minNameWidth) {
                $nameWidth = $minNameWidth;
                $colWidth  = ($availableForNameAndActs - $nameWidth) / ($actCount * 2);
                $colWidth  = max($minColWidth, round($colWidth, 1));
                $nameWidth = $availableForNameAndActs - ($actCount * 2 * $colWidth);
            }
        } else {
            // Sin actividades: columnas genéricas en blanco
            $colWidth  = 12;
            $nameWidth = 80;
        }

        $pdf = new PDF('L', 'mm', [215, 330]);
        $pdf->hideFooter = true;
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
        // Unidad en blanco para llenar a mano
        $pdf->CellUTF8(103, 5, $pdf->dec('UNIDAD: ____________________'), 0, 0, 'C');
        $pdf->CellUTF8(104, 5, $pdf->dec('AÑO: ' . $anio), 0, 1, 'R');

        $pdf->SetFont('Arial', '', 8);
        $pdf->CellUTF8(103, 4, $pdf->dec('NIVEL: ' . $nivel), 0, 0, 'L');
        $pdf->CellUTF8(103, 4, $pdf->dec('CURSO: ' . $curso), 0, 0, 'C');
        $pdf->CellUTF8(104, 4, $pdf->dec('PROFESOR(A): ' . $profesor), 0, 1, 'R');
        $pdf->Ln(1);

        // ENCABEZADO TABLA
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

        if ($actCount > 0) {
            $pdf->SetFont('Arial', 'B', 7);
            foreach ($activities as $activity) {
                $isExtra = $activity->activityType->is_extra;
                $pdf->SetFillColor($isExtra ? 255 : 217, $isExtra ? 243 : 217, $isExtra ? 205 : 217);
                $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, (string) $activity->ordering);
                $currentX += $colWidth;
                $pdf->SetFillColor(198, 239, 206);
                $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, 'Mejora');
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
        } else {
            // Columnas genéricas en blanco
            $pdf->SetFont('Arial', 'B', 7);
            $blankCols = 10;
            for ($i = 1; $i <= $blankCols; $i++) {
                $pdf->SetFillColor(217, 217, 217);
                $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, '');
                $currentX += $colWidth;
            }
            $pdf->SetFillColor(180, 180, 180);
            $pdf->rotatedHeader($currentX, $headerY, 10, $headerH, 'Total');
        }

        $pdf->SetY($headerY + $headerH);

        // FILAS DE ESTUDIANTES (vacías)
        $pdf->SetFont('Arial', '', 8);
        $rowH = 5;
        $num  = 1;

        foreach ($students as $student) {
            $currentX = $startX;
            $fill     = $num % 2 === 0 ? [245, 245, 245] : [255, 255, 255];

            $pdf->SetFillColor(...$fill);
            $pdf->SetXY($currentX, $pdf->GetY());
            $pdf->CellUTF8($numWidth, $rowH, $num, 1, 0, 'C', true);
            $currentX += $numWidth;

            $nombre = trim(
                $student->user->surname . ' ' .
                    $student->user->second_surname . ', ' .
                    $student->user->first_name . ' ' .
                    $student->user->middle_name
            );
            $pdf->SetXY($currentX, $pdf->GetY());
            $pdf->CellUTF8($nameWidth, $rowH, $pdf->dec($nombre), 1, 0, 'L', true);
            $currentX += $nameWidth;

            if ($actCount > 0) {
                foreach ($activities as $activity) {
                    $pdf->SetFillColor(...$fill);
                    $pdf->SetXY($currentX, $pdf->GetY());
                    $pdf->CellUTF8($colWidth, $rowH, '', 1, 0, 'C', true);
                    $currentX += $colWidth;
                    $pdf->SetXY($currentX, $pdf->GetY());
                    $pdf->CellUTF8($colWidth, $rowH, '', 1, 0, 'C', true);
                    $currentX += $colWidth;
                }
                $pdf->SetFillColor(230, 230, 230);
                $pdf->SetXY($currentX, $pdf->GetY());
                $pdf->CellUTF8(10, $rowH, '', 1, 0, 'C', true);
                $currentX += 10;
                if ($hasExtra) {
                    $pdf->SetFillColor(255, 235, 156);
                    $pdf->SetXY($currentX, $pdf->GetY());
                    $pdf->CellUTF8(10, $rowH, '', 1, 0, 'C', true);
                    $currentX += 10;
                }
                $pdf->SetFillColor(230, 230, 230);
                $pdf->SetXY($currentX, $pdf->GetY());
                $pdf->CellUTF8(10, $rowH, '', 1, 0, 'C', true);
            } else {
                $blankCols = 10;
                for ($i = 1; $i <= $blankCols; $i++) {
                    $pdf->SetFillColor(...$fill);
                    $pdf->SetXY($currentX, $pdf->GetY());
                    $pdf->CellUTF8($colWidth, $rowH, '', 1, 0, 'C', true);
                    $currentX += $colWidth;
                }
                $pdf->SetFillColor(230, 230, 230);
                $pdf->SetXY($currentX, $pdf->GetY());
                $pdf->CellUTF8(10, $rowH, '', 1, 0, 'C', true);
            }

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

        $safeCurso = preg_replace('/[^A-Za-z0-9_\-]/', '_', $curso);
        $name = "CuadroVacio_{$safeCurso}_" . date('dmY_His') . '.pdf';

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $name . '"');
    }

    public function cuadrosUnidad(Request $request)
    {
        $request->validate([
            'unit' => 'required|integer|min:1',
        ]);

        $professor = Auth::user()->professor;

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
                $q->where('professor_id', $professor->id)
                    ->where('unit', $request->unit)
                    ->whereHas('classroom', fn($q) => $q->where('year', date('Y')))
            )
            ->get();

        if ($gradeBooks->isEmpty()) {
            abort(404, 'No hay cuadros aprobados para esta unidad.');
        }

        $zipPath = sys_get_temp_dir() . '/cuadros_' . uniqid() . '.zip';
        $zip     = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);

        foreach ($gradeBooks as $gradeBook) {
            $students = $this->getStudentsForGradeBook($gradeBook);
            $pdf2 = new PDF('L', 'mm', [215, 330]);
            $pdf2->SetMargins(10, 10, 10);
            $pdf2->SetAutoPageBreak(true, 14);
            $pdf2->AliasNbPages();
            $this->appendGradeBookToPdf($pdf2, $gradeBook, $students);
            $content = $pdf2->Output('S');

            $grado    = $gradeBook->assignment->classroom->grade->grade_name;
            $seccion  = $gradeBook->assignment->classroom->section->section_name;
            $curso    = $gradeBook->assignment->pensumCourse->course->course_name;
            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', "{$grado}_{$seccion}_{$curso}");
            $zip->addFromString("Cuadro_U{$request->unit}_{$safeName}.pdf", $content);
        }

        $zip->close();

        $zipName = "MisCuadros_U{$request->unit}_" . date('dmY') . '.zip';

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    public function cuadrosUnidadViewOne(GradeBook $gradeBook)
    {
        $professor = Auth::user()->professor;

        if ($gradeBook->status !== 'approved') {
            abort(403, 'El cuadro no está aprobado.');
        }

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

        // Después
        $students = $this->getStudentsForGradeBook($gradeBook);

        $pdf = new PDF('L', 'mm', [215, 330]);
        $pdf->hideFooter = true;
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->AliasNbPages();

        $this->appendGradeBookToPdf($pdf, $gradeBook, $students);

        $content = $pdf->Output('S');

        $curso    = $gradeBook->assignment->pensumCourse->course->course_name;
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $curso);
        $name     = "Cuadro_U{$gradeBook->assignment->unit}_{$safeName}.pdf";

        return response($content, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $name . '"');
    }

    public function cuadrosUnidadViewAll(Request $request)
    {
        $request->validate([
            'unit' => 'required|integer|min:1',
        ]);

        $professor = Auth::user()->professor;

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
                $q->where('professor_id', $professor->id)
                    ->where('unit', $request->unit)
                    ->whereHas('classroom', fn($q) => $q->where('year', date('Y')))
            )
            ->get();

        if ($gradeBooks->isEmpty()) {
            abort(404, 'No hay cuadros aprobados para esta unidad.');
        }

        $pdf = new PDF('L', 'mm', [215, 330]);
        $pdf->hideFooter = true;
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->AliasNbPages();

        foreach ($gradeBooks as $gradeBook) {
            $students = $this->getStudentsForGradeBook($gradeBook);
            $this->appendGradeBookToPdf($pdf, $gradeBook, $students);
        }

        $name = "MisCuadros_U{$request->unit}_" . date('dmY_His') . '.pdf';

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $name . '"');
    }

    protected function getStudentsForGradeBook(GradeBook $gradeBook)
    {
        return Student::whereHas(
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
    }

    protected function appendGradeBookToPdf(PDF $pdf, GradeBook $gradeBook, $students): void
    {
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

        // ENCABEZADO TABLA
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
            $pdf->SetFillColor(198, 239, 206);
            $pdf->rotatedHeader($currentX, $headerY, $colWidth, $headerH, 'Mejora');
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

            $nombre = trim(
                $student->user->surname . ' ' . $student->user->second_surname . ', ' .
                    $student->user->first_name . ' ' . $student->user->middle_name
            );
            $pdf->SetXY($currentX, $pdf->GetY());
            $pdf->CellUTF8($nameWidth, $rowH, $pdf->dec($nombre), 1, 0, 'L', true);
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
    }

    public function studentList(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $professor = Auth::user()->professor;
        $classroom = Classroom::with(['level', 'grade', 'section'])->findOrFail($request->classroom_id);

        // Verify professor has at least one assignment in this classroom
        $hasAssignment = ClassroomCourseAssignment::where('classroom_id', $classroom->id)
            ->where('professor_id', $professor->id)
            ->exists();

        if (! $hasAssignment) {
            abort(403, 'No tienes permiso para ver este listado.');
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

        $levelName   = $classroom->level->level_name;
        $gradeName   = $classroom->grade->grade_name;
        $sectionName = $classroom->section->section_name;
        $year        = $classroom->year;
        $profName    = $professor->user->name;

        $pdf = new PDF('P', 'mm', 'Letter');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // HEADER
        $logoPath = env('APP_INSTITUTION_LOGO_IMG', 'vendor/adminlte/dist/img/Escudo.png');
        $pdf->addImage($logoPath, 15, 12, 18);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->CellUTF8(180, 7, $pdf->dec(env('APP_INSTITUTION_NAME', 'Institución Educativa')), 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->CellUTF8(180, 6, $pdf->dec('Listado de Estudiantes'), 0, 1, 'C');
        $pdf->Ln(3);

        $pdf->SetFont('Arial', '', 9);
        $pdf->CellUTF8(90, 5, $pdf->dec('NIVEL: ' . $levelName), 0, 0, 'L');
        $pdf->CellUTF8(90, 5, $pdf->dec('AÑO: ' . $year), 0, 1, 'R');

        $pdf->CellUTF8(90, 5, $pdf->dec('GRADO: ' . $gradeName . ' ' . $sectionName), 0, 0, 'L');
        $pdf->CellUTF8(90, 5, $pdf->dec('PROFESOR(A): ' . $profName), 0, 1, 'R');
        $pdf->Ln(4);

        // TABLE HEADER
        $numWidth         = 10;
        $nameWidth        = 100;
        $observationWidth = 70;
        $rowHeight        = 7;

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(47, 117, 182);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->CellUTF8($numWidth, $rowHeight, 'No.', 1, 0, 'C', true);
        $pdf->CellUTF8($nameWidth, $rowHeight, $pdf->dec('Estudiante'), 1, 0, 'C', true);
        $pdf->CellUTF8($observationWidth, $rowHeight, $pdf->dec('Observación'), 1, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 9);

        $count = 1;
        foreach ($students as $student) {
            $fillColor = $count % 2 === 0 ? [240, 240, 240] : [255, 255, 255];
            $pdf->SetFillColor(...$fillColor);

            $fullName = trim(
                $student->user->surname . ' ' .
                    $student->user->second_surname . ', ' .
                    $student->user->first_name . ' ' .
                    $student->user->middle_name
            );

            $pdf->CellUTF8($numWidth, $rowHeight, $count, 1, 0, 'C', true);
            $pdf->CellUTF8($nameWidth, $rowHeight, $pdf->dec($fullName), 1, 0, 'L', true);
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

        $professor = Auth::user()->professor;
        $classroom = Classroom::with(['grade', 'section'])->findOrFail($request->classroom_id);

        $hasAssignment = ClassroomCourseAssignment::where('classroom_id', $classroom->id)
            ->where('professor_id', $professor->id)
            ->exists();

        if (! $hasAssignment) {
            abort(403);
        }

        $grade   = preg_replace('/[^A-Za-z0-9_\-]/', '_', $classroom->grade->grade_name);
        $section = preg_replace('/[^A-Za-z0-9_\-]/', '_', $classroom->section->section_name);
        $fileName = "StudentList_{$grade}_{$section}_" . date('dmY_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\StudentListExport($classroom->id),
            $fileName
        );
    }

    public function missingActivitiesExport(Request $request)
    {
        $request->validate([
            'classroom_id'    => 'required|exists:classrooms,id',
            'pensum_course_id' => 'required|exists:pensum_courses,id',
            'unit'            => 'required|integer|min:1',
        ]);

        $professor = Auth::user()->professor;

        $export = new \App\Exports\MissingActivitiesProfesorExport(
            classroomId: (int) $request->classroom_id,
            pensumCourseId: (int) $request->pensum_course_id,
            unit: (int) $request->unit,
            professorId: $professor->id,
        );

        return \Maatwebsite\Excel\Facades\Excel::download($export, 'actividades_faltantes_' . date('dmY') . '.xlsx');
    }
}
