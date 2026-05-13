<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SabanaGeneral extends Component
{
    public string $filterYear = '';

    public string $filterLevel = '';

    public string $filterGrade = '';

    public string $filterSection = '';

    public function updatedFilterYear(): void
    {
        $this->filterLevel = $this->filterGrade = $this->filterSection = '';
    }

    public function updatedFilterLevel(): void
    {
        $this->filterGrade = $this->filterSection = '';
    }

    public function updatedFilterGrade(): void
    {
        $this->filterSection = '';
    }

    public function export(): void
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');
        if (! $userLevelIds->contains((int) $this->filterLevel)) {
            abort(403);
        }

        $this->validate([
            'filterYear' => 'required',
            'filterLevel' => 'required',
            'filterGrade' => 'required',
            'filterSection' => 'required',
        ], [
            'filterYear.required' => 'Seleccione un año.',
            'filterLevel.required' => 'Seleccione un nivel.',
            'filterGrade.required' => 'Seleccione un grado.',
            'filterSection.required' => 'Seleccione una sección.',
        ]);

        $this->dispatch('downloadSabanaGeneral', [
            'url' => route('admin.reports.sabana-general.export', [
                'year' => $this->filterYear,
                'level' => $this->filterLevel,
                'grade' => $this->filterGrade,
                'section' => $this->filterSection,
            ]),
        ]);
    }

    public function render()
    {
        $userLevelIds = Auth::user()->levels()->pluck('levels.id');

        $years = Classroom::select('year')->whereIn('level_id', $userLevelIds)->distinct()->orderByDesc('year')->pluck('year');
        $levels = Level::whereIn('id', $userLevelIds)->orderBy('level_name')->get();

        $grades = $this->filterLevel
            ? Grade::whereHas(
                'classrooms',
                fn ($q) => $q->where('level_id', $this->filterLevel)
                    ->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear))
            )->orderBy('grade_name')->get()
            : collect();

        $sections = $this->filterGrade
            ? Section::whereHas(
                'classrooms',
                fn ($q) => $q->where('grade_id', $this->filterGrade)
                    ->where('level_id', $this->filterLevel)
                    ->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear))
            )->orderBy('section_name')->get()
            : collect();

        return view('livewire.admin.reports.sabana-general', compact('years', 'levels', 'grades', 'sections'));
    }
}
