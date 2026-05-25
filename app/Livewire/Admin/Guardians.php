<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\GuardianForm;
use App\Models\Guardian;
use Livewire\Component;
use Livewire\WithPagination;

class Guardians extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public GuardianForm $form;

    public string $search = '';

    public string $cant = '15';

    public bool $readyToLoad = false;

    public ?int $selectedGuardianId = null;

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
        $this->selectedGuardianId = $id;
        $this->form->setGuardian(Guardian::findOrFail($id));
        $this->resetValidation();
        $this->dispatch('openGuardianModal');
    }

    public function resetFields(): void
    {
        $this->form->resetForm();
        $this->selectedGuardianId = null;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorize('admin.guardians.edit');
        $this->form->update();
        $name = $this->form->first_name.' '.$this->form->last_name;
        $this->resetFields();
        $this->dispatch('closeModalMessaje', [
            'title' => '¡Actualizado!',
            'message' => "Datos de {$name} actualizados correctamente.",
            'type' => 'success',
            'modalId' => 'GuardianModal',
        ]);
    }

    public function render()
    {
        $guardians = $this->readyToLoad
            ? Guardian::withCount('students')
                ->where(function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('cui', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                })
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate((int) $this->cant)
            : [];

        $students = collect();

        if ($this->selectedGuardianId) {
            $guardian = Guardian::findOrFail($this->selectedGuardianId);
            $students = $guardian->students()
                ->with('user')
                ->get()
                ->sortBy(fn ($s) => $s->user->name)
                ->values();
        }

        return view('livewire.admin.guardians', compact('guardians', 'students'));
    }
}
