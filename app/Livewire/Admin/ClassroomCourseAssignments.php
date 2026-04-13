<?php

namespace App\Livewire\Admin;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Pensum;
use App\Models\Professor;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

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

    // Guarda el estado de bloqueo de las asignaciones: lockedAssignments[pensum_course_id][unit] = true
    public array $lockedAssignments = [];

    // Almacena el valor original para la auditoría: originalAssignments[pensum_course_id][unit] = professor_id
    public array $originalAssignments = [];

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

        // Cargar asignaciones existentes y verificar bloqueos
        $this->assignments         = [];
        $this->lockedAssignments   = [];
        $this->originalAssignments = [];

        if ($this->pensum) {
            // Optimización: Traemos la relación gradeBook solo con el ID para saber si existe
            $existing = ClassroomCourseAssignment::with('gradeBook:id,classroom_course_assignment_id')
                ->where('classroom_id', $classroomId)
                ->get();

            foreach ($existing as $assignment) {
                $this->assignments[$assignment->pensum_course_id][$assignment->unit] = $assignment->professor_id;
                $this->originalAssignments[$assignment->pensum_course_id][$assignment->unit] = $assignment->professor_id;

                // Si tiene un GradeBook, lo marcamos como bloqueado
                if ($assignment->gradeBook) {
                    $this->lockedAssignments[$assignment->pensum_course_id][$assignment->unit] = true;
                }
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
        $this->lockedAssignments    = [];
        $this->originalAssignments  = [];
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

        $classroomIds = array_merge(
            [$this->managingClassroomId],
            $this->selectedClassrooms
        );

        foreach ($classroomIds as $classroomId) {
            $existingAssignments = ClassroomCourseAssignment::with('gradeBook:id,classroom_course_assignment_id')
                ->where('classroom_id', $classroomId)
                ->get()
                ->keyBy(function ($item) {
                    return $item->pensum_course_id . '_' . $item->unit;
                });

            foreach ($this->assignments as $pensumCourseId => $units) {
                foreach ($units as $unit => $professorId) {
                    $key      = $pensumCourseId . '_' . $unit;
                    $existing = $existingAssignments->get($key);

                    if (!$professorId) {
                        // Si no hay profesor y la asignación existe pero tiene GradeBook, no se puede borrar
                        if ($existing) {
                            if ($existing->gradeBook) {
                                continue; // No se puede dejar vacía una asignación con cuadro activo
                            }
                            $oldProfId = $existing->professor_id;
                            $existing->delete();
                            $this->logAudit('deleted', $existing, ['professor_id' => $oldProfId], null, 'Asignación eliminada');
                        }
                        continue;
                    }

                    if ($existing) {
                        if ($existing->professor_id != $professorId) {
                            $oldProfId = $existing->professor_id;
                            $existing->update(['professor_id' => $professorId]);

                            $description = $existing->gradeBook
                                ? 'Profesor modificado (cuadro de calificaciones transferido al nuevo profesor)'
                                : 'Asignación de profesor modificada';

                            $this->logAudit('updated', $existing, ['professor_id' => $oldProfId], ['professor_id' => $professorId], $description);
                        }
                    } else {
                        $newAssignment = ClassroomCourseAssignment::create([
                            'classroom_id'     => $classroomId,
                            'pensum_course_id' => $pensumCourseId,
                            'unit'             => $unit,
                            'professor_id'     => $professorId,
                        ]);

                        $this->logAudit('created', $newAssignment, null, ['professor_id' => $professorId], 'Nueva asignación de profesor');
                    }
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

        $this->manageAssignments($this->managingClassroomId);
    }

    public function deleteAssignment(int $pensumCourseId, int $unit): void
    {
        $this->authorize('admin.classroom-course-assignments.delete');

        $assignment = ClassroomCourseAssignment::with('gradeBook:id,classroom_course_assignment_id')
            ->where('classroom_id', $this->managingClassroomId)
            ->where('pensum_course_id', $pensumCourseId)
            ->where('unit', $unit)
            ->first();

        if ($assignment) {
            // Protección adicional de eliminación directa
            if ($assignment->gradeBook) {
                $this->dispatch('showAlert', [
                    'title'   => '¡Acción denegada!',
                    'message' => 'No se puede eliminar esta asignación porque ya tiene un cuadro de calificaciones.',
                    'type'    => 'error',
                ]);
                return;
            }

            $oldProfId = $assignment->professor_id;
            $assignment->delete();

            $this->logAudit('deleted', $assignment, ['professor_id' => $oldProfId], null, 'Asignación eliminada');
        }

        // Limpiar de los arrays locales
        unset($this->assignments[$pensumCourseId][$unit]);
        unset($this->originalAssignments[$pensumCourseId][$unit]);
        unset($this->lockedAssignments[$pensumCourseId][$unit]);

        $this->dispatch('toastMessage', [
            'type'    => 'info',
            'message' => 'Asignación eliminada.',
        ]);
    }

    private function logAudit(string $event, ClassroomCourseAssignment $assignment, ?array $oldValues, ?array $newValues, string $description): void
    {
        AuditLog::create([
            'user_id'        => Auth::id(),
            'event'          => $event,
            'auditable_type' => ClassroomCourseAssignment::class,
            'auditable_id'   => $assignment->id,
            'module'         => 'Asignaciones',
            'description'    => "{$description} (Aula: {$assignment->classroom_id}, Curso: {$assignment->pensum_course_id}, Unidad: {$assignment->unit})",
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'ip_address'     => request()->ip(),
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
