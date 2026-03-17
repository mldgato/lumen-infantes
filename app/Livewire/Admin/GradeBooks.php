<?php

namespace App\Livewire\Admin;

use App\Models\Classroom;
use App\Models\Grade;
use App\Models\GradeBook;
use App\Models\GradeBookActivity;
use App\Models\Level;
use App\Models\Section;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ClassroomCourseAssignment;
use App\Services\AuditService;

class GradeBooks extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool $readyToLoad   = false;
    public string $search      = '';
    public string $sort        = 'created_at';
    public string $direction   = 'desc';
    public string $cant        = '10';

    // Filtros en cascada
    public string $filterYear      = '';
    public string $filterStatus    = '';
    public int|string $filterLevel   = '';
    public int|string $filterGrade   = '';
    public int|string $filterSection = '';
    public int|string $filterUnit    = '';

    // Vista detalle
    public ?GradeBook $viewingGradeBook = null;

    // Rechazo
    public ?int $rejectingId        = null;
    public string $rejection_reason = '';

    protected $queryString = [
        'cant'          => ['except' => '10'],
        'sort'          => ['except' => 'created_at'],
        'direction'     => ['except' => 'desc'],
        'search'        => ['except' => ''],
        'filterStatus'  => ['except' => ''],
        'filterYear'    => ['except' => ''],
        'filterLevel'   => ['except' => ''],
        'filterGrade'   => ['except' => ''],
        'filterSection' => ['except' => ''],
        'filterUnit'    => ['except' => ''],
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
    public function updatingFilterYear(): void
    {
        $this->resetPage();
    }
    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }
    public function updatingFilterLevel(): void
    {
        $this->resetPage();
    }
    public function updatingFilterGrade(): void
    {
        $this->resetPage();
    }
    public function updatingFilterSection(): void
    {
        $this->resetPage();
    }
    public function updatingFilterUnit(): void
    {
        $this->resetPage();
    }

    // Cascada: al cambiar año o nivel se limpian los filtros inferiores
    public function updatedFilterYear(): void
    {
        $this->filterLevel   = '';
        $this->filterGrade   = '';
        $this->filterSection = '';
        $this->filterUnit    = '';
    }

    public function updatedFilterLevel(): void
    {
        $this->filterGrade   = '';
        $this->filterSection = '';
        $this->filterUnit    = '';
    }

    public function updatedFilterGrade(): void
    {
        $this->filterSection = '';
        $this->filterUnit    = '';
    }

    public function updatedFilterSection(): void
    {
        $this->filterUnit = '';
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

    // ==========================================
    // VISTA DETALLE
    // ==========================================

    public function openGradeBook(int $id): void
    {
        $this->viewingGradeBook = GradeBook::with([
            'assignment.classroom.level',
            'assignment.classroom.grade',
            'assignment.classroom.section',
            'assignment.pensumCourse.course',
            'assignment.professor.user',
            'activities.activityType',
            'activities.scores',
            'academicConfiguration',
            'totals',
        ])->findOrFail($id);
    }

    public function closeGradeBook(): void
    {
        $this->viewingGradeBook = null;
        $this->rejectingId      = null;
        $this->rejection_reason = '';
        $this->resetValidation();
    }

    public function getStudentsForGradeBook(): \Illuminate\Support\Collection
    {
        return Student::whereHas('enrollments', function ($q) {
            $q->where('classroom_id', $this->viewingGradeBook->assignment->classroom_id)
                ->where('status', 'Activo');
        })
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->select('students.*')
            ->get();
    }

    // ==========================================
    // APROBACIÓN Y RECHAZO
    // ==========================================

    public function approve(int $id): void
    {
        $this->authorize('admin.grade-books.approve');

        $gradeBook = GradeBook::with([
            'assignment.pensumCourse.course',
            'assignment.classroom.grade',
            'assignment.classroom.section',
        ])->findOrFail($id);

        $oldStatus = $gradeBook->status;

        $gradeBook->update([
            'status'           => 'approved',
            'rejection_reason' => null,
        ]);

        AuditService::gradeBookStatusChanged($gradeBook, $oldStatus, 'approved');

        if ($this->viewingGradeBook && $this->viewingGradeBook->id === $id) {
            $this->openGradeBook($id);
        }

        $this->dispatch('showAlert', [
            'title'   => '¡Aprobado!',
            'message' => 'El cuadro ha sido aprobado exitosamente.',
            'type'    => 'success',
        ]);
    }


    public function openRejectModal(int $id): void
    {
        $this->rejectingId      = $id;
        $this->rejection_reason = '';
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

        $gradeBook = GradeBook::with([
            'assignment.pensumCourse.course',
            'assignment.classroom.grade',
            'assignment.classroom.section',
        ])->findOrFail($this->rejectingId);

        $oldStatus = $gradeBook->status;

        $gradeBook->update([
            'status'           => 'rejected',
            'rejection_reason' => $this->rejection_reason,
        ]);

        AuditService::gradeBookStatusChanged($gradeBook, $oldStatus, 'rejected', $this->rejection_reason);

        if ($this->viewingGradeBook && $this->viewingGradeBook->id === $this->rejectingId) {
            $this->openGradeBook($this->rejectingId);
        }

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
        $years = Classroom::select('year')->distinct()->orderByDesc('year')->pluck('year');

        // Cascada dinámica
        $levels = Level::orderBy('level_name')->get();

        $grades = $this->filterLevel
            ? Grade::whereHas('classrooms', function ($q) {
                $q->where('level_id', $this->filterLevel)
                    ->when($this->filterYear, fn($q) => $q->where('year', $this->filterYear));
            })
            ->orderBy('grade_name')
            ->get()
            : collect();

        $sections = $this->filterGrade
            ? Section::whereHas('classrooms', function ($q) {
                $q->where('grade_id', $this->filterGrade)
                    ->where('level_id', $this->filterLevel)
                    ->when($this->filterYear, fn($q) => $q->where('year', $this->filterYear));
            })
            ->orderBy('section_name')
            ->get()
            : collect();

        $units = $this->filterSection
            ? ClassroomCourseAssignment::whereHas('classroom', function ($q) {
                $q->where('section_id', $this->filterSection)
                    ->where('grade_id', $this->filterGrade)
                    ->where('level_id', $this->filterLevel)
                    ->when($this->filterYear, fn($q) => $q->where('year', $this->filterYear));
            })
            ->distinct()
            ->pluck('unit')
            ->sort()
            ->values()
            : collect();

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
            ->when($this->filterLevel, fn($q) => $q->whereHas(
                'assignment.classroom',
                fn($q) => $q->where('level_id', $this->filterLevel)
            ))
            ->when($this->filterGrade, fn($q) => $q->whereHas(
                'assignment.classroom',
                fn($q) => $q->where('grade_id', $this->filterGrade)
            ))
            ->when($this->filterSection, fn($q) => $q->whereHas(
                'assignment.classroom',
                fn($q) => $q->where('section_id', $this->filterSection)
            ))
            ->when($this->filterUnit, fn($q) => $q->whereHas(
                'assignment',
                fn($q) => $q->where('unit', $this->filterUnit)
            ))
            ->where(function ($q) {
                $q->whereHas('assignment.classroom.grade', fn($q) =>
                $q->where('grade_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('assignment.classroom.section', fn($q) =>
                    $q->where('section_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('assignment.pensumCourse.course', fn($q) =>
                    $q->where('course_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('assignment.professor.user', fn($q) =>
                    $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant)
            : [];

        $students = $this->viewingGradeBook
            ? $this->getStudentsForGradeBook()
            : collect();

        return view('livewire.admin.grade-books', compact(
            'gradeBooks',
            'years',
            'levels',
            'grades',
            'sections',
            'units',
            'students',
        ));
    }
}
