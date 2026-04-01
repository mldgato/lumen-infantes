<?php

namespace App\Exports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBookTotal;
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

class SabanaUnidadExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(
        protected int $classroomId,
        protected int $unit,
    ) {}

    public function title(): string
    {
        return 'Sábana U' . $this->unit;
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

                $assignments = ClassroomCourseAssignment::with([
                    'pensumCourse.course',
                    'gradeBook',
                ])
                    ->where('classroom_id', $this->classroomId)
                    ->where('unit', $this->unit)
                    ->get();

                // Consulta de estudiantes con filtro de status Activo y ordenamiento completo
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
                $courseCount  = $assignments->count();

                $headerRow    = 2;
                $dataStartRow = 3;
                $dataEndRow   = $dataStartRow + $studentCount - 1;
                $aprobRow     = $dataEndRow + 1;
                $noAprobRow   = $dataEndRow + 2;
                $promedioRow  = $dataEndRow + 3;

                $firstCourseColIndex = 3;
                $lastCourseColIndex  = 2 + $courseCount;
                $lastCourseColLetter = Coordinate::stringFromColumnIndex($lastCourseColIndex);
                $promedioColIndex    = $lastCourseColIndex + 1;
                $promedioColLetter   = Coordinate::stringFromColumnIndex($promedioColIndex);
                $lastColLetter       = $promedioColLetter;

                // FILA 1: TÍTULO
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $titleText = 'Año: ' . $classroom->year
                    . '   |   Nivel: ' . $classroom->level->level_name
                    . '   |   Grado: ' . $classroom->grade->grade_name
                    . '   |   Sección: ' . $classroom->section->section_name
                    . '   |   Unidad: ' . $this->unit;
                $sheet->setCellValue('A1', $titleText);
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // FILA 2: ENCABEZADOS
                $sheet->setCellValue('A2', 'No.');
                $sheet->setCellValue('B2', 'Estudiante (Apellidos, Nombres)');

                foreach ($assignments as $idx => $assignment) {
                    $col = Coordinate::stringFromColumnIndex($idx + 3);
                    $sheet->setCellValue("{$col}2", $assignment->pensumCourse->course->course_name);
                }

                $sheet->setCellValue("{$promedioColLetter}2", 'Promedio');
                $sheet->getStyle("A2:{$lastColLetter}2")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getStyle("{$promedioColLetter}2")->getFill()->getStartColor()->setRGB('375623');
                $sheet->getRowDimension(2)->setRowHeight(50);

                // FILAS DE ESTUDIANTES
                foreach ($students as $idx => $student) {
                    $row  = $dataStartRow + $idx;
                    $fill = $idx % 2 === 0 ? 'FFFFFF' : 'DEEAF1';

                    $sheet->setCellValue("A{$row}", $idx + 1);

                    // USO DEL ACCESSOR FULL_FULL_NAME
                    $sheet->setCellValue("B{$row}", $student->user->full_full_name);

                    foreach ($assignments as $colIdx => $assignment) {
                        $col = Coordinate::stringFromColumnIndex($colIdx + 3);
                        if ($assignment->gradeBook && $assignment->gradeBook->status === 'approved') {
                            $total = GradeBookTotal::where('grade_book_id', $assignment->gradeBook->id)
                                ->where('student_id', $student->id)->first();
                            if ($total) {
                                $pts = min(100, (int) round((float) $total->total_points));
                                $sheet->setCellValue("{$col}{$row}", $pts);
                                if ($pts < 60) {
                                    $sheet->getStyle("{$col}{$row}")->applyFromArray([
                                        'font' => ['bold' => true, 'color' => ['rgb' => '9C0006']],
                                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                                    ]);
                                }
                            }
                        }
                    }

                    $rowRange = "C{$row}:{$lastCourseColLetter}{$row}";
                    $sheet->setCellValue("{$promedioColLetter}{$row}", "=IFERROR(ROUND(AVERAGEIF({$rowRange},\"<>\"),0),\"\")");

                    $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                    ]);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$row}:{$lastColLetter}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("{$promedioColLetter}{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '375623']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => ($idx % 2 === 0 ? 'D6E4BC' : 'C6EFCE')]],
                    ])->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // FILAS DE RESUMEN
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

                    for ($i = 3; $i <= $promedioColIndex; $i++) {
                        $col = Coordinate::stringFromColumnIndex($i);
                        $range = "{$col}{$dataStartRow}:{$col}{$dataEndRow}";
                        $formula = $cfg['avg'] ? "=IFERROR(ROUND(AVERAGE({$range}),0),\"\")" : "=COUNTIF({$range},\"{$cfg['op']}\")";
                        $sheet->setCellValue("{$col}{$row}", $formula);
                    }
                }

                $sheet->getStyle("A1:{$lastColLetter}{$promedioRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B8CCE4']]],
                ]);
                $sheet->setAutoFilter("A2:{$lastColLetter}2");
                $sheet->freezePane("C{$dataStartRow}");
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(45);
                for ($i = 3; $i <= $lastCourseColIndex; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(18);
                }
                $sheet->getColumnDimension($promedioColLetter)->setWidth(12);
            },
        ];
    }
}
