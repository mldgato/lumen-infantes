<?php

namespace App\Livewire\Forms;

use App\Models\Section;
use Illuminate\Validation\Rule;
use Livewire\Form;

class SectionForm extends Form
{
    public ?Section $section = null;

    public string $section_name = '';
    public int $ordering = 0;

    public function rules(): array
    {
        return [
            'section_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sections', 'section_name')->ignore($this->section),
            ],
            'ordering' => ['required', 'integer', 'min:0'],
        ];
    }

    protected $messages = [
        'section_name.required' => 'El nombre de la sección es obligatorio.',
        'section_name.unique'   => 'Ya existe una sección con ese nombre.',
        'ordering.required'     => 'El orden es obligatorio.',
        'ordering.integer'      => 'El orden debe ser un número entero.',
        'ordering.min'          => 'El orden debe ser mayor o igual a 0.',
    ];

    public function setSection(Section $section): void
    {
        $this->section      = $section;
        $this->section_name = $section->section_name;
        $this->ordering     = $section->ordering;
    }

    public function store(): void
    {
        $this->validate();

        Section::create([
            'section_name' => $this->section_name,
            'ordering'     => $this->ordering,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        $this->section->update([
            'section_name' => $this->section_name,
            'ordering'     => $this->ordering,
        ]);
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->section  = null;
        $this->ordering = 0;
    }
}
