<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Pensum;
use App\Models\PensumCourse;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentsAtRisk extends Component
{
    public string $filterYear = '';

    public string $filterLevel = '';

    public string $filterGrade = '';

    public string $filterSection = '';

    public int $riskThreshold = 60;

    public bool $readyToLoad = false;

    // ==========================================
    // CASCADA DE FILTROS
    // ==========================================

    public function updatedFilterYear(): void
    {
        $this->filterLevel = $this->filterGrade = $this->filterSection = '';
        $this->readyToLoad = false;
    }

    public function updatedFilterLevel(): void
    {
        $this->filterGrade = $this->filterSection = '';
        $this->readyToLoad = false;
    }

    public function updatedFilterGrade(): void
    {
        $this->filterSection = '';
        $this->readyToLoad = false;
    }

    public function updatedFilterSection(): void
    {
        $this->readyToLoad = false;
    }

    public function generateReport(): void
    {
        $this->validate([
            'filterYear' => 'required',
            'filterLevel' => 'required',
            'filterGrade' => 'required',
            'filterSection' => 'required',
        ], [
            'filterYear.required' => 'Selecciona un año.',
            'filterLevel.required' => 'Selecciona un nivel.',
            'filterGrade.required' => 'Selecciona un grado.',
            'filterSection.required' => 'Selecciona una sección.',
        ]);

        $userLevelIds = Auth::user()->levels()->pluck('levels.id');
        if (! $userLevelIds->contains((int) $this->filterLevel)) {
            abort(403);
        }

        $this->readyToLoad = true;
    }

    protected function computeReport(Classroom $classroom): Collection
    {
        $pensum = Pensum::where('grade_id', $classroom->grade_id)
            ->where('year', $classroom->year)
            ->first();

        if (! $pensum) {
            return collect();
        }

        $pensumCourses = PensumCourse::where('pensum_id', $pensum->id)
            ->where('is_official', true)
            ->with('course')
            ->orderBy('ordering')
            ->get();

        $assignments = ClassroomCourseAssignment::with([
            'gradeBook' => fn ($q) => $q->where('status', 'approved')->with('totals'),
        ])
            ->where('classroom_id', $classroom->id)
            ->get()
            ->keyBy(fn ($a) => $a->pensum_course_id.'-'.$a->unit);

        $students = Student::whereHas(
            'enrollments',
            fn ($q) => $q->where('classroom_id', $classroom->id)->where('status', 'Activo')
        )
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->select('students.*')
            ->with('user')
            ->get();

        $rows = collect();

        foreach ($students as $student) {
            $atRiskCourses = [];

            foreach ($pensumCourses as $pc) {
                $unitScores = [];
                $weightedSum = 0.0;
                $totalPct = 0.0;

                for ($u = 1; $u <= $pensum->units; $u++) {
                    $assignment = $assignments->get($pc->id.'-'.$u);
                    if ($assignment && $assignment->gradeBook) {
                        $total = $assignment->gradeBook->totals->firstWhere('student_id', $student->id);
                        if ($total) {
                            $score = min(100, (float) $total->total_points);
                            $pct = $pensum->getUnitPercentage($u);
                            $weightedSum += $score * $pct / 100;
                            $totalPct += $pct;
                            $unitScores[$u] = $score;
                        }
                    }
                }

                if ($totalPct > 0) {
                    $weighted = round($weightedSum, 1);
                    if ($weighted < $this->riskThreshold) {
                        $atRiskCourses[] = [
                            'course' => $pc->course->course_name,
                            'scores' => $unitScores,
                            'units' => $pensum->units,
                            'weighted' => $weighted,
                            'covered' => round($totalPct, 1),
                        ];
                    }
                }
            }

            if (! empty($atRiskCourses)) {
                $rows->push([
                    'name' => $student->user->full_full_name,
                    'at_risk_courses' => $atRiskCourses,
                ]);
            }
        }

        return $rows;
    }

    // ==========================================
    // RENDER
    // ==========================================

    public function render(): \Illuminate\View\View
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');

        $years = Classroom::select('year')
            ->whereIn('level_id', $userLevelIds)
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $levels = $this->filterYear
            ? Level::whereIn('id', $userLevelIds)
                ->whereHas('classrooms', fn ($q) => $q->where('year', $this->filterYear))
                ->orderBy('level_name')->get()
            : collect();

        $grades = $this->filterLevel
            ? Grade::whereHas(
                'classrooms',
                fn ($q) => $q->where('year', $this->filterYear)->where('level_id', $this->filterLevel)
            )->orderBy('ordering')->get()
            : collect();

        $sections = $this->filterGrade
            ? Section::whereHas(
                'classrooms',
                fn ($q) => $q->where('year', $this->filterYear)
                    ->where('level_id', $this->filterLevel)
                    ->where('grade_id', $this->filterGrade)
            )->orderBy('section_name')->get()
            : collect();

        $classroom = null;
        $rows = collect();
        $pensum = null;

        if ($this->readyToLoad && $this->filterSection) {
            $classroom = Classroom::where('year', $this->filterYear)
                ->where('level_id', $this->filterLevel)
                ->where('grade_id', $this->filterGrade)
                ->where('section_id', $this->filterSection)
                ->first();

            if ($classroom) {
                $pensum = Pensum::where('grade_id', $classroom->grade_id)
                    ->where('year', $classroom->year)
                    ->first();
                $rows = $this->computeReport($classroom);
            }
        }

        return view('livewire.admin.reports.students-at-risk', compact(
            'years',
            'levels',
            'grades',
            'sections',
            'classroom',
            'pensum',
            'rows',
        ));
    }
}
