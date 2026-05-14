<?php

namespace App\Livewire\Student;

use App\Models\ClassroomCourseAssignment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyAttendance extends Component
{
    public function render(): \Illuminate\View\View
    {
        $this->authorize('student.attendance.view');

        $student = Auth::user()->student;

        if (! $student) {
            return view('livewire.student.my-attendance', ['rows' => collect(), 'classroom' => null]);
        }

        $enrollment = $student->enrollments()
            ->with('classroom.grade', 'classroom.section')
            ->where('status', 'Activo')
            ->whereHas('classroom', fn ($q) => $q->where('year', date('Y')))
            ->first();

        if (! $enrollment) {
            return view('livewire.student.my-attendance', ['rows' => collect(), 'classroom' => null]);
        }

        $classroom = $enrollment->classroom;

        $assignments = ClassroomCourseAssignment::with([
            'pensumCourse.course',
            'attendanceRecords.entries' => fn ($q) => $q->where('student_id', $student->id),
        ])
            ->where('classroom_id', $classroom->id)
            ->get()
            ->unique('pensum_course_id');

        $rows = $assignments->map(function (ClassroomCourseAssignment $assignment): array {
            $total = 0;
            $present = 0;

            foreach ($assignment->attendanceRecords as $record) {
                foreach ($record->entries as $entry) {
                    $total++;
                    if ($entry->present) {
                        $present++;
                    }
                }
            }

            $percentage = $total > 0 ? round(($present / $total) * 100, 1) : null;

            return [
                'course'     => $assignment->pensumCourse?->course?->course_name ?? '—',
                'total'      => $total,
                'present'    => $present,
                'absent'     => $total - $present,
                'percentage' => $percentage,
                'atRisk'     => $percentage !== null && $percentage < 80,
            ];
        })->values();

        return view('livewire.student.my-attendance', compact('rows', 'classroom'));
    }
}
