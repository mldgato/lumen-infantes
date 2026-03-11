<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pensum extends Model
{
    use HasFactory;

    protected $fillable = ['grade_id', 'year', 'units'];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function pensumCourses(): HasMany
    {
        return $this->hasMany(PensumCourse::class);
    }

    public function mainCourses(): HasMany
    {
        return $this->hasMany(PensumCourse::class)
            ->whereNull('parent_id')
            ->orderBy('ordering');
    }
}
