<?php

namespace App\Exports;

use App\Models\AdmissionApplication;
use App\Models\AdmissionApplicationStatus;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdmissionReportExport implements FromQuery, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private string $filterYear = '',
        private string $filterStatus = '',
        private string $filterLevel = '',
        private string $search = '',
        private array $allowedLevelIds = [],
    ) {}

    public function query()
    {
        return AdmissionApplication::with(['level', 'grade'])
            ->when($this->allowedLevelIds, fn ($q) => $q->whereIn('level_id', $this->allowedLevelIds))
            ->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear))
            ->when($this->filterLevel, fn ($q) => $q->where('level_id', $this->filterLevel))
            ->when($this->filterStatus === 'in_progress', fn ($q) => $q->whereNotIn('current_status', ['accepted', 'rejected']))
            ->when($this->filterStatus && $this->filterStatus !== 'in_progress', fn ($q) => $q->where('current_status', $this->filterStatus))
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('student_first_name', 'like', "%{$this->search}%")
                        ->orWhere('student_first_surname', 'like', "%{$this->search}%")
                        ->orWhere('guardian_email', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Año',
            'Primer nombre',
            'Segundo nombre',
            'Primer apellido',
            'Segundo apellido',
            'Fecha de nacimiento',
            'Dirección',
            'Escuela anterior',
            'Religión',
            'Nivel',
            'Grado',
            'Estado',
            'Nombre del padre',
            'Teléfono del padre',
            'NIT del padre',
            'Lugar de trabajo (padre)',
            'Profesión del padre',
            'Nombre de la madre',
            'Teléfono de la madre',
            'NIT de la madre',
            'Lugar de trabajo (madre)',
            'Profesión de la madre',
            'Tipo de encargado',
            'Nombre del encargado',
            'Teléfono del encargado',
            'NIT del encargado',
            'Correo del encargado',
            'Hijos varones (cantidad)',
            'Hijos varones (edades)',
            'Hijas (cantidad)',
            'Hijas (edades)',
            'Cómo nos conoció',
            'URL documentos',
            'URL pago',
            'Fecha de solicitud',
        ];
    }

    public function map($app): array
    {
        return [
            $app->year,
            $app->student_first_name,
            $app->student_second_name ?? '',
            $app->student_first_surname,
            $app->student_second_surname ?? '',
            $app->student_birthdate?->format('d/m/Y') ?? '',
            $app->student_address ?? '',
            $app->student_previous_school ?? '',
            $app->student_religion ?? '',
            $app->level?->level_name ?? '',
            $app->grade?->grade_name ?? '',
            AdmissionApplicationStatus::labelFor($app->current_status ?? 'pending'),
            $app->father_first_name ?? '',
            $app->father_phone ?? '',
            $app->father_nit ?? '',
            $app->father_workplace ?? '',
            $app->father_profession ?? '',
            $app->mother_first_name ?? '',
            $app->mother_phone ?? '',
            $app->mother_nit ?? '',
            $app->mother_workplace ?? '',
            $app->mother_profession ?? '',
            $app->guardianTypeLabel(),
            $app->guardian_name ?? '',
            $app->guardian_phone ?? '',
            $app->guardianNit() ?? '',
            $app->guardian_email ?? '',
            $app->sons_count ?? '',
            $app->sons_ages ?? '',
            $app->daughters_count ?? '',
            $app->daughters_ages ?? '',
            $app->referral_source ?? '',
            $app->url_documents ?? '',
            $app->url_payment ?? '',
            $app->created_at->format('d/m/Y H:i'),
        ];
    }

    public function title(): string
    {
        return 'Solicitudes de Admisión';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 18,
            'C' => 18,
            'D' => 18,
            'E' => 18,
            'F' => 16,
            'G' => 30,
            'H' => 22,
            'I' => 14,
            'J' => 18,
            'K' => 16,
            'L' => 24,
            'M' => 22,
            'N' => 16,
            'O' => 14,
            'P' => 22,
            'Q' => 20,
            'R' => 22,
            'S' => 16,
            'T' => 14,
            'U' => 22,
            'V' => 20,
            'W' => 18,
            'X' => 24,
            'Y' => 16,
            'Z' => 14,
            'AA' => 28,
            'AB' => 12,
            'AC' => 18,
            'AD' => 12,
            'AE' => 18,
            'AF' => 22,
            'AG' => 35,
            'AH' => 35,
            'AI' => 18,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = $sheet->getHighestColumn();

                $sheet->getRowDimension(1)->setRowHeight(36);

                for ($row = 2; $row <= $lastRow; $row++) {
                    $bg = $row % 2 === 0 ? 'FFD6E4F0' : 'FFFFFFFF';
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($bg);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }

                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFCCCCCC'],
                        ],
                    ],
                ]);

                $sheet->setAutoFilter("A1:{$lastCol}1");
                $sheet->freezePane('A2');
            },
        ];
    }
}
