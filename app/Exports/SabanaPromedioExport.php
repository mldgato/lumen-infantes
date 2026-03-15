<?php

namespace App\Exports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBookTotal;
use App\Models\Pensum;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SabanaPromedioExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(
        protected int $classroomId,
    ) {}

    public function title(): string
    {
        return 'Sábana Promedio';
    }

    public function array(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $classroom = Classroom::with(['level', 'grade', 'section'])->findOrFail($this->classroomId);

                $pensum = Pensum::where('grade_id', $classroom->grade_id)
                    ->where('year', $classroom->year)
                    ->first();

                if (! $pensum) return;

                $pensumCourses = $pensum->mainCourses()
                    ->with('course')
                    ->orderBy('ordering')
                    ->get();

                $courseColumns = [];
                foreach ($pensumCourses as $pc) {
                    $assignments = ClassroomCourseAssignment::with('gradeBook')
                        ->where('classroom_id', $this->classroomId)
                        ->where('pensum_course_id', $pc->id)
                        ->orderBy('unit')
                        ->get();

                    if ($assignments->isEmpty()) continue;

                    $courseColumns[$pc->id] = [
                        'name'        => $pc->course->course_name,
                        'assignments' => $assignments,
                    ];
                }

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

                $studentCount = $students->count();
                $courseCount  = count($courseColumns);

                // Filas
                $dataStartRow = 3;
                $dataEndRow   = $dataStartRow + $studentCount - 1;
                $aprobRow     = $dataEndRow + 1;
                $noAprobRow   = $dataEndRow + 2;
                $promedioRow  = $dataEndRow + 3;

                // Columnas
                $firstCourseColIndex  = 3;
                $lastCourseColIndex   = 2 + $courseCount;
                $lastCourseColLetter  = Coordinate::stringFromColumnIndex($lastCourseColIndex);
                $promedioColIndex     = $lastCourseColIndex + 1;
                $promedioColLetter    = Coordinate::stringFromColumnIndex($promedioColIndex);
                $lastColLetter        = $promedioColLetter;

                // ==========================================
                // FILA 1: TÍTULO
                // ==========================================
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $title = 'Año: ' . $classroom->year
                    . '   |   Nivel: ' . $classroom->level->level_name
                    . '   |   Grado: ' . $classroom->grade->grade_name
                    . '   |   Sección: ' . $classroom->section->section_name;
                $sheet->setCellValue('A1', $title);
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // ==========================================
                // FILA 2: ENCABEZADOS
                // ==========================================
                $sheet->setCellValue('A2', 'No.');
                $sheet->setCellValue('B2', 'Estudiante');

                foreach (array_values($courseColumns) as $idx => $data) {
                    $col = Coordinate::stringFromColumnIndex($idx + 3);
                    $sheet->setCellValue("{$col}2", $data['name']);
                }

                $sheet->setCellValue("{$promedioColLetter}2", 'Promedio');

                $sheet->getStyle("A2:{$lastColLetter}2")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);

                // Re-aplicar verde en columna Promedio
                $sheet->getStyle("{$promedioColLetter}2")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '375623']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);

                $sheet->getRowDimension(2)->setRowHeight(50);

                // ==========================================
                // FILAS DE ESTUDIANTES
                // ==========================================
                $pensum = Pensum::where('grade_id', $classroom->grade_id)
                    ->where('year', $classroom->year)
                    ->first();
                foreach ($students as $idx => $student) {
                    $row  = $dataStartRow + $idx;
                    $fill = $idx % 2 === 0 ? 'FFFFFF' : 'DEEAF1';

                    $sheet->setCellValue("A{$row}", $idx + 1);
                    $nombre = trim(
                        $student->user->surname . ' ' .
                            $student->user->second_surname . ', ' .
                            $student->user->first_name . ' ' .
                            $student->user->middle_name
                    );
                    $sheet->setCellValue("B{$row}", $nombre);

                    $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                    ]);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    foreach (array_values($courseColumns) as $colIdx => $data) {
                        $col = Coordinate::stringFromColumnIndex($colIdx + 3);

                        $unitTotals = []; // indexed by assignment index
                        foreach ($data['assignments'] as $aIdx => $assignment) {
                            if ($assignment->gradeBook && $assignment->gradeBook->status === 'approved') {
                                $total = GradeBookTotal::where('grade_book_id', $assignment->gradeBook->id)
                                    ->where('student_id', $student->id)
                                    ->first();

                                if ($total) {
                                    $unitTotals[$aIdx] = (int) ceil($total->total_points);
                                }
                            }
                        }

                        if (! empty($unitTotals)) {
                            // Promedio ponderado
                            $weighted = 0;
                            $totalPct = 0;
                            foreach ($data['assignments'] as $aIdx => $assignment) {
                                if (isset($unitTotals[$aIdx])) {
                                    $pct      = $pensum->getUnitPercentage($assignment->unit);
                                    $weighted += $unitTotals[$aIdx] * $pct / 100;
                                    $totalPct += $pct;
                                }
                            }
                            $promedio = $totalPct > 0 ? (int) round($weighted * 100 / $totalPct) : 0;

                            $sheet->setCellValue("{$col}{$row}", $promedio);
                            $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('0');
                            $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                            if ($promedio < 60) {
                                $sheet->getStyle("{$col}{$row}")->applyFromArray([
                                    'font' => ['bold' => true, 'color' => ['rgb' => '9C0006']],
                                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                                ]);
                            }
                        }
                    }

                    // Fórmula promedio por fila
                    $firstCourseColLetter = Coordinate::stringFromColumnIndex($firstCourseColIndex);
                    $sheet->setCellValue(
                        "{$promedioColLetter}{$row}",
                        "=IFERROR(ROUND(AVERAGEIF({$firstCourseColLetter}{$row}:{$lastCourseColLetter}{$row},\"<>\"),0),\"\")"
                    );
                    $sheet->getStyle("{$promedioColLetter}{$row}")->applyFromArray([
                        'font'      => ['bold' => true, 'color' => ['rgb' => '375623']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $idx % 2 === 0 ? 'D6E4BC' : 'C6EFCE']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                    $sheet->getStyle("{$promedioColLetter}{$row}")->getNumberFormat()->setFormatCode('0');
                }

                // ==========================================
                // FILAS DE RESUMEN
                // ==========================================
                $summaryRows = [
                    $aprobRow    => ['label' => 'Aprobados',    'op' => '>=60', 'avg' => false, 'bg' => 'E2EFDA', 'fg' => '375623'],
                    $noAprobRow  => ['label' => 'No Aprobados', 'op' => '<60',  'avg' => false, 'bg' => 'FCE4D6', 'fg' => '843C0C'],
                    $promedioRow => ['label' => 'Promedio',     'op' => '',     'avg' => true,  'bg' => 'FFF2CC', 'fg' => '7F6000'],
                ];

                foreach ($summaryRows as $row => $cfg) {
                    $sheet->mergeCells("A{$row}:B{$row}");
                    $sheet->setCellValue("A{$row}", $cfg['label']);
                    $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                        'font'      => ['bold' => true, 'color' => ['rgb' => $cfg['fg']]],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cfg['bg']]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    foreach (array_values($courseColumns) as $colIdx => $data) {
                        $col   = Coordinate::stringFromColumnIndex($colIdx + 3);
                        $range = "{$col}{$dataStartRow}:{$col}{$dataEndRow}";

                        $formula = $cfg['avg']
                            ? "=IFERROR(ROUND(AVERAGE({$range}),0),\"\")"
                            : "=COUNTIF({$range},\"{$cfg['op']}\")";

                        $sheet->setCellValue("{$col}{$row}", $formula);
                        $sheet->getStyle("{$col}{$row}")->applyFromArray([
                            'font'      => ['bold' => true, 'color' => ['rgb' => $cfg['fg']]],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cfg['bg']]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }

                    // Celda promedio general
                    $promRange   = "{$promedioColLetter}{$dataStartRow}:{$promedioColLetter}{$dataEndRow}";
                    $formulaProm = $cfg['avg']
                        ? "=IFERROR(ROUND(AVERAGE({$promRange}),0),\"\")"
                        : "=COUNTIF({$promRange},\"{$cfg['op']}\")";

                    $sheet->setCellValue("{$promedioColLetter}{$row}", $formulaProm);
                    $sheet->getStyle("{$promedioColLetter}{$row}")->applyFromArray([
                        'font'      => ['bold' => true, 'color' => ['rgb' => $cfg['fg']]],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cfg['bg']]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }

                // ==========================================
                // BORDES, FILTROS Y FREEZE
                // ==========================================
                $sheet->getStyle("A1:{$lastColLetter}{$promedioRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'B8CCE4'],
                        ],
                    ],
                ]);

                $sheet->setAutoFilter("A2:{$lastColLetter}2");
                $sheet->freezePane("C{$dataStartRow}");

                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(38);
                for ($i = 0; $i < $courseCount; $i++) {
                    $col = Coordinate::stringFromColumnIndex($i + 3);
                    $sheet->getColumnDimension($col)->setWidth(20);
                }
                $sheet->getColumnDimension($promedioColLetter)->setWidth(12);
            },
        ];
    }
}
