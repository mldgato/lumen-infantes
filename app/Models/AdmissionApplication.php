<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdmissionApplication extends Model
{
    protected $fillable = [
        'year',
        'level_id',
        'grade_id',
        'student_first_name',
        'student_second_name',
        'student_first_surname',
        'student_second_surname',
        'student_birthdate',
        'student_address',
        'student_previous_school',
        'student_religion',
        'father_first_name',
        'father_last_name',
        'father_phone',
        'father_workplace',
        'father_nit',
        'father_profession',
        'mother_first_name',
        'mother_last_name',
        'mother_phone',
        'mother_workplace',
        'mother_nit',
        'mother_profession',
        'guardian_type',
        'guardian_name',
        'guardian_phone',
        'guardian_nit',
        'guardian_email',
        'referral_source',
        'sons_count',
        'sons_ages',
        'daughters_count',
        'daughters_ages',
        'url_documents',
        'url_payment',
        'current_status',
        'ip_address',
    ];

    public function casts(): array
    {
        return [
            'student_birthdate' => 'date',
            'sons_count' => 'integer',
            'daughters_count' => 'integer',
        ];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(AdmissionApplicationStatus::class);
    }

    public function latestStatus(): HasOne
    {
        return $this->hasOne(AdmissionApplicationStatus::class)->latestOfMany();
    }

    public function documents(): HasOne
    {
        return $this->hasOne(AdmissionApplicationDocument::class);
    }

    public function billing(): HasOne
    {
        return $this->hasOne(AdmissionBilling::class);
    }

    public function psychometric(): HasOne
    {
        return $this->hasOne(AdmissionPsychometric::class);
    }

    public function academicScores(): HasMany
    {
        return $this->hasMany(AdmissionAcademicScore::class);
    }

    public function guardianNit(): ?string
    {
        return match ($this->guardian_type) {
            'father' => $this->father_nit,
            'mother' => $this->mother_nit,
            'other' => $this->guardian_nit,
            default => null,
        };
    }

    public function fullStudentName(): string
    {
        return trim(implode(' ', array_filter([
            $this->student_first_name,
            $this->student_second_name,
            $this->student_first_surname,
            $this->student_second_surname,
        ])));
    }

    public function guardianTypeLabel(): string
    {
        return match ($this->guardian_type) {
            'father' => 'Padre',
            'mother' => 'Madre',
            'other' => 'Otro encargado',
            default => $this->guardian_type,
        };
    }

    public function statusLabel(): string
    {
        return AdmissionApplicationStatus::labelFor($this->current_status ?? 'pending');
    }

    public function statusColor(): string
    {
        return AdmissionApplicationStatus::colorFor($this->current_status ?? 'pending');
    }

    public function isPending(): bool
    {
        return ($this->current_status ?? 'pending') === 'pending';
    }

    public function isEmailed(): bool
    {
        return $this->current_status === 'emailed';
    }

    public function isReviewed(): bool
    {
        return $this->current_status === 'reviewed';
    }

    public function isBilled(): bool
    {
        return $this->current_status === 'billed';
    }

    public function isPsychometric(): bool
    {
        return $this->current_status === 'psychometric';
    }

    public function isAcademic(): bool
    {
        return $this->current_status === 'academic';
    }

    public function isAccepted(): bool
    {
        return $this->current_status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->current_status === 'rejected';
    }
}
