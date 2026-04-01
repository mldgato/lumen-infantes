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

                // Obtener cursos del pénsum (solo principales)
                $pensumCourses = $pensum->mainCourses()
                    ->with('course')
                    ->orderBy('ordering')
                    ->get();

                // Mapeo de columnas por curso
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

                // CONSULTA DE ESTUDIANTES: Filtro Activo, Ordenamiento alfabético completo y Eager Loading
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

                // Definición de filas
                $titleRow     = 1;
                $courseRow    = 2;
                $headerRow    = 3;
                $dataStartRow = 4;
                $dataEndRow   = $dataStartRow + $studentCount - 1;
                $aprobRow     = $dataEndRow + 1;
                $noAprobRow   = $dataEndRow + 2;
                $promedioRow  = $dataEndRow + 3;

                // Cálculo dinámico de índices de columnas
                $colMap = []; 
                $currentColIndex = 3; // Empezamos en la columna C

                foreach ($courseColumns as $pcId => $data) {
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

                // FILA 1: TÍTULO INSTITUCIONAL
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $titleText = 'Año: ' . $classroom->year
                    . '   |   Nivel: ' . $classroom->level->level_name
                    . '   |   Grado: ' . $classroom->grade->grade_name
                    . '   |   Sección: ' . $classroom->section->section_name;
                $sheet->setCellValue('A1', $titleText);
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // FILA 2: NOMBRES DE CURSOS (Celdas unidas por curso)
                $sheet->mergeCells("A2:B3");
                $sheet->getStyle("A2:B3")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                ]);

                foreach ($courseColumns as $pcId => $data) {
                    $map = $colMap[$pcId];
                    $startLetter = Coordinate::stringFromColumnIndex($map['start']);
                    $endLetter   = Coordinate::stringFromColumnIndex($map['end']);

                    $sheet->mergeCells("{$startLetter}2:{$endLetter}2");
                    $sheet->setCellValue("{$startLetter}2", $data['name']);
                    $sheet->getStyle("{$startLetter}2:{$endLetter}2")->applyFromArray([
                        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ]);
                }
                $sheet->getRowDimension(2)->setRowHeight(45);

                // FILA 3: ENCABEZADOS No., Estudiante y Unidades
                $sheet->setCellValue('A3', 'No.');
                $sheet->setCellValue('B3', 'Estudiante (Apellidos, Nombres)');

                $romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI'];

                foreach ($courseColumns as $pcId => $data) {
                    $map = $colMap[$pcId];
                    foreach ($data['assignments'] as $assignment) {
                        $col = Coordinate::stringFromColumnIndex($map['unitCols'][$assignment->unit]);
                        $sheet->setCellValue("{$col}3", $romanNumerals[$assignment->unit - 1] ?? $assignment->unit);
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

                // Re-aplicar color verde a los encabezados "Prom"
                foreach ($courseColumns as $pcId => $data) {
                    $promLetter = Coordinate::stringFromColumnIndex($colMap[$pcId]['promCol']);
                    $sheet->getStyle("{$promLetter}3")->getFill()->getStartColor()->setRGB('375623');
                }

                // FILAS DE ESTUDIANTES
                foreach ($students as $idx => $student) {
                    $row  = $dataStartRow + $idx;
                    $fill = $idx % 2 === 0 ? 'FFFFFF' : 'DEEAF1';

                    $sheet->setCellValue("A{$row}", $idx + 1);
                    
                    // NOMBRE UTILIZANDO EL ACCESSOR
                    $sheet->setCellValue("B{$row}", $student->user->full_full_name);

                    $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                    ]);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    foreach ($courseColumns as $pcId => $data) {
                        $map = $colMap[$pcId];
                        $weightedSum = 0;
                        $totalPct    = 0;

                        foreach ($data['assignments'] as $assignment) {
                            $col = Coordinate::stringFromColumnIndex($map['unitCols'][$assignment->unit]);

                            if ($assignment->gradeBook && $assignment->gradeBook->status === 'approved') {
                                $total = GradeBookTotal::where('grade_book_id', $assignment->gradeBook->id)
                                    ->where('student_id', $student->id)->first();

                                if ($total) {
                                    $value = min(100, (int) round((float) $total->total_points));
                                    $sheet->setCellValue("{$col}{$row}", $value);
                                    
                                    $pct = $pensum->getUnitPercentage($assignment->unit);
                                    $weightedSum += (float) $value * $pct / 100;
                                    $totalPct    += $pct;

                                    if ($value < 60) {
                                        $sheet->getStyle("{$col}{$row}")->applyFromArray([
                                            'font' => ['bold' => true, 'color' => ['rgb' => '9C0006']],
                                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                                        ]);
                                    }
                                }
                            }
                            $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }

                        // Cálculo y estilo de Promedio por curso
                        $promLetter = Coordinate::stringFromColumnIndex($map['promCol']);
                        $promValue = $totalPct > 0 ? (int) round($weightedSum) : '';
                        $sheet->setCellValue("{$promLetter}{$row}", $promValue);
                        
                        $sheet->getStyle("{$promLetter}{$row}")->applyFromArray([
                            'font'      => ['bold' => true, 'color' => ['rgb' => '375623']],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $idx % 2 === 0 ? 'D6E4BC' : 'C6EFCE']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);

                        if (is_int($promValue) && $promValue < 60) {
                            $sheet->getStyle("{$promLetter}{$row}")->getFont()->getColor()->setRGB('9C0006');
                            $sheet->getStyle("{$promLetter}{$row}")->getFill()->getStartColor()->setRGB('FFC7CE');
                        }
                    }
                }

                // FILAS DE RESUMEN (Aprobados, No Aprobados, Promedio)
                $summaryRows = [
                    $aprobRow    => ['label' => 'Aprobados',    'op' => '>=60', 'avg' => false, 'bg' => 'E2EFDA', 'fg' => '375623'],
                    $noAprobRow  => ['label' => 'No Aprobados', 'op' => '<60',  'avg' => false, 'bg' => 'FCE4D6', 'fg' => '843C0C'],
                    $promedioRow => ['label' => 'Promedio',     'op' => '',     'avg' => true,  'bg' => 'FFF2CC', 'fg' => '7F6000'],
                ];

                foreach ($summaryRows as $row => $cfg) {
                    $sheet->mergeCells("A{$row}:B{$row}");
                    $sheet->setCellValue("A{$row}", $cfg['label']);
                    $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
                        'font'      => ['bold' => true, 'color' => ['rgb' => $cfg['fg']]],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cfg['bg']]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    foreach ($courseColumns as $pcId => $data) {
                        $map = $colMap[$pcId];
                        // Resumen por cada unidad
                        foreach ($data['assignments'] as $assignment) {
                            $col   = Coordinate::stringFromColumnIndex($map['unitCols'][$assignment->unit]);
                            $range = "{$col}{$dataStartRow}:{$col}{$dataEndRow}";
                            $formula = $cfg['avg'] ? "=IFERROR(ROUND(AVERAGE({$range}),0),\"\")" : "=COUNTIF({$range},\"{$cfg['op']}\")";
                            $sheet->setCellValue("{$col}{$row}", $formula);
                        }
                        // Resumen columna Prom
                        $promCol   = Coordinate::stringFromColumnIndex($map['promCol']);
                        $promRange = "{$promCol}{$dataStartRow}:{$promCol}{$dataEndRow}";
                        $formulaP  = $cfg['avg'] ? "=IFERROR(ROUND(AVERAGE({$promRange}),0),\"\")" : "=COUNTIF({$promRange},\"{$cfg['op']}\")";
                        $sheet->setCellValue("{$promCol}{$row}", $formulaP);
                    }
                }

                // BORDES, FILTROS Y ANCHOS
                $sheet->getStyle("A1:{$lastColLetter}{$promedioRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B8CCE4']]],
                ]);
                $sheet->setAutoFilter("A3:{$lastColLetter}3");
                $sheet->freezePane("C{$dataStartRow}");
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(45);
                foreach ($courseColumns as $pcId => $data) {
                    $map = $colMap[$pcId];
                    foreach ($data['assignments'] as $assignment) {
                        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($map['unitCols'][$assignment->unit]))->setWidth(8);
                    }
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($map['promCol']))->setWidth(10);
                }
            },
        ];
    }
}
