<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_user_can_login_with_username_and_demo_password(): void
    {
        $this->postJson('/api/login', [
            'username' => 'Maria Cruz',
            'password' => '1234',
        ])
            ->assertOk()
            ->assertJsonPath('user.name', 'Maria Cruz')
            ->assertJsonPath('user.role.name', 'Cashier')
            ->assertJsonStructure(['token']);
    }
}
