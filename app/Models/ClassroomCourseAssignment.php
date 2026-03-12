<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClassroomCourseAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['classroom_id', 'professor_id', 'pensum_course_id', 'unit'];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Professor::class);
    }

    public function pensumCourse(): BelongsTo
    {
        return $this->belongsTo(PensumCourse::class);
    }

    public function gradeBook(): HasOne
    {
        return $this->hasOne(GradeBook::class);
    }
}
