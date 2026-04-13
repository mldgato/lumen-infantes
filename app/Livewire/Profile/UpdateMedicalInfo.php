<?php

namespace App\Livewire\Profile;

use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UpdateMedicalInfo extends Component
{
    public $takes_medication = false;

    public $medication_description;

    public $has_disease = false;

    public $disease_description;

    public $has_allergies = false;

    public $allergies_description;

    public $had_surgery = false;

    public $surgery_description;

    public $blood_type;

    public $weight;

    public $height;

    public function mount(): void
    {
        if (Auth::user()->hasRole('Estudiante')) {
            abort(403, 'Los estudiantes no pueden editar su ficha médica.');
        }

        $record = Auth::user()->medicalRecord;

        if ($record) {
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
        }
    }

    public function updateMedical(): void
    {
        $this->validate([
            'takes_medication' => 'boolean',
            'medication_description' => 'nullable|string',
            'has_disease' => 'boolean',
            'disease_description' => 'nullable|string',
            'has_allergies' => 'boolean',
            'allergies_description' => 'nullable|string',
            'had_surgery' => 'boolean',
            'surgery_description' => 'nullable|string',
            'blood_type' => 'nullable|string|max:10',
            'weight' => 'nullable|numeric|min:0|max:500',
            'height' => 'nullable|numeric|min:0|max:3',
        ]);

        $record = Auth::user()->medicalRecord;

        $newData = [
            'takes_medication' => $this->takes_medication,
            'medication_description' => $this->takes_medication ? $this->medication_description : null,
            'has_disease' => $this->has_disease,
            'disease_description' => $this->has_disease ? $this->disease_description : null,
            'has_allergies' => $this->has_allergies,
            'allergies_description' => $this->has_allergies ? $this->allergies_description : null,
            'had_surgery' => $this->had_surgery,
            'surgery_description' => $this->had_surgery ? $this->surgery_description : null,
            'blood_type' => $this->blood_type,
            'weight' => $this->weight,
            'height' => $this->height,
        ];

        $changed = [];

        if ($record) {
            $oldData = [
                'takes_medication' => $record->takes_medication,
                'medication_description' => $record->medication_description,
                'has_disease' => $record->has_disease,
                'disease_description' => $record->disease_description,
                'has_allergies' => $record->has_allergies,
                'allergies_description' => $record->allergies_description,
                'had_surgery' => $record->had_surgery,
                'surgery_description' => $record->surgery_description,
                'blood_type' => $record->blood_type,
                'weight' => $record->weight,
                'height' => $record->height,
            ];

            foreach ($newData as $field => $newValue) {
                if ($oldData[$field] != $newValue) {
                    $changed[$field] = ['old' => $oldData[$field], 'new' => $newValue];
                }
            }
        }

        Auth::user()->medicalRecord()->updateOrCreate(
            ['user_id' => Auth::id()],
            $newData,
        );

        if (! empty($changed)) {
            AuditService::medicalRecordUpdated(Auth::user(), $changed);
        }

        session()->flash('profile_message', 'Ficha médica actualizada.');
        $this->redirect(route('profile'));
    }

    public function render()
    {
        return view('livewire.profile.update-medical-info');
    }
}
