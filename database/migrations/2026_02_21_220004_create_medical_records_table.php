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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Grupo: Medicamentos
            $table->boolean('takes_medication')->default(false);
            $table->text('medication_description')->nullable();

            // Grupo: Enfermedades (Aquí las agregas)
            $table->boolean('has_disease')->default(false);
            $table->text('disease_description')->nullable();

            // Grupo: Alergias
            $table->boolean('has_allergies')->default(false);
            $table->text('allergies_description')->nullable();

            // Grupo: Cirugías
            $table->boolean('had_surgery')->default(false);
            $table->text('surgery_description')->nullable();

            // Datos físicos
            $table->string('blood_type')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
