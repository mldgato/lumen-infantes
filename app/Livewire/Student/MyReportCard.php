<?php

namespace App\Livewire\Student;

use App\Models\ClassroomCourseAssignment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyReportCard extends Component
{
    public string $selectedUnit = '';

    public function render(): \Illuminate\View\View
    {
        $this->authorize('student.report-card.view');

        $student = Auth::user()->student;
        $enrollment = null;
        $availableUnits = collect();
        $classroomId = null;

        if ($student) {
            $enrollment = $student->enrollments()
                ->with('classroom')
                ->where('status', 'Activo')
                ->whereHas('classroom', fn ($q) => $q->where('year', date('Y')))
                ->first();

            if ($enrollment) {
                $classroomId = $enrollment->classroom_id;

                $availableUnits = ClassroomCourseAssignment::whereHas('gradeBook', fn ($q) => $q->where('status', 'approved'))
                    ->where('classroom_id', $classroomId)
                    ->distinct()
                    ->pluck('unit')
                    ->sort()
                    ->values();
            }
        }

        return view('livewire.student.my-report-card', compact('enrollment', 'availableUnits', 'classroomId'));
    }
}
