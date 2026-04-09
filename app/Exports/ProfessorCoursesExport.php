<?php

namespace App\Exports;

use App\Models\Professor;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProfessorCoursesExport implements FromArray, WithEvents, WithTitle
{
    public function __construct(protected int $year) {}

    public function title(): string
    {
        return 'Profesores y Cursos';
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

                // ==========================================
                // CONSULTA
                // ==========================================
                $professors = Professor::with([
                    'user',
                    'courseAssignments' => fn($q) => $q
                        ->whereHas('classroom', fn($q) => $q->where('year', $this->year))
                        ->with([
                            'classroom.level',
                            'classroom.grade',
                            'classroom.section',
                            'pensumCourse.course',
                        ]),
                ])
                    ->whereHas(
                        'courseAssignments.classroom',
                        fn($q) => $q->where('year', $this->year)
                    )
                    ->join('users', 'professors.user_id', '=', 'users.id')
                    ->orderBy('users.surname')
                    ->orderBy('users.second_surname')
                    ->orderBy('users.first_name')
                    ->select('professors.*')
                    ->get();

                // ==========================================
                // FILA 1: TÍTULO
                // ==========================================
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', "Profesores y Cursos Asignados — Año {$this->year}");
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(26);

                // ==========================================
                // FILA 2: ENCABEZADOS
                // ==========================================
                $headers = ['No.', 'Profesor', 'Curso', 'Nivel', 'Grado', 'Sección', 'Unidades'];
                foreach ($headers as $col => $label) {
                    $colLetter = chr(65 + $col);
                    $sheet->setCellValue("{$colLetter}2", $label);
                }

                $sheet->getStyle('A2:G2')->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // ==========================================
                // FILAS DE DATOS
                // ==========================================
                $currentRow = 3;
                $profNumber = 1;

                foreach ($professors as $professor) {
                    // Agrupar asignaciones por classroom_id + pensum_course_id
                    $grouped = $professor->courseAssignments
                        ->groupBy(fn($a) => $a->classroom_id . '_' . $a->pensum_course_id)
                        ->map(function ($group) {
                            $first = $group->first();
                            return (object) [
                                'course_name'   => $first->pensumCourse->course->course_name,
                                'level_name'    => $first->classroom->level->level_name,
                                'grade_name'    => $first->classroom->grade->grade_name,
                                'grade_order'   => $first->classroom->grade->ordering,
                                'section_name'  => $first->classroom->section->section_name,
                                'units'         => $group->pluck('unit')->sort()->values()->implode(', '),
                            ];
                        })
                        ->sortBy(fn($row) => sprintf(
                            '%05d_%s_%s',
                            $row->grade_order,
                            $row->section_name,
                            $row->course_name
                        ))
                        ->values();

                    $count = $grouped->count();

                    if ($count === 0) {
                        continue;
                    }

                    $profStartRow = $currentRow;
                    $profEndRow   = $currentRow + $count - 1;
                    $fillColor    = $profNumber % 2 === 0 ? 'FFFFFF' : 'DEEAF1';

                    if ($count > 1) {
                        $sheet->mergeCells("A{$profStartRow}:A{$profEndRow}");
                        $sheet->mergeCells("B{$profStartRow}:B{$profEndRow}");
                    }

                    $sheet->setCellValue("A{$profStartRow}", $profNumber);
                    $sheet->setCellValue("B{$profStartRow}", $professor->user->full_full_name);

                    $sheet->getStyle("A{$profStartRow}:A{$profEndRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    $sheet->getStyle("B{$profStartRow}:B{$profEndRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(false);

                    foreach ($grouped as $idx => $row) {
                        $rowNum = $currentRow + $idx;

                        $sheet->setCellValue("C{$rowNum}", $row->course_name);
                        $sheet->setCellValue("D{$rowNum}", $row->level_name);
                        $sheet->setCellValue("E{$rowNum}", $row->grade_name);
                        $sheet->setCellValue("F{$rowNum}", $row->section_name);
                        $sheet->setCellValue("G{$rowNum}", $row->units);

                        $sheet->getStyle("A{$rowNum}:G{$rowNum}")->applyFromArray([
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $fillColor],
                            ],
                        ]);

                        $sheet->getStyle("C{$rowNum}:G{$rowNum}")->getAlignment()
                            ->setVertical(Alignment::VERTICAL_CENTER);

                        $sheet->getStyle("G{$rowNum}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        $sheet->getRowDimension($rowNum)->setRowHeight(16);
                    }

                    $currentRow = $profEndRow + 1;
                    $profNumber++;
                }

                $lastRow = $currentRow - 1;

                // ==========================================
                // BORDES
                // ==========================================
                if ($lastRow >= 3) {
                    $sheet->getStyle("A1:G{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['rgb' => 'B8CCE4'],
                            ],
                        ],
                    ]);
                }

                // ==========================================
                // FILTROS, FREEZE Y ANCHOS
                // ==========================================
                $sheet->setAutoFilter('A2:G2');
                $sheet->freezePane('A3');

                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                $sheet->getColumnDimension('F')->setWidth(14);
                $sheet->getColumnDimension('G')->setWidth(18);
            },
        ];
    }
}
