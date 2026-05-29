<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_applications', function (Blueprint $table) {
            // Eliminar columnas de la versión anterior
            $table->dropColumn(['status', 'notes']);

            // Estado desnormalizado para consultas eficientes
            $table->string('current_status', 20)->default('pending')->after('ip_address');

            // Teléfono y NIT del encargado (cuando es distinto a padre/madre)
            $table->string('guardian_phone', 20)->nullable()->after('guardian_email');
            $table->string('guardian_nit', 20)->nullable()->after('guardian_phone');

            // Cómo nos conoció
            $table->string('referral_source', 100)->nullable()->after('guardian_phone');

            // Datos laborales del padre
            $table->string('father_workplace', 255)->nullable()->after('father_phone');
            $table->string('father_nit', 20)->nullable()->after('father_workplace');
            $table->string('father_profession', 100)->nullable()->after('father_nit');

            // Datos laborales de la madre
            $table->string('mother_workplace', 255)->nullable()->after('mother_phone');
            $table->string('mother_nit', 20)->nullable()->after('mother_workplace');
            $table->string('mother_profession', 100)->nullable()->after('mother_nit');

            // Información familiar
            $table->unsignedSmallInteger('sons_count')->nullable()->after('mother_profession');
            $table->string('sons_ages', 100)->nullable()->after('sons_count');
            $table->unsignedSmallInteger('daughters_count')->nullable()->after('sons_ages');
            $table->string('daughters_ages', 100)->nullable()->after('daughters_count');

            // URLs de documentos (servicio de nube externo)
            $table->string('url_documents', 1000)->nullable()->after('daughters_ages');
            $table->string('url_payment', 1000)->nullable()->after('url_documents');
        });
    }

    public function down(): void
    {
        Schema::table('admission_applications', function (Blueprint $table) {
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->dropColumn([
                'current_status',
                'guardian_phone',
                'guardian_nit',
                'referral_source',
                'father_workplace', 'father_nit', 'father_profession',
                'mother_workplace', 'mother_nit', 'mother_profession',
                'sons_count', 'sons_ages',
                'daughters_count', 'daughters_ages',
                'url_documents', 'url_payment',
            ]);
        });
    }
};
