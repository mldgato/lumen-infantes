<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cui' => fake()->unique()->numerify('#############'),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional()->firstName(),
            'surname' => fake()->lastName(),
            'second_surname' => fake()->optional()->lastName(),
            'married_surname' => null,
            'name' => '', // Se llenará automáticamente por el método boot() en el modelo User
            'civil_status' => fake()->randomElement(['Soltero', 'Casado', 'Viudo', 'Divorciado']),
            'birthdate' => fake()->dateTimeBetween('-50 years', '-10 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['Masculino', 'Femenino']),

            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),

            'cellphone' => fake()->phoneNumber(),
            'personal_email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'is_active' => fake()->boolean(100),

            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn(array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
