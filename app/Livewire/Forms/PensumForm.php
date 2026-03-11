<?php

namespace App\Livewire\Forms;

use App\Models\Pensum;
use Illuminate\Validation\Rule;
use Livewire\Form;

class PensumForm extends Form
{
    public ?Pensum $pensum = null;

    public int|string $grade_id = '';
    public string $year         = '';
    public int|string $units    = '';

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
            'units' => ['required', 'integer', 'min:1', 'max:6'],
        ];
    }

    protected $messages = [
        'grade_id.required' => 'El grado es obligatorio.',
        'grade_id.exists'   => 'El grado seleccionado no es válido.',
        'year.required'     => 'El año es obligatorio.',
        'year.digits'       => 'El año debe tener exactamente 4 dígitos.',
        'year.unique'       => 'Ya existe un pénsum para este grado y año.',
        'units.required'    => 'La cantidad de unidades es obligatoria.',
        'units.min'         => 'Debe haber al menos 1 unidad.',
        'units.max'         => 'El máximo permitido es 6 unidades.',
    ];

    public function setPensum(Pensum $pensum): void
    {
        $this->pensum   = $pensum;
        $this->grade_id = $pensum->grade_id;
        $this->year     = $pensum->year;
        $this->units    = $pensum->units;
    }

    public function store(): Pensum
    {
        $this->validate();

        return Pensum::create([
            'grade_id' => $this->grade_id,
            'year'     => $this->year,
            'units'    => $this->units,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        $this->pensum->update([
            'grade_id' => $this->grade_id,
            'year'     => $this->year,
            'units'    => $this->units,
        ]);
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->pensum   = null;
        $this->grade_id = '';
        $this->year     = '';
        $this->units    = '';
    }
}
