<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeChangeRequestItem extends Model
{
    protected $fillable = [
        'grade_change_request_id',
        'student_id',
        'grade_book_activity_id',
        'old_score',
        'new_score',
        'old_improvement_score',
        'new_improvement_score',
    ];

    protected $casts = [
        'old_score'             => 'decimal:2',
        'new_score'             => 'decimal:2',
        'old_improvement_score' => 'decimal:2',
        'new_improvement_score' => 'decimal:2',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(GradeChangeRequest::class, 'grade_change_request_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(GradeBookActivity::class, 'grade_book_activity_id');
    }
}
