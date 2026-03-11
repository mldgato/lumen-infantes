<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\SectionForm;
use App\Models\Section;
use Livewire\Component;
use Livewire\WithPagination;

class Sections extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public SectionForm $form;

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

    public function loadSections(): void
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
        $this->form->setSection(Section::findOrFail($id));
    }

    public function save(): void
    {
        if ($this->form->section) {
            $this->authorize('admin.sections.edit');
            $this->form->update();
            $mensaje = 'Sección actualizada exitosamente.';
        } else {
            $this->authorize('admin.sections.create');
            $this->form->store();
            $mensaje = 'Sección creada exitosamente.';
        }

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Éxito!',
            'message' => $mensaje,
            'type'    => 'success',
            'modalId' => 'SectionModal',
        ]);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin.sections.delete');

        $section = Section::findOrFail($id);

        if ($section->classrooms()->exists()) {
            $this->dispatch('showAlert', [
                'title'   => 'No se puede eliminar',
                'message' => 'Esta sección está asignada a una o más aulas.',
                'type'    => 'warning',
            ]);
            return;
        }

        $section->delete();

        $this->dispatch('showAlert', [
            'title'   => '¡Eliminado!',
            'message' => 'Sección eliminada exitosamente.',
            'type'    => 'success',
        ]);
    }

    public function render()
    {
        $sections = $this->readyToLoad
            ? Section::where('section_name', 'like', '%' . $this->search . '%')
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant)
            : [];

        return view('livewire.admin.sections', compact('sections'));
    }
}
