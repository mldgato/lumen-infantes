<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'takes_medication' => 'boolean',
        'has_allergies' => 'boolean',
        'had_surgery' => 'boolean',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
