<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\ProfessorForm;
use App\Models\ClassroomCourseAssignment;
use App\Models\Professor;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Professors extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ProfessorForm $professorForm;

    public string $search = '';

    public string $filterYear = '';

    public string $sortField = 'surname';

    public string $sortDirection = 'asc';

    public int $perPage = 15;

    public bool $readyToLoad = false;

    // Detail / edit state
    public ?int $editingProfessorId = null;

    public ?int $detailProfessorId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterYear' => ['except' => ''],
        'sortField' => ['except' => 'surname'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function loadProfessors(): void
    {
        $this->readyToLoad = true;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterYear(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openEdit(int $professorId): void
    {
        $professor = Professor::with('user')->findOrFail($professorId);
        $this->professorForm->setProfessor($professor);
        $this->editingProfessorId = $professorId;
    }

    public function closeEdit(): void
    {
        $this->editingProfessorId = null;
        $this->professorForm->resetForm();
        $this->resetValidation();
    }

    public function save(): void
    {
        $professor = Professor::with('user')->findOrFail($this->editingProfessorId);

        $userLevelIds = Auth::user()->levels()->pluck('levels.id');
        $assignedLevelIds = $professor->courseAssignments()
            ->with('classroom')
            ->get()
            ->pluck('classroom.level_id')
            ->unique();

        if ($assignedLevelIds->isNotEmpty() && $assignedLevelIds->diff($userLevelIds)->isNotEmpty()) {
            abort(403);
        }

        $this->professorForm->save($professor->user_id);

        AuditService::userUpdated($professor->user, []);

        $this->closeEdit();
        $this->dispatch('closeModalMessaje', [
            'title' => '¡Éxito!',
            'message' => 'Datos del profesor actualizados.',
            'type' => 'success',
            'modalId' => 'ProfessorEditModal',
        ]);
    }

    public function openDetail(int $professorId): void
    {
        $this->detailProfessorId = $professorId;
    }

    public function closeDetail(): void
    {
        $this->detailProfessorId = null;
    }

    public function render(): \Illuminate\View\View
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');

        $years = ClassroomCourseAssignment::with('classroom')
            ->get()
            ->pluck('classroom.year')
            ->unique()
            ->sort()
            ->values();

        $professors = collect();

        if ($this->readyToLoad) {
            $query = Professor::with('user')
                ->join('users', 'professors.user_id', '=', 'users.id')
                ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                    ->where('users.first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('users.surname', 'like', '%'.$this->search.'%')
                    ->orWhere('users.second_surname', 'like', '%'.$this->search.'%')
                    ->orWhere('users.email', 'like', '%'.$this->search.'%')
                ))
                ->when($this->filterYear, fn ($q) => $q->whereHas(
                    'courseAssignments.classroom',
                    fn ($q) => $q->where('year', $this->filterYear)->whereIn('level_id', $userLevelIds)
                ))
                ->when(! $this->filterYear, fn ($q) => $q->whereHas(
                    'courseAssignments.classroom',
                    fn ($q) => $q->whereIn('level_id', $userLevelIds)
                ))
                ->select('professors.*')
                ->orderBy('users.'.$this->sortField, $this->sortDirection);

            $professors = $query->paginate($this->perPage);
        }

        $detailProfessor = null;
        $detailAssignments = collect();

        if ($this->detailProfessorId) {
            $detailProfessor = Professor::with('user')->find($this->detailProfessorId);
            if ($detailProfessor) {
                $detailQuery = ClassroomCourseAssignment::with([
                    'classroom.level',
                    'classroom.grade',
                    'classroom.section',
                    'pensumCourse.course',
                ])->where('professor_id', $detailProfessor->id)
                    ->whereHas('classroom', fn ($q) => $q->whereIn('level_id', $userLevelIds));

                if ($this->filterYear) {
                    $detailQuery->whereHas('classroom', fn ($q) => $q->where('year', $this->filterYear));
                }

                $detailAssignments = $detailQuery
                    ->get()
                    ->sortByDesc(fn ($a) => $a->classroom->year);
            }
        }

        return view('livewire.admin.professors', compact(
            'professors',
            'years',
            'detailProfessor',
            'detailAssignments',
        ));
    }
}
