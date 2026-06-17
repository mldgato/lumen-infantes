<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['level_name' => 'Preprimaria',     'ordering' => 1],
            ['level_name' => 'Primaria', 'ordering' => 2],
            ['level_name' => 'Básicos',     'ordering' => 3],
            ['level_name' => 'Diversificado', 'ordering' => 4],
        ];

        foreach ($levels as $level) {
            Level::create($level);
        }
    }
}
