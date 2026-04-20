<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    public function test_superadmin_login()
    {
        $this->withoutExceptionHandling();
        $response = $this->post('/login', [
            'email' => 'admin@nexacore.com.mx',
            'password' => 'NexaCore2026!',
        ]);

        $response->assertStatus(302);
    }
}
