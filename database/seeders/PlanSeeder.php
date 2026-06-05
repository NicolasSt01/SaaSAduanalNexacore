<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\PlanCustom;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'nombre' => 'Básico',
            'precio_mensual' => 1500.00,
            'max_usuarios' => 3,
            'max_operaciones_mes' => 50,
            'max_documentos_mes' => 200,
            'features' => json_encode(['reportes_basicos', 'bot_doda', 'email_notifications']),
        ]);

        Plan::create([
            'nombre' => 'Profesional',
            'precio_mensual' => 3500.00,
            'max_usuarios' => 10,
            'max_operaciones_mes' => 200,
            'max_documentos_mes' => 1000,
            'features' => json_encode(['reportes_avanzados', 'bot_doda', 'email_notifications', 'whatsapp', 'api_access']),
        ]);

        Plan::create([
            'nombre' => 'Enterprise',
            'precio_mensual' => 7000.00,
            'max_usuarios' => 50,
            'max_operaciones_mes' => null,
            'max_documentos_mes' => null,
            'features' => json_encode(['reportes_avanzados', 'bot_doda_auto', 'email_notifications', 'whatsapp', 'api_access', 'soporte_prioritario', 'personalizacion']),
        ]);

        PlanCustom::create([
            'nombre' => 'Trial 15 Días',
            'descripcion' => 'Plan de prueba gratuito por 15 días con acceso limitado',
            'precio_base' => 0,
            'max_usuarios' => 1,
            'max_operaciones_mes' => 20,
            'max_documentos_mes' => 40,
            'max_modulaciones_mes' => 20,
            'reportes_habilitados' => ['clientes', 'operacion_semanal', 'aduanas', 'pedimentos'],
            'features_habilitadas' => ['email_notifications'],
        ]);
    }
}
