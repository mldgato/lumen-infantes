<?php

use App\Livewire\Profile\UpdateMedicalInfo;
use App\Models\User;
use Livewire\Livewire;

test('updating medical info creates an audit log entry for changed fields', function () {
    $user = User::factory()->create();
    $user->medicalRecord()->create([
        'takes_medication' => false,
        'medication_description' => null,
        'has_disease' => false,
        'disease_description' => null,
        'has_allergies' => false,
        'allergies_description' => null,
        'had_surgery' => false,
        'surgery_description' => null,
        'blood_type' => 'O+',
        'weight' => 70.0,
        'height' => 1.70,
    ]);

    $this->actingAs($user);

    Livewire::test(UpdateMedicalInfo::class)
        ->set('takes_medication', true)
        ->set('medication_description', 'Ibuprofeno 400mg')
        ->set('has_disease', false)
        ->set('has_allergies', false)
        ->set('had_surgery', false)
        ->set('blood_type', 'O+')
        ->set('weight', 70.0)
        ->set('height', 1.70)
        ->call('updateMedical')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'event' => 'updated',
        'module' => 'Perfil',
    ]);
});

test('no audit log is created when medical info has no changes', function () {
    $user = User::factory()->create();
    $user->medicalRecord()->create([
        'takes_medication' => false,
        'medication_description' => null,
        'has_disease' => false,
        'disease_description' => null,
        'has_allergies' => false,
        'allergies_description' => null,
        'had_surgery' => false,
        'surgery_description' => null,
        'blood_type' => 'O+',
        'weight' => 70.0,
        'height' => 1.70,
    ]);

    $this->actingAs($user);

    Livewire::test(UpdateMedicalInfo::class)
        ->set('takes_medication', false)
        ->set('has_disease', false)
        ->set('has_allergies', false)
        ->set('had_surgery', false)
        ->set('blood_type', 'O+')
        ->set('weight', 70.0)
        ->set('height', 1.70)
        ->call('updateMedical')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('audit_logs', [
        'user_id' => $user->id,
        'module' => 'Perfil',
    ]);
});
