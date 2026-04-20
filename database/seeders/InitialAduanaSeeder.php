<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InitialAduanaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('aduanas')->updateOrInsert(
        ['nombre' => 'REYNOSA'],
        ['clave' => '440']
        );

        \DB::table('aduanas')->updateOrInsert(
        ['nombre' => 'NUEVO LAREDO'],
        ['clave' => '240']
        );

        \DB::table('aduanas')->updateOrInsert(
        ['nombre' => 'MATAMOROS'],
        ['clave' => '120']
        );
    }
}