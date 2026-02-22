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
        Schema::create('professors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('hire_date');
            $table->string('nit')->nullable();
            $table->string('teaching_cedula')->nullable();
            $table->string('igss_affiliation')->nullable();
            $table->string('title')->nullable();
            $table->string('bachelor_degree')->nullable();
            $table->string('spouse_name')->nullable();
            $table->string('spouse_phone')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professors');
    }
};
