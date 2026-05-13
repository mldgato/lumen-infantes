<?php

namespace App\Livewire;

use App\Models\EnrollmentPeriod;
use App\Models\Student;
use App\Models\StudentDataUpdate;
use App\Notifications\StudentDataUpdateNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Component;

class StudentDataRequest extends Component
{
    public string $code = '';

    public string $email = '';

    public bool $submitted = false;

    public bool $alreadyUpdated = false;

    public bool $periodActive = true;

    public ?string $updatedAtLabel = null;

    public function mount(): void
    {
        $this->periodActive = EnrollmentPeriod::activeForDataUpdates();
    }

    public function submit(): void
    {
        if (! $this->periodActive) {
            return;
        }

        $this->validate([
            'code' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
        ], [
            'code.required' => 'Ingresa tu código personal o carné.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
        ]);

        $student = Student::where('personal_code', $this->code)
            ->orWhere('carne', $this->code)
            ->first();

        if (! $student) {
            $this->addError('code', 'No se encontró ningún estudiante con ese código o carné.');

            return;
        }

        $existing = StudentDataUpdate::where('student_id', $student->id)
            ->where('year', now()->year)
            ->first();

        if ($existing) {
            $this->alreadyUpdated = true;
            $this->updatedAtLabel = $existing->completed_at->format('d/m/Y');

            return;
        }

        $token = Str::random(60);

        Cache::put("student_update_{$token}", [
            'student_id' => $student->id,
            'email_nuevo' => $this->email,
        ], now()->addMinutes(60));

        Notification::route('mail', $this->email)
            ->notify(new StudentDataUpdateNotification($token, $student->user->first_name));

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.student-data-request')
            ->extends('layouts.public')
            ->section('content');
    }
}
