<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GuardianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'birthplace' => fake()->city(),
            'birthdate' => fake()->dateTimeBetween('-60 years', '-30 years')->format('Y-m-d'),
            'nationality' => 'Guatemalteca',
            'cui' => fake()->unique()->numerify('#############'),
            'cui_extended_in' => fake()->city(),
            'profession' => fake()->jobTitle(),
            'residence_address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'company_name' => fake()->optional()->company(),
            'company_address' => fake()->optional()->address(),
            'company_phone' => fake()->optional()->phoneNumber(),
        ];
    }
}
