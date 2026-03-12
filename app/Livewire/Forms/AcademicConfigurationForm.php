<?php

namespace App\Livewire\Forms;

use App\Models\AcademicConfiguration;
use Illuminate\Validation\Rule;
use Livewire\Form;

class AcademicConfigurationForm extends Form
{
    public ?AcademicConfiguration $configuration = null;

    public string $year = '';
    public string $mode = 'free';

    public function rules(): array
    {
        return [
            'year' => [
                'required',
                'digits:4',
                'integer',
                Rule::unique('academic_configurations', 'year')->ignore($this->configuration),
            ],
            'mode' => ['required', 'in:free,assigned'],
        ];
    }

    protected $messages = [
        'year.required' => 'El año es obligatorio.',
        'year.digits'   => 'El año debe tener exactamente 4 dígitos.',
        'year.unique'   => 'Ya existe una configuración para ese año.',
        'mode.required' => 'El modo es obligatorio.',
        'mode.in'       => 'El modo seleccionado no es válido.',
    ];

    public function setConfiguration(AcademicConfiguration $configuration): void
    {
        $this->configuration = $configuration;
        $this->year          = $configuration->year;
        $this->mode          = $configuration->mode;
    }

    public function store(): AcademicConfiguration
    {
        $this->validate();

        return AcademicConfiguration::create([
            'year' => $this->year,
            'mode' => $this->mode,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        $this->configuration->update([
            'year' => $this->year,
            'mode' => $this->mode,
        ]);
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->configuration = null;
        $this->year          = '';
        $this->mode          = 'free';
    }
}
