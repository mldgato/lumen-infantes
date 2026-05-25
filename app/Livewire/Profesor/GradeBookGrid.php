<?php

namespace App\Livewire\Profesor;

use App\Models\GradeBook;
use App\Models\GradeBookScore;
use App\Models\GradeBookTotal;
use App\Models\Student;
use App\Services\GradeBookCalculationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class GradeBookGrid extends Component
{
    public GradeBook $gradeBook;

    public function mount(GradeBook $gradeBook): void
    {
        $professor = Auth::user()->professor;

        if (! $professor) {
            abort(403);
        }

        $this->gradeBook = $gradeBook->load([
            'assignment.classroom.grade',
            'assignment.classroom.section',
            'assignment.classroom.level',
            'assignment.pensumCourse.course',
            'activities.activityType',
            'academicConfiguration',
        ]);

        if ($this->gradeBook->assignment->professor_id !== $professor->id) {
            abort(403);
        }
    }

    public function saveGrid(array $grid): void
    {
        if ($this->gradeBook->status !== 'open') {
            $this->dispatch('showAlert', [
                'title' => 'Cuadro no editable',
                'message' => 'Solo se pueden guardar notas en cuadros con estado Abierto.',
                'type' => 'warning',
            ]);

            return;
        }

        $activities = $this->gradeBook->activities->keyBy('id');
        $config = $this->gradeBook->academicConfiguration;
        $hasImprovement = $config->improvement_type !== 'none';
        $students = $this->getStudents();

        $rules = [];
        $messages = [];

        foreach ($students as $student) {
            foreach ($activities as $activity) {
                $scoreVal = data_get($grid, "{$student->id}.{$activity->id}.score");
                $ruleKey = "grid.{$student->id}.{$activity->id}";

                if (! is_null($scoreVal) && $scoreVal !== '') {
                    $rules["{$ruleKey}.score"] = "numeric|min:0|max:{$activity->max_points}";
                    $messages["{$ruleKey}.score.max"] = "{$student->user->first_name} — '{$activity->name}': supera el máximo ({$activity->max_points} pts).";
                    $messages["{$ruleKey}.score.min"] = 'La nota no puede ser negativa.';
                }

                if ($hasImprovement) {
                    $impVal = data_get($grid, "{$student->id}.{$activity->id}.improvement");
                    if (! is_null($impVal) && $impVal !== '' && (float) $impVal > 0) {
                        $currentScore = (is_null($scoreVal) || $scoreVal === '') ? 0.0 : (float) $scoreVal;
                        $maxImprovement = $config->maxImprovementScore($currentScore, (float) $activity->max_points);
                        $rules["{$ruleKey}.improvement"] = "numeric|min:0|max:{$maxImprovement}";
                        $messages["{$ruleKey}.improvement.max"] = "{$student->user->first_name} — '{$activity->name}': mejora supera el máximo permitido ({$maxImprovement} pts).";
                    }
                }
            }
        }

        if (! empty($rules)) {
            $validator = Validator::make(['grid' => $grid], $rules, $messages);

            if ($validator->fails()) {
                $this->dispatch('showAlert', [
                    'title' => 'Error en las notas',
                    'message' => $validator->errors()->first(),
                    'type' => 'error',
                ]);

                return;
            }
        }

        DB::transaction(function () use ($grid, $activities, $config, $hasImprovement, $students): void {
            foreach ($students as $student) {
                foreach ($activities as $activityId => $activity) {
                    $scoreVal = data_get($grid, "{$student->id}.{$activityId}.score");

                    if (is_null($scoreVal) || $scoreVal === '') {
                        continue;
                    }

                    $improvementVal = null;
                    if ($hasImprovement) {
                        $imp = data_get($grid, "{$student->id}.{$activityId}.improvement");
                        if (! is_null($imp) && $imp !== '' && (float) $imp > 0) {
                            $improvementVal = (float) $imp;
                        }
                    }

                    GradeBookScore::updateOrCreate(
                        ['grade_book_activity_id' => $activityId, 'student_id' => $student->id],
                        ['score' => (float) $scoreVal, 'improvement_score' => $improvementVal]
                    );
                }
            }
        });

        GradeBookCalculationService::recalculateAll($this->gradeBook, $students);

        $this->dispatch('toastMessage', [
            'type' => 'success',
            'message' => 'Calificaciones guardadas correctamente.',
        ]);
    }

    public function getStudents()
    {
        return Student::whereHas('enrollments', function ($q): void {
            $q->where('classroom_id', $this->gradeBook->assignment->classroom_id)
                ->where('status', 'Activo');
        })
            ->join('users', 'students.user_id', '=', 'users.id')
            ->select('students.*')
            ->orderBy('users.surname')
            ->orderBy('users.second_surname')
            ->orderBy('users.first_name')
            ->orderBy('users.middle_name')
            ->with('user')
            ->get();
    }

    public function render()
    {
        $students = $this->getStudents();
        $activities = $this->gradeBook->activities->sortBy('ordering')->values();
        $config = $this->gradeBook->academicConfiguration;
        $hasImprovement = $config->improvement_type !== 'none';

        $activityIds = $activities->pluck('id');
        $scoresFlat = GradeBookScore::whereIn('grade_book_activity_id', $activityIds)->get();

        $initialGrid = [];
        foreach ($students as $student) {
            $initialGrid[$student->id] = [];
            foreach ($activities as $activity) {
                $record = $scoresFlat->first(
                    fn ($s) => $s->student_id === $student->id && $s->grade_book_activity_id === $activity->id
                );
                $initialGrid[$student->id][$activity->id] = [
                    'score'       => $record ? (string) $record->score : '',
                    'improvement' => ($record && ! is_null($record->improvement_score))
                        ? (string) $record->improvement_score
                        : '',
                ];
            }
        }

        $activitiesMeta = $activities->map(fn ($a) => [
            'id'        => $a->id,
            'maxPoints' => (float) $a->max_points,
            'isExtra'   => (bool) $a->activityType->is_extra,
        ])->values()->all();

        $totals = GradeBookTotal::where('grade_book_id', $this->gradeBook->id)
            ->get()->keyBy('student_id');

        return view('livewire.profesor.grade-book-grid', compact(
            'students',
            'activities',
            'config',
            'hasImprovement',
            'initialGrid',
            'activitiesMeta',
            'totals',
        ));
    }
}
