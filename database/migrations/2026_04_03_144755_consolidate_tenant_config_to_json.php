<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Consolida todas las columnas individuales de configuración del tenant
     * en el campo JSON 'configuracion' para mayor flexibilidad.
     */
    public function up(): void
    {
        // Paso 1: Migrar datos existentes a configuracion JSON
        $tenants = DB::table('tenants')->get();

        foreach ($tenants as $tenant) {
            $config = json_decode($tenant->configuracion ?? '{}', true) ?? [];

            // Migrar configuración del bot
            if ($tenant->bot_mode ?? null) {
                $config['bot'] = [
                    'mode' => $tenant->bot_mode,
                    'consultas_limite_mes' => $tenant->bot_consultas_limite_mes,
                    'consultas_mes' => $tenant->bot_consultas_mes ?? 0,
                    'consultas_mes_periodo' => $tenant->bot_consultas_mes_periodo,
                    'config' => $tenant->bot_config ? json_decode($tenant->bot_config, true) : null,
                ];
            }

            // Migrar límites de recursos
            $limites = [];
            foreach (['clientes', 'importadores', 'bodegas', 'aduanas', 'patentes', 'pedimentos_mes', 'documentos_mes'] as $campo) {
                $valor = $tenant->{'limite_' . $campo} ?? null;
                if ($valor !== null) {
                    $limites[$campo] = $valor;
                }
            }
            if (!empty($limites)) {
                $config['limites']['recursos'] = $limites;
            }

            // Migrar límites de funcionalidades
            $funcionalidades = [];
            foreach (['reportes_mes', 'correos_dia', 'whatsapp_mes'] as $campo) {
                $valor = $tenant->{'limite_' . $campo} ?? null;
                if ($valor !== null) {
                    $funcionalidades[$campo] = $valor;
                }
            }
            if (!empty($funcionalidades)) {
                $config['limites']['funcionalidades'] = $funcionalidades;
            }

            // Migrar features habilitadas
            if ($tenant->features_enabled ?? null) {
                $config['features_enabled'] = json_decode($tenant->features_enabled, true);
            }

            // Actualizar el tenant con la nueva configuración
            DB::table('tenants')
                ->where('id', $tenant->id)
                ->update(['configuracion' => json_encode($config)]);
        }

        // Paso 2: Eliminar columnas individuales
        Schema::table('tenants', function (Blueprint $table) {
            // Columnas del bot
            $table->dropColumn([
                'bot_mode',
                'bot_consultas_limite_mes',
                'bot_consultas_mes',
                'bot_consultas_mes_periodo',
                'bot_config',
            ]);

            // Columnas de límites de recursos
            $table->dropColumn([
                'limite_clientes',
                'limite_importadores',
                'limite_bodegas',
                'limite_aduanas',
                'limite_patentes',
                'limite_pedimentos_mes',
                'limite_documentos_mes',
            ]);

            // Columnas de límites de funcionalidades
            $table->dropColumn([
                'limite_reportes_mes',
                'limite_correos_dia',
                'limite_whatsapp_mes',
            ]);

            // Columna de features
            $table->dropColumn(['features_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Paso 1: Recrear columnas individuales
        Schema::table('tenants', function (Blueprint $table) {
            // Columnas del bot
            $table->enum('bot_mode', ['manual', 'automatico', 'deshabilitado'])->default('manual')->after('configuracion');
            $table->integer('bot_consultas_limite_mes')->nullable()->after('bot_mode');
            $table->integer('bot_consultas_mes')->default(0)->after('bot_consultas_limite_mes');
            $table->string('bot_consultas_mes_periodo')->nullable()->after('bot_consultas_mes');
            $table->json('bot_config')->nullable()->after('bot_consultas_mes_periodo');

            // Columnas de límites de recursos
            $table->integer('limite_clientes')->nullable()->after('max_operaciones_mes');
            $table->integer('limite_importadores')->nullable()->after('limite_clientes');
            $table->integer('limite_bodegas')->nullable()->after('limite_importadores');
            $table->integer('limite_aduanas')->nullable()->after('limite_bodegas');
            $table->integer('limite_patentes')->nullable()->after('limite_aduanas');
            $table->integer('limite_pedimentos_mes')->nullable()->after('limite_patentes');
            $table->integer('limite_documentos_mes')->nullable()->after('limite_pedimentos_mes');

            // Columnas de límites de funcionalidades
            $table->integer('limite_reportes_mes')->nullable()->after('limite_documentos_mes');
            $table->integer('limite_correos_dia')->nullable()->after('limite_reportes_mes');
            $table->integer('limite_whatsapp_mes')->nullable()->after('limite_correos_dia');

            // Columna de features
            $table->json('features_enabled')->nullable()->after('limite_whatsapp_mes');
        });

        // Paso 2: Migrar datos de vuelta (opcional, para rollback completo)
        $tenants = DB::table('tenants')->get();

        foreach ($tenants as $tenant) {
            $config = json_decode($tenant->configuracion ?? '{}', true) ?? [];

            $updateData = [];

            // Migrar configuración del bot
            if (isset($config['bot'])) {
                $updateData['bot_mode'] = $config['bot']['mode'] ?? 'manual';
                $updateData['bot_consultas_limite_mes'] = $config['bot']['consultas_limite_mes'] ?? null;
                $updateData['bot_consultas_mes'] = $config['bot']['consultas_mes'] ?? 0;
                $updateData['bot_consultas_mes_periodo'] = $config['bot']['consultas_mes_periodo'] ?? null;
                $updateData['bot_config'] = isset($config['bot']['config']) ? json_encode($config['bot']['config']) : null;
            }

            // Migrar límites de recursos
            if (isset($config['limites']['recursos'])) {
                foreach ($config['limites']['recursos'] as $campo => $valor) {
                    $updateData['limite_' . $campo] = $valor;
                }
            }

            // Migrar límites de funcionalidades
            if (isset($config['limites']['funcionalidades'])) {
                foreach ($config['limites']['funcionalidades'] as $campo => $valor) {
                    $updateData['limite_' . $campo] = $valor;
                }
            }

            // Migrar features
            if (isset($config['features_enabled'])) {
                $updateData['features_enabled'] = json_encode($config['features_enabled']);
            }

            if (!empty($updateData)) {
                DB::table('tenants')
                    ->where('id', $tenant->id)
                    ->update($updateData);
            }
        }
    }
};
