<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = ['level_id', 'grade_id', 'section_id', 'year'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function courseAssignments(): HasMany
    {
        return $this->hasMany(ClassroomCourseAssignment::class);
    }

    public function pensum()
    {
        return $this->hasOneThrough(
            Pensum::class,
            Grade::class,
            'id',
            'grade_id',
            'grade_id',
            'id'
        )->whereColumn('pensums.year', 'classrooms.year');
    }
}
