<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_academic_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
            $table->foreignId('admission_course_id')->constrained('admission_courses')->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->unique(['admission_application_id', 'admission_course_id'], 'aas_application_course_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_academic_scores');
    }
};
