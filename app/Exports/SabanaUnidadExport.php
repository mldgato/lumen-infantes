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

                // Filas
                $headerRow    = 2;
                $dataStartRow = 3;
                $dataEndRow   = $dataStartRow + $studentCount - 1;
                $aprobRow     = $dataEndRow + 1;
                $noAprobRow   = $dataEndRow + 2;
                $promedioRow  = $dataEndRow + 3;

                $lastColIndex  = 2 + $courseCount;
                $lastColLetter = Coordinate::stringFromColumnIndex($lastColIndex + 1);

                // ==========================================
                // FILA 1: TÍTULO
                // ==========================================
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $title = 'Año: ' . $classroom->year
                    . '   |   Nivel: ' . $classroom->level->level_name
                    . '   |   Grado: ' . $classroom->grade->grade_name
                    . '   |   Sección: ' . $classroom->section->section_name
                    . '   |   Unidad: ' . $this->unit;
                $sheet->setCellValue('A1', $title);
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // ==========================================
                // FILA 2: ENCABEZADOS DE COLUMNAS
                // ==========================================
                $sheet->setCellValue('A2', 'No.');
                $sheet->setCellValue('B2', 'Estudiante');

                foreach ($assignments as $idx => $assignment) {
                    $col = Coordinate::stringFromColumnIndex($idx + 3);
                    $sheet->setCellValue("{$col}2", $assignment->pensumCourse->course->course_name);
                }

                $sheet->getStyle("A2:{$lastColLetter}2")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
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

                    foreach ($assignments as $colIdx => $assignment) {
                        $col = Coordinate::stringFromColumnIndex($colIdx + 3);

                        if ($assignment->gradeBook && $assignment->gradeBook->status === 'approved') {
                            $total = GradeBookTotal::where('grade_book_id', $assignment->gradeBook->id)
                                ->where('student_id', $student->id)
                                ->first();

                            if ($total) {
                                $sheet->setCellValue("{$col}{$row}", (int) ceil($total->total_points));
                                $sheet->getStyle("{$col}{$row}")
                                    ->getNumberFormat()
                                    ->setFormatCode('0');
                            }
                        }
                    }

                    $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                    ]);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$row}:{$lastColLetter}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // ==========================================
                // FILAS DE RESUMEN CON FÓRMULAS EXCEL
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
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);

                    foreach ($assignments as $colIdx => $assignment) {
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

                // ==========================================
                // ANCHOS DE COLUMNA
                // ==========================================
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(38);
                for ($i = 0; $i < $courseCount; $i++) {
                    $col = Coordinate::stringFromColumnIndex($i + 3);
                    $sheet->getColumnDimension($col)->setWidth(20);
                }
            },
        ];
    }
}
