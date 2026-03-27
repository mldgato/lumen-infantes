<?php

namespace App\Exports;

use App\Models\GradeBookActivity;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ActivityTemplateExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(
        protected GradeBookActivity $activity,
        protected Collection $students
    ) {}

    public function title(): string
    {
        return substr('Plantilla - ' . $this->activity->name, 0, 31); // Excel limita las pestañas a 31 caracteres
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

                $studentCount = $this->students->count();
                $dataStartRow = 4;
                $dataEndRow   = $dataStartRow + $studentCount - 1;

                // ==========================================
                // FILA 1: TÍTULO DE LA ACTIVIDAD
                // ==========================================
                $sheet->mergeCells('A1:D1');
                $titleText = 'Actividad: ' . $this->activity->name . ' | Puntos Máximos: ' . $this->activity->max_points . ' | [ACT_ID:' . $this->activity->id . ']';

                $sheet->setCellValue('A1', $titleText);
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // ==========================================
                // FILA 2: ADVERTENCIA IMPORTANTE
                // ==========================================
                $sheet->mergeCells('A2:D2');
                $sheet->setCellValue('A2', '⚠️ IMPORTANTE: NO CAMBIE EL ORDEN DE LOS ESTUDIANTES NI MODIFIQUE LA COLUMNA DE ID ⚠️');
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => '9C0006']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);

                // ==========================================
                // FILA 3: ENCABEZADOS
                // ==========================================
                $sheet->setCellValue('A3', 'ID Sistema');
                $sheet->setCellValue('B3', 'No.');
                $sheet->setCellValue('C3', 'Estudiante (Apellidos, Nombres)');
                $sheet->setCellValue('D3', 'Nota');

                $sheet->getStyle('A3:D3')->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(18);

                // ==========================================
                // FILAS DE ESTUDIANTES
                // ==========================================
                foreach ($this->students as $idx => $student) {
                    $row  = $dataStartRow + $idx;
                    $fill = $idx % 2 === 0 ? 'FFFFFF' : 'DEEAF1';

                    $sheet->setCellValue("A{$row}", $student->id); // ID vital para la futura subida
                    $sheet->setCellValue("B{$row}", $idx + 1);
                    $sheet->setCellValue("C{$row}", $student->user->full_full_name);
                    $sheet->setCellValue("D{$row}", ''); // Celda vacía para que ingresen la nota

                    $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                    ]);

                    $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Resaltar la celda donde va la nota para guiar al usuario
                    $sheet->getStyle("D{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
                    ]);

                    $sheet->getRowDimension($row)->setRowHeight(16);
                }

                // ==========================================
                // BORDES Y PROTECCIÓN BÁSICA
                // ==========================================
                $sheet->getStyle("A1:D{$dataEndRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'B8CCE4'],
                        ],
                    ],
                ]);

                // Ajuste de columnas
                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(8);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setWidth(15);

                // Bloquear panel para que los encabezados queden fijos
                $sheet->freezePane("A{$dataStartRow}");
            },
        ];
    }
}
