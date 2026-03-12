<?php

namespace App\Livewire\Profesor;

use App\Models\AcademicConfiguration;
use App\Models\ActivityType;
use App\Models\ClassroomCourseAssignment;
use App\Models\GradeBook;
use App\Models\GradeBookActivity;
use App\Models\GradeBookScore;
use App\Models\GradeBookTotal;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class GradeBooks extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool $readyToLoad = false;
    public string $search    = '';
    public string $cant      = '10';

    // Cuadro activo
    public ?GradeBook $gradeBook           = null;
    public ?ClassroomCourseAssignment $assignment = null;

    // Formulario de actividad
    public bool $showActivityForm      = false;
    public ?int $editingActivityId     = null;
    public int|string $activity_type_id = '';
    public string $activityName         = '';
    public int|string $max_points       = '';
    public int|string $ordering         = 0;

    // Calificaciones
    public bool $showScoresForm = false;
    public ?int $scoringActivityId = null;
    public array $scores = [];

    public string $configMode = 'free';

    protected $queryString = [
        'cant'   => ['except' => '10'],
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

    public function updatingCant(): void
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

        // Buscar configuración académica del año del classroom
        $academicConfig = AcademicConfiguration::where('year', $this->assignment->classroom->year)->first();

        if (!$academicConfig) {
            $this->dispatch('showAlert', [
                'title'   => 'Sin configuración',
                'message' => 'No existe una configuración académica para el año ' . $this->assignment->classroom->year . '.',
                'type'    => 'warning',
            ]);
            return;
        }

        // Buscar o crear el cuadro
        $this->gradeBook = GradeBook::with([
            'activities.activityType',
            'activities.scores',
        ])->firstOrCreate(
            ['classroom_course_assignment_id' => $assignmentId],
            [
                'academic_configuration_id' => $academicConfig->id,
                'status'                    => 'open',
            ]
        );

        $this->configMode = $academicConfig->mode;

        // Inicializar totales para estudiantes inscritos si no existen
        $this->initializeTotals();

        $this->resetActivityForm();
        $this->showScoresForm  = false;
        $this->scoringActivityId = null;
    }

    protected function initializeTotals(): void
    {
        $students = $this->getStudents();

        foreach ($students as $student) {
            GradeBookTotal::firstOrCreate(
                [
                    'grade_book_id' => $this->gradeBook->id,
                    'student_id'    => $student->id,
                ],
                [
                    'normal_points' => 0,
                    'extra_points'  => 0,
                    'total_points'  => 0,
                ]
            );
        }
    }

    public function getStudents()
    {
        return Student::whereHas('enrollments', function ($q) {
            $q->where('classroom_id', $this->assignment->classroom_id)
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

    public function closeGradeBook(): void
    {
        $this->gradeBook    = null;
        $this->assignment   = null;
        $this->resetActivityForm();
        $this->showScoresForm   = false;
        $this->scoringActivityId = null;
        $this->scores           = [];
    }

    // ==========================================
    // ACTIVIDADES
    // ==========================================

    public function resetActivityForm(): void
    {
        $this->showActivityForm   = false;
        $this->editingActivityId  = null;
        $this->activity_type_id   = '';
        $this->activityName       = '';
        $this->max_points         = '';
        $this->ordering           = $this->gradeBook
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
        $this->activity_type_id  = $activity->activity_type_id;
        $this->activityName      = $activity->name;
        $this->max_points        = $activity->max_points;
        $this->ordering          = $activity->ordering;
        $this->showActivityForm  = true;
    }

    public function saveActivity(): void
    {
        $this->validate([
            'activity_type_id' => 'required|exists:activity_types,id',
            'activityName'     => 'required|string|max:255',
            'max_points'       => 'required|numeric|min:0.01',
            'ordering'         => 'required|integer|min:0',
        ], [
            'activity_type_id.required' => 'El tipo de actividad es obligatorio.',
            'activityName.required'     => 'El nombre de la actividad es obligatorio.',
            'max_points.required'       => 'Los puntos máximos son obligatorios.',
            'max_points.min'            => 'Los puntos deben ser mayor a 0.',
            'ordering.required'         => 'El orden es obligatorio.',
        ]);

        // Validar límite de cantidad en modo asignado
        if ($this->configMode === 'assigned') {
            $configActivity = $this->gradeBook->academicConfiguration
                ->activities
                ->firstWhere('activity_type_id', $this->activity_type_id);

            if ($configActivity) {
                $existingCount = GradeBookActivity::where('grade_book_id', $this->gradeBook->id)
                    ->where('activity_type_id', $this->activity_type_id)
                    ->when($this->editingActivityId, fn($q) => $q->where('id', '!=', $this->editingActivityId))
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
                'name'             => $this->activityName,
                'max_points'       => $this->max_points,
                'ordering'         => $this->ordering,
            ]);
        } else {
            GradeBookActivity::create([
                'grade_book_id'    => $this->gradeBook->id,
                'activity_type_id' => $this->activity_type_id,
                'name'             => $this->activityName,
                'max_points'       => $this->max_points,
                'ordering'         => $this->ordering,
            ]);
        }

        // Recargar cuadro
        $this->reloadGradeBook();
        $this->recalculateAllTotals();
        $this->resetActivityForm();

        $this->dispatch('toastMessage', [
            'type'    => 'success',
            'message' => $this->editingActivityId ? 'Actividad actualizada.' : 'Actividad agregada.',
        ]);
    }

    public function deleteActivity(int $id): void
    {
        GradeBookActivity::findOrFail($id)->delete();
        $this->reloadGradeBook();
        $this->recalculateAllTotals();

        $this->dispatch('toastMessage', [
            'type'    => 'info',
            'message' => 'Actividad eliminada.',
        ]);
    }

    // ==========================================
    // CALIFICACIONES
    // ==========================================

    public function openScores(int $activityId): void
    {
        $this->scoringActivityId = $activityId;
        $this->showScoresForm    = true;
        $this->showActivityForm  = false;

        $activity = GradeBookActivity::with('scores')->findOrFail($activityId);
        $students = $this->getStudents();

        $this->scores = [];
        foreach ($students as $student) {
            $score = $activity->scores->firstWhere('student_id', $student->id);
            $this->scores[$student->id] = $score ? (float) $score->score : 0;
        }
    }

    public function saveScores(): void
    {
        $activity = GradeBookActivity::findOrFail($this->scoringActivityId);

        foreach ($this->scores as $studentId => $score) {
            $this->validate([
                "scores.{$studentId}" => "required|numeric|min:0|max:{$activity->max_points}",
            ], [
                "scores.{$studentId}.max" => "La nota no puede superar {$activity->max_points} puntos.",
                "scores.{$studentId}.min" => "La nota no puede ser negativa.",
            ]);

            GradeBookScore::updateOrCreate(
                [
                    'grade_book_activity_id' => $this->scoringActivityId,
                    'student_id'             => $studentId,
                ],
                ['score' => $score]
            );
        }

        $this->recalculateAllTotals();

        $this->showScoresForm    = false;
        $this->scoringActivityId = null;
        $this->scores            = [];

        $this->dispatch('toastMessage', [
            'type'    => 'success',
            'message' => 'Calificaciones guardadas correctamente.',
        ]);
    }

    public function closeScores(): void
    {
        $this->showScoresForm    = false;
        $this->scoringActivityId = null;
        $this->scores            = [];
    }

    // ==========================================
    // TOTALES
    // ==========================================

    protected function recalculateAllTotals(): void
    {
        $students  = $this->getStudents();
        $activities = GradeBookActivity::with(['scores', 'activityType'])
            ->where('grade_book_id', $this->gradeBook->id)
            ->get();

        foreach ($students as $student) {
            $normalPoints = 0;
            $extraPoints  = 0;

            foreach ($activities as $activity) {
                $score = $activity->scores->firstWhere('student_id', $student->id);
                $value = $score ? (float) $score->score : 0;

                if ($activity->activityType->is_extra) {
                    $extraPoints += $value;
                } else {
                    $normalPoints += $value;
                }
            }

            GradeBookTotal::updateOrCreate(
                [
                    'grade_book_id' => $this->gradeBook->id,
                    'student_id'    => $student->id,
                ],
                [
                    'normal_points' => $normalPoints,
                    'extra_points'  => $extraPoints,
                    'total_points'  => $normalPoints + $extraPoints,
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

        // Verificar que los puntos normales sumen 100
        $normalMax = $this->gradeBook->activities
            ->filter(fn($a) => !$a->activityType->is_extra)
            ->sum('max_points');

        if ($normalMax < 100) {
            $this->dispatch('showAlert', [
                'title'   => 'No se puede bloquear',
                'message' => "Las actividades normales suman {$normalMax} puntos. Deben sumar exactamente 100.",
                'type'    => 'warning',
            ]);
            return;
        }

        $this->gradeBook->update(['status' => 'locked']);
        $this->reloadGradeBook();

        $this->dispatch('showAlert', [
            'title'   => 'Cuadro bloqueado',
            'message' => 'El cuadro ha sido bloqueado exitosamente.',
            'type'    => 'success',
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

    public function render()
    {
        $professor = Auth::user()->professor;

        $assignments = $this->readyToLoad
            ? ClassroomCourseAssignment::with([
                'classroom.level',
                'classroom.grade',
                'classroom.section',
                'pensumCourse.course',
                'gradeBook',
            ])
            ->where('professor_id', $professor->id)
            ->whereHas('classroom', fn($q) => $q->where('year', date('Y')))
            ->where(function ($q) {
                $q->whereHas('classroom.level', fn($q) => $q->where('level_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('classroom.grade', fn($q) => $q->where('grade_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('classroom.section', fn($q) => $q->where('section_name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('pensumCourse.course', fn($q) => $q->where('course_name', 'like', '%' . $this->search . '%'));
            })
            ->paginate($this->cant)
            : [];

        // En modo asignado, solo los tipos configurados; en libre, todos
        $configActivities = $this->gradeBook
            ? $this->gradeBook->academicConfiguration->activities->load('activityType')
            : collect();

        $activityTypes = $this->gradeBook && $this->gradeBook->academicConfiguration->mode === 'assigned'
            ? $configActivities->map(fn($ca) => $ca->activityType)
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
