<?php

namespace Database\Seeders;

use App\Models\Pensum;
use Illuminate\Database\Seeder;

class PensumSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'grade_id' => 1, 'year' => 2026, 'units' => 4, 'unit_percentages' => [25, 25, 25, 25]],
            ['id' => 2, 'grade_id' => 2, 'year' => 2026, 'units' => 4, 'unit_percentages' => [25, 25, 25, 25]],
            ['id' => 3, 'grade_id' => 3, 'year' => 2026, 'units' => 4, 'unit_percentages' => [25, 25, 25, 25]],
            ['id' => 4, 'grade_id' => 4, 'year' => 2026, 'units' => 4, 'unit_percentages' => [25, 25, 25, 25]],
            ['id' => 5, 'grade_id' => 5, 'year' => 2026, 'units' => 4, 'unit_percentages' => [25, 25, 25, 25]],
            ['id' => 6, 'grade_id' => 6, 'year' => 2026, 'units' => 4, 'unit_percentages' => [25, 25, 25, 25]],
            ['id' => 7, 'grade_id' => 7, 'year' => 2026, 'units' => 4, 'unit_percentages' => [25, 25, 25, 25]],
            ['id' => 8, 'grade_id' => 8, 'year' => 2026, 'units' => 3, 'unit_percentages' => [33, 33, 34]],
        ];

        foreach ($data as $item) {
            Pensum::updateOrCreate(['id' => $item['id']], $item);
        }
    }
}
