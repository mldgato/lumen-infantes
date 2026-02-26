<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'birthplace',
        'birthdate',
        'nationality',
        'cui',
        'cui_extended_in',
        'profession',
        'residence_address',
        'phone',
        'email',
        'company_name',
        'company_address',
        'company_phone',
    ];

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
