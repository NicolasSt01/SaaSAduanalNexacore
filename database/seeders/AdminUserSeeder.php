<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin CrossPoint',
            'email' => 'sistemas@crosspoint.com.mx',
            'password' => Hash::make('admin123'),
            'role' => 'admin'
        ]);
    }
}