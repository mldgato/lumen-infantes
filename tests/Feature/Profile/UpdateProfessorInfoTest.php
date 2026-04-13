<?php

use App\Livewire\Profile\UpdateProfessorInfo;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('updating professor info creates an audit log entry for changed fields', function () {
    Role::create(['name' => 'Profesor', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole('Profesor');
    $user->professor()->create([
        'hire_date' => '2020-01-15',
        'nit' => '1234567-8',
        'teaching_cedula' => 'A-01-12345',
        'igss_affiliation' => '12345678901',
        'title' => 'Profesor de Enseñanza Media',
        'bachelor_degree' => 'Licenciatura en Educación',
        'spouse_name' => null,
        'spouse_phone' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(UpdateProfessorInfo::class)
        ->set('hire_date', '2020-01-15')
        ->set('nit', '9999999-0')
        ->set('teaching_cedula', 'A-01-12345')
        ->set('igss_affiliation', '12345678901')
        ->set('title', 'Profesor de Enseñanza Media')
        ->set('bachelor_degree', 'Licenciatura en Educación')
        ->set('spouse_name', null)
        ->set('spouse_phone', null)
        ->call('updateProfessor')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'event' => 'updated',
        'module' => 'Perfil',
    ]);
});

test('no audit log is created when professor info has no changes', function () {
    Role::create(['name' => 'Profesor', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole('Profesor');
    $user->professor()->create([
        'hire_date' => '2020-01-15',
        'nit' => '1234567-8',
        'teaching_cedula' => 'A-01-12345',
        'igss_affiliation' => '12345678901',
        'title' => 'Profesor de Enseñanza Media',
        'bachelor_degree' => 'Licenciatura en Educación',
        'spouse_name' => null,
        'spouse_phone' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(UpdateProfessorInfo::class)
        ->set('hire_date', '2020-01-15')
        ->set('nit', '1234567-8')
        ->set('teaching_cedula', 'A-01-12345')
        ->set('igss_affiliation', '12345678901')
        ->set('title', 'Profesor de Enseñanza Media')
        ->set('bachelor_degree', 'Licenciatura en Educación')
        ->set('spouse_name', null)
        ->set('spouse_phone', null)
        ->call('updateProfessor')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('audit_logs', [
        'user_id' => $user->id,
        'module' => 'Perfil',
    ]);
});
