<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\CourseForm;
use App\Models\Course;
use Livewire\Component;
use Livewire\WithPagination;

class Courses extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public CourseForm $form;

    public string $search    = '';
    public string $sort      = 'course_name';
    public string $direction = 'asc';
    public string $cant      = '10';
    public bool $readyToLoad = false;

    protected $queryString = [
        'cant'      => ['except' => '10'],
        'sort'      => ['except' => 'course_name'],
        'direction' => ['except' => 'asc'],
        'search'    => ['except' => ''],
    ];

    public function loadCourses(): void
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
        $this->form->setCourse(Course::findOrFail($id));
    }

    public function save(): void
    {
        if ($this->form->course) {
            $this->authorize('admin.courses.edit');
            $this->form->update();
            $mensaje = 'Curso actualizado exitosamente.';
        } else {
            $this->authorize('admin.courses.create');
            $this->form->store();
            $mensaje = 'Curso creado exitosamente.';
        }

        $this->resetFields();

        $this->dispatch('closeModalMessaje', [
            'title'   => '¡Éxito!',
            'message' => $mensaje,
            'type'    => 'success',
            'modalId' => 'CourseModal',
        ]);
    }

    public function delete(int $id): void
    {
        $this->authorize('admin.courses.delete');

        $course = Course::withCount('pensumCourses')->findOrFail($id);

        if ($course->pensum_courses_count > 0) {
            $this->dispatch('showAlert', [
                'title'   => 'No permitido',
                'message' => 'El curso "' . $course->course_name . '" no puede eliminarse porque está asignado a uno o más pénsums.',
                'type'    => 'warning',
            ]);
            return;
        }

        $course->delete();

        $this->dispatch('showAlert', [
            'title'   => '¡Eliminado!',
            'message' => 'Curso eliminado exitosamente.',
            'type'    => 'success',
        ]);
    }

    public function render()
    {
        $courses = $this->readyToLoad
            ? Course::where('course_name', 'like', '%' . $this->search . '%')
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant)
            : [];

        return view('livewire.admin.courses', compact('courses'));
    }
}
