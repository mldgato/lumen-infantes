<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicConfigurationActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_configuration_id',
        'activity_type_id',
        'quantity',
        'points_each',
    ];

    protected $casts = [
        'points_each' => 'decimal:2',
    ];

    public function academicConfiguration(): BelongsTo
    {
        return $this->belongsTo(AcademicConfiguration::class);
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }
}
