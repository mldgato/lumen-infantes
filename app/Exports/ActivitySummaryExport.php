<?php

namespace App\Exports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBookScore;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ActivitySummaryExport implements FromArray, WithCustomStartCell, WithEvents, WithTitle
{
    private array $rows = [];

    private array $courseHeaders = [];

    private int $courseCount = 0;

    private string $year = '';

    private string $levelName = '';

    private string $gradeName = '';

    private string $sectionName = '';

    private int $unit = 1;

    public function __construct(int $classroomId, int $unit)
    {
        $classroom = Classroom::with(['level', 'grade', 'section'])->find($classroomId);
        if (! $classroom) {
            return;
        }

        $this->year = (string) $classroom->year;
        $this->levelName = $classroom->level->level_name ?? '—';
        $this->gradeName = $classroom->grade->grade_name ?? '—';
        $this->sectionName = $classroom->section->section_name ?? '—';
        $this->unit = $unit;

        $students = Student::whereHas(
            'enrollments',
            fn ($q) => $q->where('classroom_id', $classroomId)->where('status', 'Activo')
        )
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->select('students.*')
            ->with('user')
            ->get();

        $assignments = ClassroomCourseAssignment::with([
            'pensumCourse.course',
            'gradeBook.activities',
        ])
            ->where('classroom_id', $classroomId)
            ->where('unit', $unit)
            ->get();

        $courseScoreData = [];

        foreach ($assignments as $assignment) {
            $courseName = $assignment->pensumCourse->course->course_name;
            $gradeBook = $assignment->gradeBook;

            $mainActivities = $gradeBook ? $gradeBook->activities->where('activity_type_id', 1) : collect();

            if (! $gradeBook || $mainActivities->isEmpty()) {
                $this->courseHeaders[] = ['name' => $courseName, 'total' => 0, 'has_activities' => false];
                $courseScoreData[] = null;

                continue;
            }

            $activityIds = $mainActivities->pluck('id');
            $total = $activityIds->count();

            $scoresByStudent = GradeBookScore::whereIn('grade_book_activity_id', $activityIds)
                ->get()
                ->groupBy('student_id')
                ->map(fn ($group) => $group->keyBy('grade_book_activity_id'));

            $this->courseHeaders[] = ['name' => $courseName, 'total' => $total, 'has_activities' => true];
            $courseScoreData[] = ['activity_ids' => $activityIds, 'scores_by_student' => $scoresByStudent, 'total' => $total];
        }

        $this->courseCount = count($this->courseHeaders);

        foreach ($students->values() as $idx => $student) {
            $row = [$idx + 1, $student->user->full_full_name];
            $totalMissing = 0;

            foreach ($courseScoreData as $i => $data) {
                if ($data === null) {
                    $row[] = '—';

                    continue;
                }

                $studentScores = $data['scores_by_student']->get($student->id, collect());
                $done = 0;

                foreach ($data['activity_ids'] as $activityId) {
                    $score = $studentScores->get($activityId);
                    if ($score !== null && $score->score !== null && (float) $score->score > 0) {
                        $done++;
                    }
                }

                $totalMissing += ($data['total'] - $done);
                $row[] = "{$done}/{$data['total']}";
            }

            $row[] = $totalMissing;
            $this->rows[] = $row;
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Resumen de Actividades';
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dataStart = 5;
                $lastDataRow = $dataStart + count($this->rows) - 1;
                $lastColIdx = $this->courseCount + 3;
                $lastCol = Coordinate::stringFromColumnIndex($lastColIdx);

                // ── FILA 1: Título ──────────────────────────────────────────────
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'RESUMEN DE ACTIVIDADES POR ESTUDIANTE');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // ── FILA 2: Info del aula ───────────────────────────────────────
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', "Año: {$this->year}   |   Nivel: {$this->levelName}   |   Grado: {$this->gradeName}   |   Sección: {$this->sectionName}   |   Unidad: {$this->unit}");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD6E4F0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── FILA 3: Leyenda ─────────────────────────────────────────────
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', 'Formato de celdas: actividades entregadas / total de actividades   |   "—" = sin cuadro registrado');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEAF2FB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── FILA 4: Encabezados ─────────────────────────────────────────
                $sheet->setCellValue('A4', 'No.');
                $sheet->setCellValue('B4', 'Estudiante (Apellidos, Nombres)');

                foreach ($this->courseHeaders as $i => $header) {
                    $colLetter = Coordinate::stringFromColumnIndex($i + 3);
                    $label = $header['name'];
                    if ($header['has_activities']) {
                        $label .= "\n({$header['total']} act.)";
                    }
                    $sheet->setCellValue("{$colLetter}4", $label);
                    $sheet->getStyle("{$colLetter}4")->getAlignment()->setWrapText(true);
                }

                $sheet->setCellValue("{$lastCol}4", 'Total Faltantes');
                $sheet->getRowDimension(4)->setRowHeight(42);

                $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2F75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // ── FILAS DE DATOS ──────────────────────────────────────────────
                foreach ($this->rows as $rowIdx => $row) {
                    $excelRow = $dataStart + $rowIdx;
                    $bgFill = $rowIdx % 2 === 0 ? 'FFFFFFFF' : 'FFDEEAF1';

                    $sheet->getStyle("A{$excelRow}:{$lastCol}{$excelRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgFill]],
                    ]);
                    $sheet->getStyle("A{$excelRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Columnas de cursos (índice 2 en adelante hasta courseCount+1)
                    foreach ($this->courseHeaders as $i => $header) {
                        $colLetter = Coordinate::stringFromColumnIndex($i + 3);
                        $cellRef = "{$colLetter}{$excelRow}";
                        $value = $row[$i + 2]; // +2 por No. y Estudiante

                        $sheet->getStyle($cellRef)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        if ($value === '—' || ! $header['has_activities']) {
                            $sheet->getStyle($cellRef)->applyFromArray([
                                'font' => ['color' => ['argb' => 'FF888888']],
                            ]);

                            continue;
                        }

                        [$done, $total] = array_map('intval', explode('/', $value));
                        $ratio = $total > 0 ? $done / $total : 1;

                        if ($ratio >= 1) {
                            $sheet->getStyle($cellRef)->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['argb' => 'FF276221']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC6EFCE']],
                            ]);
                        } elseif ($ratio >= 0.5) {
                            $sheet->getStyle($cellRef)->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['argb' => 'FF9C6500']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFEB9C']],
                            ]);
                        } else {
                            $sheet->getStyle($cellRef)->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['argb' => 'FF9C0006']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFC7CE']],
                            ]);
                        }
                    }

                    // Columna Total Faltantes
                    $missingRef = "{$lastCol}{$excelRow}";
                    $missing = (int) $row[$this->courseCount + 2];
                    $sheet->getStyle($missingRef)->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    if ($missing === 0) {
                        $sheet->getStyle($missingRef)->applyFromArray([
                            'font' => ['color' => ['argb' => 'FF276221']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC6EFCE']],
                        ]);
                    } elseif ($missing <= 3) {
                        $sheet->getStyle($missingRef)->applyFromArray([
                            'font' => ['color' => ['argb' => 'FF9C6500']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFEB9C']],
                        ]);
                    } else {
                        $sheet->getStyle($missingRef)->applyFromArray([
                            'font' => ['color' => ['argb' => 'FF9C0006']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFC7CE']],
                        ]);
                    }
                }

                // ── BORDES GENERALES ────────────────────────────────────────────
                if ($lastDataRow >= $dataStart) {
                    $sheet->getStyle("A1:{$lastCol}{$lastDataRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFB8CCE4']],
                        ],
                    ]);
                }

                // ── ANCHOS DE COLUMNA ───────────────────────────────────────────
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(45);
                for ($i = 0; $i < $this->courseCount; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 3))->setWidth(16);
                }
                $sheet->getColumnDimension($lastCol)->setWidth(14);

                $sheet->freezePane("C{$dataStart}");
                $sheet->setAutoFilter("A4:{$lastCol}4");
            },
        ];
    }
}
