<?php

namespace App\Livewire\Forms;

use App\Models\Pensum;
use Illuminate\Validation\Rule;
use Livewire\Form;

class PensumForm extends Form
{
    public ?Pensum $pensum = null;

    public int|string $grade_id       = '';
    public string $year               = '';
    public int|string $units          = '';
    public array $unit_percentages    = [];

    public function rules(): array
    {
        return [
            'grade_id' => ['required', 'exists:grades,id'],
            'year'     => [
                'required',
                'digits:4',
                'integer',
                Rule::unique('pensums')->where(fn($q) => $q->where('grade_id', $this->grade_id))
                    ->ignore($this->pensum),
            ],
            'units'             => ['required', 'integer', 'min:1', 'max:6'],
            'unit_percentages'  => ['required', 'array'],
            'unit_percentages.*' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    protected $messages = [
        'grade_id.required'          => 'El grado es obligatorio.',
        'grade_id.exists'            => 'El grado seleccionado no es válido.',
        'year.required'              => 'El año es obligatorio.',
        'year.digits'                => 'El año debe tener exactamente 4 dígitos.',
        'year.unique'                => 'Ya existe un pénsum para este grado y año.',
        'units.required'             => 'La cantidad de unidades es obligatoria.',
        'units.min'                  => 'Debe haber al menos 1 unidad.',
        'units.max'                  => 'El máximo permitido es 6 unidades.',
        'unit_percentages.required'  => 'Debe configurar los porcentajes de las unidades.',
        'unit_percentages.*.required' => 'Todos los porcentajes son obligatorios.',
        'unit_percentages.*.numeric' => 'Los porcentajes deben ser números.',
        'unit_percentages.*.min'     => 'El porcentaje no puede ser negativo.',
        'unit_percentages.*.max'     => 'El porcentaje no puede superar 100.',
    ];

    public function updatedUnits(): void
    {
        $this->initPercentages();
    }

    public function initPercentages(): void
    {
        $units = (int) $this->units;
        if ($units < 1) return;

        $base      = intdiv(100, $units);
        $remainder = 100 - ($base * $units);

        $this->unit_percentages = [];
        for ($i = 0; $i < $units; $i++) {
            $this->unit_percentages[] = $base + ($i === $units - 1 ? $remainder : 0);
        }
    }

    public function getPercentageSum(): int
    {
        return (int) array_sum($this->unit_percentages);
    }

    public function setPensum(Pensum $pensum): void
    {
        $this->pensum            = $pensum;
        $this->grade_id          = $pensum->grade_id;
        $this->year              = $pensum->year;
        $this->units             = $pensum->units;
        $this->unit_percentages  = $pensum->unit_percentages ?? [];

        // Si no tiene porcentajes configurados, inicializarlos
        if (empty($this->unit_percentages)) {
            $this->initPercentages();
        }
    }

    public function store(): Pensum
    {
        $this->validatePercentages();
        $this->validate();

        return Pensum::create([
            'grade_id'          => $this->grade_id,
            'year'              => $this->year,
            'units'             => $this->units,
            'unit_percentages'  => $this->unit_percentages,
        ]);
    }

    public function update(): void
    {
        $this->validatePercentages();
        $this->validate();

        $this->pensum->update([
            'grade_id'          => $this->grade_id,
            'year'              => $this->year,
            'units'             => $this->units,
            'unit_percentages'  => $this->unit_percentages,
        ]);
    }

    protected function validatePercentages(): void
    {
        $sum = $this->getPercentageSum();
        if ($sum !== 100) {
            $this->addError('unit_percentages', "Los porcentajes deben sumar exactamente 100%. Actualmente suman {$sum}%.");
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
            );
        }
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->pensum           = null;
        $this->grade_id         = '';
        $this->year             = '';
        $this->units            = '';
        $this->unit_percentages = [];
    }
}
