<?php

use App\Livewire\Profile\UpdateProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('password change creates an audit log entry', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    Livewire::test(UpdateProfile::class)
        ->set('current_password', 'password')
        ->set('password', 'new-password123')
        ->set('password_confirmation', 'new-password123')
        ->call('updatePassword')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'event' => 'password_changed',
        'module' => 'Seguridad',
    ]);
});

test('photo update creates an audit log entry', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(UpdateProfile::class)
        ->set('photo', UploadedFile::fake()->image('avatar.jpg'))
        ->assertHasNoErrors();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'event' => 'updated',
        'module' => 'Perfil',
    ]);
});
