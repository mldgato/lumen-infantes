<?php

namespace App\Livewire\Forms;

use App\Models\Classroom;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ClassroomForm extends Form
{
    public ?Classroom $classroom = null;

    public int|string $level_id   = '';
    public int|string $grade_id   = '';
    public int|string $section_id = '';
    public string $year           = '';

    public function rules(): array
    {
        return [
            'level_id'   => ['required', 'exists:levels,id'],
            'grade_id'   => ['required', 'exists:grades,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'year'       => [
                'required',
                'digits:4',
                'integer',
                Rule::unique('classrooms')->where(function ($query) {
                    return $query
                        ->where('level_id',   $this->level_id)
                        ->where('grade_id',   $this->grade_id)
                        ->where('section_id', $this->section_id)
                        ->where('year',       $this->year);
                })->ignore($this->classroom),
            ],
        ];
    }

    protected $messages = [
        'level_id.required'   => 'El nivel es obligatorio.',
        'level_id.exists'     => 'El nivel seleccionado no es válido.',
        'grade_id.required'   => 'El grado es obligatorio.',
        'grade_id.exists'     => 'El grado seleccionado no es válido.',
        'section_id.required' => 'La sección es obligatoria.',
        'section_id.exists'   => 'La sección seleccionada no es válida.',
        'year.required'       => 'El año es obligatorio.',
        'year.digits'         => 'El año debe tener exactamente 4 dígitos.',
        'year.integer'        => 'El año debe ser un número entero.',
        'year.unique'         => 'Ya existe un aula con ese nivel, grado, sección y año.',
    ];

    public function setClassroom(Classroom $classroom): void
    {
        $this->classroom  = $classroom;
        $this->level_id   = $classroom->level_id;
        $this->grade_id   = $classroom->grade_id;
        $this->section_id = $classroom->section_id;
        $this->year       = $classroom->year;
    }

    public function store(): void
    {
        $this->validate();

        Classroom::create([
            'level_id'   => $this->level_id,
            'grade_id'   => $this->grade_id,
            'section_id' => $this->section_id,
            'year'       => $this->year,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        $this->classroom->update([
            'level_id'   => $this->level_id,
            'grade_id'   => $this->grade_id,
            'section_id' => $this->section_id,
            'year'       => $this->year,
        ]);
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->classroom  = null;
        $this->level_id   = '';
        $this->grade_id   = '';
        $this->section_id = '';
        $this->year       = '';
    }
}
