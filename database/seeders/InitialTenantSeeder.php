<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class InitialTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear el tenant inicial
        $tenantId = DB::table('tenants')->insertGetId([
            'slug' => 'crosspoint',
            'nombre_empresa' => 'Portal Crosspoint',
            'rfc' => 'POR123456789',
            'correo_admin' => 'crosspointservices01@gmail.com',
            'plan' => 'enterprise',
            'estado' => 'activo',
            'fecha_inicio' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tables = [
            'users',
            'cliente',
            'importadores',
            'bodegas',
            'patentes',
            'expedientes',
            'exportaciones',
            'documentos',
            'notificaciones',
            'facturas',
            'conceptos_adicionales',
            'recorridos',
            'referencias',
        ];

        // 2. Asignar todos los registros existentes a este tenant
        foreach ($tables as $tableName) {
            if (DB::getSchemaBuilder()->hasTable($tableName) && DB::getSchemaBuilder()->hasColumn($tableName, 'tenant_id')) {
                DB::table($tableName)->update(['tenant_id' => $tenantId]);
            }
        }
    }
}