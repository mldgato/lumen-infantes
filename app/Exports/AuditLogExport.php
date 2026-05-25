<?php

namespace App\Exports;

use App\Models\AuditLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AuditLogExport implements FromQuery, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        protected string $filterModule = '',
        protected string $filterEvent = '',
        protected string $filterUser = '',
        protected string $filterDateFrom = '',
        protected string $filterDateTo = '',
        protected string $search = '',
    ) {}

    public function title(): string
    {
        return 'Auditoría';
    }

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
        return [
            'Fecha y Hora',
            'Módulo',
            'Evento',
            'Descripción',
            'Usuario',
            'IP',
        ];
    }

    public function map($log): array
    {
        return [
            $log->created_at->format('d/m/Y H:i:s'),
            $log->module,
            $log->event,
            $log->description,
            $log->user?->name ?? 'Sistema',
            $log->ip_address ?? '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:F1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(20);

                $highestRow = $sheet->getHighestRow();

                if ($highestRow > 1) {
                    $sheet->getStyle("A2:F{$highestRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B8CCE4']],
                        ],
                    ]);

                    $sheet->getStyle("A1:F{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }

                $sheet->setAutoFilter('A1:F1');
                $sheet->freezePane('A2');
                $sheet->getColumnDimension('A')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(60);
                $sheet->getColumnDimension('E')->setWidth(30);
            },
        ];
    }
}
