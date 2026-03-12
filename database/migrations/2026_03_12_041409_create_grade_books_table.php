<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grade_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_course_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_configuration_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['open', 'locked', 'rejected', 'approved'])->default('open');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique('classroom_course_assignment_id', 'grade_books_assignment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_books');
    }
};
