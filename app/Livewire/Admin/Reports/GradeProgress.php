<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GradeProgress extends Component
{
    public bool $readyToLoad = false;

    public string $filterYear    = '';
    public string $filterLevel   = '';
    public string $filterGrade   = '';
    public string $filterSection = '';

    public array $reportData = [];
    public bool  $generated  = false;

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function updatedFilterYear(): void
    {
        $this->filterLevel = $this->filterGrade = $this->filterSection = '';
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

    public function generateReport(): void
    {
        $this->validate(
            ['filterYear' => 'required'],
            ['filterYear.required' => 'Seleccione un año.'],
        );

        $assignments = ClassroomCourseAssignment::with([
            'professor.user',
            'classroom',
            'gradeBook' => fn($q) => $q->withCount('activities as actual_activities'),
        ])
        ->whereHas('classroom', function ($q) {
            $q->where('year', $this->filterYear);
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
        ->get();

        // Alumnos activos inscritos por aula
        $classroomIds      = $assignments->pluck('classroom_id')->unique();
        $enrolledByClassroom = StudentEnrollment::where('status', 'Activo')
            ->whereIn('classroom_id', $classroomIds)
            ->groupBy('classroom_id')
            ->selectRaw('classroom_id, COUNT(*) as cnt')
            ->pluck('cnt', 'classroom_id');

        // Calificaciones ingresadas (score NOT NULL) por cuadro
        $gradeBookIds = $assignments->pluck('gradeBook')->filter()->pluck('id');
        $scoresByBook = collect();

        if ($gradeBookIds->isNotEmpty()) {
            $scoresByBook = DB::table('grade_book_scores')
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

        // Agrupar por profesor y calcular métricas
        $this->reportData = $assignments
            ->groupBy('professor_id')
            ->map(function ($profAssignments) use ($enrolledByClassroom, $scoresByBook) {
                $professor = $profAssignments->first()->professor;

                if (!$professor) {
                    return null;
                }

                $totalAssignments = $profAssignments->count();
                $booksCreated     = $profAssignments->filter(fn($a) => $a->gradeBook !== null)->count();
                $pending          = $totalAssignments - $booksCreated;

                $statusCounts  = ['open' => 0, 'locked' => 0, 'approved' => 0, 'rejected' => 0];
                $totalExpected = 0;
                $totalActual   = 0;

                foreach ($profAssignments as $assignment) {
                    $gb = $assignment->gradeBook;

                    if (!$gb) {
                        continue;
                    }

                    $statusCounts[$gb->status] = ($statusCounts[$gb->status] ?? 0) + 1;

                    $enrolled       = (int) $enrolledByClassroom->get($assignment->classroom_id, 0);
                    $activities     = (int) ($gb->actual_activities ?? 0);
                    $totalExpected += $activities * $enrolled;
                    $totalActual   += (int) $scoresByBook->get($gb->id, 0);
                }

                $scoresPct = $totalExpected > 0
                    ? round(($totalActual / $totalExpected) * 100, 1)
                    : 0;

                return [
                    'name'     => $professor->user->full_full_name,
                    'total'    => $totalAssignments,
                    'created'  => $booksCreated,
                    'pending'  => $pending,
                    'open'     => $statusCounts['open'],
                    'locked'   => $statusCounts['locked'],
                    'approved' => $statusCounts['approved'],
                    'rejected' => $statusCounts['rejected'],
                    'expected' => $totalExpected,
                    'actual'   => $totalActual,
                    'pct'      => $scoresPct,
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
        $this->generated  = false;
    }

    public function render()
    {
        $years = Classroom::select('year')->distinct()->orderByDesc('year')->pluck('year');

        $levels = $this->filterYear
            ? Level::whereHas('classrooms', fn($q) => $q->where('year', $this->filterYear))
                ->orderBy('level_name')
                ->get()
            : collect();

        $grades = $this->filterLevel
            ? Grade::whereHas('classrooms', fn($q) => $q
                ->where('year', $this->filterYear)
                ->where('level_id', $this->filterLevel))
                ->orderBy('ordering')
                ->get()
            : collect();

        $sections = $this->filterGrade
            ? Section::whereHas('classrooms', fn($q) => $q
                ->where('year', $this->filterYear)
                ->where('level_id', $this->filterLevel)
                ->where('grade_id', $this->filterGrade))
                ->orderBy('section_name')
                ->get()
            : collect();

        return view('livewire.admin.reports.grade-progress', compact(
            'years',
            'levels',
            'grades',
            'sections',
        ));
    }
}
