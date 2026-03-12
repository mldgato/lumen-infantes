<?php

namespace App\Livewire\Admin;

use App\Models\GradeBook;
use App\Models\Professor;
use Livewire\Component;
use Livewire\WithPagination;

class GradeBooks extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool $readyToLoad  = false;
    public string $search     = '';
    public string $sort       = 'created_at';
    public string $direction  = 'desc';
    public string $cant       = '10';
    public string $filterStatus = '';
    public string $filterYear   = '';

    // Rechazo
    public ?int $rejectingId       = null;
    public string $rejection_reason = '';

    protected $queryString = [
        'cant'          => ['except' => '10'],
        'sort'          => ['except' => 'created_at'],
        'direction'     => ['except' => 'desc'],
        'search'        => ['except' => ''],
        'filterStatus'  => ['except' => ''],
        'filterYear'    => ['except' => ''],
    ];

    public function loadGradeBooks(): void
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

    public function approve(int $id): void
    {
        $this->authorize('admin.grade-books.approve');

        GradeBook::findOrFail($id)->update([
            'status'           => 'approved',
            'rejection_reason' => null,
        ]);

        $this->dispatch('showAlert', [
            'title'   => '¡Aprobado!',
            'message' => 'El cuadro ha sido aprobado exitosamente.',
            'type'    => 'success',
        ]);
    }

    public function openRejectModal(int $id): void
    {
        $this->rejectingId       = $id;
        $this->rejection_reason  = '';
        $this->resetValidation();
    }

    public function reject(): void
    {
        $this->authorize('admin.grade-books.reject');

        $this->validate([
            'rejection_reason' => 'required|string|min:10',
        ], [
            'rejection_reason.required' => 'El motivo de rechazo es obligatorio.',
            'rejection_reason.min'      => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        GradeBook::findOrFail($this->rejectingId)->update([
            'status'           => 'rejected',
            'rejection_reason' => $this->rejection_reason,
        ]);

        $this->rejectingId      = null;
        $this->rejection_reason = '';

        $this->dispatch('closeModalMessaje', [
            'title'   => 'Rechazado',
            'message' => 'El cuadro ha sido rechazado.',
            'type'    => 'warning',
            'modalId' => 'RejectModal',
        ]);
    }

    public function render()
    {
        $gradeBooks = $this->readyToLoad
            ? GradeBook::with([
                'assignment.classroom.level',
                'assignment.classroom.grade',
                'assignment.classroom.section',
                'assignment.pensumCourse.course',
                'assignment.professor.user',
                'academicConfiguration',
            ])
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterYear, fn($q) => $q->whereHas(
                'assignment.classroom',
                fn($q) => $q->where('year', $this->filterYear)
            ))
            ->where(function ($q) {
                $q->whereHas('assignment.classroom.grade', fn($q) => $q->where('grade_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('assignment.classroom.section', fn($q) => $q->where('section_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('assignment.pensumCourse.course', fn($q) => $q->where('course_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('assignment.professor.user', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant)
            : [];

        $years = \App\Models\Classroom::select('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('livewire.admin.grade-books', compact('gradeBooks', 'years'));
    }
}
