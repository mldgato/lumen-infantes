<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LevelFactory extends Factory
{
    public function definition(): array
    {
        $levels = ['Preprimaria', 'Primaria', 'Básicos', 'Diversificado'];

        return [
            'level_name' => $this->faker->unique()->randomElement($levels),
            'ordering'   => $this->faker->numberBetween(1, 10),
        ];
    }
}
