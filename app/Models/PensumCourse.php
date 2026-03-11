<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PensumCourse extends Model
{
    use HasFactory;

    protected $fillable = ['pensum_id', 'course_id', 'parent_id', 'units', 'is_main', 'ordering'];

    protected $casts = [
        'units'   => 'array',
        'is_main' => 'boolean',
    ];

    public function pensum(): BelongsTo
    {
        return $this->belongsTo(Pensum::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PensumCourse::class, 'parent_id');
    }

    public function subCourses(): HasMany
    {
        return $this->hasMany(PensumCourse::class, 'parent_id');
    }
}
