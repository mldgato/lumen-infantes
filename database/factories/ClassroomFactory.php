<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassroomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'level_id'   => Level::inRandomOrder()->first()->id,
            'grade_id'   => Grade::inRandomOrder()->first()->id,
            'section_id' => Section::inRandomOrder()->first()->id,
            'year'       => '2026',
        ];
    }
}
