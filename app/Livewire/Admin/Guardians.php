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

    public GuardianForm $guardianForm;

    public string $search = '';

    public int $perPage = 15;

    public bool $readyToLoad = false;

    public ?int $editingGuardianId = null;

    public ?int $detailGuardianId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 15],
    ];

    public function loadGuardians(): void
    {
        $this->readyToLoad = true;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openEdit(int $guardianId): void
    {
        $guardian = Guardian::findOrFail($guardianId);
        $this->guardianForm->setGuardian($guardian);
        $this->editingGuardianId = $guardianId;
    }

    public function closeEdit(): void
    {
        $this->editingGuardianId = null;
        $this->guardianForm->resetForm();
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->guardianForm->update();
        $this->closeEdit();
        $this->dispatch('closeModalMessaje', [
            'title' => '¡Éxito!',
            'message' => 'Datos del guardián actualizados.',
            'type' => 'success',
            'modalId' => 'GuardianEditModal',
        ]);
    }

    public function openDetail(int $guardianId): void
    {
        $this->detailGuardianId = $guardianId;
    }

    public function closeDetail(): void
    {
        $this->detailGuardianId = null;
    }

    public function render(): \Illuminate\View\View
    {
        $guardians = collect();

        if ($this->readyToLoad) {
            $guardians = Guardian::when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('first_name', 'like', '%'.$this->search.'%')
                ->orWhere('last_name', 'like', '%'.$this->search.'%')
                ->orWhere('cui', 'like', '%'.$this->search.'%')
                ->orWhere('phone', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%')
            ))
                ->withCount('students')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate($this->perPage);
        }

        $detailGuardian = null;
        $detailStudents = collect();

        if ($this->detailGuardianId) {
            $detailGuardian = Guardian::with(['students.user'])->find($this->detailGuardianId);
            if ($detailGuardian) {
                $detailStudents = $detailGuardian->students->sortBy(fn ($s) => $s->user->full_full_name);
            }
        }

        return view('livewire.admin.guardians', compact(
            'guardians',
            'detailGuardian',
            'detailStudents',
        ));
    }
}
