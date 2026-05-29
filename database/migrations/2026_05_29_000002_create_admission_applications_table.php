<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->foreignId('level_id')->constrained();
            $table->foreignId('grade_id')->constrained();
            // Student data
            $table->string('student_first_name', 100);
            $table->string('student_second_name', 100)->nullable();
            $table->string('student_first_surname', 100);
            $table->string('student_second_surname', 100)->nullable();
            $table->date('student_birthdate');
            $table->string('student_address', 255);
            $table->string('student_previous_school', 255)->nullable();
            $table->string('student_religion', 100)->nullable();
            // Father
            $table->string('father_first_name', 100)->nullable();
            $table->string('father_last_name', 100)->nullable();
            $table->string('father_phone', 20)->nullable();
            // Mother
            $table->string('mother_first_name', 100)->nullable();
            $table->string('mother_last_name', 100)->nullable();
            $table->string('mother_phone', 20)->nullable();
            // Guardian
            $table->string('guardian_type', 20);
            $table->string('guardian_name', 200);
            $table->string('guardian_email', 255);
            // Status: pending, reviewed, accepted, rejected
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};
