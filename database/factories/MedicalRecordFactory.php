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
            'has_disease' => $hasDisease = fake()->boolean(10), // Nueva
            'disease_description' => $hasDisease ? fake()->sentence() : null, // Nueva
            'takes_medication' => fake()->boolean(20),
            'medication_description' => fake()->boolean(20) ? fake()->sentence() : null, // Nueva
            'has_allergies' => fake()->boolean(30),
            'allergies_description' => fake()->boolean(30) ? fake()->sentence() : null, // Nueva
            'had_surgery' => fake()->boolean(15),
            'surgery_description' => fake()->boolean(15) ? fake()->sentence() : null, // Nueva
            'blood_type' => fake()->randomElement(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-']),
            'weight' => fake()->randomFloat(2, 40, 100),
            'height' => fake()->randomFloat(2, 1.20, 1.90),
        ];
    }
}
