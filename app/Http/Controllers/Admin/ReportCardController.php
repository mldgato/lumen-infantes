<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PDF;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Grade;
use App\Models\GradeBookTotal;
use App\Models\Pensum;
use App\Models\PensumCourse;
use App\Models\Student;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    public function all(Request $request)
    {
        $request->validate([
            'year'    => 'required',
            'level'   => 'required|exists:levels,id',
            'grade'   => 'required|exists:grades,id',
            'section' => 'required',
            'unit'    => 'required|integer|min:1',
        ]);

        $classroomQuery = Classroom::with(['level', 'grade', 'section'])
            ->where('year', $request->year)
            ->where('level_id', $request->level)
            ->where('grade_id', $request->grade);

        if ($request->section !== 'all') {
            $classroomQuery->where('section_id', $request->section);
        }

        $classrooms = $classroomQuery->get();

        if ($classrooms->isEmpty()) {
            abort(404, 'No se encontraron aulas para los filtros seleccionados.');
        }

        $pdf = new PDF('P', 'mm', [210, 279]);
        $pdf->SetMargins(7.5, 10, 7.5);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AliasNbPages();

        foreach ($classrooms as $classroom) {
            $pensum = Pensum::where('grade_id', $classroom->grade_id)
                ->where('year', $classroom->year)
                ->first();

            if (! $pensum) continue;

            $assignments = $this->loadAssignments($classroom->id);

            $students = $this->getStudents($classroom->id);

            foreach ($students as $clave => $student) {
                $this->appendBoleta($pdf, $student, $classroom, $pensum, $assignments, (int) $request->unit, $clave + 1);
            }
        }

        $grade    = Grade::find($request->grade);
        $safeName = preg_replace('/\s+/', '_', $grade->grade_name);
        $name     = "Boletas_{$safeName}_" . date('dmY_His') . '.pdf';

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $name . '"');
    }

    public function student(Request $request)
    {
        $request->validate([
            'student_id'   => 'required|exists:students,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'unit'         => 'required|integer|min:1',
        ]);

        $student  = Student::with('user')->findOrFail($request->student_id);
        $classroom = Classroom::with(['level', 'grade', 'section'])->findOrFail($request->classroom_id);

        $pensum = Pensum::where('grade_id', $classroom->grade_id)
            ->where('year', $classroom->year)
            ->first();

        if (! $pensum) abort(404, 'No existe un pénsum para este grado y año.');

        $enrolled = $student->enrollments()
            ->where('classroom_id', $classroom->id)
            ->where('status', 'Activo')
            ->exists();

        if (! $enrolled) abort(403, 'El estudiante no está inscrito en esta aula.');

        $students = $this->getStudents($classroom->id)->pluck('id')->values();
        $clave    = $students->search($student->id) + 1;

        $assignments = $this->loadAssignments($classroom->id);

        $pdf = new PDF('P', 'mm', [210, 279]);
        $pdf->SetMargins(7.5, 10, 7.5);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AliasNbPages();

        $this->appendBoleta($pdf, $student, $classroom, $pensum, $assignments, (int) $request->unit, $clave);

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $student->user->surname . '_' . $student->user->first_name);
        $name     = "Boleta_{$safeName}_" . date('dmY_His') . '.pdf';

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $name . '"');
    }

    // ==========================================
    // HELPERS
    // ==========================================

    protected function getStudents(int $classroomId)
    {
        return Student::whereHas(
            'enrollments',
            fn($q) =>
            $q->where('classroom_id', $classroomId)->where('status', 'Activo')
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

    protected function loadAssignments(int $classroomId): \Illuminate\Support\Collection
    {
        return ClassroomCourseAssignment::with([
            'gradeBook' => fn($q) => $q->where('status', 'approved')->with('totals'),
        ])
            ->where('classroom_id', $classroomId)
            ->get()
            ->keyBy(fn($a) => $a->pensum_course_id . '-' . $a->unit);
    }

    protected function appendBoleta(PDF $pdf, Student $student, Classroom $classroom, Pensum $pensum, \Illuminate\Support\Collection $assignments, int $unit, int $clave): void
    {
        $pdf->AddPage();

        $levelName       = $classroom->level->level_name;
        $gradeName       = $classroom->grade->grade_name;
        $sectionName     = $classroom->section->section_name;
        $year            = $classroom->year;
        $institutionName = env('APP_INSTITUTION_NAME', 'Institución Educativa');
        $logoPath        = env('APP_INSTITUTION_LOGO_IMG', 'vendor/adminlte/dist/img/AdminLTELogo.png');
        $usableWidth     = 195;
        $romanNumerals   = ['I', 'II', 'III', 'IV', 'V', 'VI'];

        // Dynamic column widths
        $totalUnits  = $pensum->units; // total de columnas siempre fijas
        $numWidth    = 10;
        $acumWidth   = 22;
        $unitWidth   = 18;
        $courseWidth = $usableWidth - $numWidth - ($totalUnits * $unitWidth) - $acumWidth;

        if ($courseWidth < 50) {
            $courseWidth = 50;
            $unitWidth   = max(12, round(($usableWidth - $numWidth - $acumWidth - $courseWidth) / $totalUnits, 1));
            $courseWidth = $usableWidth - $numWidth - ($totalUnits * $unitWidth) - $acumWidth;
        }

        $rowH = 7;

        // ==========================================
        // HEADER
        // ==========================================
        $pdf->addImage($logoPath, 7.5, 8, 16);

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetXY(7.5, 8);
        $pdf->CellUTF8($usableWidth, 6, $pdf->dec($institutionName), 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetX(7.5);
        $pdf->CellUTF8($usableWidth, 5, $pdf->dec('BOLETA DE CALIFICACIONES'), 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetX(7.5);
        $pdf->CellUTF8($usableWidth, 5, $pdf->dec('CICLO ESCOLAR ' . $year), 0, 1, 'C');
        $pdf->Ln(2);

        // Student info block
        $pdf->SetLineWidth(0.4);
        $blockY = $pdf->GetY();
        $pdf->Rect(7.5, $blockY, $usableWidth, 25);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY(10, $blockY + 2);
        $pdf->CellUTF8(35, 4, $pdf->dec('Código del Alumno:'), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->CellUTF8(100, 4, $pdf->dec($student->carnet ?? ''), 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(10);
        $pdf->CellUTF8(35, 4, $pdf->dec('Nombre del Alumno:'), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $fullName = trim(
            $student->user->surname . ' ' .
                $student->user->second_surname . ', ' .
                $student->user->first_name . ' ' .
                $student->user->middle_name
        );
        $pdf->CellUTF8(120, 4, $pdf->dec($fullName), 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(10);
        $pdf->CellUTF8(12, 4, 'Grado:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->CellUTF8(55, 4, $pdf->dec($gradeName), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->CellUTF8(12, 4, 'Nivel:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->CellUTF8(35, 4, $pdf->dec($levelName), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->CellUTF8(14, 4, $pdf->dec('Sección:'), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->CellUTF8(20, 4, $pdf->dec($sectionName), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->CellUTF8(12, 4, 'Clave:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->CellUTF8(15, 4, (string) $clave, 0, 1, 'L');

        $pdf->Ln(4);

        // ==========================================
        // TABLE HEADER
        // ==========================================
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(47, 117, 182);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetLineWidth(0.2);
        $pdf->SetX(7.5);
        $pdf->CellUTF8($numWidth, $rowH, 'No.', 1, 0, 'C', true);
        $pdf->CellUTF8($courseWidth, $rowH, 'Curso', 1, 0, 'C', true);

        for ($u = 1; $u <= $totalUnits; $u++) {
            $label = ($romanNumerals[$u - 1] ?? "U{$u}") . ' UNIDAD';
            $pdf->CellUTF8($unitWidth, $rowH, $pdf->dec($label), 1, 0, 'C', true);
        }

        $pdf->CellUTF8($acumWidth, $rowH, 'ACUMULADO', 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);

        // ==========================================
        // COURSE ROWS
        // ==========================================
        $pensumCourses = PensumCourse::with('course')
            ->where('pensum_id', $pensum->id)
            ->where('is_official', true)
            ->orderBy('ordering')
            ->get();

        $pdf->SetFont('Arial', '', 8);
        $num        = 1;
        $unitSums   = array_fill(1, $totalUnits, 0);
        $unitCounts = array_fill(1, $totalUnits, 0);
        $acumSum    = 0;
        $acumCount  = 0;

        foreach ($pensumCourses as $pc) {
            $fillBg = $num % 2 === 0 ? [245, 245, 245] : [255, 255, 255];
            $pdf->SetFillColor(...$fillBg);
            $pdf->SetX(7.5);
            $pdf->CellUTF8($numWidth, $rowH, $num, 1, 0, 'C', true);
            $pdf->CellUTF8($courseWidth, $rowH, $pdf->dec($pc->course->course_name), 1, 0, 'L', true);

            $weightedSum = 0;
            $totalPct    = 0;
            $unitScores  = [];

            for ($u = 1; $u <= $totalUnits; $u++) {
                $key        = $pc->id . '-' . $u;
                $assignment = $assignments->get($key);
                $score      = '';

                // Solo mostrar score si la unidad es <= a la consultada
                if ($u <= $unit && $assignment && $assignment->gradeBook) {
                    $total = $assignment->gradeBook->totals->firstWhere('student_id', $student->id);
                    if ($total) {
                        $scoreVal      = (int) ceil((float) $total->total_points);
                        $score         = (string) $scoreVal;
                        $pct           = $pensum->getUnitPercentage($u);
                        $weightedSum   += $scoreVal * $pct / 100;
                        $totalPct      += $pct;
                        $unitSums[$u]  += $scoreVal;
                        $unitCounts[$u] += 1;
                    }
                }

                $pdf->SetFillColor(...$fillBg);
                $pdf->CellUTF8($unitWidth, $rowH, $score, 1, 0, 'C', true);
            }

            // ACUMULADO
            $acumValue = '';
            if ($totalPct > 0) {
                $acumVal   = (int) round($weightedSum * 100 / $totalPct);
                $acumValue = (string) $acumVal;
                $acumSum   += $acumVal;
                $acumCount++;
            }

            $pdf->SetFillColor(198, 239, 206);
            if ($num % 2 === 0) $pdf->SetFillColor(180, 220, 185);
            $pdf->SetFont('Arial', 'B', 8);

            if ($acumValue !== '' && (int) $acumValue < 60) {
                $pdf->SetTextColor(156, 0, 6);
            }
            $pdf->CellUTF8($acumWidth, $rowH, $acumValue, 1, 1, 'C', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', '', 8);

            $num++;
        }

        // ==========================================
        // PROMEDIO ROW
        // ==========================================
        $pdf->SetFillColor(217, 217, 217);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(7.5);
        $pdf->CellUTF8($numWidth, $rowH, '', 1, 0, 'C', true);
        $pdf->CellUTF8($courseWidth, $rowH, $pdf->dec('Promedio'), 1, 0, 'L', true);

        for ($u = 1; $u <= $totalUnits; $u++) {
            $avg = $unitCounts[$u] > 0 ? (int) round($unitSums[$u] / $unitCounts[$u]) : '';
            $pdf->CellUTF8($unitWidth, $rowH, $avg !== '' ? (string) $avg : '', 1, 0, 'C', true);
        }

        $pdf->SetFillColor(150, 200, 150);
        $acumAvg = $acumCount > 0 ? (int) round($acumSum / $acumCount) : '';
        $pdf->CellUTF8($acumWidth, $rowH, $acumAvg !== '' ? (string) $acumAvg : '', 1, 1, 'C', true);

        $pdf->Ln(4);

        // ==========================================
        // OBSERVATIONS
        // ==========================================
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetX(7.5);
        $pdf->CellUTF8($usableWidth, 5, 'OBSERVACIONES', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 8);

        for ($i = 0; $i < 4; $i++) {
            $pdf->SetX(7.5);
            $pdf->CellUTF8($usableWidth, 7, '', 1, 1, 'L');
        }

        $pdf->Ln(3);
        $pdf->SetX(7.5);
        $pdf->CellUTF8($usableWidth, 7, $pdf->dec('Firma:'), 1, 1, 'R');
    }
}
