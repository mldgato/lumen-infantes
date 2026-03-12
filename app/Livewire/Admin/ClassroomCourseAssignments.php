<?php

namespace App\Livewire\Admin;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Pensum;
use App\Models\Professor;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ClassroomCourseAssignments extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search    = '';
    public string $sort      = 'year';
    public string $direction = 'desc';
    public string $cant      = '10';
    public bool $readyToLoad = false;
    public string $filterYear = '';

    // Gestión de asignaciones
    public ?int $managingClassroomId  = null;
    public ?Classroom $managingClassroom = null;
    public ?Pensum $pensum            = null;

    // assignments[pensum_course_id][unit] = professor_id
    public array $assignments = [];

    // Classrooms del mismo grado para asignación en bloque
    public array $selectedClassrooms = [];
    public array $sameGradeClassrooms = [];

    protected $queryString = [
        'cant'       => ['except' => '10'],
        'sort'       => ['except' => 'year'],
        'direction'  => ['except' => 'desc'],
        'search'     => ['except' => ''],
        'filterYear' => ['except' => ''],
    ];

    public function loadClassrooms(): void
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

    public function manageAssignments(int $classroomId): void
    {
        $this->managingClassroomId = $classroomId;

        $this->managingClassroom = Classroom::with([
            'level',
            'grade',
            'section',
        ])->findOrFail($classroomId);

        // Buscar el pénsum que coincida con el grado y año del classroom
        $this->pensum = Pensum::with([
            'mainCourses.course',
            'mainCourses.subCourses.course',
        ])
            ->where('grade_id', $this->managingClassroom->grade_id)
            ->where('year', $this->managingClassroom->year)
            ->first();

        // Cargar asignaciones existentes
        $this->assignments = [];

        if ($this->pensum) {
            $existing = ClassroomCourseAssignment::where('classroom_id', $classroomId)
                ->get();

            foreach ($existing as $assignment) {
                $this->assignments[$assignment->pensum_course_id][$assignment->unit]
                    = $assignment->professor_id;
            }
        }

        // Otros classrooms del mismo grado y año para asignación en bloque
        $this->sameGradeClassrooms = Classroom::with(['section'])
            ->where('grade_id', $this->managingClassroom->grade_id)
            ->where('year', $this->managingClassroom->year)
            ->where('id', '!=', $classroomId)
            ->get()
            ->toArray();

        $this->selectedClassrooms = [];
    }

    public function resetManaging(): void
    {
        $this->managingClassroomId  = null;
        $this->managingClassroom    = null;
        $this->pensum               = null;
        $this->assignments          = [];
        $this->selectedClassrooms   = [];
        $this->sameGradeClassrooms  = [];
    }

    public function saveAssignments(): void
    {
        $this->authorize('admin.classroom-course-assignments.create');

        if (!$this->pensum) {
            $this->dispatch('showAlert', [
                'title'   => 'Sin pénsum',
                'message' => 'No existe un pénsum para este grado y año.',
                'type'    => 'warning',
            ]);
            return;
        }

        // Classrooms a los que se aplicará (el actual + los seleccionados)
        $classroomIds = array_merge(
            [$this->managingClassroomId],
            $this->selectedClassrooms
        );

        foreach ($classroomIds as $classroomId) {
            foreach ($this->assignments as $pensumCourseId => $units) {
                foreach ($units as $unit => $professorId) {
                    if (!$professorId) {
                        // Si no hay profesor seleccionado, eliminar la asignación si existía
                        ClassroomCourseAssignment::where('classroom_id', $classroomId)
                            ->where('pensum_course_id', $pensumCourseId)
                            ->where('unit', $unit)
                            ->delete();
                        continue;
                    }

                    ClassroomCourseAssignment::updateOrCreate(
                        [
                            'classroom_id'    => $classroomId,
                            'pensum_course_id' => $pensumCourseId,
                            'unit'            => $unit,
                        ],
                        [
                            'professor_id' => $professorId,
                        ]
                    );
                }
            }
        }

        $total = count($classroomIds);
        $msg   = $total > 1
            ? "Asignaciones guardadas en {$total} aulas."
            : 'Asignaciones guardadas correctamente.';

        $this->dispatch('showAlert', [
            'title'   => '¡Éxito!',
            'message' => $msg,
            'type'    => 'success',
        ]);

        $this->dispatch('closeModal', ['modalId' => 'AssignmentsModal']);

        // Recargar asignaciones del classroom actual
        $this->manageAssignments($this->managingClassroomId);
    }

    public function deleteAssignment(int $pensumCourseId, int $unit): void
    {
        $this->authorize('admin.classroom-course-assignments.delete');

        ClassroomCourseAssignment::where('classroom_id', $this->managingClassroomId)
            ->where('pensum_course_id', $pensumCourseId)
            ->where('unit', $unit)
            ->delete();

        // Limpiar del array local
        unset($this->assignments[$pensumCourseId][$unit]);

        $this->dispatch('toastMessage', [
            'type'    => 'info',
            'message' => 'Asignación eliminada.',
        ]);
    }

    public function render()
    {
        $years = Classroom::select('year')->distinct()->orderByDesc('year')->pluck('year');

        $classrooms = $this->readyToLoad
            ? Classroom::with(['level', 'grade', 'section'])
            ->withCount('courseAssignments')
            ->withExists([
                'pensum as has_pensum'
            ])
            ->when($this->filterYear, fn($q) => $q->where('year', $this->filterYear))
            ->where(function ($query) {
                $query->whereHas('level', fn($q) => $q->where('level_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('grade', fn($q) => $q->where('grade_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('section', fn($q) => $q->where('section_name', 'like', '%' . $this->search . '%'))
                    ->orWhere('year', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant)
            : [];

        $professors = Professor::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get()
            ->sortBy('user.name');

        return view('livewire.admin.classroom-course-assignments', compact('classrooms', 'professors', 'years'));
    }
}
