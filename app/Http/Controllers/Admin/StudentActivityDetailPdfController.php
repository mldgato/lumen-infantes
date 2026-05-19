<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PDF;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBookScore;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentActivityDetailPdfController extends Controller
{
    public function student(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'student_id' => 'required|exists:students,id',
            'unit' => 'required|integer|min:1',
        ]);

        $classroom = Classroom::with(['level', 'grade', 'section'])->findOrFail($request->classroom_id);
        $student = Student::with('user')->findOrFail($request->student_id);

        $enrolled = $student->enrollments()
            ->where('classroom_id', $classroom->id)
            ->where('status', 'Activo')
            ->exists();

        abort_unless($enrolled, 403);

        $unit = (int) $request->unit;
        $courses = $this->buildCourseData($classroom->id, $student->id, $unit);

        $pdf = $this->buildPdf();
        $this->renderStudent($pdf, $student->user->full_full_name, $courses, $classroom, $unit);

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $student->user->full_full_name);

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Actividades_{$safeName}_U{$request->unit}.pdf\"",
        ]);
    }

    public function classroom(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'unit' => 'required|integer|min:1',
        ]);

        $classroom = Classroom::with(['level', 'grade', 'section'])->findOrFail($request->classroom_id);

        $students = Student::whereHas(
            'enrollments',
            fn ($q) => $q->where('classroom_id', $classroom->id)->where('status', 'Activo')
        )
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->select('students.*')
            ->with('user')
            ->get();

        $unit = (int) $request->unit;
        $pdf = $this->buildPdf();

        foreach ($students as $student) {
            $courses = $this->buildCourseData($classroom->id, $student->id, $unit);
            $this->renderStudent($pdf, $student->user->full_full_name, $courses, $classroom, $unit);
        }

        $grade = $classroom->grade->grade_name ?? '';
        $section = $classroom->section->section_name ?? '';

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Actividades_Seccion_{$grade}_{$section}_U{$request->unit}.pdf\"",
        ]);
    }

    public function studentCompact(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'student_id' => 'required|exists:students,id',
            'unit' => 'required|integer|min:1',
        ]);

        $classroom = Classroom::with(['level', 'grade', 'section'])->findOrFail($request->classroom_id);
        $student = Student::with('user')->findOrFail($request->student_id);

        $enrolled = $student->enrollments()
            ->where('classroom_id', $classroom->id)
            ->where('status', 'Activo')
            ->exists();

        abort_unless($enrolled, 403);

        $unit = (int) $request->unit;
        $courses = $this->buildCourseData($classroom->id, $student->id, $unit);

        $pdf = new PDF('P', 'mm', [210, 279]);
        $pdf->SetMargins(12, 10, 12);
        $pdf->SetAutoPageBreak(false);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $logoPath = env('APP_INSTITUTION_LOGO_IMG', 'vendor/adminlte/dist/img/Escudo.png');
        $pdf->addImage($logoPath, 12, 8, 15);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(186, 5, $pdf->dec(env('APP_INSTITUTION_NAME', 'Institución Educativa')), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->CellUTF8(186, 4, $pdf->dec('Resumen de Actividades por Estudiante'), 0, 1, 'C');
        $pdf->Ln(3);

        // Info del aula
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetFillColor(230, 238, 247);
        $pdf->CellUTF8(93, 4, $pdf->dec('Año: '.$classroom->year.'   |   Unidad: '.$unit), 0, 0, 'L', true);
        $pdf->CellUTF8(93, 4, $pdf->dec('Nivel: '.($classroom->level->level_name ?? '—')), 0, 1, 'L', true);
        $pdf->CellUTF8(93, 4, $pdf->dec('Grado: '.($classroom->grade->grade_name ?? '—').'   '.($classroom->section->section_name ?? '')), 0, 0, 'L', true);
        $pdf->CellUTF8(93, 4, '', 0, 1, 'L', true);
        $pdf->Ln(3);

        // Nombre del estudiante
        $pdf->SetFillColor(31, 78, 121);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->CellUTF8(186, 8, $pdf->dec('  '.$student->user->full_full_name), 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(4);

        // Encabezado de la tabla resumen
        $pdf->SetFillColor(47, 117, 182);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->CellUTF8(10, 6, 'No.', 1, 0, 'C', true);
        $pdf->CellUTF8(110, 6, $pdf->dec('Materia'), 1, 0, 'L', true);
        $pdf->CellUTF8(22, 6, $pdf->dec('Hechas'), 1, 0, 'C', true);
        $pdf->CellUTF8(22, 6, 'Total', 1, 0, 'C', true);
        $pdf->CellUTF8(22, 6, $pdf->dec('Faltantes'), 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);

        // Filas de cursos
        $totalDone = 0;
        $totalAll = 0;
        $totalMissing = 0;

        foreach ($courses as $i => $course) {
            $fill = $i % 2 === 0;
            $pdf->SetFillColor(245, 245, 245);
            $pdf->SetFont('Arial', '', 8);

            $done = $course['done'];
            $total = $course['total'];
            $missing = $total - $done;

            $totalDone += $done;
            $totalAll += $total;
            $totalMissing += $missing;

            $pdf->CellUTF8(10, 6, (string) ($i + 1), 1, 0, 'C', $fill);
            $pdf->CellUTF8(110, 6, $pdf->dec('  '.$course['course_name']), 1, 0, 'L', $fill);

            if (! $course['has_activities']) {
                $pdf->SetTextColor(120, 120, 120);
                $pdf->CellUTF8(66, 6, $pdf->dec('Sin cuadro registrado'), 1, 1, 'C', $fill);
                $pdf->SetTextColor(0, 0, 0);

                continue;
            }

            // Hechas
            $pdf->SetTextColor(0, 0, 0);
            $pdf->CellUTF8(22, 6, (string) $done, 1, 0, 'C', $fill);

            // Total
            $pdf->CellUTF8(22, 6, (string) $total, 1, 0, 'C', $fill);

            // Faltantes con color
            if ($missing === 0) {
                $pdf->SetTextColor(39, 98, 33);
                $pdf->SetFillColor(198, 239, 206);
            } elseif ($missing <= 3) {
                $pdf->SetTextColor(156, 101, 0);
                $pdf->SetFillColor(255, 235, 156);
            } else {
                $pdf->SetTextColor(156, 0, 6);
                $pdf->SetFillColor(255, 199, 206);
            }
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->CellUTF8(22, 6, (string) $missing, 1, 1, 'C', true);
            $pdf->SetTextColor(0, 0, 0);
        }

        // Fila de totales
        $pdf->SetFillColor(217, 226, 243);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->CellUTF8(120, 7, $pdf->dec('  TOTAL'), 1, 0, 'L', true);
        $pdf->CellUTF8(22, 7, (string) $totalDone, 1, 0, 'C', true);
        $pdf->CellUTF8(22, 7, (string) $totalAll, 1, 0, 'C', true);

        if ($totalMissing === 0) {
            $pdf->SetTextColor(39, 98, 33);
            $pdf->SetFillColor(198, 239, 206);
        } elseif ($totalMissing <= 5) {
            $pdf->SetTextColor(156, 101, 0);
            $pdf->SetFillColor(255, 235, 156);
        } else {
            $pdf->SetTextColor(156, 0, 6);
            $pdf->SetFillColor(255, 199, 206);
        }
        $pdf->CellUTF8(22, 7, (string) $totalMissing, 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $student->user->full_full_name);

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Resumen_{$safeName}_U{$unit}.pdf\"",
        ]);
    }

    private function buildCourseData(int $classroomId, int $studentId, int $unit): array
    {
        $assignments = ClassroomCourseAssignment::with([
            'pensumCourse.course',
            'professor.user',
            'gradeBook.activities.activityType',
        ])
            ->where('classroom_id', $classroomId)
            ->where('unit', $unit)
            ->get();

        $courses = [];

        foreach ($assignments as $assignment) {
            $gradeBook = $assignment->gradeBook;
            $courseName = $assignment->pensumCourse->course->course_name;

            $mainActivities = $gradeBook ? $gradeBook->activities->where('activity_type_id', 1) : collect();

            if (! $gradeBook || $mainActivities->isEmpty()) {
                $courses[] = [
                    'course_name' => $courseName,
                    'professor_name' => $assignment->professor->user->name ?? '—',
                    'has_activities' => false,
                    'activities' => [],
                    'done' => 0,
                    'total' => 0,
                ];

                continue;
            }

            $activityIds = $mainActivities->pluck('id');
            $studentScores = GradeBookScore::whereIn('grade_book_activity_id', $activityIds)
                ->where('student_id', $studentId)
                ->get()
                ->keyBy('grade_book_activity_id');

            $activities = [];
            $done = 0;

            foreach ($mainActivities as $activity) {
                $score = $studentScores->get($activity->id);
                $isDone = $score !== null && $score->score !== null && (float) $score->score > 0;
                if ($isDone) {
                    $done++;
                }
                $activities[] = [
                    'name' => $activity->name,
                    'type' => $activity->activityType->name ?? '',
                    'done' => $isDone,
                ];
            }

            $courses[] = [
                'course_name' => $courseName,
                'professor_name' => $assignment->professor->user->name ?? '—',
                'has_activities' => true,
                'activities' => $activities,
                'done' => $done,
                'total' => $gradeBook->activities->count(),
            ];
        }

        return $courses;
    }

    private function buildPdf(): PDF
    {
        $pdf = new PDF('P', 'mm', [210, 279]);
        $pdf->SetMargins(12, 10, 12);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AliasNbPages();

        return $pdf;
    }

    private function renderStudent(PDF $pdf, string $studentName, array $courses, Classroom $classroom, int $unit): void
    {
        $pdf->AddPage();

        $logoPath = env('APP_INSTITUTION_LOGO_IMG', 'vendor/adminlte/dist/img/Escudo.png');
        $pdf->addImage($logoPath, 12, 8, 18);

        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(186, 6, $pdf->dec(env('APP_INSTITUTION_NAME', 'Institución Educativa')), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->CellUTF8(186, 5, $pdf->dec('Informe de Actividades por Estudiante'), 0, 1, 'C');
        $pdf->Ln(3);

        // Info del aula
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetFillColor(230, 238, 247);
        $pdf->CellUTF8(93, 5, $pdf->dec('Año: '.$classroom->year.'   |   Unidad: '.$unit), 0, 0, 'L', true);
        $pdf->CellUTF8(93, 5, $pdf->dec('Nivel: '.($classroom->level->level_name ?? '—')), 0, 1, 'L', true);
        $pdf->CellUTF8(93, 5, $pdf->dec('Grado: '.($classroom->grade->grade_name ?? '—').'   '.($classroom->section->section_name ?? '')), 0, 0, 'L', true);
        $pdf->CellUTF8(93, 5, '', 0, 1, 'L', true);
        $pdf->Ln(2);

        // Nombre del estudiante
        $pdf->SetFillColor(31, 78, 121);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->CellUTF8(186, 8, $pdf->dec('  '.$studentName), 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(3);

        // Cursos
        foreach ($courses as $course) {
            // Encabezado de curso
            $pdf->SetFillColor(47, 117, 182);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 9);

            $doneLabel = $course['has_activities']
                ? $pdf->dec("  {$course['done']}/{$course['total']} entregadas")
                : $pdf->dec('  Sin actividades registradas');

            $pdf->CellUTF8(120, 6, $pdf->dec('  '.$course['course_name']), 0, 0, 'L', true);
            $pdf->CellUTF8(66, 6, $doneLabel, 0, 1, 'R', true);
            $pdf->SetTextColor(0, 0, 0);

            // Profesor
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->SetFillColor(234, 242, 251);
            $pdf->CellUTF8(186, 4, $pdf->dec('  Prof. '.$course['professor_name']), 0, 1, 'L', true);

            if (! $course['has_activities']) {
                $pdf->SetFont('Arial', '', 8);
                $pdf->CellUTF8(186, 5, $pdf->dec('  El profesor aun no ha registrado actividades en este cuadro.'), 0, 1, 'L');
                $pdf->Ln(1);

                continue;
            }

            // Tabla de actividades
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(217, 217, 217);
            $pdf->CellUTF8(100, 5, $pdf->dec('Actividad'), 1, 0, 'L', true);
            $pdf->CellUTF8(50, 5, $pdf->dec('Tipo'), 1, 0, 'L', true);
            $pdf->CellUTF8(36, 5, 'Estado', 1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 8);
            foreach ($course['activities'] as $i => $activity) {
                $fill = $i % 2 === 0;
                $pdf->SetFillColor(245, 245, 245);

                $pdf->CellUTF8(100, 5, $pdf->dec('  '.$activity['name']), 1, 0, 'L', $fill);
                $pdf->CellUTF8(50, 5, $pdf->dec($activity['type']), 1, 0, 'L', $fill);

                if ($activity['done']) {
                    $pdf->SetTextColor(39, 98, 33);
                    $pdf->SetFillColor(198, 239, 206);
                    $pdf->CellUTF8(36, 5, $pdf->dec('Entregada'), 1, 1, 'C', true);
                } else {
                    $pdf->SetTextColor(156, 0, 6);
                    $pdf->SetFillColor(255, 199, 206);
                    $pdf->CellUTF8(36, 5, $pdf->dec('No entregada'), 1, 1, 'C', true);
                }
                $pdf->SetTextColor(0, 0, 0);
            }

            $pdf->Ln(3);
        }
    }
}
