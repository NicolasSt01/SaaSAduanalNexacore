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
            // SOIA-Bot Configuration
            $table->enum('bot_mode', ['manual', 'automatico', 'deshabilitado'])->default('manual')->after('configuracion');
            $table->integer('bot_consultas_limite_mes')->nullable()->after('bot_mode'); // Límite de consultas al bot por mes
            $table->json('bot_config')->nullable()->after('bot_consultas_limite_mes'); // Configuración adicional del bot

            // Límites de recursos por tenant
            $table->integer('limite_clientes')->nullable()->after('max_operaciones_mes');
            $table->integer('limite_importadores')->nullable()->after('limite_clientes');
            $table->integer('limite_bodegas')->nullable()->after('limite_importadores');
            $table->integer('limite_aduanas')->nullable()->after('limite_bodegas');
            $table->integer('limite_patentes')->nullable()->after('limite_aduanas');
            $table->integer('limite_pedimentos_mes')->nullable()->after('limite_patentes'); // Pedimentos por mes
            $table->integer('limite_documentos_mes')->nullable()->after('limite_pedimentos_mes'); // Documentos subidos por mes

            // Límites de funcionalidades
            $table->integer('limite_reportes_mes')->nullable()->after('limite_documentos_mes'); // Reportes generados por mes
            $table->integer('limite_correos_dia')->nullable()->after('limite_reportes_mes'); // Correos enviados por día
            $table->integer('limite_whatsapp_mes')->nullable()->after('limite_correos_dia'); // WhatsApp enviados por mes

            // Flags de características habilitadas
            $table->json('features_enabled')->nullable()->after('limite_whatsapp_mes'); // Qué features están habilitadas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'bot_mode',
                'bot_consultas_limite_mes',
                'bot_config',
                'limite_clientes',
                'limite_importadores',
                'limite_bodegas',
                'limite_aduanas',
                'limite_patentes',
                'limite_pedimentos_mes',
                'limite_documentos_mes',
                'limite_reportes_mes',
                'limite_correos_dia',
                'limite_whatsapp_mes',
                'features_enabled',
            ]);
        });
    }
};
