<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_applications', function (Blueprint $table) {
            $table->boolean('billing_unlocked')->default(false)->after('current_status');
            $table->boolean('psychometric_unlocked')->default(false)->after('billing_unlocked');
            $table->boolean('academic_unlocked')->default(false)->after('psychometric_unlocked');
        });
    }

    public function down(): void
    {
        Schema::table('admission_applications', function (Blueprint $table) {
            $table->dropColumn(['billing_unlocked', 'psychometric_unlocked', 'academic_unlocked']);
        });
    }
};
