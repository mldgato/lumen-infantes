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
        Schema::create('grade_book_totals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('normal_points', 6, 2)->default(0);
            $table->decimal('extra_points', 6, 2)->default(0);
            $table->decimal('total_points', 6, 2)->default(0);
            $table->timestamps();

            $table->unique(['grade_book_id', 'student_id'], 'grade_book_totals_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_book_totals');
    }
};
