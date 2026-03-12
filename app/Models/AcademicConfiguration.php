<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicConfiguration extends Model
{
    use HasFactory;

    protected $fillable = ['year', 'mode'];

    public function activities(): HasMany
    {
        return $this->hasMany(AcademicConfigurationActivity::class);
    }
}
