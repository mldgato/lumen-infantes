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
        Schema::table('academic_configurations', function (Blueprint $table) {
            // Se redefine el ENUM agregando 'none' a la lista de valores permitidos
            $table->enum('improvement_type', ['none', 'full', 'percentage', 'additive'])
                ->default('full') // Opcional: mantén el default que ya tuvieras antes
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_configurations', function (Blueprint $table) {
            // Revertir al estado original (si se hace un rollback)
            // Nota: Si haces rollback y hay registros con 'none', MySQL dará error.
            $table->enum('improvement_type', ['full', 'percentage', 'additive'])->change();
        });
    }
};
