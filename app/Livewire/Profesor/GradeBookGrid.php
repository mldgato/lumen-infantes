<?php

namespace App\Livewire\Profesor;

use App\Models\AcademicConfiguration;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBook;
use App\Models\GradeBookActivity;
use App\Models\GradeBookScore;
use App\Models\GradeBookTotal;
use App\Models\Student;
use App\Models\User;
use App\Notifications\GradeBookLocked;
use App\Services\AuditService;
use App\Services\GradeBookCalculationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class GradeBookGrid extends Component
{
    public GradeBook $gradeBook;

    public bool $showCloneModal = false;

    public array $cloneTargets = [];

    public array $selectedCloneTargets = [];

    public function mount(GradeBook $gradeBook): void
    {
        $professor = Auth::user()->professor;

        if (! $professor) {
            abort(403);
        }

        $this->gradeBook = $gradeBook->load([
            'assignment.classroom.grade',
            'assignment.classroom.section',
            'assignment.classroom.level',
            'assignment.pensumCourse.course',
            'activities.activityType',
            'academicConfiguration',
        ]);

        if ($this->gradeBook->assignment->professor_id !== $professor->id) {
            abort(403);
        }
    }

    public function saveGrid(array $grid): void
    {
        if ($this->gradeBook->status !== 'open') {
            $this->dispatch('showAlert', [
                'title' => 'Cuadro no editable',
                'message' => 'Solo se pueden guardar notas en cuadros con estado Abierto.',
                'type' => 'warning',
            ]);

            return;
        }

        $activities = $this->gradeBook->activities->keyBy('id');
        $config = $this->gradeBook->academicConfiguration;
        $hasImprovement = $config->improvement_type !== 'none';
        $students = $this->getStudents();

        $rules = [];
        $messages = [];

        foreach ($students as $student) {
            foreach ($activities as $activity) {
                $scoreVal = data_get($grid, "{$student->id}.{$activity->id}.score");
                $ruleKey = "grid.{$student->id}.{$activity->id}";

                if (! is_null($scoreVal) && $scoreVal !== '') {
                    $rules["{$ruleKey}.score"] = "numeric|min:0|max:{$activity->max_points}";
                    $messages["{$ruleKey}.score.max"] = "{$student->user->first_name} — '{$activity->name}': supera el máximo ({$activity->max_points} pts).";
                    $messages["{$ruleKey}.score.min"] = 'La nota no puede ser negativa.';
                }

                if ($hasImprovement) {
                    $impVal = data_get($grid, "{$student->id}.{$activity->id}.improvement");
                    if (! is_null($impVal) && $impVal !== '' && (float) $impVal > 0) {
                        $currentScore = (is_null($scoreVal) || $scoreVal === '') ? 0.0 : (float) $scoreVal;
                        $maxImprovement = $config->maxImprovementScore($currentScore, (float) $activity->max_points);
                        $rules["{$ruleKey}.improvement"] = "numeric|min:0|max:{$maxImprovement}";
                        $messages["{$ruleKey}.improvement.max"] = "{$student->user->first_name} — '{$activity->name}': mejora supera el máximo permitido ({$maxImprovement} pts).";
                    }
                }
            }
        }

        if (! empty($rules)) {
            $validator = Validator::make(['grid' => $grid], $rules, $messages);

            if ($validator->fails()) {
                $this->dispatch('showAlert', [
                    'title' => 'Error en las notas',
                    'message' => $validator->errors()->first(),
                    'type' => 'error',
                ]);

                return;
            }
        }

        DB::transaction(function () use ($grid, $activities, $hasImprovement, $students): void {
            foreach ($students as $student) {
                foreach ($activities as $activityId => $activity) {
                    $scoreVal = data_get($grid, "{$student->id}.{$activityId}.score");

                    if (is_null($scoreVal) || $scoreVal === '') {
                        continue;
                    }

                    $improvementVal = null;
                    if ($hasImprovement) {
                        $imp = data_get($grid, "{$student->id}.{$activityId}.improvement");
                        if (! is_null($imp) && $imp !== '' && (float) $imp > 0) {
                            $improvementVal = (float) $imp;
                        }
                    }

                    GradeBookScore::updateOrCreate(
                        ['grade_book_activity_id' => $activityId, 'student_id' => $student->id],
                        ['score' => (float) $scoreVal, 'improvement_score' => $improvementVal]
                    );
                }
            }
        });

        GradeBookCalculationService::recalculateAll($this->gradeBook, $students);

        $this->dispatch('toastMessage', [
            'type' => 'success',
            'message' => 'Calificaciones guardadas correctamente.',
        ]);
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
            'assignment.classroom.grade',
            'assignment.classroom.section',
            'assignment.classroom.level',
            'assignment.pensumCourse.course',
            'activities.activityType',
            'academicConfiguration',
        ])->findOrFail($this->gradeBook->id);
    }

    // ==========================================
    // CLONAR CUADRO
    // ==========================================

    public function openCloneModal(): void
    {
        $this->reloadGradeBook();

        $normalMax = $this->gradeBook->activities
            ->filter(fn ($a) => ! $a->activityType->is_extra)
            ->sum('max_points');

        if ($normalMax < 100) {
            $this->dispatch('showAlert', [
                'title' => 'No se puede clonar',
                'message' => "Las actividades normales suman {$normalMax} pts. Deben completar 100 pts para clonar.",
                'type' => 'warning',
            ]);

            return;
        }

        $this->selectedCloneTargets = [];

        $compatibleAssignments = ClassroomCourseAssignment::with([
            'classroom.grade',
            'classroom.section',
            'classroom.level',
            'pensumCourse.course',
            'gradeBook.activities',
        ])
            ->where('professor_id', $this->gradeBook->assignment->professor_id)
            ->where('id', '!=', $this->gradeBook->assignment->id)
            ->whereHas('classroom', fn ($q) => $q->where('year', $this->gradeBook->assignment->classroom->year))
            ->get();

        if ($compatibleAssignments->isEmpty()) {
            $this->dispatch('showAlert', [
                'title' => 'Sin destinos disponibles',
                'message' => 'No tienes otras asignaciones en este año para clonar.',
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

    public function getStudents()
    {
        return Student::whereHas('enrollments', function ($q): void {
            $q->where('classroom_id', $this->gradeBook->assignment->classroom_id)
                ->where('status', 'Activo');
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

    public function render()
    {
        $students = $this->getStudents();
        $activities = $this->gradeBook->activities->sortBy('ordering')->values();
        $config = $this->gradeBook->academicConfiguration;
        $hasImprovement = $config->improvement_type !== 'none';

        $activityIds = $activities->pluck('id');
        $scoresFlat = GradeBookScore::whereIn('grade_book_activity_id', $activityIds)->get();

        // Index by [activity_id][student_id] with explicit int cast to avoid
        // PDO string-vs-int type mismatch between environments.
        $scoresMap = [];
        foreach ($scoresFlat as $score) {
            $scoresMap[(int) $score->grade_book_activity_id][(int) $score->student_id] = $score;
        }

        $initialGrid = [];
        foreach ($students as $student) {
            $initialGrid[$student->id] = [];
            foreach ($activities as $activity) {
                $record = $scoresMap[$activity->id][$student->id] ?? null;
                $initialGrid[$student->id][$activity->id] = [
                    'score' => $record ? (string) $record->score : '',
                    'improvement' => ($record && ! is_null($record->improvement_score))
                        ? (string) $record->improvement_score
                        : '',
                ];
            }
        }

        $activitiesMeta = $activities->map(fn ($a) => [
            'id' => $a->id,
            'maxPoints' => (float) $a->max_points,
            'isExtra' => (bool) $a->activityType->is_extra,
        ])->values()->all();

        $totals = GradeBookTotal::where('grade_book_id', $this->gradeBook->id)
            ->get()->keyBy('student_id');

        $normalMax = $activities->filter(fn ($a) => ! $a->activityType->is_extra)->sum('max_points');

        return view('livewire.profesor.grade-book-grid', compact(
            'students',
            'activities',
            'config',
            'hasImprovement',
            'initialGrid',
            'activitiesMeta',
            'totals',
            'normalMax',
        ));
    }
}
