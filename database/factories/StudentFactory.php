<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'personal_code' => fake()->bothify('???-####'),
            'carne' => fake()->numerify('202#-#####'),
            'is_own_guardian' => false,
        ];
    }
}
