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
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('birthplace')->nullable();
            $table->date('birthdate');
            $table->string('nationality');
            $table->string('cui')->unique();
            $table->string('cui_extended_in');
            $table->string('profession');
            $table->text('residence_address');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
