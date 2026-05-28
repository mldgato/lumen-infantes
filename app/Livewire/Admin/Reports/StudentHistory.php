<?php

namespace App\Livewire\Admin\Reports;

use App\Models\ClassroomCourseAssignment;
use App\Models\Pensum;
use App\Models\PensumCourse;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentHistory extends Component
{
    public string $search = '';

    public ?int $selectedStudentId = null;

    public bool $readyToLoad = false;

    public function updatingSearch(): void
    {
        $this->selectedStudentId = null;
        $this->readyToLoad = false;
    }

    public function selectStudent(int $studentId): void
    {
        $this->selectedStudentId = $studentId;
        $this->readyToLoad = true;
    }

    protected function buildHistory(Student $student): Collection
    {
        $enrollments = StudentEnrollment::with([
            'classroom.level',
            'classroom.grade',
            'classroom.section',
        ])
            ->where('student_id', $student->id)
            ->whereHas('classroom')
            ->get()
            ->sortByDesc(fn ($e) => $e->classroom->year);

        return $enrollments->map(function (StudentEnrollment $enrollment) use ($student) {
            $classroom = $enrollment->classroom;

            $pensum = Pensum::where('grade_id', $classroom->grade_id)
                ->where('year', $classroom->year)
                ->first();

            if (! $pensum) {
                return [
                    'year' => $classroom->year,
                    'level' => $classroom->level->level_name,
                    'grade' => $classroom->grade->grade_name,
                    'section' => $classroom->section->section_name,
                    'status' => $enrollment->status,
                    'average' => null,
                    'courses' => collect(),
                ];
            }

            $officialCourses = PensumCourse::where('pensum_id', $pensum->id)
                ->where('is_official', true)
                ->with('course')
                ->orderBy('ordering')
                ->get();

            $assignments = ClassroomCourseAssignment::with([
                'gradeBook' => fn ($q) => $q->where('status', 'approved')->with([
                    'totals' => fn ($q) => $q->where('student_id', $student->id),
                ]),
            ])
                ->where('classroom_id', $classroom->id)
                ->get()
                ->keyBy(fn ($a) => $a->pensum_course_id.'-'.$a->unit);

            $courseRows = $officialCourses->map(function (PensumCourse $pc) use ($assignments, $pensum) {
                $weightedSum = 0.0;
                $totalPct = 0.0;

                for ($u = 1; $u <= $pensum->units; $u++) {
                    $assignment = $assignments->get($pc->id.'-'.$u);
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

                return [
                    'course' => $pc->course->course_name,
                    'weighted' => $totalPct > 0 ? round($weightedSum, 1) : null,
                    'covered' => round($totalPct, 1),
                ];
            })->filter(fn ($r) => $r['weighted'] !== null);

            $yearAvg = $courseRows->isNotEmpty()
                ? round($courseRows->avg('weighted'), 1)
                : null;

            return [
                'year' => $classroom->year,
                'level' => $classroom->level->level_name,
                'grade' => $classroom->grade->grade_name,
                'section' => $classroom->section->section_name,
                'status' => $enrollment->status,
                'average' => $yearAvg,
                'courses' => $courseRows->values(),
            ];
        });
    }

    public function render(): \Illuminate\View\View
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');

        $students = collect();

        if (strlen($this->search) >= 3) {
            $students = Student::with('user')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->where(fn ($q) => $q
                    ->where('users.first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('users.surname', 'like', '%'.$this->search.'%')
                    ->orWhere('users.second_surname', 'like', '%'.$this->search.'%')
                    ->orWhere('users.cui', 'like', '%'.$this->search.'%')
                )
                ->whereHas('enrollments.classroom', fn ($q) => $q->whereIn('level_id', $userLevelIds))
                ->select('students.*')
                ->orderBy('users.surname')
                ->orderBy('users.first_name')
                ->limit(20)
                ->get();
        }

        $selectedStudent = null;
        $history = collect();

        if ($this->readyToLoad && $this->selectedStudentId) {
            $selectedStudent = Student::with('user')->find($this->selectedStudentId);
            if ($selectedStudent) {
                $history = $this->buildHistory($selectedStudent);
            }
        }

        return view('livewire.admin.reports.student-history', compact(
            'students',
            'selectedStudent',
            'history',
        ));
    }
}
