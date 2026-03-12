<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_extra'];

    protected $casts = [
        'is_extra' => 'boolean',
    ];

    public function configurationActivities(): HasMany
    {
        return $this->hasMany(AcademicConfigurationActivity::class);
    }
}
