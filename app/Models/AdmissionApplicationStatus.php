<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionApplicationStatus extends Model
{
    protected $fillable = ['admission_application_id', 'status', 'notes', 'user_id'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function labelFor(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendiente',
            'emailed' => 'Correo enviado',
            'reviewed' => 'Documentación completa',
            'accepted' => 'Aceptado',
            'rejected' => 'Rechazado',
            default => $status,
        };
    }

    public static function colorFor(string $status): string
    {
        return match ($status) {
            'pending' => 'warning',
            'emailed' => 'primary',
            'reviewed' => 'info',
            'accepted' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}
