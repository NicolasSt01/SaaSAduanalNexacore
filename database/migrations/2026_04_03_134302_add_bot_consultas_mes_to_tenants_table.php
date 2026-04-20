<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Contador de consultas del bot en el mes actual
            // Se resetea automáticamente al inicio de cada mes
            $table->integer('bot_consultas_mes')->default(0)->after('bot_consultas_limite_mes');
            $table->string('bot_consultas_mes_periodo')->nullable()->after('bot_consultas_mes'); // Formato: "2026-04"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['bot_consultas_mes', 'bot_consultas_mes_periodo']);
        });
    }
};
