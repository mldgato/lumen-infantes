<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdmissionCourse extends Model
{
    protected $fillable = ['name', 'ordering'];

    public function casts(): array
    {
        return [
            'ordering' => 'integer',
        ];
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AdmissionAcademicScore::class);
    }
}
