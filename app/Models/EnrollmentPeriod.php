<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'start_date',
        'end_date',
        'allow_enrollments',
        'allow_data_updates',
    ];

    public function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'allow_enrollments' => 'boolean',
            'allow_data_updates' => 'boolean',
        ];
    }

    public static function activeForEnrollments(): bool
    {
        $today = now()->toDateString();

        return static::where('allow_enrollments', true)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->exists();
    }

    public static function activeForDataUpdates(): bool
    {
        $today = now()->toDateString();

        return static::where('allow_data_updates', true)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->exists();
    }

    /**
     * Checks if a date range overlaps with any existing period that has the given flag active.
     * Excludes the given ID (for edit validation).
     */
    public static function hasOverlap(
        string $flag,
        string $startDate,
        string $endDate,
        ?int $excludeId = null
    ): bool {
        return static::where($flag, true)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}
