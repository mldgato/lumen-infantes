<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionAcademicScore extends Model
{
    protected $fillable = [
        'admission_application_id',
        'admission_course_id',
        'score',
        'user_id',
    ];

    public function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(AdmissionCourse::class, 'admission_course_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
