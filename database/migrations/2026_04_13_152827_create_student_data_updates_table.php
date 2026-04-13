<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_data_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->timestamp('completed_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_data_updates');
    }
};
