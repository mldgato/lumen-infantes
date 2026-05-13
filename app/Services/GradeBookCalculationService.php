<?php

namespace App\Services;

use App\Models\AcademicConfiguration;
use App\Models\GradeBook;
use App\Models\GradeBookActivity;
use App\Models\GradeBookTotal;
use Illuminate\Support\Collection;

class GradeBookCalculationService
{
    /**
     * Recalculates totals for all students in a grade book.
     * Accepts Student models or integer IDs.
     */
    public static function recalculateAll(GradeBook $gradeBook, iterable $students): void
    {
        $activities = GradeBookActivity::with(['scores', 'activityType'])
            ->where('grade_book_id', $gradeBook->id)
            ->get();

        $config = $gradeBook->academicConfiguration;

        foreach ($students as $student) {
            $studentId = is_object($student) ? $student->id : (int) $student;
            static::persistTotals($gradeBook->id, $studentId, $activities, $config);
        }
    }

    /**
     * Recalculates totals for specific student IDs, loading activities from DB.
     */
    public static function recalculateForStudents(GradeBook $gradeBook, iterable $studentIds): void
    {
        $activities = GradeBookActivity::with(['scores', 'activityType'])
            ->where('grade_book_id', $gradeBook->id)
            ->get();

        $config = $gradeBook->academicConfiguration;

        foreach ($studentIds as $studentId) {
            static::persistTotals($gradeBook->id, (int) $studentId, $activities, $config);
        }
    }

    private static function persistTotals(
        int $gradeBookId,
        int $studentId,
        Collection $activities,
        AcademicConfiguration $config
    ): void {
        $normalPoints = 0.0;
        $extraPoints = 0.0;

        foreach ($activities as $activity) {
            $score = $activity->scores->firstWhere('student_id', $studentId);
            $rawScore = $score ? (float) $score->score : 0.0;
            $improvement = $score ? $score->improvement_score : null;
            $effective = $config->effectiveScore($rawScore, $improvement, (float) $activity->max_points);

            if ($activity->activityType->is_extra) {
                $extraPoints += $effective;
            } else {
                $normalPoints += $effective;
            }
        }

        GradeBookTotal::updateOrCreate(
            ['grade_book_id' => $gradeBookId, 'student_id' => $studentId],
            [
                'normal_points' => $normalPoints,
                'extra_points' => $extraPoints,
                'total_points' => ceil($normalPoints + $extraPoints),
            ]
        );
    }
}
