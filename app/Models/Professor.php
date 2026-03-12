<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Professor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hire_date',
        'nit',
        'teaching_cedula',
        'igss_affiliation',
        'title',
        'bachelor_degree',
        'spouse_name',
        'spouse_phone',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courseAssignments(): HasMany
    {
        return $this->hasMany(ClassroomCourseAssignment::class);
    }
}
