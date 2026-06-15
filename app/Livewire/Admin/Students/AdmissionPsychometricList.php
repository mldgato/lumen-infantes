<?php

namespace App\Livewire\Admin\Students;

use App\Models\AdmissionApplication;
use App\Models\AdmissionPsychometric;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AdmissionPsychometricList extends Component
{
    use WithPagination;

    // ── Filtros ──────────────────────────────────────────────────
    public string $search = '';

    public string $filterYear = '';

    public string $filterStatus = '';

    public string $filterLevel = '';

    public int $cant = 15;

    protected $queryString = ['search', 'filterYear', 'filterStatus', 'filterLevel', 'cant'];

    // ── Modal ────────────────────────────────────────────────────
    public ?AdmissionApplication $viewing = null;

    public string $psychometricResult = '';

    public string $psychometricNotes = '';

    // ── Mount ────────────────────────────────────────────────────
    public function mount(): void
    {
        $this->authorize('admin.admissions.psychometric');
        $this->filterYear = (string) now()->year;
    }

    // ── Reseteo de paginación ─────────────────────────────────────
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

    public function updatingCant(): void
    {
        $this->resetPage();
    }

    // ── Computed ──────────────────────────────────────────────────
    #[Computed]
    public function applications()
    {
        $allowedLevelIds = Auth::user()->levels()->pluck('levels.id');

        return AdmissionApplication::with(['level', 'grade', 'psychometric'])
            ->whereIn('level_id', $allowedLevelIds)
            ->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear))
            ->when($this->filterStatus, fn ($q) => $q->where('current_status', $this->filterStatus))
            ->when($this->filterLevel, fn ($q) => $q->where('level_id', $this->filterLevel))
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->where('student_first_name', 'like', "%{$this->search}%")
                        ->orWhere('student_first_surname', 'like', "%{$this->search}%")
                        ->orWhere('guardian_email', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($this->cant);
    }

    #[Computed]
    public function allLevels(): Collection
    {
        return Auth::user()->levels()->orderBy('ordering')->get();
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

    // ── Abrir modal ───────────────────────────────────────────────
    public function openModal(int $id): void
    {
        $this->authorize('admin.admissions.psychometric');

        $this->viewing = AdmissionApplication::with([
            'level', 'grade', 'statuses.user', 'documents', 'psychometric.user',
        ])->findOrFail($id);

        $canOpen = ! in_array($this->viewing->current_status, ['pending', 'emailed']) || $this->viewing->psychometric_unlocked;
        if (! $canOpen) {
            return;
        }

        $this->psychometricResult = $this->viewing->psychometric?->result ?? '';
        $this->psychometricNotes = $this->viewing->psychometric?->notes ?? '';
        $this->resetValidation();

        $this->dispatch('openPsychometricDetailModal');
    }

    // ── Guardar evaluación ────────────────────────────────────────
    public function savePsychometric(string $notes = ''): void
    {
        $this->authorize('admin.admissions.psychometric');

        $this->psychometricNotes = $notes;

        $this->validate([
            'psychometricResult' => ['required', 'string', 'max:100'],
            'psychometricNotes' => ['nullable', 'string'],
        ], [
            'psychometricResult.required' => 'El resultado psicométrico es requerido.',
        ]);

        $isFirst = ! $this->viewing->psychometric;
        $isCorrection = $this->viewing->psychometric_unlocked && ! $isFirst;

        AdmissionPsychometric::updateOrCreate(
            ['admission_application_id' => $this->viewing->id],
            [
                'result' => $this->psychometricResult,
                'notes' => $this->psychometricNotes ?: null,
                'user_id' => Auth::id(),
            ]
        );

        if ($isFirst) {
            $this->viewing->update(['current_status' => 'psychometric']);
            $this->viewing->statuses()->create([
                'status' => 'psychometric',
                'notes' => 'Evaluación psicométrica registrada. Resultado: '.$this->psychometricResult,
                'user_id' => Auth::id(),
            ]);
        } elseif ($isCorrection) {
            $this->viewing->update(['psychometric_unlocked' => false]);
        }

        $this->viewing->load('statuses.user', 'documents', 'psychometric.user');
        $this->viewing->refresh();
        unset($this->applications);

        $this->dispatch('closePsychometricModal');
        $this->dispatch('showAlert', ['title' => 'Evaluación psicométrica guardada correctamente.', 'type' => 'success']);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.students.admission-psychometric-list');
    }
}
