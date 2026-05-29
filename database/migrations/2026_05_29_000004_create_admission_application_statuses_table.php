<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_application_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_application_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20); // pending, reviewed, accepted, rejected
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_application_statuses');
    }
};
