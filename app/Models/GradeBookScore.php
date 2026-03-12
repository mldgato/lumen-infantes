<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeBookScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_book_activity_id',
        'student_id',
        'score',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(GradeBookActivity::class, 'grade_book_activity_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
