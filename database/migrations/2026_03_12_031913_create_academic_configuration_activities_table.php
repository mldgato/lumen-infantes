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
        Schema::create('academic_configuration_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_configuration_id');
            $table->foreign('academic_configuration_id', 'aca_config_act_config_fk')
                ->references('id')
                ->on('academic_configurations')
                ->cascadeOnDelete();
            $table->foreignId('activity_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('quantity');
            $table->decimal('points_each', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_configuration_activities');
    }
};
