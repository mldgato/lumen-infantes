<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AcademicConfiguration;

class GradeBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'classroom_course_assignment_id',
        'academic_configuration_id',
        'status',
        'rejection_reason',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ClassroomCourseAssignment::class, 'classroom_course_assignment_id');
    }

    public function academicConfiguration(): BelongsTo
    {
        return $this->belongsTo(AcademicConfiguration::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(GradeBookActivity::class)->orderBy('ordering');
    }

    public function totals(): HasMany
    {
        return $this->hasMany(GradeBookTotal::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getImprovementConfig(): AcademicConfiguration
    {
        return $this->academicConfiguration;
    }
}
