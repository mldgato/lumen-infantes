<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['level_name' => 'Básicos',     'ordering' => 1],
            ['level_name' => 'Diversificado', 'ordering' => 2],
        ];

        foreach ($levels as $level) {
            Level::create($level);
        }
    }
}
