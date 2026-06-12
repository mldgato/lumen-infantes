<?php

namespace App\Livewire\Admin;

use App\Models\AdmissionCourse;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AdmissionCourses extends Component
{
    use WithPagination;

    public string $search = '';

    public int $cant = 15;

    // ── Modal ────────────────────────────────────────────────────
    public ?int $editId = null;

    public string $editName = '';

    public int $editOrdering = 0;

    protected $queryString = ['search', 'cant'];

    public function mount(): void
    {
        $this->authorize('admin.admission-courses.index');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function courses()
    {
        return AdmissionCourse::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('ordering')
            ->orderBy('name')
            ->paginate($this->cant);
    }

    // ── Abrir modal ───────────────────────────────────────────────
    public function openCreate(): void
    {
        $this->authorize('admin.admission-courses.index');
        $this->editId = null;
        $this->editName = '';
        $this->editOrdering = AdmissionCourse::max('ordering') + 1 ?: 1;
        $this->resetValidation();
        $this->dispatch('openCourseModal');
    }

    public function openEdit(int $id): void
    {
        $this->authorize('admin.admission-courses.index');
        $course = AdmissionCourse::findOrFail($id);
        $this->editId = $course->id;
        $this->editName = $course->name;
        $this->editOrdering = $course->ordering;
        $this->resetValidation();
        $this->dispatch('openCourseModal');
    }

    // ── Guardar ───────────────────────────────────────────────────
    public function save(): void
    {
        $this->authorize('admin.admission-courses.index');

        $this->validate([
            'editName' => ['required', 'string', 'max:100'],
            'editOrdering' => ['required', 'integer', 'min:0'],
        ], [
            'editName.required' => 'El nombre de la materia es requerido.',
            'editOrdering.required' => 'El orden es requerido.',
            'editOrdering.integer' => 'El orden debe ser un número entero.',
        ]);

        if ($this->editId) {
            AdmissionCourse::findOrFail($this->editId)->update([
                'name' => trim($this->editName),
                'ordering' => $this->editOrdering,
            ]);
        } else {
            AdmissionCourse::create([
                'name' => trim($this->editName),
                'ordering' => $this->editOrdering,
            ]);
        }

        unset($this->courses);
        $this->dispatch('closeCourseModal');
        $this->dispatch('toastMessage', ['message' => 'Materia guardada.', 'type' => 'success']);
    }

    // ── Eliminar ──────────────────────────────────────────────────
    public function delete(int $id): void
    {
        $this->authorize('admin.admission-courses.index');
        AdmissionCourse::findOrFail($id)->delete();
        unset($this->courses);
        $this->dispatch('toastMessage', ['message' => 'Materia eliminada.', 'type' => 'info']);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.admission-courses');
    }
}
