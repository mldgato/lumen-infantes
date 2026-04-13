<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDataUpdate extends Model
{
    protected $fillable = [
        'student_id',
        'year',
        'completed_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'year' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
