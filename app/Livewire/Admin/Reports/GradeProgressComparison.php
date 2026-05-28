<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Grade;
use App\Models\GradeBookTotal;
use App\Models\Level;
use App\Models\Pensum;
use App\Models\PensumCourse;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GradeProgressComparison extends Component
{
    public string $filterYear = '';

    public string $filterLevel = '';

    public string $filterGrade = '';

    public string $filterSection = '';

    public bool $readyToLoad = false;

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

    protected function computeComparison(Classroom $classroom): array
    {
        $pensum = Pensum::where('grade_id', $classroom->grade_id)
            ->where('year', $classroom->year)
            ->first();

        if (! $pensum) {
            return ['pensum' => null, 'courses' => collect(), 'unitAverages' => []];
        }

        $courses = PensumCourse::where('pensum_id', $pensum->id)
            ->where('is_official', true)
            ->with('course')
            ->orderBy('ordering')
            ->get();

        $unitAverages = [];

        for ($u = 1; $u <= $pensum->units; $u++) {
            $assignments = ClassroomCourseAssignment::with(['gradeBook'])
                ->where('classroom_id', $classroom->id)
                ->where('unit', $u)
                ->whereHas('gradeBook', fn ($q) => $q->where('status', 'approved'))
                ->get();

            if ($assignments->isEmpty()) {
                $unitAverages[$u] = null;

                continue;
            }

            $totals = GradeBookTotal::whereIn('grade_book_id', $assignments->pluck('gradeBook.id')->filter())
                ->get();

            $unitAverages[$u] = $totals->isNotEmpty()
                ? round($totals->avg('total_points'), 1)
                : null;
        }

        $courseRows = $courses->map(function (PensumCourse $pc) use ($classroom, $pensum) {
            $byUnit = [];
            for ($u = 1; $u <= $pensum->units; $u++) {
                $assignment = ClassroomCourseAssignment::with(['gradeBook'])
                    ->where('classroom_id', $classroom->id)
                    ->where('pensum_course_id', $pc->id)
                    ->where('unit', $u)
                    ->first();

                if ($assignment && $assignment->gradeBook && $assignment->gradeBook->status === 'approved') {
                    $avg = GradeBookTotal::where('grade_book_id', $assignment->gradeBook->id)
                        ->avg('total_points');
                    $byUnit[$u] = $avg !== null ? round((float) $avg, 1) : null;
                } else {
                    $byUnit[$u] = null;
                }
            }

            return [
                'course' => $pc->course->course_name,
                'byUnit' => $byUnit,
            ];
        });

        return [
            'pensum' => $pensum,
            'courses' => $courseRows,
            'unitAverages' => $unitAverages,
        ];
    }

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
        $comparisonData = null;

        if ($this->readyToLoad && $this->filterSection) {
            $classroom = Classroom::where('year', $this->filterYear)
                ->where('level_id', $this->filterLevel)
                ->where('grade_id', $this->filterGrade)
                ->where('section_id', $this->filterSection)
                ->first();

            if ($classroom) {
                $comparisonData = $this->computeComparison($classroom);
            }
        }

        return view('livewire.admin.reports.grade-progress-comparison', compact(
            'years',
            'levels',
            'grades',
            'sections',
            'classroom',
            'comparisonData',
        ));
    }
}
