<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class)
            ->withPivot('relationship_type')
            ->withTimestamps();
    }
}
