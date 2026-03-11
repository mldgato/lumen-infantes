<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    protected $fillable = ['grade_name', 'ordering'];

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function pensums(): HasMany
    {
        return $this->hasMany(Pensum::class);
    }
}
