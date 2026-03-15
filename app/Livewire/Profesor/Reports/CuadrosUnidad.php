<?php

namespace App\Livewire\Profesor\Reports;

use App\Models\GradeBook;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CuadrosUnidad extends Component
{
    public string $filterUnit    = '';
    public int $approvedCount    = 0;
    public array $approvedGradeBooks = [];

    public function updatedFilterUnit(): void
    {
        $this->loadGradeBooks();
    }

    protected function loadGradeBooks(): void
    {
        if (! $this->filterUnit) {
            $this->approvedCount     = 0;
            $this->approvedGradeBooks = [];
            return;
        }

        $professor = Auth::user()->professor;

        $gradeBooks = GradeBook::with([
            'assignment.classroom.level',
            'assignment.classroom.grade',
            'assignment.classroom.section',
            'assignment.pensumCourse.course',
        ])
            ->where('status', 'approved')
            ->whereHas(
                'assignment',
                fn($q) =>
                $q->where('professor_id', $professor->id)
                    ->where('unit', $this->filterUnit)
                    ->whereHas('classroom', fn($q) => $q->where('year', date('Y')))
            )
            ->get();

        $this->approvedCount = $gradeBooks->count();

        $this->approvedGradeBooks = $gradeBooks->map(fn($gb) => [
            'id'      => $gb->id,
            'grado'   => $gb->assignment->classroom->grade->grade_name,
            'seccion' => $gb->assignment->classroom->section->section_name,
            'nivel'   => $gb->assignment->classroom->level->level_name,
            'curso'   => $gb->assignment->pensumCourse->course->course_name,
            'view_url' => route('profesor.reports.cuadros-unidad.view-one', $gb->id),
        ])->toArray();
    }

    public function download(): void
    {
        $this->validate([
            'filterUnit' => 'required|integer|min:1',
        ], [
            'filterUnit.required' => 'Seleccione una unidad.',
        ]);

        $this->dispatch('downloadCuadrosUnidad', [
            'url' => route('profesor.reports.cuadros-unidad.download', [
                'unit' => $this->filterUnit,
            ]),
        ]);
    }

    public function viewAll(): void
    {
        $this->validate([
            'filterUnit' => 'required|integer|min:1',
        ], [
            'filterUnit.required' => 'Seleccione una unidad.',
        ]);

        $this->dispatch('viewAllCuadrosUnidad', [
            'url' => route('profesor.reports.cuadros-unidad.view-all', [
                'unit' => $this->filterUnit,
            ]),
        ]);
    }

    public function render()
    {
        $professor = Auth::user()->professor;

        // Obtener las unidades disponibles del año actual
        $units = \App\Models\ClassroomCourseAssignment::where('professor_id', $professor->id)
            ->whereHas('classroom', fn($q) => $q->where('year', date('Y')))
            ->distinct()
            ->pluck('unit')
            ->sort()
            ->values();

        return view('livewire.profesor.reports.cuadros-unidad', compact('units'));
    }
}
