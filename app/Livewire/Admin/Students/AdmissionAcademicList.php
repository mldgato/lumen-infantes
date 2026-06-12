<?php

namespace App\Livewire\Admin\Students;

use App\Models\AdmissionAcademicScore;
use App\Models\AdmissionApplication;
use App\Models\AdmissionCourse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AdmissionAcademicList extends Component
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

    public string $selectedCourseId = '';

    public string $score = '';

    // ── Mount ────────────────────────────────────────────────────
    public function mount(): void
    {
        $this->authorize('admin.admissions.academic');
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

        return AdmissionApplication::with(['level', 'grade', 'academicScores'])
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

    #[Computed]
    public function availableCourses(): Collection
    {
        if (! $this->viewing) {
            return collect();
        }

        $addedIds = $this->viewing->academicScores->pluck('admission_course_id');

        return AdmissionCourse::whereNotIn('id', $addedIds)->orderBy('ordering')->get();
    }

    // ── Abrir modal ───────────────────────────────────────────────
    public function openModal(int $id): void
    {
        $this->authorize('admin.admissions.academic');

        $this->viewing = AdmissionApplication::with([
            'level', 'grade', 'statuses.user', 'academicScores.course',
        ])->findOrFail($id);

        $this->selectedCourseId = '';
        $this->score = '';
        $this->resetValidation();
        unset($this->availableCourses);

        $this->dispatch('openAcademicModal');
    }

    // ── Agregar punteo ─────────────────────────────────────────────
    public function addScore(): void
    {
        $this->authorize('admin.admissions.academic');

        if ($this->viewing->current_status !== 'psychometric') {
            return;
        }

        $this->validate([
            'selectedCourseId' => ['required', 'exists:admission_courses,id'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'selectedCourseId.required' => 'Seleccione una materia.',
            'selectedCourseId.exists' => 'Materia no válida.',
            'score.required' => 'El punteo es requerido.',
            'score.numeric' => 'El punteo debe ser un número.',
            'score.min' => 'El punteo mínimo es 0.',
            'score.max' => 'El punteo máximo es 100.',
        ]);

        $exists = AdmissionAcademicScore::where('admission_application_id', $this->viewing->id)
            ->where('admission_course_id', $this->selectedCourseId)
            ->exists();

        if ($exists) {
            $this->addError('selectedCourseId', 'Esta materia ya fue registrada.');

            return;
        }

        AdmissionAcademicScore::create([
            'admission_application_id' => $this->viewing->id,
            'admission_course_id' => $this->selectedCourseId,
            'score' => $this->score,
            'user_id' => Auth::id(),
        ]);

        $this->selectedCourseId = '';
        $this->score = '';
        $this->resetValidation();

        $this->viewing->load('academicScores.course');
        $this->viewing->refresh();
        unset($this->availableCourses, $this->applications);

        $this->dispatch('toastMessage', ['message' => 'Materia agregada.', 'type' => 'success']);
    }

    // ── Eliminar punteo ────────────────────────────────────────────
    public function removeScore(int $scoreId): void
    {
        $this->authorize('admin.admissions.academic');

        if ($this->viewing->current_status !== 'psychometric') {
            return;
        }

        AdmissionAcademicScore::where('id', $scoreId)
            ->where('admission_application_id', $this->viewing->id)
            ->delete();

        $this->viewing->load('academicScores.course');
        $this->viewing->refresh();
        unset($this->availableCourses, $this->applications);

        $this->dispatch('toastMessage', ['message' => 'Materia eliminada.', 'type' => 'info']);
    }

    // ── Finalizar evaluación ──────────────────────────────────────
    public function finalizeEvaluation(): void
    {
        $this->authorize('admin.admissions.academic');

        if ($this->viewing->current_status !== 'psychometric') {
            return;
        }

        if ($this->viewing->academicScores->isEmpty()) {
            $this->dispatch('showAlert', [
                'title' => 'Debe agregar al menos una materia antes de finalizar.',
                'type' => 'warning',
            ]);

            return;
        }

        $count = $this->viewing->academicScores->count();

        $this->viewing->update(['current_status' => 'academic']);
        $this->viewing->statuses()->create([
            'status' => 'academic',
            'notes' => 'Evaluaciones académicas registradas. '.$count.' materia(s).',
            'user_id' => Auth::id(),
        ]);

        $this->viewing->load('statuses.user', 'academicScores.course');
        $this->viewing->refresh();
        unset($this->applications);

        $this->dispatch('closeAcademicModal');
        $this->dispatch('showAlert', [
            'title' => 'Evaluaciones académicas finalizadas correctamente.',
            'type' => 'success',
        ]);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.students.admission-academic-list');
    }
}
