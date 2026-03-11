<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\GradeForm;
use App\Models\Grade;
use Livewire\Component;
use Livewire\WithPagination;

class Grades extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public GradeForm $form;

    public string $search    = '';
    public string $sort      = 'ordering';
    public string $direction = 'asc';
    public string $cant      = '10';
    public bool $readyToLoad = false;

    protected $queryString = [
        'cant'      => ['except' => '10'],
        'sort'      => ['except' => 'ordering'],
        'direction' => ['except' => 'asc'],
        'search'    => ['except' => ''],
    ];

    public function loadGrades(): void
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
        $this->form->setGrade(Grade::findOrFail($id));
    }

    public function save(): void
    {
        if ($this->form->grade) {
            $this->authorize('admin.grades.edit');
            $this->form->update();
            $mensaje = 'Grado actualizado exitosamente.';
        } else {
            $this->authorize('admin.grades.create');
            $this->form->store();
            $mensaje = 'Grado creado exitosamente.';
        }

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Éxito!',
            'message' => $mensaje,
            'type'    => 'success',
            'modalId' => 'GradeModal',
        ]);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin.grades.delete');

        $grade = Grade::findOrFail($id);

        if ($grade->classrooms()->exists()) {
            $this->dispatch('showAlert', [
                'title'   => 'No se puede eliminar',
                'message' => 'Este grado está asignado a una o más aulas.',
                'type'    => 'warning',
            ]);
            return;
        }

        $grade->delete();

        $this->dispatch('showAlert', [
            'title'   => '¡Eliminado!',
            'message' => 'Grado eliminado exitosamente.',
            'type'    => 'success',
        ]);
    }

    public function render()
    {
        $grades = $this->readyToLoad
            ? Grade::where('grade_name', 'like', '%' . $this->search . '%')
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant)
            : [];

        return view('livewire.admin.grades', compact('grades'));
    }
}
