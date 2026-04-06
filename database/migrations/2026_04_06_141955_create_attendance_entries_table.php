<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_record_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->boolean('present')->default(true);
            $table->timestamps();
            $table->unique(
                ['attendance_record_id', 'student_id'],
                'attendance_entry_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_entries');
    }
};
