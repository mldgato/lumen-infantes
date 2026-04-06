<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_course_assignment_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->date('date');
            $table->timestamps();
            $table->unique(
                ['classroom_course_assignment_id', 'date'],
                'attendance_record_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
