<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicConfiguration;
use App\Models\AcademicConfigurationActivity;
use App\Models\GradeBook;
use App\Models\GradeBookActivity;
use App\Models\GradeBookScore;
use App\Models\GradeBookTotal;

class CleanupSeedDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Limpiando datos de GradeBookSeeder...');

        // 1. Obtener los IDs de GradeBooks ligados a academic_configuration_id = 1
        $gradeBookIds = GradeBook::where('academic_configuration_id', 1)->pluck('id');

        // 2. Obtener IDs de actividades de esos GradeBooks
        $activityIds = GradeBookActivity::whereIn('grade_book_id', $gradeBookIds)->pluck('id');

        // 3. Eliminar en orden (de hijos a padres)
        GradeBookScore::whereIn('grade_book_activity_id', $activityIds)->delete();
        $this->command->info('GradeBookScore eliminados.');

        GradeBookTotal::whereIn('grade_book_id', $gradeBookIds)->delete();
        $this->command->info('GradeBookTotal eliminados.');

        GradeBookActivity::whereIn('grade_book_id', $gradeBookIds)->delete();
        $this->command->info('GradeBookActivity eliminados.');

        GradeBook::whereIn('id', $gradeBookIds)->delete();
        $this->command->info('GradeBook eliminados.');

        $this->command->info('Limpiando datos de AcademicConfigurationSeeder...');

        AcademicConfigurationActivity::where('academic_configuration_id', 1)->delete();
        $this->command->info('AcademicConfigurationActivity eliminados.');

        AcademicConfiguration::where('id', 1)->delete();
        $this->command->info('AcademicConfiguration eliminada.');

        $this->command->info('¡Limpieza completada exitosamente!');
    }
}