<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'mode',
        'improvement_type',
        'improvement_percentage',
    ];

    protected $casts = [
        'improvement_percentage' => 'decimal:2',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(AcademicConfigurationActivity::class);
    }

    /**
     * Calcula el punteo máximo permitido en la casilla de mejora.
     * 
     * @param float $score        Nota original del estudiante
     * @param float $maxPoints    Valor total de la actividad
     */
    public function maxImprovementScore(float $score, float $maxPoints): float
    {
        return match ($this->improvement_type) {
            'none'       => 0.0,
            'full'       => $maxPoints,
            'percentage' => round($maxPoints * ($this->improvement_percentage / 100), 2),
            'additive'   => max(0, $maxPoints - $score),
            default      => $maxPoints,
        };
    }

    /**
     * Calcula la nota efectiva del estudiante tomando en cuenta la mejora.
     *
     * @param float      $score            Nota original
     * @param float|null $improvementScore Nota de mejora
     * @param float      $maxPoints        Valor total de la actividad
     */
    public function effectiveScore(float $score, ?float $improvementScore, float $maxPoints): float
    {
        if (is_null($improvementScore)) {
            return $score;
        }

        $effective = match ($this->improvement_type) {
            'full', 'percentage' => max($score, $improvementScore),
            'additive'           => min($score + $improvementScore, $maxPoints),
            default              => $score,
        };

        return round($effective, 2);
    }
}
