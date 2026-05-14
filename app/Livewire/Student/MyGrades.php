<?php

namespace App\Livewire\Student;

use App\Models\ClassroomCourseAssignment;
use App\Models\Pensum;
use App\Models\PensumCourse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyGrades extends Component
{
    public function render(): \Illuminate\View\View
    {
        $this->authorize('student.grades.view');

        $student = Auth::user()->student;

        if (! $student) {
            return view('livewire.student.my-grades', ['rows' => collect(), 'pensum' => null, 'classroom' => null]);
        }

        $enrollment = $student->enrollments()
            ->with('classroom.level', 'classroom.grade', 'classroom.section')
            ->where('status', 'Activo')
            ->whereHas('classroom', fn ($q) => $q->where('year', date('Y')))
            ->first();

        if (! $enrollment) {
            return view('livewire.student.my-grades', ['rows' => collect(), 'pensum' => null, 'classroom' => null]);
        }

        $classroom = $enrollment->classroom;

        $pensum = Pensum::where('grade_id', $classroom->grade_id)
            ->where('year', $classroom->year)
            ->first();

        if (! $pensum) {
            return view('livewire.student.my-grades', ['rows' => collect(), 'pensum' => null, 'classroom' => $classroom]);
        }

        $assignments = ClassroomCourseAssignment::with([
            'pensumCourse.course',
            'gradeBook' => fn ($q) => $q->where('status', 'approved')->with([
                'totals' => fn ($q) => $q->where('student_id', $student->id),
            ]),
        ])
            ->where('classroom_id', $classroom->id)
            ->get()
            ->keyBy(fn ($a) => $a->pensum_course_id . '-' . $a->unit);

        $pensumCourses = PensumCourse::with('course')
            ->where('pensum_id', $pensum->id)
            ->where('is_official', true)
            ->orderBy('ordering')
            ->get();

        $rows = $pensumCourses->map(function (PensumCourse $pc) use ($assignments, $pensum): array {
            $unitScores = [];
            $weightedSum = 0.0;
            $totalPct = 0.0;

            for ($u = 1; $u <= $pensum->units; $u++) {
                $key = $pc->id . '-' . $u;
                $assignment = $assignments->get($key);
                $score = null;

                if ($assignment && $assignment->gradeBook) {
                    $total = $assignment->gradeBook->totals->first();
                    if ($total) {
                        $score = min(100, (int) round((float) $total->total_points));
                        $pct = $pensum->getUnitPercentage($u);
                        $weightedSum += $score * $pct / 100;
                        $totalPct += $pct;
                    }
                }

                $unitScores[$u] = $score;
            }

            $accumulated = $totalPct > 0 ? (int) round($weightedSum) : null;

            return [
                'course'      => $pc->course->course_name,
                'unitScores'  => $unitScores,
                'accumulated' => $accumulated,
                'atRisk'      => $accumulated !== null && $accumulated < 60,
            ];
        });

        return view('livewire.student.my-grades', compact('rows', 'pensum', 'classroom'));
    }
}
