<?php

namespace App\Livewire\Profesor\Reports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SabanaPromedio extends Component
{
    public string $filterClassroom = '';

    public function download(): void
    {
        $this->validate([
            'filterClassroom' => 'required|exists:classrooms,id',
        ], [
            'filterClassroom.required' => 'Seleccione un aula.',
        ]);

        $this->dispatch('downloadSabanaProfesor', [
            'url' => route('profesor.reports.sabana-promedio', [
                'classroom_id' => $this->filterClassroom,
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
            ->sortBy(fn($classroom) => $classroom->grade->ordering);

        return view('livewire.profesor.reports.sabana-promedio', compact('classrooms'));
    }
}
