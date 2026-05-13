<?php

namespace App\Livewire\Admin;

use App\Models\ActivityType;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityTypes extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool $readyToLoad = false;

    public string $search = '';

    public string $sort = 'name';

    public string $direction = 'asc';

    public string $cant = '10';

    // Formulario
    public ?ActivityType $editing = null;

    public string $name = '';

    public bool $is_extra = false;

    protected $queryString = [
        'cant' => ['except' => '10'],
        'sort' => ['except' => 'name'],
        'direction' => ['except' => 'asc'],
        'search' => ['except' => ''],
    ];

    public function loadTypes(): void
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
            $this->sort = $sort;
            $this->direction = 'asc';
        }
    }

    public function resetFields(): void
    {
        $this->editing = null;
        $this->name = '';
        $this->is_extra = false;
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $this->resetFields();
        $type = ActivityType::findOrFail($id);
        $this->editing = $type;
        $this->name = $type->name;
        $this->is_extra = $type->is_extra;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 100 caracteres.',
        ]);

        if ($this->editing) {
            $this->authorize('admin.activity-types.edit');
            $this->editing->update([
                'name' => $this->name,
                'is_extra' => $this->is_extra,
            ]);
            $mensaje = 'Tipo de actividad actualizado exitosamente.';
        } else {
            $this->authorize('admin.activity-types.create');
            ActivityType::create([
                'name' => $this->name,
                'is_extra' => $this->is_extra,
            ]);
            $mensaje = 'Tipo de actividad creado exitosamente.';
        }

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title' => '¡Éxito!',
            'message' => $mensaje,
            'type' => 'success',
            'modalId' => 'ActivityTypeModal',
        ]);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin.activity-types.delete');

        $type = ActivityType::findOrFail($id);

        if ($type->configurationActivities()->exists() || $type->gradeBookActivities()->exists()) {
            $this->dispatch('showAlert', [
                'title' => 'No se puede eliminar',
                'message' => 'Este tipo está en uso en configuraciones o cuadros de calificaciones.',
                'type' => 'warning',
            ]);

            return;
        }

        $type->delete();

        $this->dispatch('showAlert', [
            'title' => '¡Eliminado!',
            'message' => 'Tipo de actividad eliminado exitosamente.',
            'type' => 'success',
        ]);
    }

    public function render()
    {
        $types = $this->readyToLoad
            ? ActivityType::where('name', 'like', '%'.$this->search.'%')
                ->orderBy($this->sort, $this->direction)
                ->paginate($this->cant)
            : [];

        return view('livewire.admin.activity-types', compact('types'));
    }
}
