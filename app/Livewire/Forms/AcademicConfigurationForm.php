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
    public string $improvement_type = 'full';
    public string $improvement_percentage = '';

    public function rules(): array
    {
        return [
            'year' => [
                'required',
                'digits:4',
                'integer',
                Rule::unique('academic_configurations', 'year')->ignore($this->configuration),
            ],
            'mode'                   => ['required', 'in:free,assigned'],
            'improvement_type'       => ['required', 'in:full,percentage,additive'],
            'improvement_percentage' => [
                'nullable',
                'numeric',
                'min:1',
                'max:100',
                $this->improvement_type === 'percentage' ? 'required' : 'nullable',
            ],
        ];
    }

    protected $messages = [
        'year.required'                    => 'El año es obligatorio.',
        'year.digits'                      => 'El año debe tener exactamente 4 dígitos.',
        'year.unique'                      => 'Ya existe una configuración para ese año.',
        'mode.required'                    => 'El modo es obligatorio.',
        'mode.in'                          => 'El modo seleccionado no es válido.',
        'improvement_type.required'        => 'El tipo de mejora es obligatorio.',
        'improvement_type.in'              => 'El tipo de mejora no es válido.',
        'improvement_percentage.required'  => 'El porcentaje es obligatorio cuando el tipo es Porcentaje.',
        'improvement_percentage.numeric'   => 'El porcentaje debe ser un número.',
        'improvement_percentage.min'       => 'El porcentaje debe ser al menos 1.',
        'improvement_percentage.max'       => 'El porcentaje no puede superar 100.',
    ];

    public function setConfiguration(AcademicConfiguration $configuration): void
    {
        $this->configuration          = $configuration;
        $this->year                   = $configuration->year;
        $this->mode                   = $configuration->mode;
        $this->improvement_type       = $configuration->improvement_type;
        $this->improvement_percentage = $configuration->improvement_percentage ?? '';
    }

    public function store(): AcademicConfiguration
    {
        $this->validate();

        return AcademicConfiguration::create([
            'year'                   => $this->year,
            'mode'                   => $this->mode,
            'improvement_type'       => $this->improvement_type,
            'improvement_percentage' => $this->improvement_type === 'percentage' ? $this->improvement_percentage : null,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        $this->configuration->update([
            'year'                   => $this->year,
            'mode'                   => $this->mode,
            'improvement_type'       => $this->improvement_type,
            'improvement_percentage' => $this->improvement_type === 'percentage' ? $this->improvement_percentage : null,
        ]);
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->configuration          = null;
        $this->year                   = '';
        $this->mode                   = 'free';
        $this->improvement_type       = 'full';
        $this->improvement_percentage = '';
    }
}
