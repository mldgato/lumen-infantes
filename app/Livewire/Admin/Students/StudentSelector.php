<?php

namespace App\Livewire\Admin\Students;

use App\Models\AcademicConfiguration;
use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Grade;
use App\Models\GradeBook;
use App\Models\GradeBookActivity;
use App\Models\GradeBookScore;
use App\Models\Pensum;
use App\Models\PensumCourse;
use App\Models\Section;
use App\Models\Student;
use App\Services\GradeBookCalculationService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StudentSelector extends Component
{
    public bool $readyToLoad = false;

    public string $filterYear = '';

    public string $filterLevel = '';

    public string $filterGrade = '';

    public string $filterSection = '';

    public string $search = '';

    public array $selected = [];

    public bool $selectAll = false;

    // Modal — paso 1: selección de cursos y unidades
    public int $modalStep = 1;

    public int|string $originCourseId = '';

    public int|string $originUnit = '';

    public int|string $destinationCourseId = '';

    public int|string $destinationUnit = '';

    // Modal — paso 2: mapeo de actividades (selección parcial con destino que ya tiene actividades)
    public array $originActivities = [];

    public array $destinationActivities = [];

    public array $activityMapping = [];

    protected $queryString = [
        'filterYear' => ['except' => ''],
        'filterLevel' => ['except' => ''],
        'filterGrade' => ['except' => ''],
        'filterSection' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    // ==========================================
    // CARGA Y FILTROS
    // ==========================================

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function updatedFilterYear(): void
    {
        $this->filterLevel = '';
        $this->filterGrade = '';
        $this->filterSection = '';
        $this->resetSelection();
    }

    public function updatedFilterLevel(): void
    {
        $this->filterGrade = '';
        $this->filterSection = '';
        $this->resetSelection();
    }

    public function updatedFilterGrade(): void
    {
        $this->filterSection = '';
        $this->resetSelection();
    }

    public function updatedFilterSection(): void
    {
        $this->resetSelection();
    }

    public function updatedSearch(): void
    {
        $this->resetSelection();
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selected = $value ? $this->getVisibleStudentIds() : [];
    }

    public function updatedSelected(): void
    {
        $visibleIds = $this->getVisibleStudentIds();
        $this->selectAll = ! empty($visibleIds)
            && empty(array_diff($visibleIds, $this->selected));
    }

    private function resetSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    // ==========================================
    // MODAL — PASO 1
    // ==========================================

    public function openScoreModal(): void
    {
        $this->resetModal();
        $this->dispatch('openScoreModal');
    }

    public function updatedOriginCourseId(): void
    {
        $this->originUnit = '';
    }

    public function updatedDestinationCourseId(): void
    {
        $this->destinationUnit = '';
    }

    /**
     * Valida la selección y determina el camino:
     *   A) Selección completa o destino sin actividades → confirmación SweetAlert y ejecuta
     *   B) Selección parcial + destino con actividades → paso 2 (mapeo)
     */
    public function continuar(): void
    {
        $this->validate([
            'originCourseId' => 'required',
            'originUnit' => 'required',
            'destinationCourseId' => 'required',
            'destinationUnit' => 'required',
        ], [
            'originCourseId.required' => 'Seleccione el curso de origen.',
            'originUnit.required' => 'Seleccione la unidad de origen.',
            'destinationCourseId.required' => 'Seleccione el curso de destino.',
            'destinationUnit.required' => 'Seleccione la unidad de destino.',
        ]);

        if ($this->originCourseId === $this->destinationCourseId
            && (string) $this->originUnit === (string) $this->destinationUnit) {
            $this->addError('destinationCourseId', 'El origen y el destino no pueden ser iguales.');

            return;
        }

        $classroom = $this->getSelectedClassroom();
        if (! $classroom) {
            return;
        }

        // --- Buscar asignación y cuadro de origen ---
        $originAssignment = $this->findAssignment($classroom, (int) $this->originCourseId, (int) $this->originUnit);
        if (! $originAssignment) {
            $this->addError('originCourseId', 'No existe asignación de profesor para el curso/unidad de origen.');

            return;
        }

        $originGradeBook = $originAssignment->gradeBook()->with(['activities.scores'])->first();
        if (! $originGradeBook || $originGradeBook->activities->isEmpty()) {
            $this->addError('originCourseId', 'El cuadro de origen no tiene actividades registradas.');

            return;
        }

        // --- Buscar asignación de destino ---
        $destAssignment = $this->findAssignment($classroom, (int) $this->destinationCourseId, (int) $this->destinationUnit);
        if (! $destAssignment) {
            $this->addError('destinationCourseId', 'No existe asignación de profesor para el curso/unidad de destino.');

            return;
        }

        $destGradeBook = $destAssignment->gradeBook()->with('activities')->first();

        // --- Determinar si es selección completa ---
        $allEnrolledIds = $this->getAllEnrolledStudentIds($classroom);
        $selectedInts = array_map('intval', $this->selected);
        $isFullSelection = empty(array_diff($allEnrolledIds, $selectedInts));

        // --- Validar estado del cuadro de destino ---
        if ($destGradeBook) {
            if ($isFullSelection && ! $destGradeBook->isOpen()) {
                $this->addError('destinationCourseId',
                    'El cuadro de destino está '.$this->statusLabel($destGradeBook->status).'. '
                    .'Al seleccionar todos los alumnos solo se puede modificar un cuadro en estado "Abierto".'
                );

                return;
            }

            if (! $isFullSelection && ($destGradeBook->isApproved() || $destGradeBook->isRejected())) {
                $this->addError('destinationCourseId',
                    'El cuadro de destino está '.$this->statusLabel($destGradeBook->status).' y no puede ser modificado.'
                );

                return;
            }
        }

        // --- Determinar camino ---
        $destHasActivities = $destGradeBook && $destGradeBook->activities->isNotEmpty();

        if (! $isFullSelection && $destHasActivities) {
            // Paso 2: mapeo manual
            $this->loadMappingData($originGradeBook, $destGradeBook);
            $this->modalStep = 2;
        } else {
            // Confirmación directa via SweetAlert
            $this->dispatch('confirmScoreCopy', [
                'warnReplace' => $isFullSelection && $destHasActivities,
            ]);
        }
    }

    // ==========================================
    // MODAL — PASO 2 (MAPEO)
    // ==========================================

    public function volverPaso1(): void
    {
        $this->modalStep = 1;
        $this->activityMapping = [];
        $this->originActivities = [];
        $this->destinationActivities = [];
    }

    private function loadMappingData(GradeBook $origin, GradeBook $destination): void
    {
        $this->originActivities = $origin->activities
            ->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'max_points' => (float) $a->max_points,
                'type' => $a->activity_type_id,
                'is_extra' => $a->activityType->is_extra ?? false,
            ])
            ->values()
            ->toArray();

        $this->destinationActivities = $destination->activities
            ->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'max_points' => (float) $a->max_points,
                'type' => $a->activity_type_id,
                'is_extra' => $a->activityType->is_extra ?? false,
            ])
            ->values()
            ->toArray();

        // Pre-mapear por posición si la cantidad coincide
        $this->activityMapping = [];
        $sameCount = count($this->originActivities) === count($this->destinationActivities);

        foreach ($this->destinationActivities as $i => $dest) {
            $this->activityMapping[(string) $dest['id']] = $sameCount
                ? (string) ($this->originActivities[$i]['id'] ?? '')
                : '';
        }
    }

    // ==========================================
    // EJECUCIÓN
    // ==========================================

    /** Llamado desde SweetAlert (camino directo sin mapeo). */
    public function ejecutarCopia(): void
    {
        $this->executeOperation(useMapping: false);
    }

    /** Llamado desde el botón "Aplicar" del paso 2 (mapeo manual). */
    public function ejecutarConMapeo(): void
    {
        $hasMappings = ! empty(array_filter($this->activityMapping, fn ($v) => $v !== '' && $v !== null));
        if (! $hasMappings) {
            $this->addError('activityMapping', 'Debe asignar al menos una actividad de origen.');

            return;
        }

        $this->executeOperation(useMapping: true);
    }

    private function executeOperation(bool $useMapping): void
    {
        $classroom = $this->getSelectedClassroom();
        if (! $classroom) {
            return;
        }

        $originAssignment = $this->findAssignment($classroom, (int) $this->originCourseId, (int) $this->originUnit);
        $originGradeBook = $originAssignment->gradeBook()->with(['activities.scores', 'activities.activityType'])->first();

        $destAssignment = $this->findAssignment($classroom, (int) $this->destinationCourseId, (int) $this->destinationUnit);
        $destGradeBook = $destAssignment->gradeBook()->with('activities')->first();

        $allEnrolledIds = $this->getAllEnrolledStudentIds($classroom);
        $selectedInts = array_map('intval', $this->selected);
        $isFullSelection = empty(array_diff($allEnrolledIds, $selectedInts));

        $academicConfig = AcademicConfiguration::where('year', $classroom->year)->first();
        if (! $academicConfig) {
            $this->dispatch('showAlert', [
                'title' => 'Error',
                'message' => 'No existe configuración académica para el año '.$classroom->year.'.',
                'type' => 'error',
            ]);

            return;
        }

        DB::transaction(function () use (
            $originGradeBook,
            $destAssignment,
            $destGradeBook,
            $academicConfig,
            $allEnrolledIds,
            $selectedInts,
            $useMapping
        ) {
            // Obtener o crear el cuadro de destino
            $targetGradeBook = $destGradeBook ?? GradeBook::create([
                'classroom_course_assignment_id' => $destAssignment->id,
                'academic_configuration_id' => $academicConfig->id,
                'status' => 'open',
            ]);

            if (! $useMapping) {
                // ── Camino directo: reemplazar actividades completas ──────────
                $targetGradeBook->activities()->delete(); // cascadea scores

                $activityMap = []; // [origin_activity_id => new_dest_activity_id]
                foreach ($originGradeBook->activities as $originAct) {
                    $newAct = GradeBookActivity::create([
                        'grade_book_id' => $targetGradeBook->id,
                        'activity_type_id' => $originAct->activity_type_id,
                        'name' => $originAct->name,
                        'max_points' => $originAct->max_points,
                        'ordering' => $originAct->ordering,
                    ]);
                    $activityMap[$originAct->id] = $newAct->id;
                }

                foreach ($allEnrolledIds as $studentId) {
                    $isSelected = in_array($studentId, $selectedInts);

                    foreach ($originGradeBook->activities as $originAct) {
                        $newActId = $activityMap[$originAct->id];
                        $originScore = $originAct->scores->firstWhere('student_id', $studentId);

                        GradeBookScore::updateOrCreate(
                            ['grade_book_activity_id' => $newActId, 'student_id' => $studentId],
                            [
                                'score' => $isSelected ? ($originScore?->score ?? 0) : 0,
                                'improvement_score' => $isSelected ? ($originScore?->improvement_score ?? 0) : 0,
                            ]
                        );
                    }
                }

                GradeBookCalculationService::recalculateAll($targetGradeBook, $allEnrolledIds);

            } else {
                // ── Camino con mapeo: solo actualizar alumnos seleccionados ──
                $targetGradeBook->load(['activities.scores', 'activities.activityType']);

                foreach ($this->activityMapping as $destActIdStr => $originActIdStr) {
                    if ($originActIdStr === '' || $originActIdStr === null) {
                        continue;
                    }

                    $destActivityId = (int) $destActIdStr;
                    $originActivityId = (int) $originActIdStr;

                    $originAct = $originGradeBook->activities->find($originActivityId);
                    if (! $originAct) {
                        continue;
                    }

                    foreach ($selectedInts as $studentId) {
                        $originScore = $originAct->scores->firstWhere('student_id', $studentId);

                        GradeBookScore::updateOrCreate(
                            ['grade_book_activity_id' => $destActivityId, 'student_id' => $studentId],
                            [
                                'score' => $originScore?->score ?? 0,
                                'improvement_score' => $originScore?->improvement_score ?? 0,
                            ]
                        );
                    }
                }

                GradeBookCalculationService::recalculateForStudents($targetGradeBook, $selectedInts);
            }
        });

        $this->resetModal();
        $this->dispatch('closeModalMessaje', [
            'title' => '¡Actualización completada!',
            'message' => 'Las calificaciones fueron copiadas exitosamente.',
            'type' => 'success',
            'modalId' => 'ScoreUpdateModal',
        ]);
    }

    // ==========================================
    // HELPERS PRIVADOS
    // ==========================================

    private function resetModal(): void
    {
        $this->modalStep = 1;
        $this->originCourseId = '';
        $this->originUnit = '';
        $this->destinationCourseId = '';
        $this->destinationUnit = '';
        $this->activityMapping = [];
        $this->originActivities = [];
        $this->destinationActivities = [];
        $this->resetValidation();
    }

    private function getSelectedClassroom(): ?Classroom
    {
        if (! $this->filterYear || ! $this->filterLevel || ! $this->filterGrade || ! $this->filterSection) {
            return null;
        }

        return Classroom::where('year', $this->filterYear)
            ->where('level_id', $this->filterLevel)
            ->where('grade_id', $this->filterGrade)
            ->where('section_id', $this->filterSection)
            ->first();
    }

    private function findAssignment(Classroom $classroom, int $pcId, int $unit): ?ClassroomCourseAssignment
    {
        return ClassroomCourseAssignment::where('classroom_id', $classroom->id)
            ->where('pensum_course_id', $pcId)
            ->where('unit', $unit)
            ->first();
    }

    private function getAllEnrolledStudentIds(Classroom $classroom): array
    {
        return Student::whereHas('enrollments', fn ($q) => $q
            ->where('classroom_id', $classroom->id)
            ->where('status', 'Activo')
        )->pluck('id')->map(fn ($id) => (int) $id)->toArray();
    }

    private function getVisibleStudentIds(): array
    {
        $classroom = $this->getSelectedClassroom();
        if (! $classroom) {
            return [];
        }

        return Student::whereHas('enrollments', fn ($q) => $q
            ->where('classroom_id', $classroom->id)
            ->where('status', 'Activo')
        )
            ->join('users', 'students.user_id', '=', 'users.id')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('users.name', 'like', '%'.$this->search.'%')
                ->orWhere('users.surname', 'like', '%'.$this->search.'%')
                ->orWhere('users.first_name', 'like', '%'.$this->search.'%')
                ->orWhere('students.carne', 'like', '%'.$this->search.'%')
                ->orWhere('students.personal_code', 'like', '%'.$this->search.'%')
            ))
            ->pluck('students.id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    private function getPensum(Classroom $classroom): ?Pensum
    {
        return Pensum::where('grade_id', $classroom->grade_id)
            ->where('year', $classroom->year)
            ->first();
    }

    private function buildModalCourses(Classroom $classroom): array
    {
        $pensum = $this->getPensum($classroom);
        if (! $pensum) {
            return ['standalone' => [], 'groups' => []];
        }

        $totalUnits = $pensum->units;

        // Cargar solo los CCAs que existen para esta aula
        $assignments = ClassroomCourseAssignment::with([
            'pensumCourse.course',
            'pensumCourse.parent.course',
        ])
            ->where('classroom_id', $classroom->id)
            ->orderBy('pensum_course_id')
            ->orderBy('unit')
            ->get();

        if ($assignments->isEmpty()) {
            return ['standalone' => [], 'groups' => []];
        }

        $standalone = [];
        $groupsMap = []; // [parent_pensum_course_id => ['label', 'courses']]
        $processedPcIds = [];

        foreach ($assignments as $assignment) {
            $pc = $assignment->pensumCourse;
            $pcId = $pc->id;

            if (in_array($pcId, $processedPcIds)) {
                continue;
            }
            $processedPcIds[] = $pcId;

            $pcUnits = $pc->units ?? [];

            if ($pc->parent_id === null && ! $pc->is_main) {
                // Completo o Parcial
                $standalone[] = [
                    'id' => $pcId,
                    'label' => $pc->course->course_name,
                    'badge' => count($pcUnits) >= $totalUnits ? 'Completo' : 'Parcial',
                ];
            } elseif ($pc->parent_id === null && $pc->is_main) {
                // Curso principal — inicializa grupo
                if (! isset($groupsMap[$pcId])) {
                    $groupsMap[$pcId] = ['label' => $pc->course->course_name, 'courses' => []];
                }
                // Si tiene unidades propias, aparece también como entrada del grupo
                if (! empty($pcUnits)) {
                    array_unshift($groupsMap[$pcId]['courses'], [
                        'id' => $pcId,
                        'label' => $pc->course->course_name.' (General)',
                        'badge' => count($pcUnits) >= $totalUnits ? 'Completo' : 'Parcial',
                    ]);
                }
            } else {
                // Sub curso
                $parentId = $pc->parent_id;
                if (! isset($groupsMap[$parentId])) {
                    $parentPc = PensumCourse::with('course')->find($parentId);
                    $groupsMap[$parentId] = [
                        'label' => $parentPc ? $parentPc->course->course_name : 'Grupo',
                        'courses' => [],
                    ];
                }
                $groupsMap[$parentId]['courses'][] = [
                    'id' => $pcId,
                    'label' => $pc->course->course_name,
                    'badge' => 'Sub',
                ];
            }
        }

        $groups = array_values(array_filter($groupsMap, fn ($g) => ! empty($g['courses'])));

        return ['standalone' => $standalone, 'groups' => $groups];
    }

    private function getUnitsForCourse(int|string $pcId, Classroom $classroom): array
    {
        if (! $pcId) {
            return [];
        }

        return ClassroomCourseAssignment::where('classroom_id', $classroom->id)
            ->where('pensum_course_id', (int) $pcId)
            ->orderBy('unit')
            ->pluck('unit')
            ->toArray();
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'open' => 'Abierto',
            'locked' => 'Bloqueado',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
            default => $status,
        };
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render()
    {
        $years = Classroom::select('year')->distinct()->orderByDesc('year')->pluck('year');

        $levels = auth()->user()->levels()->orderBy('ordering')->get();

        $grades = $this->filterLevel
            ? Grade::whereHas('classrooms', fn ($q) => $q
                ->where('level_id', $this->filterLevel)
                ->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear))
            )->orderBy('ordering')->get()
            : collect();

        $sections = $this->filterGrade
            ? Section::whereHas('classrooms', fn ($q) => $q
                ->where('grade_id', $this->filterGrade)
                ->where('level_id', $this->filterLevel)
                ->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear))
            )->orderBy('section_name')->get()
            : collect();

        $classroom = $this->getSelectedClassroom();
        $students = collect();

        if ($classroom && $this->readyToLoad) {
            $students = Student::whereHas('enrollments', fn ($q) => $q
                ->where('classroom_id', $classroom->id)
                ->where('status', 'Activo')
            )
                ->with('user')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                    ->where('users.name', 'like', '%'.$this->search.'%')
                    ->orWhere('users.surname', 'like', '%'.$this->search.'%')
                    ->orWhere('users.first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('students.carne', 'like', '%'.$this->search.'%')
                    ->orWhere('students.personal_code', 'like', '%'.$this->search.'%')
                ))
                ->orderBy('users.surname')
                ->orderBy('users.second_surname')
                ->orderBy('users.first_name')
                ->orderBy('users.middle_name')
                ->select('students.*')
                ->get();
        }

        $modalCourses = $classroom ? $this->buildModalCourses($classroom) : ['standalone' => [], 'groups' => []];
        $originUnits = ($classroom && $this->originCourseId) ? $this->getUnitsForCourse($this->originCourseId, $classroom) : [];
        $destinationUnits = ($classroom && $this->destinationCourseId) ? $this->getUnitsForCourse($this->destinationCourseId, $classroom) : [];

        return view('livewire.admin.students.student-selector', compact(
            'years',
            'levels',
            'grades',
            'sections',
            'classroom',
            'students',
            'modalCourses',
            'originUnits',
            'destinationUnits',
        ));
    }
}
