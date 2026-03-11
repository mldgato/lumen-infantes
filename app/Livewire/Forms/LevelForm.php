<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\Level;
use Illuminate\Validation\Rule;

class LevelForm extends Form
{
    public ?Level $level = null;

    public string $level_name = '';
    public int $ordering = 0;

    public function rules(): array
    {
        return [
            'level_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('levels', 'level_name')->ignore($this->level),
            ],
            'ordering' => ['required', 'integer', 'min:0'],
        ];
    }

    protected $messages = [
        'level_name.required' => 'El nombre del nivel es obligatorio.',
        'level_name.unique'   => 'Ya existe un nivel con ese nombre.',
        'ordering.required'   => 'El orden es obligatorio.',
        'ordering.integer'    => 'El orden debe ser un número entero.',
        'ordering.min'        => 'El orden debe ser mayor o igual a 0.',
    ];

    public function setLevel(Level $level): void
    {
        $this->level      = $level;
        $this->level_name = $level->level_name;
        $this->ordering   = $level->ordering;
    }

    public function store(): void
    {
        $this->validate();

        Level::create([
            'level_name' => $this->level_name,
            'ordering'   => $this->ordering,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        $this->level->update([
            'level_name' => $this->level_name,
            'ordering'   => $this->ordering,
        ]);
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->level   = null;
        $this->ordering = 0;
    }
}
