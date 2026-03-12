<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeBookTotal extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_book_id',
        'student_id',
        'normal_points',
        'extra_points',
        'total_points',
    ];

    protected $casts = [
        'normal_points' => 'decimal:2',
        'extra_points'  => 'decimal:2',
        'total_points'  => 'decimal:2',
    ];

    public function gradeBook(): BelongsTo
    {
        return $this->belongsTo(GradeBook::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
