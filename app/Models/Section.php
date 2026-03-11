<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = ['section_name', 'ordering'];

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }
}
