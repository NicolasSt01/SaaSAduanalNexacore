<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
        ['email' => 'admin@nexacore.com.mx'],
        [
            'name' => 'Super Admin NexaCore',
            'password' => Hash::make('NexaCore2026!'),
            'role' => 'super_admin',
            'tenant_id' => null, // Super Admin no pertenece a un tenant específico
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]
        );
    }
}