<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'takes_medication' => fake()->boolean(20),
            'has_allergies' => fake()->boolean(30),
            'had_surgery' => fake()->boolean(15),
            'blood_type' => fake()->randomElement(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-']),
            'weight' => fake()->randomFloat(2, 40, 100),
            'height' => fake()->randomFloat(2, 1.20, 1.90),
        ];
    }
}
