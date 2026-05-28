<?php

namespace App\Exports;

use App\Models\AuditLog;
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

class AuditLogExport implements FromQuery, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private string $filterModule = '',
        private string $filterEvent = '',
        private string $filterUser = '',
        private string $filterDateFrom = '',
        private string $filterDateTo = '',
        private string $search = '',
    ) {}

    public function query()
    {
        return AuditLog::with('user')
            ->when($this->filterModule, fn ($q) => $q->where('module', $this->filterModule))
            ->when($this->filterEvent, fn ($q) => $q->where('event', $this->filterEvent))
            ->when($this->filterUser, fn ($q) => $q->where('user_id', $this->filterUser))
            ->when($this->filterDateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->filterDateTo))
            ->when($this->search, fn ($q) => $q->where('description', 'like', '%'.$this->search.'%'))
            ->latest();
    }

    public function headings(): array
    {
        return ['Fecha y Hora', 'Usuario', 'Módulo', 'Evento', 'Descripción', 'IP'];
    }

    public function map($log): array
    {
        return [
            $log->created_at->format('d/m/Y H:i:s'),
            $log->user?->name ?? 'Sistema',
            $log->module,
            $log->event,
            $log->description,
            $log->ip_address ?? '',
        ];
    }

    public function title(): string
    {
        return 'Auditoría';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 28,
            'C' => 18,
            'D' => 22,
            'E' => 55,
            'F' => 16,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                for ($row = 2; $row <= $lastRow; $row++) {
                    $bg = $row % 2 === 0 ? 'FFD6E4F0' : 'FFFFFFFF';
                    $sheet->getStyle("A{$row}:F{$row}")
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($bg);
                }

                $sheet->getStyle("A1:F{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFCCCCCC'],
                        ],
                    ],
                ]);

                $sheet->getStyle("A2:F{$lastRow}")
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->setAutoFilter('A1:F1');
                $sheet->freezePane('A2');
            },
        ];
    }
}
