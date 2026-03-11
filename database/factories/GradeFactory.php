<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    public function definition(): array
    {
        $grades = [
            'Primero Básico',
            'Segundo Básico',
            'Tercero Básico',
            'Cuarto Bachillerato en Ciencias y Letras',
            'Quinto Bachillerato en Ciencias y Letras',
        ];

        return [
            'grade_name' => $this->faker->unique()->randomElement($grades),
            'ordering'   => $this->faker->numberBetween(1, 20),
        ];
    }
}
