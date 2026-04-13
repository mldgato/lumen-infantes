<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'personal_code',
        'carne',
        'is_own_guardian',
    ];

    protected $casts = [
        'is_own_guardian' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class)
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function gradeBookScores(): HasMany
    {
        return $this->hasMany(GradeBookScore::class);
    }

    public function gradeBookTotals(): HasMany
    {
        return $this->hasMany(GradeBookTotal::class);
    }

    public function dataUpdates(): HasMany
    {
        return $this->hasMany(StudentDataUpdate::class);
    }
}
