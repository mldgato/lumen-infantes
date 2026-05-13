<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Grade;
use App\Models\GradeBookActivity;
use App\Models\Level;
use App\Models\Section;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GradeProgress extends Component
{
    public string $filterYear = '';

    public string $filterLevel = '';

    public string $filterGrade = '';

    public string $filterSection = '';

    public string $filterUnit = '';

    public array $reportData = [];

    public bool $generated = false;

    public function updatedFilterYear(): void
    {
        $this->filterLevel = $this->filterGrade = $this->filterSection = $this->filterUnit = '';
        $this->resetReport();
    }

    public function updatedFilterLevel(): void
    {
        $this->filterGrade = $this->filterSection = '';
        $this->resetReport();
    }

    public function updatedFilterGrade(): void
    {
        $this->filterSection = '';
        $this->resetReport();
    }

    public function updatedFilterSection(): void
    {
        $this->resetReport();
    }

    public function updatedFilterUnit(): void
    {
        $this->resetReport();
    }

    public function generateReport(): void
    {
        $this->validate(
            ['filterYear' => 'required'],
            ['filterYear.required' => 'Seleccione un año.'],
        );

        $userLevelIds = Auth::user()->levels()->pluck('levels.id');

        // 1. Cargar asignaciones con los filtros seleccionados
        $assignments = ClassroomCourseAssignment::with([
            'professor.user',
            'classroom',
            'gradeBook',
        ])
            ->whereHas('classroom', function ($q) use ($userLevelIds) {
                $q->where('year', $this->filterYear)
                    ->whereIn('level_id', $userLevelIds);
                if ($this->filterLevel) {
                    $q->where('level_id', $this->filterLevel);
                }
                if ($this->filterGrade) {
                    $q->where('grade_id', $this->filterGrade);
                }
                if ($this->filterSection) {
                    $q->where('section_id', $this->filterSection);
                }
            })
            ->when($this->filterUnit !== '', fn ($q) => $q->where('unit', $this->filterUnit))
            ->get();

        if ($assignments->isEmpty()) {
            $this->reportData = [];
            $this->generated = true;

            return;
        }

        // 2. Alumnos activos por aula
        $classroomIds = $assignments->pluck('classroom_id')->unique()->values();
        $enrolledByClassroom = StudentEnrollment::where('status', 'Activo')
            ->whereIn('classroom_id', $classroomIds)
            ->groupBy('classroom_id')
            ->selectRaw('classroom_id, COUNT(*) as cnt')
            ->pluck('cnt', 'classroom_id');

        // 3. Actividades creadas por cuadro
        $gradeBookIds = $assignments->pluck('gradeBook')->filter()->pluck('id')->unique()->values();
        $activityCountsByBook = collect();
        $scoreCountsByBook = collect();

        if ($gradeBookIds->isNotEmpty()) {
            $activityCountsByBook = GradeBookActivity::whereIn('grade_book_id', $gradeBookIds)
                ->groupBy('grade_book_id')
                ->selectRaw('grade_book_id, COUNT(*) as cnt')
                ->pluck('cnt', 'grade_book_id');

            // 4. Calificaciones ingresadas (score NOT NULL) por cuadro
            $scoreCountsByBook = DB::table('grade_book_scores')
                ->join(
                    'grade_book_activities',
                    'grade_book_scores.grade_book_activity_id',
                    '=',
                    'grade_book_activities.id'
                )
                ->whereIn('grade_book_activities.grade_book_id', $gradeBookIds)
                ->whereNotNull('grade_book_scores.score')
                ->groupBy('grade_book_activities.grade_book_id')
                ->selectRaw('grade_book_activities.grade_book_id, COUNT(*) as cnt')
                ->pluck('cnt', 'grade_book_activities.grade_book_id');
        }

        // 5. Agrupar por profesor y calcular métricas
        $this->reportData = $assignments
            ->groupBy('professor_id')
            ->map(function ($profAssignments) use ($enrolledByClassroom, $activityCountsByBook, $scoreCountsByBook) {
                $professor = $profAssignments->first()->professor;

                if (! $professor) {
                    return null;
                }

                $totalAssignments = $profAssignments->count();
                $booksCreated = $profAssignments->filter(fn ($a) => $a->gradeBook !== null)->count();
                $pending = $totalAssignments - $booksCreated;

                $statusCounts = ['open' => 0, 'locked' => 0, 'approved' => 0, 'rejected' => 0];
                $totalExpected = 0;
                $totalActual = 0;

                foreach ($profAssignments as $assignment) {
                    $gb = $assignment->gradeBook;

                    if (! $gb) {
                        continue;
                    }

                    $statusCounts[$gb->status] = ($statusCounts[$gb->status] ?? 0) + 1;

                    $enrolled = (int) $enrolledByClassroom->get($assignment->classroom_id, 0);
                    $activities = (int) $activityCountsByBook->get($gb->id, 0);
                    $totalExpected += $activities * $enrolled;
                    $totalActual += (int) $scoreCountsByBook->get($gb->id, 0);
                }

                $scoresPct = $totalExpected > 0
                    ? round(($totalActual / $totalExpected) * 100, 1)
                    : 0;

                return [
                    'name' => $professor->user->full_full_name,
                    'total' => $totalAssignments,
                    'created' => $booksCreated,
                    'pending' => $pending,
                    'open' => $statusCounts['open'],
                    'locked' => $statusCounts['locked'],
                    'approved' => $statusCounts['approved'],
                    'rejected' => $statusCounts['rejected'],
                    'expected' => $totalExpected,
                    'actual' => $totalActual,
                    'pct' => $scoresPct,
                ];
            })
            ->filter()
            ->sortBy('name')
            ->values()
            ->toArray();

        $this->generated = true;
    }

    private function resetReport(): void
    {
        $this->reportData = [];
        $this->generated = false;
    }

    public function render()
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');

        $years = Classroom::select('year')->whereIn('level_id', $userLevelIds)->distinct()->orderByDesc('year')->pluck('year');

        $levels = $this->filterYear
            ? Level::whereIn('id', $userLevelIds)
                ->whereHas('classrooms', fn ($q) => $q->where('year', $this->filterYear))
                ->orderBy('level_name')
                ->get()
            : collect();

        $grades = $this->filterLevel
            ? Grade::whereHas('classrooms', fn ($q) => $q
                ->where('year', $this->filterYear)
                ->where('level_id', $this->filterLevel))
                ->orderBy('ordering')
                ->get()
            : collect();

        $sections = $this->filterGrade
            ? Section::whereHas('classrooms', fn ($q) => $q
                ->where('year', $this->filterYear)
                ->where('level_id', $this->filterLevel)
                ->where('grade_id', $this->filterGrade))
                ->orderBy('section_name')
                ->get()
            : collect();

        $units = $this->filterYear
            ? ClassroomCourseAssignment::whereHas(
                'classroom',
                fn ($q) => $q->where('year', $this->filterYear)->whereIn('level_id', $userLevelIds)
            )
                ->distinct()
                ->orderBy('unit')
                ->pluck('unit')
            : collect();

        return view('livewire.admin.reports.grade-progress', compact(
            'years',
            'levels',
            'grades',
            'sections',
            'units',
        ));
    }
}
