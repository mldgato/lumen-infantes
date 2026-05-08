<?php

namespace App\Livewire\Profesor;

use App\Exports\ActivityTemplateExport;
use App\Imports\ActivityScoresImport;
use App\Models\AcademicConfiguration;
use App\Models\ActivityType;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBook;
use App\Models\GradeBookActivity;
use App\Models\GradeBookScore;
use App\Models\GradeBookTotal;
use App\Models\Student;
use App\Models\User;
use App\Notifications\GradeBookLocked;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class GradeBooks extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool $readyToLoad = false;

    public string $search = '';

    // Cuadro activo
    public ?GradeBook $gradeBook = null;

    public ?ClassroomCourseAssignment $assignment = null;

    // Formulario de actividad
    public bool $showActivityForm = false;

    public ?int $editingActivityId = null;

    public int|string $activity_type_id = '';

    public string $activityName = '';

    public int|string $max_points = '';

    public int|string $ordering = 0;

    // Calificaciones
    public bool $showScoresForm = false;

    public ?int $scoringActivityId = null;

    public array $scores = [];

    public array $improvement_scores = [];

    public string $configMode = 'free';

    // ==========================================
    // CLONAR CUADRO
    // ==========================================
    public bool $showCloneModal = false;

    public array $cloneTargets = [];

    public array $selectedCloneTargets = [];

    // ==========================================
    // EXCEL TEMPLATES (Nuevas Propiedades)
    // ==========================================
    public bool $showExcelModal = false;

    public ?int $excelActivityId = null;

    public ?string $excelActivityName = '';

    public $excelFile;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function loadGradeBooks(): void
    {
        $this->readyToLoad = true;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getProfessorId(): int
    {
        return Auth::user()->professor->id;
    }

    public function openGradeBook(int $assignmentId): void
    {
        $this->assignment = ClassroomCourseAssignment::with([
            'classroom.grade',
            'classroom.section',
            'classroom.level',
            'pensumCourse.course',
        ])->findOrFail($assignmentId);

        $academicConfig = AcademicConfiguration::where('year', $this->assignment->classroom->year)->first();

        if (! $academicConfig) {
            $this->dispatch('showAlert', [
                'title' => 'Sin configuración',
                'message' => 'No existe una configuración académica para el año '.$this->assignment->classroom->year.'.',
                'type' => 'warning',
            ]);

            return;
        }

        $this->gradeBook = GradeBook::with([
            'activities.activityType',
            'activities.scores',
        ])->firstOrCreate(
            ['classroom_course_assignment_id' => $assignmentId],
            [
                'academic_configuration_id' => $academicConfig->id,
                'status' => 'open',
            ]
        );

        $this->configMode = $academicConfig->mode;

        $this->initializeTotals();
        $this->recalculateAllTotals(); // <-- agrega esta línea

        $this->resetActivityForm();
        $this->showScoresForm = false;
        $this->scoringActivityId = null;
    }

    protected function initializeTotals(): void
    {
        $students = $this->getStudents();

        foreach ($students as $student) {
            GradeBookTotal::firstOrCreate(
                [
                    'grade_book_id' => $this->gradeBook->id,
                    'student_id' => $student->id,
                ],
                [
                    'normal_points' => 0,
                    'extra_points' => 0,
                    'total_points' => 0,
                ]
            );
        }
    }

    public function getStudents()
    {
        return Student::whereHas('enrollments', function ($q) {
            $q->where('classroom_id', $this->assignment->classroom_id)
                ->where('status', 'Activo'); // <-- Garantizamos solo activos
        })
            ->join('users', 'students.user_id', '=', 'users.id')
            ->select('students.*')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->with('user')
            ->get();
    }

    public function closeGradeBook(): void
    {
        $this->gradeBook = null;
        $this->assignment = null;
        $this->resetActivityForm();
        $this->showScoresForm = false;
        $this->scoringActivityId = null;
        $this->scores = [];
    }

    // ==========================================
    // ACTIVIDADES
    // ==========================================

    public function resetActivityForm(): void
    {
        $this->showActivityForm = false;
        $this->editingActivityId = null;
        $this->activity_type_id = '';
        $this->activityName = '';
        $this->max_points = '';
        $this->ordering = $this->gradeBook
            ? $this->gradeBook->activities->count() + 1
            : 0;
        $this->resetValidation();
    }

    public function openActivityForm(): void
    {
        $this->resetActivityForm();
        $this->showActivityForm = true;
    }

    public function editActivity(int $id): void
    {
        $activity = GradeBookActivity::findOrFail($id);

        $this->editingActivityId = $activity->id;
        $this->activity_type_id = $activity->activity_type_id;
        $this->activityName = $activity->name;
        $this->max_points = $activity->max_points;
        $this->ordering = $activity->ordering;
        $this->showActivityForm = true;
    }

    public function saveActivity(): void
    {
        $this->validate([
            'activity_type_id' => 'required|exists:activity_types,id',
            'activityName' => 'required|string|max:255',
            'max_points' => 'required|numeric|min:0.01',
            'ordering' => 'required|integer|min:0',
        ], [
            'activity_type_id.required' => 'El tipo de actividad es obligatorio.',
            'activityName.required' => 'El nombre de la actividad es obligatorio.',
            'max_points.required' => 'Los puntos máximos son obligatorios.',
            'max_points.min' => 'Los puntos deben ser mayor a 0.',
            'ordering.required' => 'El orden es obligatorio.',
        ]);

        // Validación del límite de 100 puntos para actividades normales
        $activityType = ActivityType::find($this->activity_type_id);

        if ($activityType && ! $activityType->is_extra) {
            $currentNormalPoints = GradeBookActivity::where('grade_book_id', $this->gradeBook->id)
                ->whereHas('activityType', function ($query) {
                    $query->where('is_extra', false);
                })
                ->when($this->editingActivityId, function ($query) {
                    $query->where('id', '!=', $this->editingActivityId);
                })
                ->sum('max_points');

            $projectedTotal = $currentNormalPoints + (float) $this->max_points;

            if ($projectedTotal > 100) {
                $this->addError('max_points', 'Excede los 100 puntos permitidos.');
                $this->dispatch('showAlert', [
                    'title' => 'Límite excedido',
                    'message' => "Las actividades normales no pueden sumar más de 100 puntos. Llevas {$currentNormalPoints} pts e intentas agregar {$this->max_points} pts.",
                    'type' => 'error',
                ]);

                return;
            }
        }

        // Validar límite de cantidad en modo asignado
        if ($this->configMode === 'assigned') {
            $configActivity = $this->gradeBook->academicConfiguration
                ->activities
                ->firstWhere('activity_type_id', $this->activity_type_id);

            if ($configActivity) {
                $existingCount = GradeBookActivity::where('grade_book_id', $this->gradeBook->id)
                    ->where('activity_type_id', $this->activity_type_id)
                    ->when($this->editingActivityId, fn ($q) => $q->where('id', '!=', $this->editingActivityId))
                    ->count();

                if ($existingCount >= $configActivity->quantity) {
                    $this->addError('activity_type_id', "Ya alcanzaste el límite de {$configActivity->quantity} actividad(es) de este tipo.");

                    return;
                }
            }
        }

        if ($this->editingActivityId) {
            GradeBookActivity::findOrFail($this->editingActivityId)->update([
                'activity_type_id' => $this->activity_type_id,
                'name' => $this->activityName,
                'max_points' => $this->max_points,
                'ordering' => $this->ordering,
            ]);
        } else {
            GradeBookActivity::create([
                'grade_book_id' => $this->gradeBook->id,
                'activity_type_id' => $this->activity_type_id,
                'name' => $this->activityName,
                'max_points' => $this->max_points,
                'ordering' => $this->ordering,
            ]);
        }

        // Recargar cuadro
        $this->reloadGradeBook();
        $this->recalculateAllTotals();
        $this->resetActivityForm();

        $this->dispatch('toastMessage', [
            'type' => 'success',
            'message' => $this->editingActivityId ? 'Actividad actualizada.' : 'Actividad agregada.',
        ]);
    }

    public function deleteActivity(int $id): void
    {
        GradeBookActivity::findOrFail($id)->delete();
        $this->reloadGradeBook();
        $this->recalculateAllTotals();

        $this->dispatch('toastMessage', [
            'type' => 'info',
            'message' => 'Actividad eliminada.',
        ]);
    }

    // ==========================================
    // CALIFICACIONES
    // ==========================================

    public function openScores(int $activityId): void
    {
        $this->scoringActivityId = $activityId;
        $this->showScoresForm = true;
        $this->showActivityForm = false;

        $activity = GradeBookActivity::with('scores')->findOrFail($activityId);
        $students = $this->getStudents();

        $this->scores = [];
        $this->improvement_scores = [];

        foreach ($students as $student) {
            $score = $activity->scores->firstWhere('student_id', $student->id);
            $this->scores[$student->id] = $score ? (float) $score->score : 0;
            $this->improvement_scores[$student->id] = $score && ! is_null($score->improvement_score)
                ? (float) $score->improvement_score
                : null;
        }
    }

    public function saveScores(): void
    {
        $activity = GradeBookActivity::findOrFail($this->scoringActivityId);
        $config = $this->gradeBook->academicConfiguration;
        $hasImprovement = $config->improvement_type !== 'none';

        $rules = [];
        $messages = [];

        // 1. Construir las reglas de validación para todos los estudiantes a la vez
        foreach ($this->scores as $studentId => $score) {
            $rules["scores.{$studentId}"] = "required|numeric|min:0|max:{$activity->max_points}";
            $messages["scores.{$studentId}.required"] = 'La nota es obligatoria.';
            $messages["scores.{$studentId}.max"] = "La nota no puede superar {$activity->max_points} puntos.";
            $messages["scores.{$studentId}.min"] = 'La nota no puede ser negativa.';

            if ($hasImprovement) {
                $inputImprovement = $this->improvement_scores[$studentId] ?? null;

                if (! is_null($inputImprovement) && $inputImprovement !== '' && $inputImprovement > 0) {
                    $maxImprovement = $config->maxImprovementScore((float) $score, (float) $activity->max_points);

                    $rules["improvement_scores.{$studentId}"] = "nullable|numeric|min:0|max:{$maxImprovement}";
                    $messages["improvement_scores.{$studentId}.max"] = "La mejora no puede superar {$maxImprovement} puntos para este estudiante.";
                    $messages["improvement_scores.{$studentId}.min"] = 'La mejora no puede ser negativa.';
                }
            }
        }

        // 2. Ejecutar la validación global en un bloque Try-Catch
        try {
            $this->validate($rules, $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si la validación falla, disparamos el SweetAlert visible
            $this->dispatch('showAlert', [
                'title' => 'Error en las notas',
                'message' => 'Hay calificaciones o mejoras que superan el máximo permitido o tienen formato incorrecto. Revisa los campos en rojo.',
                'type' => 'error',
            ]);

            // Volvemos a lanzar la excepción para que Livewire pinte los inputs de rojo
            throw $e;
        }

        // 3. Si todo está correcto, guardamos en base de datos
        foreach ($this->scores as $studentId => $score) {
            $improvementScore = null;

            if ($hasImprovement) {
                $inputImprovement = $this->improvement_scores[$studentId] ?? null;
                if (! is_null($inputImprovement) && $inputImprovement !== '' && $inputImprovement > 0) {
                    $improvementScore = $inputImprovement;
                }
            }

            GradeBookScore::updateOrCreate(
                [
                    'grade_book_activity_id' => $this->scoringActivityId,
                    'student_id' => $studentId,
                ],
                [
                    'score' => $score,
                    'improvement_score' => $improvementScore,
                ]
            );
        }

        $this->recalculateAllTotals();

        $this->showScoresForm = false;
        $this->scoringActivityId = null;
        $this->scores = [];
        $this->improvement_scores = [];

        $this->dispatch('toastMessage', [
            'type' => 'success',
            'message' => 'Calificaciones guardadas correctamente.',
        ]);
    }

    public function closeScores(): void
    {
        $this->showScoresForm = false;
        $this->scoringActivityId = null;
        $this->scores = [];
        $this->improvement_scores = [];
    }

    // ==========================================
    // TOTALES
    // ==========================================

    protected function recalculateAllTotals(): void
    {
        $students = $this->getStudents();
        $activities = GradeBookActivity::with(['scores', 'activityType'])
            ->where('grade_book_id', $this->gradeBook->id)
            ->get();

        $config = $this->gradeBook->academicConfiguration;

        foreach ($students as $student) {
            $normalPoints = 0;
            $extraPoints = 0;

            foreach ($activities as $activity) {
                $score = $activity->scores->firstWhere('student_id', $student->id);

                $rawScore = $score ? (float) $score->score : 0;
                $improvement = $score ? $score->improvement_score : null;

                $effective = $config->effectiveScore($rawScore, $improvement, (float) $activity->max_points);

                if ($activity->activityType->is_extra) {
                    $extraPoints += $effective;
                } else {
                    $normalPoints += $effective;
                }
            }

            GradeBookTotal::updateOrCreate(
                [
                    'grade_book_id' => $this->gradeBook->id,
                    'student_id' => $student->id,
                ],
                [
                    'normal_points' => $normalPoints,
                    'extra_points' => $extraPoints,
                    'total_points' => ceil($normalPoints + $extraPoints), // <-- ceil aquí
                ]
            );
        }
    }

    // ==========================================
    // ESTADO DEL CUADRO
    // ==========================================

    public function lockGradeBook(): void
    {
        $this->reloadGradeBook();

        $normalMax = $this->gradeBook->activities
            ->filter(fn ($a) => ! $a->activityType->is_extra)
            ->sum('max_points');

        if ($normalMax < 100) {
            $this->dispatch('showAlert', [
                'title' => 'No se puede bloquear',
                'message' => "Las actividades normales suman {$normalMax} puntos. Deben sumar exactamente 100.",
                'type' => 'warning',
            ]);

            return;
        }

        $oldStatus = $this->gradeBook->status;
        $this->gradeBook->update(['status' => 'locked']);

        $this->gradeBook->load(
            'assignment.professor.user',
            'assignment.pensumCourse.course',
            'assignment.classroom.grade',
            'assignment.classroom.section'
        );

        AuditService::gradeBookStatusChanged($this->gradeBook, $oldStatus, 'locked');

        User::role(['Super Administrador', 'Director'])->get()
            ->each(fn ($admin) => $admin->notify(new GradeBookLocked($this->gradeBook)));

        $this->reloadGradeBook();

        $this->dispatch('showAlert', [
            'title' => 'Cuadro bloqueado',
            'message' => 'El cuadro ha sido bloqueado exitosamente.',
            'type' => 'success',
        ]);
    }

    public function reopenGradeBook(): void
    {
        $oldStatus = $this->gradeBook->status;

        $this->gradeBook->update([
            'status' => 'open',
            'rejection_reason' => null,
        ]);

        AuditService::gradeBookStatusChanged(
            $this->gradeBook->load(
                'assignment.pensumCourse.course',
                'assignment.classroom.grade',
                'assignment.classroom.section'
            ),
            $oldStatus,
            'open'
        );

        $this->reloadGradeBook();

        $this->dispatch('showAlert', [
            'title' => 'Cuadro reabierto',
            'message' => 'El cuadro está nuevamente abierto para edición.',
            'type' => 'success',
        ]);
    }

    protected function reloadGradeBook(): void
    {
        $this->gradeBook = GradeBook::with([
            'activities.activityType',
            'activities.scores',
        ])->findOrFail($this->gradeBook->id);
    }

    public function updatedActivityTypeId($value): void
    {
        if ($this->configMode === 'assigned' && $value) {
            $configActivity = $this->gradeBook->academicConfiguration
                ->activities
                ->firstWhere('activity_type_id', $value);

            if ($configActivity) {
                $this->max_points = $configActivity->points_each;
            }
        }
    }

    // ==========================================
    // CLONAR CUADRO
    // ==========================================

    public function openCloneModal(): void
    {
        $this->selectedCloneTargets = [];

        $compatibleAssignments = ClassroomCourseAssignment::with([
            'classroom.grade',
            'classroom.section',
            'classroom.level',
            'pensumCourse.course',
            'gradeBook.activities',
        ])
            ->where('professor_id', $this->assignment->professor_id)
            ->where('id', '!=', $this->assignment->id)
            ->whereHas('classroom', fn ($q) => $q->where('year', $this->assignment->classroom->year))
            ->get();

        if ($compatibleAssignments->isEmpty()) {
            $this->dispatch('showAlert', [
                'title' => 'Sin destinos disponibles',
                'message' => 'No tienes otras asignaciones para el mismo curso y unidad en este año.',
                'type' => 'info',
            ]);

            return;
        }

        $this->cloneTargets = $compatibleAssignments->map(function ($target) {
            $hasActivities = $target->gradeBook && $target->gradeBook->activities->isNotEmpty();

            return [
                'assignment_id' => $target->id,
                'label' => $target->classroom->level->level_name.' — '.
                    $target->classroom->grade->grade_name.' '.
                    $target->classroom->section->section_name.' — '.
                    $target->pensumCourse->course->course_name.
                    ' (U'.$target->unit.')',
                'has_activities' => $hasActivities,
                'grade_book_status' => $target->gradeBook?->status,
                'can_clone' => ! $hasActivities,
            ];
        })->values()->toArray();

        $this->showCloneModal = true;
    }

    public function closeCloneModal(): void
    {
        $this->showCloneModal = false;
        $this->cloneTargets = [];
        $this->selectedCloneTargets = [];
    }

    public function cloneActivities(): void
    {
        if (empty($this->selectedCloneTargets)) {
            $this->addError('selectedCloneTargets', 'Debes seleccionar al menos un destino.');

            return;
        }

        $sourceActivities = $this->gradeBook->activities;
        $clonedCount = 0;

        DB::transaction(function () use ($sourceActivities, &$clonedCount) {
            foreach ($this->selectedCloneTargets as $targetAssignmentId) {
                $targetAssignment = ClassroomCourseAssignment::with('classroom')->findOrFail($targetAssignmentId);

                $academicConfig = AcademicConfiguration::where('year', $targetAssignment->classroom->year)->first();

                if (! $academicConfig) {
                    continue;
                }

                $targetGradeBook = GradeBook::firstOrCreate(
                    ['classroom_course_assignment_id' => $targetAssignmentId],
                    [
                        'academic_configuration_id' => $academicConfig->id,
                        'status' => 'open',
                    ]
                );

                // Seguridad: omitir si ya tiene actividades
                if ($targetGradeBook->activities()->exists()) {
                    continue;
                }

                foreach ($sourceActivities as $activity) {
                    GradeBookActivity::create([
                        'grade_book_id' => $targetGradeBook->id,
                        'activity_type_id' => $activity->activity_type_id,
                        'name' => $activity->name,
                        'max_points' => $activity->max_points,
                        'ordering' => $activity->ordering,
                    ]);
                }

                $targetStudents = Student::whereHas('enrollments', function ($q) use ($targetAssignment) {
                    $q->where('classroom_id', $targetAssignment->classroom_id)
                        ->where('status', 'Activo');
                })->get();

                foreach ($targetStudents as $student) {
                    GradeBookTotal::firstOrCreate(
                        [
                            'grade_book_id' => $targetGradeBook->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'normal_points' => 0,
                            'extra_points' => 0,
                            'total_points' => 0,
                        ]
                    );
                }

                $clonedCount++;
            }
        });

        $this->closeCloneModal();

        $this->dispatch('toastMessage', [
            'type' => 'success',
            'message' => "Actividades copiadas a {$clonedCount} cuadro(s) exitosamente.",
        ]);
    }

    // ==========================================
    // MÉTODOS PARA EXCEL (Nuevos Métodos Completos)
    // ==========================================
    public function openExcelModal(int $activityId): void
    {
        $activity = GradeBookActivity::findOrFail($activityId);

        $this->excelActivityId = $activity->id;
        $this->excelActivityName = $activity->name;
        $this->showExcelModal = true;
    }

    public function closeExcelModal(): void
    {
        $this->showExcelModal = false;
        $this->excelActivityId = null;
        $this->excelActivityName = '';
        $this->excelFile = null;
        $this->resetValidation('excelFile');
    }

    public function importExcel(): void
    {
        // 1. Validar que sea un archivo válido
        $this->validate([
            'excelFile' => 'required|mimes:xlsx,xls|max:5120', // Máximo 5MB
        ], [
            'excelFile.required' => 'Debes seleccionar un archivo Excel.',
            'excelFile.mimes' => 'El archivo debe ser de tipo xlsx o xls.',
            'excelFile.max' => 'El archivo es demasiado grande (máximo 5MB).',
        ]);

        $activity = GradeBookActivity::findOrFail($this->excelActivityId);
        $maxPoints = (float) $activity->max_points;

        // Obtenemos los estudiantes de la sección y los indexamos por su ID para validación rápida
        $students = $this->getStudents()->keyBy('id');

        try {
            // 2. Convertir Excel a Array
            $data = Excel::toArray(new ActivityScoresImport, $this->excelFile->getRealPath())[0];

            // ==========================================
            // VALIDADOR DE SEGURIDAD DE LA ACTIVIDAD
            // ==========================================
            $headerRow = $data[0][0] ?? ''; // Leemos la celda A1
            $expectedTag = '[ACT_ID:'.$activity->id.']';

            if (! str_contains($headerRow, $expectedTag)) {
                throw new \Exception('El archivo que intentas subir no pertenece a esta actividad. Por favor, verifica que sea la plantilla correcta.');
            }

            // 3. Iniciar Transacción
            DB::beginTransaction();

            // Empezamos desde el índice 3 (que corresponde a la Fila 4 en el Excel real)
            for ($i = 3; $i < count($data); $i++) {
                $row = $data[$i];
                $studentId = $row[0]; // Columna A: ID Sistema

                // Si la fila está vacía o el ID no es numérico, ignoramos la fila
                if (empty($studentId) || ! is_numeric($studentId)) {
                    continue;
                }

                // Seguridad: Validar que el estudiante realmente pertenece a esta asignación
                if (! $students->has($studentId)) {
                    continue;
                }

                $scoreValue = $row[3]; // Columna D: Nota

                // Lógica requerida: celda en blanco = 0
                $score = (is_null($scoreValue) || trim($scoreValue) === '') ? 0 : (float) $scoreValue;

                // Validación estricta de límites
                if ($score < 0 || $score > $maxPoints) {
                    throw new \Exception("La nota {$score} del estudiante {$row[2]} es inválida. Debe estar entre 0 y {$maxPoints} puntos.");
                }

                // 4. Guardar o actualizar la nota
                GradeBookScore::updateOrCreate(
                    [
                        'grade_book_activity_id' => $activity->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'score' => $score,
                        // No tocamos improvement_score para no borrar recuperaciones previas
                    ]
                );
            }

            // 5. Recalcular los cuadros tras la carga masiva y guardar en DB
            $this->recalculateAllTotals();
            DB::commit();

            $this->closeExcelModal();
            $this->dispatch('toastMessage', [
                'type' => 'success',
                'message' => 'Calificaciones importadas y actualizadas correctamente.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Si algo falla, revertimos toda la base de datos
            $this->addError('excelFile', 'Error en el archivo: '.$e->getMessage());
        }
    }

    public function downloadExcelTemplate()
    {
        if (! $this->excelActivityId) {
            return;
        }

        $activity = GradeBookActivity::findOrFail($this->excelActivityId);
        $students = $this->getStudents(); // Reutilizamos tu método exacto para mantener el orden

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $activity->name);
        $fileName = "Plantilla_{$safeName}_".date('dmY_His').'.xlsx';

        $this->closeExcelModal();

        $this->dispatch('toastMessage', [
            'type' => 'success',
            'message' => 'Generando archivo Excel...',
        ]);

        return Excel::download(
            new ActivityTemplateExport($activity, $students),
            $fileName
        );
    }

    public function render()
    {
        $professor = Auth::user()->professor;

        if ($this->readyToLoad) {
            $assignments = ClassroomCourseAssignment::with([
                'classroom.level',
                'classroom.grade',
                'classroom.section',
                'pensumCourse.course',
                'gradeBook',
            ])
                ->where('professor_id', $professor->id)
                ->whereHas('classroom', fn ($q) => $q->where('year', date('Y')))
                ->where(function ($q) {
                    $q->whereHas('classroom.level', fn ($q) => $q->where('level_name', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('classroom.grade', fn ($q) => $q->where('grade_name', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('classroom.section', fn ($q) => $q->where('section_name', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('pensumCourse.course', fn ($q) => $q->where('course_name', 'like', '%'.$this->search.'%'));
                })
                ->orderBy('classroom_id')
                ->orderBy('pensum_course_id')
                ->orderBy('unit')
                ->get()
                ->groupBy(fn ($a) => $a->classroom_id.'-'.$a->pensum_course_id);
        } else {
            $assignments = collect();
        }

        $activityTypes = ActivityType::orderBy('name')->get();

        $configActivities = $this->gradeBook
            ? $this->gradeBook->academicConfiguration->activities->load('activityType')
            : collect();

        $activityTypes = $this->gradeBook && $this->gradeBook->academicConfiguration->mode === 'assigned'
            ? $configActivities->map(fn ($ca) => $ca->activityType)
            : ActivityType::orderBy('name')->get();

        $students = $this->assignment ? $this->getStudents() : collect();

        $scoringActivity = $this->scoringActivityId
            ? GradeBookActivity::with('activityType')->find($this->scoringActivityId)
            : null;

        return view('livewire.profesor.grade-books', compact(
            'assignments',
            'activityTypes',
            'configActivities',
            'students',
            'scoringActivity',
        ));
    }
}
