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

class SabanaGeneralExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(
        protected int $classroomId,
    ) {}

    public function title(): string
    {
        return 'Sábana General';
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

                // Obtener cursos del pénsum (solo principales, sin sub cursos)
                $pensumCourses = $pensum->mainCourses()
                    ->with('course')
                    ->orderBy('ordering')
                    ->get();

                // Para cada curso, obtener las unidades asignadas en este classroom
                $courseColumns = []; // [pensumCourseId => [unit => assignmentId, ...]]
                foreach ($pensumCourses as $pc) {
                    $assignments = ClassroomCourseAssignment::with('gradeBook')
                        ->where('classroom_id', $this->classroomId)
                        ->where('pensum_course_id', $pc->id)
                        ->orderBy('unit')
                        ->get();

                    if ($assignments->isEmpty()) continue;

                    $courseColumns[$pc->id] = [
                        'name'        => $pc->course->course_name,
                        'assignments' => $assignments, // colección ordenada por unit
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

                // Filas
                $titleRow     = 1;
                $courseRow    = 2;
                $headerRow    = 3;
                $dataStartRow = 4;
                $dataEndRow   = $dataStartRow + $studentCount - 1;
                $aprobRow     = $dataEndRow + 1;
                $noAprobRow   = $dataEndRow + 2;
                $promedioRow  = $dataEndRow + 3;

                // Calcular columnas dinámicamente
                // A=No, B=Estudiante, luego por cada curso: unidades + Prom
                $colMap = []; // [pensumCourseId => ['start' => colIndex, 'units' => [...], 'prom' => colIndex]]
                $currentColIndex = 3; // empieza en C

                foreach ($courseColumns as $pcId => $data) {
                    $unitCount = $data['assignments']->count();
                    $startCol  = $currentColIndex;
                    $unitCols  = [];

                    foreach ($data['assignments'] as $assignment) {
                        $unitCols[$assignment->unit] = $currentColIndex;
                        $currentColIndex++;
                    }

                    $promCol = $currentColIndex;
                    $currentColIndex++;

                    $colMap[$pcId] = [
                        'start'    => $startCol,
                        'end'      => $promCol,
                        'unitCols' => $unitCols,
                        'promCol'  => $promCol,
                    ];
                }

                $lastColIndex  = $currentColIndex - 1;
                $lastColLetter = Coordinate::stringFromColumnIndex($lastColIndex);

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
                // FILA 2: NOMBRES DE CURSOS (merged por curso)
                // ==========================================
                // Celdas A2 y B2 vacías
                $sheet->mergeCells("A2:B3");
                $sheet->getStyle("A2:B3")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                ]);

                foreach ($courseColumns as $pcId => $data) {
                    $map       = $colMap[$pcId];
                    $startLetter = Coordinate::stringFromColumnIndex($map['start']);
                    $endLetter   = Coordinate::stringFromColumnIndex($map['end']);

                    $sheet->mergeCells("{$startLetter}2:{$endLetter}2");
                    $sheet->setCellValue("{$startLetter}2", $data['name']);
                    $sheet->getStyle("{$startLetter}2:{$endLetter}2")->applyFromArray([
                        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'wrapText'   => true,
                        ],
                    ]);
                }
                $sheet->getRowDimension(2)->setRowHeight(45);

                // ==========================================
                // FILA 3: ENCABEZADOS No., Estudiante, I, II, III... Prom
                // ==========================================
                $sheet->setCellValue('A3', 'No.');
                $sheet->setCellValue('B3', 'Estudiante');

                $romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI'];

                foreach ($courseColumns as $pcId => $data) {
                    $map = $colMap[$pcId];

                    foreach ($data['assignments'] as $assignment) {
                        $col    = Coordinate::stringFromColumnIndex($map['unitCols'][$assignment->unit]);
                        $unitNum = $assignment->unit - 1; // 0-indexed para romanos
                        $sheet->setCellValue("{$col}3", $romanNumerals[$unitNum] ?? $assignment->unit);
                    }

                    $promLetter = Coordinate::stringFromColumnIndex($map['promCol']);
                    $sheet->setCellValue("{$promLetter}3", 'Prom');
                    $sheet->getStyle("{$promLetter}3")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '375623']],
                    ]);
                }

                $sheet->getStyle("A3:{$lastColLetter}3")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Re-aplicar verde en columnas Prom de cada curso
                foreach ($courseColumns as $pcId => $data) {
                    $promLetter = Coordinate::stringFromColumnIndex($colMap[$pcId]['promCol']);
                    $sheet->getStyle("{$promLetter}3")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '375623']],
                    ]);
                }

                $sheet->getRowDimension(3)->setRowHeight(20);

                // ==========================================
                // FILAS DE ESTUDIANTES
                // ==========================================
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

                    foreach ($courseColumns as $pcId => $data) {
                        $map       = $colMap[$pcId];
                        $unitLetters = [];

                        foreach ($data['assignments'] as $assignment) {
                            $col = Coordinate::stringFromColumnIndex($map['unitCols'][$assignment->unit]);
                            $unitLetters[] = "{$col}{$row}";

                            if ($assignment->gradeBook && $assignment->gradeBook->status === 'approved') {
                                $total = GradeBookTotal::where('grade_book_id', $assignment->gradeBook->id)
                                    ->where('student_id', $student->id)
                                    ->first();

                                if ($total) {
                                    $value = (int) ceil($total->total_points);
                                    $sheet->setCellValue("{$col}{$row}", $value);
                                    $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('0');
                                    $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                                    if ($value < 60) {
                                        $sheet->getStyle("{$col}{$row}")->applyFromArray([
                                            'font' => ['bold' => true, 'color' => ['rgb' => '9C0006']],
                                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                                        ]);
                                    }
                                }
                            }
                        }

                        // Fórmula Prom por curso por fila
                        $promLetter = Coordinate::stringFromColumnIndex($map['promCol']);
                        $startUnitLetter = Coordinate::stringFromColumnIndex($map['start']);
                        $endUnitLetter   = Coordinate::stringFromColumnIndex($map['end'] - 1);
                        // Promedio ponderado por curso usando porcentajes del pénsum
                        $weightedSum = 0;
                        $totalPct    = 0;
                        foreach ($data['assignments'] as $assignment) {
                            $unitCol = Coordinate::stringFromColumnIndex($map['unitCols'][$assignment->unit]);
                            $cellVal = $sheet->getCell("{$unitCol}{$row}")->getValue();
                            if ($cellVal !== null && $cellVal !== '') {
                                $pct          = $pensum->getUnitPercentage($assignment->unit);
                                $weightedSum += (float) $cellVal * $pct / 100;
                                $totalPct    += $pct;
                            }
                        }
                        $promValue = $totalPct > 0 ? (int) round($weightedSum * 100 / $totalPct) : '';
                        $sheet->setCellValue("{$promLetter}{$row}", $promValue ?: '');
                        if (is_int($promValue) && $promValue < 60) {
                            $sheet->getStyle("{$promLetter}{$row}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => '9C0006']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                            ]);
                        }
                        $sheet->getStyle("{$promLetter}{$row}")->applyFromArray([
                            'font'      => ['bold' => true, 'color' => ['rgb' => '375623']],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $idx % 2 === 0 ? 'D6E4BC' : 'C6EFCE']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                        $sheet->getStyle("{$promLetter}{$row}")->getNumberFormat()->setFormatCode('0');
                    }
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

                    foreach ($courseColumns as $pcId => $data) {
                        $map = $colMap[$pcId];

                        foreach ($data['assignments'] as $assignment) {
                            $col   = Coordinate::stringFromColumnIndex($map['unitCols'][$assignment->unit]);
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

                        // Resumen columna Prom
                        $promLetter  = Coordinate::stringFromColumnIndex($map['promCol']);
                        $promRange   = "{$promLetter}{$dataStartRow}:{$promLetter}{$dataEndRow}";
                        $formulaProm = $cfg['avg']
                            ? "=IFERROR(ROUND(AVERAGE({$promRange}),0),\"\")"
                            : "=COUNTIF({$promRange},\"{$cfg['op']}\")";

                        $sheet->setCellValue("{$promLetter}{$row}", $formulaProm);
                        $sheet->getStyle("{$promLetter}{$row}")->applyFromArray([
                            'font'      => ['bold' => true, 'color' => ['rgb' => $cfg['fg']]],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cfg['bg']]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }
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

                $sheet->setAutoFilter("A3:{$lastColLetter}3");
                $sheet->freezePane("C{$dataStartRow}");

                // Anchos
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(38);
                foreach ($courseColumns as $pcId => $data) {
                    $map = $colMap[$pcId];
                    foreach ($data['assignments'] as $assignment) {
                        $col = Coordinate::stringFromColumnIndex($map['unitCols'][$assignment->unit]);
                        $sheet->getColumnDimension($col)->setWidth(8);
                    }
                    $promLetter = Coordinate::stringFromColumnIndex($map['promCol']);
                    $sheet->getColumnDimension($promLetter)->setWidth(10);
                }
            },
        ];
    }
}
