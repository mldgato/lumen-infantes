<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\ProfessorForm;
use App\Models\ClassroomCourseAssignment;
use App\Models\Professor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Professors extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ProfessorForm $form;

    public string $search = '';

    public string $cant = '15';

    public bool $readyToLoad = false;

    public ?int $selectedProfessorId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'cant' => ['except' => '15'],
    ];

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCant(): void
    {
        $this->resetPage();
    }

    public function openModal(int $id): void
    {
        $this->selectedProfessorId = $id;
        $this->form->setProfessor(Professor::with('user')->findOrFail($id));
        $this->resetValidation();
        $this->dispatch('openProfessorModal');
    }

    public function resetFields(): void
    {
        $this->form->resetForm();
        $this->selectedProfessorId = null;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorize('admin.professors.edit');
        $this->form->save($this->form->professor->user_id);

        $name = $this->form->professor->user->name;
        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title' => '¡Actualizado!',
            'message' => "Datos de {$name} actualizados correctamente.",
            'type' => 'success',
            'modalId' => 'ProfessorModal',
        ]);
    }

    public function render(): \Illuminate\View\View
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');

        $professors = $this->readyToLoad
            ? Professor::with('user')
                ->join('users', 'professors.user_id', '=', 'users.id')
                ->where(function ($q) {
                    $q->where('users.name', 'like', '%'.$this->search.'%')
                        ->orWhere('users.email', 'like', '%'.$this->search.'%');
                })
                ->whereHas('courseAssignments.classroom', fn ($q) => $q->whereIn('level_id', $userLevelIds))
                ->orderBy('users.name')
                ->select('professors.*')
                ->paginate((int) $this->cant)
            : [];

        $assignments = collect();

        if ($this->selectedProfessorId) {
            $assignments = ClassroomCourseAssignment::with([
                'classroom.level', 'classroom.grade', 'classroom.section',
                'pensumCourse.course', 'gradeBook',
            ])
                ->where('professor_id', $this->selectedProfessorId)
                ->whereHas('classroom', fn ($q) => $q->whereIn('level_id', $userLevelIds))
                ->get()
                ->groupBy(fn ($a) => $a->classroom->year)
                ->sortKeysDesc();
        }

        return view('livewire.admin.professors', compact('professors', 'assignments'));
    }
}
