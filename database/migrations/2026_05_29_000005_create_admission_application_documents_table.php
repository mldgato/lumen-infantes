<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_application_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('payment_receipt')->default(false);    // Boleta de pago
            $table->boolean('grades_certificate')->default(false); // Calificaciones año anterior
            $table->boolean('registration_form')->default(false);  // Ficha
            $table->boolean('reference_letter')->default(false);   // Referencias
            $table->boolean('photo')->default(false);              // Fotografía
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_application_documents');
    }
};
