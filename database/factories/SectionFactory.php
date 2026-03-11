<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    public function definition(): array
    {
        $sections = ['Sección A', 'Sección B', 'Sección C', 'Sección D'];

        return [
            'section_name' => $this->faker->unique()->randomElement($sections),
            'ordering'     => $this->faker->numberBetween(1, 10),
        ];
    }
}
