<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionBilling extends Model
{
    protected $fillable = [
        'admission_application_id',
        'invoice_number',
        'invoice_date',
        'user_id',
    ];

    public function casts(): array
    {
        return [
            'invoice_date' => 'date',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
