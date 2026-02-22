<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfessorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'nit' => fake()->numerify('#######-#'),
            'teaching_cedula' => fake()->bothify('?-##-#####'),
            'igss_affiliation' => fake()->numerify('###########'),
            'title' => 'Profesor de Enseñanza Media',
            'bachelor_degree' => 'Licenciatura en Educación',
            'spouse_name' => fake()->optional()->name(),
            'spouse_phone' => fake()->optional()->phoneNumber(),
        ];
    }
}
