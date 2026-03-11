<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\ClassroomForm;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use Livewire\Component;
use Livewire\WithPagination;

class Classrooms extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ClassroomForm $form;

    public string $search    = '';
    public string $sort      = 'year';
    public string $direction = 'desc';
    public string $cant      = '10';
    public bool $readyToLoad = false;

    protected $queryString = [
        'cant'      => ['except' => '10'],
        'sort'      => ['except' => 'year'],
        'direction' => ['except' => 'desc'],
        'search'    => ['except' => ''],
    ];

    public function loadClassrooms(): void
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

    public function order(string $sort): void
    {
        if ($this->sort === $sort) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort      = $sort;
            $this->direction = 'asc';
        }
    }

    public function resetFields(): void
    {
        $this->form->resetForm();
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $this->resetFields();
        $this->form->setClassroom(Classroom::findOrFail($id));
    }

    public function save(): void
    {
        if ($this->form->classroom) {
            $this->authorize('admin.classrooms.edit');
            $this->form->update();
            $mensaje = 'Aula actualizada exitosamente.';
        } else {
            $this->authorize('admin.classrooms.create');
            $this->form->store();
            $mensaje = 'Aula creada exitosamente.';
        }

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Éxito!',
            'message' => $mensaje,
            'type'    => 'success',
            'modalId' => 'ClassroomModal',
        ]);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin.classrooms.delete');

        $classroom = Classroom::findOrFail($id);

        if ($classroom->enrollments()->exists()) {
            $this->dispatch('showAlert', [
                'title'   => 'No se puede eliminar',
                'message' => 'Este aula tiene estudiantes inscritos.',
                'type'    => 'warning',
            ]);
            return;
        }

        $classroom->delete();

        $this->dispatch('showAlert', [
            'title'   => '¡Eliminado!',
            'message' => 'Aula eliminada exitosamente.',
            'type'    => 'success',
        ]);
    }

    public function render()
    {
        $classrooms = $this->readyToLoad
            ? Classroom::with(['level', 'grade', 'section'])
            ->where(function ($query) {
                $query->whereHas('level', fn($q) => $q->where('level_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('grade', fn($q) => $q->where('grade_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('section', fn($q) => $q->where('section_name', 'like', '%' . $this->search . '%'))
                    ->orWhere('year', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant)
            : [];

        return view('livewire.admin.classrooms', [
            'classrooms' => $classrooms,
            'levels'     => Level::orderBy('ordering')->get(),
            'grades'     => Grade::orderBy('ordering')->get(),
            'sections'   => Section::orderBy('ordering')->get(),
        ]);
    }
}
