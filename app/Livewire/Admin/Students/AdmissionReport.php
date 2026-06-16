<?php

namespace App\Livewire\Admin\Students;

use App\Exports\AdmissionReportExport;
use App\Models\AdmissionApplication;
use App\Models\AdmissionApplicationStatus;
use App\Services\AuditService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdmissionReport extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterYear = '';

    public string $filterStatus = '';

    public string $filterLevel = '';

    public int $cant = 25;

    protected $queryString = ['search', 'filterYear', 'filterStatus', 'filterLevel', 'cant'];

    public function mount(): void
    {
        $this->authorize('admin.admissions.report');
        $this->filterYear = (string) now()->year;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterYear(): void
    {
        $this->resetPage();
    }

    public function updatingFilterLevel(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function applications()
    {
        $allowedLevelIds = Auth::user()->levels()->pluck('levels.id');

        return AdmissionApplication::with(['level', 'grade'])
            ->whereIn('level_id', $allowedLevelIds)
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
            ->orderByDesc('created_at')
            ->paginate($this->cant);
    }

    #[Computed]
    public function availableYears(): array
    {
        $years = AdmissionApplication::selectRaw('DISTINCT year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        return empty($years) ? [now()->year, now()->addYear()->year] : $years;
    }

    #[Computed]
    public function allLevels(): Collection
    {
        return Auth::user()->levels()->orderBy('ordering')->get();
    }

    public function statusLabel(string $status): string
    {
        return AdmissionApplicationStatus::labelFor($status);
    }

    public function statusColor(string $status): string
    {
        return AdmissionApplicationStatus::colorFor($status);
    }

    public function exportExcel(): BinaryFileResponse
    {
        $this->authorize('admin.admissions.report');

        $allowedLevelIds = Auth::user()->levels()->pluck('levels.id')->toArray();

        AuditService::admissionReportDownloaded([
            'año' => $this->filterYear ?: 'todos',
            'estado' => $this->filterStatus ?: 'todos',
            'nivel' => $this->filterLevel ?: 'todos',
            'búsqueda' => $this->search,
        ]);

        $filename = 'admisiones_'.($this->filterYear ?: 'todos').'_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(
            new AdmissionReportExport(
                filterYear: $this->filterYear,
                filterStatus: $this->filterStatus,
                filterLevel: $this->filterLevel,
                search: $this->search,
                allowedLevelIds: $allowedLevelIds,
            ),
            $filename
        );
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.students.admission-report');
    }
}
