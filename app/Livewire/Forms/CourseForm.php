<?php

namespace App\Livewire\Forms;

use App\Models\Course;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CourseForm extends Form
{
    public ?Course $course = null;

    public string $course_name = '';

    public function rules(): array
    {
        return [
            'course_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses', 'course_name')->ignore($this->course),
            ],
        ];
    }

    protected $messages = [
        'course_name.required' => 'El nombre del curso es obligatorio.',
        'course_name.unique'   => 'Ya existe un curso con ese nombre.',
    ];

    public function setCourse(Course $course): void
    {
        $this->course      = $course;
        $this->course_name = $course->course_name;
    }

    public function store(): void
    {
        $this->validate();

        Course::create([
            'course_name' => $this->course_name,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        $this->course->update([
            'course_name' => $this->course_name,
        ]);
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->course = null;
    }
}
