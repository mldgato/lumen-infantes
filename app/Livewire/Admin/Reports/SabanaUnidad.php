<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Classroom;
use App\Models\ClassroomCourseAssignment;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use Livewire\Component;

class SabanaUnidad extends Component
{
    public string $filterYear    = '';
    public string $filterLevel   = '';
    public string $filterGrade   = '';
    public string $filterSection = '';
    public string $filterUnit    = '';

    public function updatedFilterYear(): void
    {
        $this->filterLevel = $this->filterGrade = $this->filterSection = $this->filterUnit = '';
    }
    public function updatedFilterLevel(): void
    {
        $this->filterGrade = $this->filterSection = $this->filterUnit = '';
    }
    public function updatedFilterGrade(): void
    {
        $this->filterSection = $this->filterUnit = '';
    }
    public function updatedFilterSection(): void
    {
        $this->filterUnit = '';
    }

    public function export(): void
    {
        $this->validate([
            'filterYear'    => 'required',
            'filterLevel'   => 'required',
            'filterGrade'   => 'required',
            'filterSection' => 'required',
            'filterUnit'    => 'required',
        ], [
            'filterYear.required'    => 'Seleccione un año.',
            'filterLevel.required'   => 'Seleccione un nivel.',
            'filterGrade.required'   => 'Seleccione un grado.',
            'filterSection.required' => 'Seleccione una sección.',
            'filterUnit.required'    => 'Seleccione una unidad.',
        ]);

        $this->dispatch('downloadSabana', [
            'url' => route('admin.reports.sabana-unidad.export', [
                'year'    => $this->filterYear,
                'level'   => $this->filterLevel,
                'grade'   => $this->filterGrade,
                'section' => $this->filterSection,
                'unit'    => $this->filterUnit,
            ]),
        ]);
    }

    public function render()
    {
        $years = Classroom::select('year')->distinct()->orderByDesc('year')->pluck('year');
        $levels = Level::orderBy('level_name')->get();

        $grades = $this->filterLevel
            ? Grade::whereHas(
                'classrooms',
                fn($q) =>
                $q->where('level_id', $this->filterLevel)
                    ->when($this->filterYear, fn($q) => $q->where('year', $this->filterYear))
            )->orderBy('grade_name')->get()
            : collect();

        $sections = $this->filterGrade
            ? Section::whereHas(
                'classrooms',
                fn($q) =>
                $q->where('grade_id', $this->filterGrade)
                    ->where('level_id', $this->filterLevel)
                    ->when($this->filterYear, fn($q) => $q->where('year', $this->filterYear))
            )->orderBy('section_name')->get()
            : collect();

        $units = $this->filterSection
            ? ClassroomCourseAssignment::whereHas(
                'classroom',
                fn($q) =>
                $q->where('section_id', $this->filterSection)
                    ->where('grade_id', $this->filterGrade)
                    ->where('level_id', $this->filterLevel)
                    ->when($this->filterYear, fn($q) => $q->where('year', $this->filterYear))
            )->distinct()->pluck('unit')->sort()->values()
            : collect();

        return view('livewire.admin.reports.sabana-unidad', compact('years', 'levels', 'grades', 'sections', 'units'));
    }
}
