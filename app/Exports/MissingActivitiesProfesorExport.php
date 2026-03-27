<?php

namespace App\Exports;

use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBook;
use App\Models\GradeBookScore;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class MissingActivitiesProfesorExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithCustomStartCell, WithEvents
{
    private array  $activities    = [];
    private array  $rows          = [];
    private string $courseName    = '';
    private string $professorName = '';
    private string $gradeName     = '';
    private string $sectionName   = '';
    private string $levelName     = '';
    private string $year          = '';

    public function __construct(
        private int $classroomId,
        private int $pensumCourseId,
        private int $unit,
        private int $professorId,
    ) {
        $this->buildData();
    }

    private function buildData(): void
    {
        $assignment = ClassroomCourseAssignment::with([
            'pensumCourse.course',
            'professor.user',
            'classroom.grade',
            'classroom.section',
            'classroom.level',
        ])
            ->where('classroom_id', $this->classroomId)
            ->where('pensum_course_id', $this->pensumCourseId)
            ->where('unit', $this->unit)
            ->where('professor_id', $this->professorId)
            ->first();

        if (! $assignment) return;

        $this->courseName    = $assignment->pensumCourse->course->course_name . ' — Unidad ' . $this->unit;
        $this->professorName = $assignment->professor->user->name ?? '—';
        $this->gradeName     = $assignment->classroom->grade->grade_name ?? '—';
        $this->sectionName   = $assignment->classroom->section->section_name ?? '—';
        $this->levelName     = $assignment->classroom->level->level_name ?? '—';
        $this->year          = $assignment->classroom->year ?? date('Y');

        $gradeBook = GradeBook::where('classroom_course_assignment_id', $assignment->id)->first();
        if (! $gradeBook) return;

        $activitiesCollection = $gradeBook->activities()->get();
        $this->activities     = $activitiesCollection->toArray();

        $scoresByStudent = GradeBookScore::whereIn('grade_book_activity_id', $activitiesCollection->pluck('id'))
            ->get()
            ->groupBy('student_id')
            ->map(fn($g) => $g->keyBy('grade_book_activity_id'));

        $students = Student::whereHas(
            'enrollments',
            fn($q) =>
            $q->where('classroom_id', $this->classroomId)->where('status', 'Activo')
        )
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->select('students.*')
            ->with('user')
            ->get();

        foreach ($students->values() as $idx => $student) {
            $studentScores = $scoresByStudent->get($student->id, collect());
            $row = [
                $idx + 1,
                $student->user->full_full_name,
            ];
            $missing = 0;

            foreach ($activitiesCollection as $activity) {
                $score   = $studentScores->get($activity->id);
                $done    = $score !== null && $score->score !== null && (float) $score->score > 0;
                $row[]   = $done ? '✔' : '✘';
                if (! $done) $missing++;
            }

            $row[]        = $missing;
            $this->rows[] = $row;
        }
    }

    public function array(): array
    {
        return $this->rows;
    }
    public function startCell(): string
    {
        return 'A5';
    }
    public function title(): string
    {
        return 'Actividades Faltantes';
    }

    public function headings(): array
    {
        $heads = ['No.', 'Estudiante'];
        foreach ($this->activities as $a) {
            $heads[] = $a['name'];
        }
        $heads[] = 'Faltantes';
        return $heads;
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 5, 'B' => 40];
        for ($i = 3; $i <= count($this->activities) + 2; $i++) {
            $widths[Coordinate::stringFromColumnIndex($i)] = 14;
        }
        $widths[Coordinate::stringFromColumnIndex(count($this->activities) + 3)] = 12;
        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->rows) + 5;

        // Colores por actividad
        for ($row = 6; $row <= $lastRow; $row++) {

            for ($col = 3; $col <= count($this->activities) + 2; $col++) {
                $cellRef = Coordinate::stringFromColumnIndex($col) . $row;
                $cell    = $sheet->getCell($cellRef)->getValue();
                if ($cell === '✘') {
                    $sheet->getStyle($cellRef)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC7CE');
                    $sheet->getStyle($cellRef)->getFont()->getColor()->setARGB('FF9C0006');
                } else {
                    $sheet->getStyle($cellRef)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC6EFCE');
                    $sheet->getStyle($cellRef)->getFont()->getColor()->setARGB('FF276221');
                }
                $sheet->getStyle($cellRef)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // <-- aquí, fuera del for de columnas, dentro del for de filas
            $faltantesCol = Coordinate::stringFromColumnIndex(count($this->activities) + 3);
            $faltantesRef = $faltantesCol . $row;
            $faltantesVal = (int) $sheet->getCell($faltantesRef)->getValue();

            $sheet->getStyle($faltantesRef)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($faltantesRef)->getFont()->setBold(true);

            if ($faltantesVal === 0) {
                $sheet->getStyle($faltantesRef)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC6EFCE');
                $sheet->getStyle($faltantesRef)->getFont()->getColor()->setARGB('FF276221');
            } elseif ($faltantesVal <= 2) {
                $sheet->getStyle($faltantesRef)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFEB9C');
                $sheet->getStyle($faltantesRef)->getFont()->getColor()->setARGB('FF9C6500');
            } else {
                $sheet->getStyle($faltantesRef)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC7CE');
                $sheet->getStyle($faltantesRef)->getFont()->getColor()->setARGB('FF9C0006');
            }
        }

        // Encabezado de columnas (fila 5)
        return [
            5 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2F75B6']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(count($this->activities) + 3);
                $lastRow = count($this->rows) + 5;

                // Merge y estilos del encabezado institucional
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'REPORTE DE ACTIVIDADES NO ENTREGADAS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Fila 2: Año, Nivel, Grado, Sección
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', "Año: {$this->year}   |   Nivel: {$this->levelName}   |   Grado: {$this->gradeName}   |   {$this->sectionName}");
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD6E4F0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Fila 3: Curso y Unidad
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', "Curso: {$this->courseName}");
                $sheet->getStyle('A3')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD6E4F0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Fila 4: Profesor
                $sheet->mergeCells("A4:{$lastCol}4");
                $sheet->setCellValue('A4', "Profesor(a): {$this->professorName}");
                $sheet->getStyle('A4')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEAF2FB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Bordes para toda la tabla (filas 5 en adelante)
                $tableRange = "A5:{$lastCol}{$lastRow}";
                $sheet->getStyle($tableRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
