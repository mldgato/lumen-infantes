<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Classroom;
use Livewire\Component;

class ProfessorCoursesExcel extends Component
{
    public string $filterYear = '';

    public function download(): void
    {
        $this->validate(
            ['filterYear' => 'required|integer'],
            ['filterYear.required' => 'Seleccione un año.']
        );

        $this->dispatch('downloadProfessorCoursesExcel', [
            'url' => route('admin.reports.professor-courses.download', [
                'year' => $this->filterYear,
            ]),
        ]);
    }

    public function render()
    {
        $years = Classroom::select('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('livewire.admin.reports.professor-courses-excel', compact('years'));
    }
}
