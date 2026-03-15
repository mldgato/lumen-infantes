<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pensum extends Model
{
    use HasFactory;

    protected $fillable = ['grade_id', 'year', 'units', 'unit_percentages'];

    protected $casts = [
        'unit_percentages' => 'array',
    ];

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

    public function getUnitPercentage(int $unit): float
    {
        $percentages = $this->unit_percentages;

        if (empty($percentages) || ! isset($percentages[$unit - 1])) {
            // Si no hay configuración, distribuir equitativamente
            return $this->units > 0 ? round(100 / $this->units, 4) : 0;
        }

        return (float) $percentages[$unit - 1];
    }
}
