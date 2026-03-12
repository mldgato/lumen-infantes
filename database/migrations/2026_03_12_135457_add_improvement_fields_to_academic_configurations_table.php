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
            $table->enum('improvement_type', ['full', 'percentage', 'additive'])
                ->default('full')
                ->after('mode');
            $table->decimal('improvement_percentage', 5, 2)
                ->nullable()
                ->after('improvement_type');
        });
    }

    public function down(): void
    {
        Schema::table('academic_configurations', function (Blueprint $table) {
            $table->dropColumn(['improvement_type', 'improvement_percentage']);
        });
    }
};
