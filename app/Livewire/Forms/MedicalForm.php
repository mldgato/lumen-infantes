<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\MedicalRecord;

class MedicalForm extends Form
{
    public ?MedicalRecord $medicalRecord = null;

    public $takes_medication = false;
    public $medication_description = '';
    public $has_disease = false;
    public $disease_description;
    public $has_allergies = false;
    public $allergies_description = '';
    public $had_surgery = false;
    public $surgery_description = '';
    public $blood_type = '';
    public $weight = '';
    public $height = '';

    public function rules()
    {
        return [
            'takes_medication' => 'boolean',
            'medication_description' => 'nullable|string',
            'has_disease' => 'boolean',
            'disease_description' => 'nullable|string',
            'has_allergies' => 'boolean',
            'allergies_description' => 'nullable|string',
            'had_surgery' => 'boolean',
            'surgery_description' => 'nullable|string',
            'blood_type' => 'nullable|string|in:O+,O-,A+,A-,B+,B-,AB+,AB-',
            'weight' => 'nullable|numeric|min:0|max:500',
            'height' => 'nullable|numeric|min:0|max:3',
        ];
    }

    public function setMedicalRecord(?MedicalRecord $record)
    {
        if ($record) {
            $this->medicalRecord = $record;
            $this->takes_medication = $record->takes_medication;
            $this->medication_description = $record->medication_description;
            $this->has_disease = $record->has_disease;
            $this->disease_description = $record->disease_description;
            $this->has_allergies = $record->has_allergies;
            $this->allergies_description = $record->allergies_description;
            $this->had_surgery = $record->had_surgery;
            $this->surgery_description = $record->surgery_description;
            $this->blood_type = $record->blood_type;
            $this->weight = $record->weight;
            $this->height = $record->height;
        } else {
            $this->resetForm();
        }
    }

    public function save($userId)
    {
        $this->validate();

        MedicalRecord::updateOrCreate(
            ['user_id' => $userId],
            [
                'takes_medication'       => $this->takes_medication,
                'medication_description' => $this->takes_medication ? $this->medication_description : null,
                'has_disease'            => $this->has_disease,
                'disease_description'    => $this->has_disease    ? $this->disease_description    : null,
                'has_allergies'          => $this->has_allergies,
                'allergies_description'  => $this->has_allergies  ? $this->allergies_description  : null,
                'had_surgery'            => $this->had_surgery,
                'surgery_description'    => $this->had_surgery    ? $this->surgery_description    : null,
                'blood_type'             => $this->blood_type     ?: null,
                'weight'                 => $this->weight         ?: null,
                'height'                 => $this->height         ?: null,
            ]
        );
    }

    public function resetForm()
    {
        $this->reset();
        $this->medicalRecord = null;
    }
}
