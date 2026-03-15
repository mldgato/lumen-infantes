<?php

namespace App\Livewire\Forms;

use App\Models\Grade;
use Illuminate\Validation\Rule;
use Livewire\Form;

class GradeForm extends Form
{
    public ?Grade $grade = null;

    public string $grade_name          = '';
    public int $ordering               = 0;
    public bool $supervised_practice   = false;

    public function rules(): array
    {
        return [
            'grade_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grades', 'grade_name')->ignore($this->grade),
            ],
            'ordering'             => ['required', 'integer', 'min:0'],
            'supervised_practice'  => ['boolean'],
        ];
    }

    protected $messages = [
        'grade_name.required' => 'El nombre del grado es obligatorio.',
        'grade_name.unique'   => 'Ya existe un grado con ese nombre.',
        'ordering.required'   => 'El orden es obligatorio.',
        'ordering.integer'    => 'El orden debe ser un número entero.',
        'ordering.min'        => 'El orden debe ser mayor o igual a 0.',
    ];

    public function setGrade(Grade $grade): void
    {
        $this->grade               = $grade;
        $this->grade_name          = $grade->grade_name;
        $this->ordering            = $grade->ordering;
        $this->supervised_practice = (bool) $grade->supervised_practice;
    }

    public function store(): void
    {
        $this->validate();

        Grade::create([
            'grade_name'           => $this->grade_name,
            'ordering'             => $this->ordering,
            'supervised_practice'  => $this->supervised_practice,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        $this->grade->update([
            'grade_name'           => $this->grade_name,
            'ordering'             => $this->ordering,
            'supervised_practice'  => $this->supervised_practice,
        ]);
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->grade               = null;
        $this->ordering            = 0;
        $this->supervised_practice = false;
    }
}
