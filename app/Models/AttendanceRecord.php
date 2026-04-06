<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = ['classroom_course_assignment_id', 'date'];

    protected $casts = [
        'date' => 'date',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ClassroomCourseAssignment::class, 'classroom_course_assignment_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(AttendanceEntry::class);
    }
}
