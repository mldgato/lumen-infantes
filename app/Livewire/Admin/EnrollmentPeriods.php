<?php

namespace App\Livewire\Admin;

use App\Models\EnrollmentPeriod;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class EnrollmentPeriods extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool $showForm = false;

    public ?int $editingId = null;

    public int $year = 0;

    public string $start_date = '';

    public string $end_date = '';

    public bool $allow_enrollments = false;

    public bool $allow_data_updates = false;

    public function mount(): void
    {
        $this->authorize('admin.enrollment-periods.index');
        $this->year = now()->year;
    }

    public function openCreate(): void
    {
        $this->authorize('admin.enrollment-periods.create');
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorize('admin.enrollment-periods.edit');
        $period = EnrollmentPeriod::findOrFail($id);

        $this->editingId = $id;
        $this->year = $period->year;
        $this->start_date = $period->start_date->format('Y-m-d');
        $this->end_date = $period->end_date->format('Y-m-d');
        $this->allow_enrollments = $period->allow_enrollments;
        $this->allow_data_updates = $period->allow_data_updates;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->editingId
            ? $this->authorize('admin.enrollment-periods.edit')
            : $this->authorize('admin.enrollment-periods.create');

        $validated = $this->validate([
            'year' => 'required|integer|min:2020|max:2099',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'allow_enrollments' => 'boolean',
            'allow_data_updates' => 'boolean',
        ], [
            'year.required' => 'El año es obligatorio.',
            'year.min' => 'El año mínimo es 2020.',
            'year.max' => 'El año máximo es 2099.',
            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'end_date.required' => 'La fecha de fin es obligatoria.',
            'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
        ]);

        if ($validated['allow_enrollments'] && EnrollmentPeriod::hasOverlap('allow_enrollments', $validated['start_date'], $validated['end_date'], $this->editingId)) {
            $this->addError('allow_enrollments', 'Ya existe un período de inscripciones activo que se solapa con esas fechas.');

            return;
        }

        if ($validated['allow_data_updates'] && EnrollmentPeriod::hasOverlap('allow_data_updates', $validated['start_date'], $validated['end_date'], $this->editingId)) {
            $this->addError('allow_data_updates', 'Ya existe un período de actualización de datos activo que se solapa con esas fechas.');

            return;
        }

        EnrollmentPeriod::updateOrCreate(
            ['id' => $this->editingId],
            $validated
        );

        $this->resetForm();
        $this->showForm = false;

        $this->dispatch('toastMessage', [
            'type' => 'success',
            'message' => $this->editingId ? 'Período actualizado.' : 'Período creado.',
        ]);

        $this->editingId = null;
    }

    public function delete(int $id): void
    {
        $this->authorize('admin.enrollment-periods.delete');
        EnrollmentPeriod::findOrFail($id)->delete();

        $this->dispatch('toastMessage', [
            'type' => 'success',
            'message' => 'Período eliminado.',
        ]);
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->year = now()->year;
        $this->start_date = '';
        $this->end_date = '';
        $this->allow_enrollments = false;
        $this->allow_data_updates = false;
        $this->resetValidation();
    }

    public function render(): View
    {
        $today = now()->toDateString();
        $periods = EnrollmentPeriod::orderByDesc('year')->orderByDesc('start_date')->paginate(10);

        return view('livewire.admin.enrollment-periods', compact('periods', 'today'));
    }
}
