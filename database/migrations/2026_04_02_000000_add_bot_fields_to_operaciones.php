<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('operaciones', function (Blueprint $table) {
            $table->json('bot_logs_json')->nullable()->after('observaciones');
            $table->timestamp('ultimo_scraping_at')->nullable()->after('bot_logs_json');
            $table->timestamp('modulacion_detectada_at')->nullable()->after('ultimo_scraping_at');
        });

        // Extender operacion_historial_doda para que tenga los campos del nuevo bot
        // La tabla ya tiene: doda, estatus_anterior, estatus_nuevo, hubo_cambio, respuesta_json
        // Solo necesitamos agregar execution_id para trackear la ejecución del bot
        Schema::table('operacion_historial_doda', function (Blueprint $table) {
            $table->string('execution_id', 100)->nullable()->after('respuesta_json');
            $table->string('source', 50)->default('bot')->after('execution_id'); // bot, manual, api
        });
    }

    public function down(): void
    {
        Schema::table('operaciones', function (Blueprint $table) {
            $table->dropColumn(['bot_logs_json', 'ultimo_scraping_at', 'modulacion_detectada_at']);
        });

        Schema::table('operacion_historial_doda', function (Blueprint $table) {
            $table->dropColumn(['execution_id', 'source']);
        });
    }
};
