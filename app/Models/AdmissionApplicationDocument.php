<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionApplicationDocument extends Model
{
    protected $fillable = [
        'admission_application_id',
        'payment_receipt',
        'grades_certificate',
        'registration_form',
        'reference_letter',
        'photo',
        'completed_at',
    ];

    public function casts(): array
    {
        return [
            'payment_receipt' => 'boolean',
            'grades_certificate' => 'boolean',
            'registration_form' => 'boolean',
            'reference_letter' => 'boolean',
            'photo' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }

    public function isComplete(): bool
    {
        return $this->payment_receipt
            && $this->grades_certificate
            && $this->registration_form
            && $this->reference_letter
            && $this->photo;
    }

    public function receivedCount(): int
    {
        return (int) $this->payment_receipt
            + (int) $this->grades_certificate
            + (int) $this->registration_form
            + (int) $this->reference_letter
            + (int) $this->photo;
    }

    /** @return array<string, string> field => label */
    public static function fields(): array
    {
        return [
            'payment_receipt' => 'Boleta de pago',
            'grades_certificate' => 'Calificaciones año anterior',
            'registration_form' => 'Ficha de inscripción',
            'reference_letter' => 'Carta de referencias',
            'photo' => 'Fotografía',
        ];
    }
}
