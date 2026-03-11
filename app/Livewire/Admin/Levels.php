<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\LevelForm;
use App\Models\Level;
use Livewire\Component;
use Livewire\WithPagination;

class Levels extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public LevelForm $form;

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

    public function loadLevels(): void
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
        $this->form->setLevel(Level::findOrFail($id));
    }

    public function save(): void
    {
        if ($this->form->level) {
            $this->authorize('admin.levels.edit');
            $this->form->update();
            $mensaje = 'Nivel actualizado exitosamente.';
        } else {
            $this->authorize('admin.levels.create');
            $this->form->store();
            $mensaje = 'Nivel creado exitosamente.';
        }

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Éxito!',
            'message' => $mensaje,
            'type'    => 'success',
            'modalId' => 'LevelModal',
        ]);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin.levels.delete');

        $level = Level::findOrFail($id);

        if ($level->classrooms()->exists()) {
            $this->dispatch('showAlert', [
                'title'   => 'No se puede eliminar',
                'message' => 'Este nivel está asignado a una o más aulas.',
                'type'    => 'warning',
            ]);
            return;
        }

        $level->delete();

        $this->dispatch('showAlert', [
            'title'   => '¡Eliminado!',
            'message' => 'Nivel eliminado exitosamente.',
            'type'    => 'success',
        ]);
    }
    public function render()
    {
        $levels = $this->readyToLoad
            ? Level::where('level_name', 'like', '%' . $this->search . '%')
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant)
            : [];

        return view('livewire.admin.levels', compact('levels'));
    }
}
