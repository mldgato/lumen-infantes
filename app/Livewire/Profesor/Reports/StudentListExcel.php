<?php

namespace App\Livewire\Profesor\Reports;

use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentListExcel extends Component
{
    public string $selectedClassroom = '';

    public function download(): void
    {
        $this->validate([
            'selectedClassroom' => 'required|exists:classrooms,id',
        ], [
            'selectedClassroom.required' => 'Seleccione un aula.',
        ]);

        $this->dispatch('downloadStudentListExcel', [
            'url' => route('profesor.reports.student-list-excel', [
                'classroom_id' => $this->selectedClassroom,
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

        return view('livewire.profesor.reports.student-list-excel', compact('classrooms'));
    }
}
