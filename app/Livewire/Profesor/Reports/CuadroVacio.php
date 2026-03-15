<?php

namespace App\Livewire\Profesor\Reports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\PensumCourse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CuadroVacio extends Component
{
    public string $filterClassroom    = '';
    public string $filterPensumCourse = '';

    public function updatedFilterClassroom(): void
    {
        $this->filterPensumCourse = '';
    }

    public function download(): void
    {
        $this->validate([
            'filterClassroom'    => 'required|exists:classrooms,id',
            'filterPensumCourse' => 'required|exists:pensum_courses,id',
        ], [
            'filterClassroom.required'    => 'Seleccione un aula.',
            'filterPensumCourse.required' => 'Seleccione un curso.',
        ]);

        $this->dispatch('downloadCuadroVacio', [
            'url' => route('profesor.reports.cuadro-vacio', [
                'classroom_id'     => $this->filterClassroom,
                'pensum_course_id' => $this->filterPensumCourse,
            ]),
        ]);
    }

    public function render()
    {
        $professor = Auth::user()->professor;

        $classrooms = Classroom::with(['grade', 'section', 'level'])
            ->whereHas(
                'courseAssignments',
                fn($q) =>
                $q->where('professor_id', $professor->id)
            )
            ->where('year', date('Y'))
            ->get()
            ->sortBy(fn($c) => $c->grade->ordering);

        $courses = collect();
        if ($this->filterClassroom) {
            $courses = PensumCourse::with('course')
                ->whereHas(
                    'assignments',
                    fn($q) =>
                    $q->where('classroom_id', $this->filterClassroom)
                        ->where('professor_id', $professor->id)
                )
                ->get()
                ->sortBy('ordering');
        }

        return view('livewire.profesor.reports.cuadro-vacio', compact('classrooms', 'courses'));
    }
}
