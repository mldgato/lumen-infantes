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
        Schema::create('grade_change_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_change_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_book_activity_id')->constrained()->cascadeOnDelete();
            $table->decimal('old_score', 5, 2);
            $table->decimal('new_score', 5, 2);
            $table->decimal('old_improvement_score', 5, 2)->nullable();
            $table->decimal('new_improvement_score', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(
                ['grade_change_request_id', 'student_id', 'grade_book_activity_id'],
                'gcri_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_change_request_items');
    }
};
