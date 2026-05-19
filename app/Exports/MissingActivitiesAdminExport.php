<?php

namespace App\Exports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBookScore;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MissingActivitiesAdminExport implements WithMultipleSheets
{
    private array $sheets = [];

    public function __construct(int $classroomId, int $unit)
    {
        $classroom = Classroom::find($classroomId);
        if (! $classroom) {
            return;
        }

        // CONSULTA DE ESTUDIANTES ESTANDARIZADA
        $students = Student::whereHas(
            'enrollments',
            fn ($q) => $q->where('classroom_id', $classroomId)->where('status', 'Activo')
        )
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->select('students.*')->with('user')
            ->get();

        $assignments = ClassroomCourseAssignment::with([
            'pensumCourse.course',
            'gradeBook.activities',
        ])
            ->where('classroom_id', $classroomId)
            ->where('unit', $unit)
            ->get();

        foreach ($assignments as $assignment) {
            $gradeBook = $assignment->gradeBook;
            if (! $gradeBook) {
                continue;
            }

            $activitiesCollection = $gradeBook->activities->where('activity_type_id', 1);
            if ($activitiesCollection->isEmpty()) {
                continue;
            }

            $activityIds = $activitiesCollection->pluck('id');

            $scoresByStudent = GradeBookScore::whereIn('grade_book_activity_id', $activityIds)
                ->get()
                ->groupBy('student_id')
                ->map(fn ($g) => $g->keyBy('grade_book_activity_id'));

            $rows = [];
            foreach ($students->values() as $idx => $student) {
                $studentScores = $scoresByStudent->get($student->id, collect());

                // ASIGNACIÓN DE FILA CON ACCESSOR
                $row = [
                    $idx + 1,
                    $student->user->full_full_name,
                ];

                $missing = 0;
                foreach ($activitiesCollection as $activity) {
                    $score = $studentScores->get($activity->id);
                    $done = $score !== null && $score->score !== null && (float) $score->score > 0;
                    $row[] = $done ? '✔' : '✘';
                    if (! $done) {
                        $missing++;
                    }
                }
                $row[] = $missing;
                $rows[] = $row;
            }

            $courseName = $assignment->pensumCourse->course->course_name;
            $safeTitle = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $courseName), 0, 30);

            $classroom->load(['grade', 'section', 'level']);

            $this->sheets[] = new MissingActivitiesSheet(
                title: $safeTitle ?: 'Curso',
                headings: array_merge(['No.', 'Estudiante'], $activitiesCollection->pluck('name')->toArray(), ['Faltantes']),
                rows: $rows,
                activityCount: $activitiesCollection->count(),
                year: $classroom->year,
                levelName: $classroom->level->level_name ?? '—',
                gradeName: $classroom->grade->grade_name ?? '—',
                sectionName: $classroom->section->section_name ?? '—',
                courseName: $assignment->pensumCourse->course->course_name,
                professorName: $assignment->professor->user->name ?? '—',
                unit: (int) $unit,
            );
        }
    }

    public function sheets(): array
    {
        return $this->sheets;
    }
}

class MissingActivitiesSheet implements FromArray, WithColumnWidths, WithCustomStartCell, WithEvents, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private string $title,
        private array $headings,
        private array $rows,
        private int $activityCount,
        private string $year = '',
        private string $levelName = '',
        private string $gradeName = '',
        private string $sectionName = '',
        private string $courseName = '',
        private string $professorName = '',
        private int $unit = 1,
    ) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 5, 'B' => 40];
        for ($i = 3; $i <= $this->activityCount + 2; $i++) {
            $widths[Coordinate::stringFromColumnIndex($i)] = 14;
        }
        $widths[Coordinate::stringFromColumnIndex($this->activityCount + 3)] = 12;

        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->rows) + 5;
        for ($row = 6; $row <= $lastRow; $row++) {

            for ($col = 3; $col <= $this->activityCount + 2; $col++) {
                $cellRef = Coordinate::stringFromColumnIndex($col).$row;
                $cell = $sheet->getCell($cellRef)->getValue();
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
            $faltantesCol = Coordinate::stringFromColumnIndex($this->activityCount + 3);
            $faltantesRef = $faltantesCol.$row;
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

        return [
            5 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2F75B6']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex($this->activityCount + 3);
                $lastRow = count($this->rows) + 5;

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'REPORTE DE ACTIVIDADES NO ENTREGADAS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(22);

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', "Año: {$this->year}   |   Nivel: {$this->levelName}   |   Grado: {$this->gradeName}   |   {$this->sectionName}");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD6E4F0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', "Curso: {$this->courseName} — Unidad {$this->unit}");
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD6E4F0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells("A4:{$lastCol}4");
                $sheet->setCellValue('A4', "Profesor(a): {$this->professorName}");
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEAF2FB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle("A5:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
