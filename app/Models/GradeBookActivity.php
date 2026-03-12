<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeBookActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_book_id',
        'activity_type_id',
        'name',
        'max_points',
        'ordering',
    ];

    protected $casts = [
        'max_points' => 'decimal:2',
    ];

    public function gradeBook(): BelongsTo
    {
        return $this->belongsTo(GradeBook::class);
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(GradeBookScore::class);
    }
}
