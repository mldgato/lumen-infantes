<?php

namespace App\Livewire\Dashboard;

use App\Models\AttendanceEntry;
use App\Models\ClassroomCourseAssignment;
use App\Models\Pensum;
use App\Models\PensumCourse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentSummary extends Component
{
    public bool $readyToLoad = false;

    public ?int $totalCourses = null;

    public ?int $coursesAtRisk = null;

    public ?float $attendancePercentage = null;

    public ?int $approvedUnits = null;

    public function loadData(): void
    {
        $student = Auth::user()->student;

        if (! $student) {
            $this->readyToLoad = true;
            return;
        }

        $enrollment = $student->enrollments()
            ->with('classroom')
            ->where('status', 'Activo')
            ->whereHas('classroom', fn ($q) => $q->where('year', date('Y')))
            ->first();

        if (! $enrollment) {
            $this->readyToLoad = true;
            return;
        }

        $classroom = $enrollment->classroom;

        $pensum = Pensum::where('grade_id', $classroom->grade_id)
            ->where('year', $classroom->year)
            ->first();

        if ($pensum) {
            $assignments = ClassroomCourseAssignment::with([
                'gradeBook' => fn ($q) => $q->where('status', 'approved')->with([
                    'totals' => fn ($q) => $q->where('student_id', $student->id),
                ]),
            ])
                ->where('classroom_id', $classroom->id)
                ->get()
                ->keyBy(fn ($a) => $a->pensum_course_id . '-' . $a->unit);

            $pensumCourses = PensumCourse::where('pensum_id', $pensum->id)
                ->where('is_official', true)
                ->get();

            $this->totalCourses = $pensumCourses->count();
            $atRisk = 0;

            foreach ($pensumCourses as $pc) {
                $weightedSum = 0.0;
                $totalPct = 0.0;

                for ($u = 1; $u <= $pensum->units; $u++) {
                    $assignment = $assignments->get($pc->id . '-' . $u);
                    if ($assignment && $assignment->gradeBook) {
                        $total = $assignment->gradeBook->totals->first();
                        if ($total) {
                            $score = min(100, (float) $total->total_points);
                            $pct = $pensum->getUnitPercentage($u);
                            $weightedSum += $score * $pct / 100;
                            $totalPct += $pct;
                        }
                    }
                }

                if ($totalPct > 0 && round($weightedSum) < 60) {
                    $atRisk++;
                }
            }

            $this->coursesAtRisk = $atRisk;

            $this->approvedUnits = ClassroomCourseAssignment::whereHas('gradeBook', fn ($q) => $q->where('status', 'approved'))
                ->where('classroom_id', $classroom->id)
                ->distinct('unit')
                ->count('unit');
        }

        $assignmentIds = ClassroomCourseAssignment::where('classroom_id', $classroom->id)->pluck('id');

        $totalEntries = AttendanceEntry::where('student_id', $student->id)
            ->whereHas('record', fn ($q) => $q->whereIn('classroom_course_assignment_id', $assignmentIds))
            ->count();

        $presentEntries = AttendanceEntry::where('student_id', $student->id)
            ->where('present', true)
            ->whereHas('record', fn ($q) => $q->whereIn('classroom_course_assignment_id', $assignmentIds))
            ->count();

        $this->attendancePercentage = $totalEntries > 0
            ? round(($presentEntries / $totalEntries) * 100, 1)
            : null;

        $this->readyToLoad = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.student-summary');
    }
}
