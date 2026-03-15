<?php

namespace App\Exports;

use App\Models\Classroom;
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

class StudentListExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(
        protected int $classroomId,
    ) {}

    public function title(): string
    {
        return 'Student List';
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
                $dataStartRow = 3;
                $dataEndRow   = $dataStartRow + $studentCount - 1;

                // ==========================================
                // FILA 1: TÍTULO
                // ==========================================
                $sheet->mergeCells('A1:C1');
                $title = 'Nivel: ' . $classroom->level->level_name
                    . '   |   Grado: ' . $classroom->grade->grade_name
                    . ' ' . $classroom->section->section_name
                    . '   |   Año: ' . $classroom->year;
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
                $sheet->setCellValue('C2', 'Observación');

                $sheet->getStyle('A2:C2')->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // ==========================================
                // FILAS DE ESTUDIANTES
                // ==========================================
                foreach ($students as $idx => $student) {
                    $row  = $dataStartRow + $idx;
                    $fill = $idx % 2 === 0 ? 'FFFFFF' : 'DEEAF1';

                    $fullName = trim(
                        $student->user->surname . ' ' .
                            $student->user->second_surname . ', ' .
                            $student->user->first_name . ' ' .
                            $student->user->middle_name
                    );

                    $sheet->setCellValue("A{$row}", $idx + 1);
                    $sheet->setCellValue("B{$row}", $fullName);
                    $sheet->setCellValue("C{$row}", '');

                    $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                    ]);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getRowDimension($row)->setRowHeight(16);
                }

                // ==========================================
                // BORDES Y FILTROS
                // ==========================================
                $sheet->getStyle("A1:C{$dataEndRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'B8CCE4'],
                        ],
                    ],
                ]);

                $sheet->setAutoFilter("A2:C2");
                $sheet->freezePane("A{$dataStartRow}");

                // ==========================================
                // ANCHOS
                // ==========================================
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(45);
                $sheet->getColumnDimension('C')->setWidth(50);
            },
        ];
    }
}
