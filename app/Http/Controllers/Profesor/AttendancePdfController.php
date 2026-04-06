<?php

namespace App\Http\Controllers\Profesor;

use App\Helpers\PDF;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ClassroomCourseAssignment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AttendancePdfController extends Controller
{
    // ==========================================
    // PROFESOR: solo ve sus propias asignaciones
    // ==========================================
    public function generate(Request $request): Response
    {
        $request->validate([
            'assignment_id' => 'required|exists:classroom_course_assignments,id',
            'from'          => 'required|date',
            'to'            => 'required|date|after_or_equal:from',
        ]);

        $assignment = ClassroomCourseAssignment::with([
            'classroom.level',
            'classroom.grade',
            'classroom.section',
            'pensumCourse.course',
            'professor.user',
        ])->findOrFail($request->assignment_id);

        $professor = Auth::user()->professor;
        if ($assignment->professor_id !== $professor->id) {
            abort(403, 'No tienes permiso para ver esta asistencia.');
        }

        return $this->buildPdf($assignment, $request->from, $request->to);
    }

    // ==========================================
    // ADMIN: puede ver cualquier asignación
    // ==========================================
    public function adminGenerate(Request $request): Response
    {
        $request->validate([
            'assignment_id' => 'required|exists:classroom_course_assignments,id',
            'from'          => 'required|date',
            'to'            => 'required|date|after_or_equal:from',
        ]);

        $assignment = ClassroomCourseAssignment::with([
            'classroom.level',
            'classroom.grade',
            'classroom.section',
            'pensumCourse.course',
            'professor.user',
        ])->findOrFail($request->assignment_id);

        return $this->buildPdf($assignment, $request->from, $request->to);
    }

    // ==========================================
    // GENERACIÓN COMÚN
    // ==========================================
    private function buildPdf(ClassroomCourseAssignment $assignment, string $from, string $to): Response
    {
        $records = AttendanceRecord::where('classroom_course_assignment_id', $assignment->id)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->with('entries')
            ->get();

        if ($records->isEmpty()) {
            abort(404, 'No hay registros de asistencia para el rango seleccionado.');
        }

        $students = Student::whereHas('enrollments', function ($q) use ($assignment) {
            $q->where('classroom_id', $assignment->classroom_id)
                ->where('status', 'Activo');
        })
            ->join('users', 'students.user_id', '=', 'users.id')
            ->select('students.*')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->with('user')
            ->get();

        // Constantes de layout (oficio horizontal = 330×215 mm)
        $usableWidth   = 310;
        $numWidth      = 7;
        $nameWidth     = 65;
        $summaryWidth  = 12; // columnas Pres. y Aus.
        $dateColWidth  = 7;
        $headerH       = 14;
        $rowH          = 4;

        $maxPerPage = (int) floor(
            ($usableWidth - $numWidth - $nameWidth - $summaryWidth * 2) / $dateColWidth
        );

        $chunks = $records->chunk($maxPerPage);

        // Datos del encabezado
        $classroom = $assignment->classroom;
        $nivel     = $classroom->level->level_name;
        $grado     = $classroom->grade->grade_name;
        $seccion   = $classroom->section->section_name;
        $curso     = $assignment->pensumCourse->course->course_name;
        $unidad    = 'Unidad ' . $assignment->unit;
        $anio      = $classroom->year;
        $profesor  = $assignment->professor->user->name;
        $periodo   = \Carbon\Carbon::parse($from)->format('d/m/Y')
            . ' al '
            . \Carbon\Carbon::parse($to)->format('d/m/Y');

        $pdf = new PDF('L', 'mm', [215, 330]);
        $pdf->hideFooter = true;
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->AliasNbPages();

        foreach ($chunks as $chunk) {
            $pdf->AddPage();

            // ---- Encabezado institucional ----
            $logoPath = env('APP_INSTITUTION_LOGO_IMG', 'vendor/adminlte/dist/img/Escudo.png');
            $pdf->addImage($logoPath, 10, 6, 16);

            $pdf->SetFont('Arial', 'B', 13);
            $pdf->CellUTF8(310, 6, $pdf->dec(env('APP_INSTITUTION_NAME', 'Institución Educativa')), 0, 1, 'C');

            $pdf->SetFont('Arial', 'B', 11);
            $pdf->CellUTF8(310, 5, $pdf->dec('Registro de Asistencia'), 0, 1, 'C');
            $pdf->Ln(2);

            $pdf->SetFont('Arial', '', 9);
            $pdf->CellUTF8(103, 5, $pdf->dec('GRADO: ' . $grado . ' ' . $seccion), 0, 0, 'L');
            $pdf->CellUTF8(103, 5, $pdf->dec('UNIDAD: ' . $unidad), 0, 0, 'C');
            $pdf->CellUTF8(104, 5, $pdf->dec('AÑO: ' . $anio), 0, 1, 'R');

            $pdf->SetFont('Arial', '', 8);
            $pdf->CellUTF8(103, 4, $pdf->dec('NIVEL: ' . $nivel), 0, 0, 'L');
            $pdf->CellUTF8(103, 4, $pdf->dec('CURSO: ' . $curso), 0, 0, 'C');
            $pdf->CellUTF8(104, 4, $pdf->dec('PROFESOR(A): ' . $profesor), 0, 1, 'R');

            $pdf->SetFont('Arial', '', 8);
            $pdf->CellUTF8(310, 4, $pdf->dec('PERÍODO: ' . $periodo), 0, 1, 'C');
            $pdf->Ln(2);

            // ---- Encabezado de tabla ----
            $headerY  = $pdf->GetY();
            $currentX = $pdf->GetX();

            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(217, 217, 217);

            // No.
            $pdf->SetXY($currentX, $headerY + $headerH - 4);
            $pdf->CellUTF8($numWidth, 4, 'No.', 1, 0, 'C', true);
            $currentX += $numWidth;

            // Estudiante
            $pdf->SetXY($currentX, $headerY + $headerH - 4);
            $pdf->CellUTF8($nameWidth, 4, $pdf->dec('Estudiante (Apellidos, Nombres)'), 1, 0, 'C', true);
            $currentX += $nameWidth;

            // Columnas de fechas (rotadas)
            foreach ($chunk as $record) {
                $pdf->SetFillColor(198, 239, 206);
                $pdf->rotatedHeader($currentX, $headerY, $dateColWidth, $headerH, $record->date->format('d/m'));
                $currentX += $dateColWidth;
            }

            // Columnas resumen
            $pdf->SetFillColor(155, 194, 230);
            $pdf->rotatedHeader($currentX, $headerY, $summaryWidth, $headerH, 'Pres.');
            $currentX += $summaryWidth;

            $pdf->SetFillColor(255, 199, 206);
            $pdf->rotatedHeader($currentX, $headerY, $summaryWidth, $headerH, 'Aus.');

            $pdf->SetY($headerY + $headerH);

            // ---- Filas de estudiantes ----
            $pdf->SetFont('Arial', '', 8);
            $num = 1;

            foreach ($students as $student) {
                $fillRgb = $num % 2 === 0 ? [245, 245, 245] : [255, 255, 255];

                $pdf->SetFillColor(...$fillRgb);
                $pdf->CellUTF8($numWidth, $rowH, $num, 1, 0, 'C', true);
                $pdf->CellUTF8($nameWidth, $rowH, $pdf->dec($student->user->full_full_name), 1, 0, 'L', true);

                $presentCount = 0;
                $absentCount  = 0;

                foreach ($chunk as $record) {
                    $entry = $record->entries->firstWhere('student_id', $student->id);

                    if ($entry !== null) {
                        if ($entry->present) {
                            $presentCount++;
                            $pdf->SetFillColor(198, 239, 206); // verde
                            $cellText = 'P';
                        } else {
                            $absentCount++;
                            $pdf->SetFillColor(255, 199, 206); // rojo
                            $cellText = 'A';
                        }
                    } else {
                        $pdf->SetFillColor(...$fillRgb);
                        $cellText = '';
                    }

                    $pdf->CellUTF8($dateColWidth, $rowH, $cellText, 1, 0, 'C', true);
                }

                // Totales de la fila
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetFillColor(155, 194, 230);
                $pdf->CellUTF8($summaryWidth, $rowH, $presentCount, 1, 0, 'C', true);
                $pdf->SetFillColor(255, 199, 206);
                $pdf->CellUTF8($summaryWidth, $rowH, $absentCount, 1, 0, 'C', true);
                $pdf->SetFont('Arial', '', 8);

                $pdf->Ln($rowH);
                $num++;
            }
        }

        $filename = 'Asistencia_' . date('dmY_His') . '.pdf';

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}
