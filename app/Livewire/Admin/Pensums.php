<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\PensumForm;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Pensum;
use App\Models\PensumCourse;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class Pensums extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public PensumForm $form;

    public string $search    = '';
    public string $sort      = 'year';
    public string $direction = 'desc';
    public string $cant      = '10';
    public bool $readyToLoad = false;

    // Gestión de cursos del pénsum
    public ?int $managingPensumId = null;
    public ?Pensum $managingPensum = null;

    // Formulario de curso
    public bool $showCourseForm  = false;
    public int|string $course_id = '';
    public string $scenario      = 'common'; // common | partial | main
    public array $selectedUnits  = [];
    public int|string $subCourseCount = 0;
    public int|string $courseOrdering = 0;
    public array $subCourses     = []; // [['course_id' => '', 'units' => []]]
    public ?int $editingCourseId = null;

    //Copia de pénsum
    public ?int $copyingPensumId     = null;
    public int|string $copy_grade_id = '';
    public string $copy_year         = '';

    protected $queryString = [
        'cant'      => ['except' => '10'],
        'sort'      => ['except' => 'year'],
        'direction' => ['except' => 'desc'],
        'search'    => ['except' => ''],
    ];

    public function loadPensums(): void
    {
        $this->readyToLoad = true;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCant(): void
    {
        $this->resetPage();
    }

    public function order(string $sort): void
    {
        if ($this->sort === $sort) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort      = $sort;
            $this->direction = 'asc';
        }
    }

    public function resetFields(): void
    {
        $this->form->resetForm();
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $this->resetFields();
        $this->form->setPensum(Pensum::findOrFail($id));
    }

    public function save(): void
    {
        if ($this->form->pensum) {
            $this->authorize('admin.pensums.edit');
            $this->form->update();
            $mensaje = 'Pénsum actualizado exitosamente.';
        } else {
            $this->authorize('admin.pensums.create');
            $this->form->store();
            $mensaje = 'Pénsum creado exitosamente.';
        }

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Éxito!',
            'message' => $mensaje,
            'type'    => 'success',
            'modalId' => 'PensumModal',
        ]);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin.pensums.delete');
        Pensum::findOrFail($id)->delete();

        $this->dispatch('showAlert', [
            'title'   => '¡Eliminado!',
            'message' => 'Pénsum eliminado exitosamente.',
            'type'    => 'success',
        ]);
    }

    // ==========================================
    // GESTIÓN DE CURSOS DEL PÉNSUM
    // ==========================================

    public function manageCourses(int $id): void
    {
        $this->managingPensumId = $id;
        $this->managingPensum   = Pensum::with([
            'grade',
            'mainCourses.course',
            'mainCourses.subCourses.course',
        ])->findOrFail($id);
        $this->resetCourseForm();
    }

    public function resetCourseForm(): void
    {
        $this->showCourseForm  = false;
        $this->course_id       = '';
        $this->scenario        = 'common';
        $this->selectedUnits   = [];
        $this->subCourseCount  = 0;
        $this->subCourses      = [];
        $this->editingCourseId = null;
        $this->courseOrdering = 0;
        $this->resetValidation();
    }

    public function openCourseForm(): void
    {
        $this->resetCourseForm();
        $this->showCourseForm = true;
    }

    public function updatedSubCourseCount(): void
    {
        $count = (int) $this->subCourseCount;
        $this->subCourses = array_fill(0, $count, ['course_id' => '', 'units' => []]);
    }

    public function editCourse(int $pensumCourseId): void
    {
        $pc = PensumCourse::with('subCourses.course', 'course')->findOrFail($pensumCourseId);

        $this->editingCourseId = $pc->id;
        $this->course_id       = $pc->course_id;
        $this->showCourseForm  = true;
        $this->courseOrdering = $pc->ordering;

        if ($pc->is_main) {
            $this->scenario       = 'main';
            $this->selectedUnits  = $pc->units ?? [];
            $this->subCourseCount = $pc->subCourses->count();
            $this->subCourses     = $pc->subCourses->map(fn($s) => [
                'course_id' => $s->course_id,
                'units'     => $s->units ?? [],
            ])->toArray();
        } elseif ($pc->units !== null && $pc->units !== array_values(range(1, $this->managingPensum->units))) {
            $this->scenario      = 'partial';
            $this->selectedUnits = $pc->units;
        } else {
            $this->scenario      = 'common';
            $this->selectedUnits = [];
        }
    }

    public function saveCourse(): void
    {
        $units = (int) $this->managingPensum->units;

        $this->validate([
            'course_id' => 'required|exists:courses,id',
            'scenario'  => 'required|in:common,partial,main',
        ], [
            'course_id.required' => 'Debe seleccionar un curso.',
            'scenario.required'  => 'Debe seleccionar un escenario.',
        ]);

        if ($this->scenario === 'partial') {
            $this->validate([
                'selectedUnits' => 'required|array|min:1',
            ], [
                'selectedUnits.required' => 'Debe seleccionar al menos una unidad.',
                'selectedUnits.min'      => 'Debe seleccionar al menos una unidad.',
            ]);
        }

        if ($this->scenario === 'main') {
            $this->validate([
                'subCourseCount'   => 'required|integer|min:1',
                'subCourses'       => 'required|array|min:1',
                'subCourses.*.course_id' => 'required|exists:courses,id',
                'subCourses.*.units'     => 'required|array|min:1',
            ], [
                'subCourseCount.min'             => 'Debe haber al menos 1 sub curso.',
                'subCourses.*.course_id.required' => 'Cada sub curso debe tener un curso seleccionado.',
                'subCourses.*.units.required'     => 'Cada sub curso debe tener unidades asignadas.',
                'subCourses.*.units.min'          => 'Cada sub curso debe tener al menos una unidad.',
            ]);
        }

        // Determinar unidades del curso principal
        $mainUnits = match ($this->scenario) {
            'common'  => range(1, $units),
            'partial' => array_map('intval', $this->selectedUnits),
            'main'    => $this->selectedUnits ? array_map('intval', $this->selectedUnits) : null,
        };

        $isMain = $this->scenario === 'main';

        if ($this->editingCourseId) {
            $pc = PensumCourse::findOrFail($this->editingCourseId);
            $pc->update([
                'course_id' => $this->course_id,
                'units'     => $mainUnits,
                'is_main'   => $isMain,
                'ordering'  => $this->courseOrdering,
            ]);
            $pc->subCourses()->delete();
        } else {
            $pc = PensumCourse::create([
                'pensum_id' => $this->managingPensumId,
                'course_id' => $this->course_id,
                'parent_id' => null,
                'units'     => $mainUnits,
                'is_main'   => $isMain,
                'ordering'  => $this->courseOrdering,
            ]);
        }

        // Guardar sub cursos
        if ($isMain) {
            foreach ($this->subCourses as $sub) {
                PensumCourse::create([
                    'pensum_id' => $this->managingPensumId,
                    'course_id' => $sub['course_id'],
                    'parent_id' => $pc->id,
                    'units'     => array_map('intval', $sub['units']),
                    'is_main'   => false,
                ]);
            }
        }

        // Recargar el pénsum
        $this->managingPensum = Pensum::with([
            'grade',
            'mainCourses.course',
            'mainCourses.subCourses.course',
        ])->findOrFail($this->managingPensumId);

        $this->resetCourseForm();

        $this->dispatch('toastMessage', [
            'type'    => 'success',
            'message' => $this->editingCourseId ? 'Curso actualizado.' : 'Curso agregado al pénsum.',
        ]);
    }

    public function deleteCourse(int $pensumCourseId): void
    {
        PensumCourse::findOrFail($pensumCourseId)->delete();

        $this->managingPensum = Pensum::with([
            'grade',
            'mainCourses.course',
            'mainCourses.subCourses.course',
        ])->findOrFail($this->managingPensumId);

        $this->dispatch('toastMessage', [
            'type'    => 'info',
            'message' => 'Curso eliminado del pénsum.',
        ]);
    }

    public function openCopyModal(int $id): void
    {
        $this->copyingPensumId = $id;
        $this->copy_grade_id   = '';
        $this->copy_year       = '';
        $this->resetValidation();
    }

    public function copyPensum(): void
    {
        $this->validate([
            'copy_grade_id' => 'required|exists:grades,id',
            'copy_year' => [
                'required',
                'digits:4',
                'integer',
                Rule::unique('pensums', 'year')->where(fn($q) => $q->where('grade_id', $this->copy_grade_id)),
            ],
        ], [
            'copy_grade_id.required' => 'El grado destino es obligatorio.',
            'copy_grade_id.exists'   => 'El grado seleccionado no es válido.',
            'copy_year.required'     => 'El año destino es obligatorio.',
            'copy_year.digits'       => 'El año debe tener exactamente 4 dígitos.',
            'copy_year.unique'       => 'Ya existe un pénsum para ese grado y año.',
        ]);

        $original = Pensum::with('mainCourses.subCourses')->findOrFail($this->copyingPensumId);

        $nuevo = Pensum::create([
            'grade_id' => $this->copy_grade_id,
            'year'     => $this->copy_year,
            'units'    => $original->units,
        ]);

        foreach ($original->mainCourses as $pc) {
            $nuevoPc = PensumCourse::create([
                'pensum_id' => $nuevo->id,
                'course_id' => $pc->course_id,
                'parent_id' => null,
                'units'     => $pc->units,
                'is_main'   => $pc->is_main,
                'ordering'  => $pc->ordering,
            ]);

            foreach ($pc->subCourses as $sub) {
                PensumCourse::create([
                    'pensum_id' => $nuevo->id,
                    'course_id' => $sub->course_id,
                    'parent_id' => $nuevoPc->id,
                    'units'     => $sub->units,
                    'is_main'   => false,
                    'ordering'  => $sub->ordering,
                ]);
            }
        }

        $this->copyingPensumId = null;
        $this->copy_grade_id   = '';
        $this->copy_year       = '';

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Copiado!',
            'message' => 'Pénsum copiado exitosamente.',
            'type'    => 'success',
            'modalId' => 'CopyPensumModal',
        ]);
    }

    public function render()
    {
        $pensums = $this->readyToLoad
            ? Pensum::with('grade')
            ->whereHas('grade', fn($q) => $q->where('grade_name', 'like', '%' . $this->search . '%'))
            ->orWhere('year', 'like', '%' . $this->search . '%')
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant)
            : [];

        return view('livewire.admin.pensums', [
            'pensums'  => $pensums,
            'grades'   => Grade::orderBy('ordering')->get(),
            'courses'  => Course::orderBy('course_name')->get(),
        ]);
    }
}
