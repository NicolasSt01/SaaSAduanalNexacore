<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

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
    }
}
