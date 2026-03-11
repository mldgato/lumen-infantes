<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['section_name' => 'Sección A', 'ordering' => 1],
            ['section_name' => 'Sección B', 'ordering' => 2],
            ['section_name' => 'Sección C', 'ordering' => 3],
            ['section_name' => 'Sección D', 'ordering' => 4],
        ];

        foreach ($sections as $section) {
            Section::create($section);
        }
    }
}
