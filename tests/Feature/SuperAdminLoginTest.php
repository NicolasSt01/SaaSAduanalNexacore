<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class SuperAdminLoginTest extends TestCase
{
    public function test_superadmin_dashboard()
    {
        $this->withoutExceptionHandling();
        $user = User::where('email', 'admin@nexacore.com.mx')->first();
        $response = $this->actingAs($user)->get('/nexacore-admin/tenants');
        $response->assertStatus(200);
    }
}
