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
        Schema::table('pensum_courses', function (Blueprint $table) {
            $table->boolean('is_official')->default(true)->after('is_main');
        });
    }

    public function down(): void
    {
        Schema::table('pensum_courses', function (Blueprint $table) {
            $table->dropColumn('is_official');
        });
    }
};
